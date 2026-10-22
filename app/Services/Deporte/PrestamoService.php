<?php

namespace App\Services\Deporte;

use App\Exceptions\Deporte\OperacionDeporteException;
use App\Models\Deporte\DetallePrestamoDeportivo;
use App\Models\Deporte\PrestamoDeportivo;
use App\Models\Deporte\SancionDeportiva;
use App\Models\Lote;
use App\Models\Persona;
use App\Models\TipoProducto;
use App\Support\Auditoria\Bitacora;
use App\Support\Cuentas\Cedula;
use App\Support\Inventario\ServicioInventario;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrestamoService
{
    public const REFERENCIA_PRESTAMO = 'prestamo_deportivo';

    public const REFERENCIA_SANCION = 'sancion_deportiva';

    public const MOTIVO_VENCIDO = 'Préstamo vencido sin devolver lo prestado.';

    public const MOTIVO_DANADO = 'Material devuelto dañado: debe reponerlo.';

    public const MOTIVO_PERDIDO = 'Material perdido o no devuelto: debe reponerlo.';

    public const MOTIVO_DANADO_PERDIDO = 'Material devuelto dañado y material perdido: debe reponerlo.';

    private const TOLERANCIA = 0.0001;

    public function __construct(private ServicioInventario $inventario)
    {
    }

    public static function buscarPersona(?string $cedula): ?Persona
    {
        $normalizada = Cedula::normalizar($cedula);

        if ($normalizada === null) {
            return null;
        }

        $variantes = [$normalizada];
        foreach (['V', 'E'] as $prefijo) {
            $variantes[] = $prefijo . $normalizada;
            $variantes[] = $prefijo . '-' . $normalizada;
        }

        return Persona::query()->whereIn('cedula_per', $variantes)->orderBy('id_per')->first();
    }

    public static function moduloDeporteId(): ?int
    {
        $id = DB::table('modulo')->where('key_mod', 'deporte')->value('id_mod');

        return $id === null ? null : (int) $id;
    }

    public static function tipoDeProducto(int $productoId): ?string
    {
        foreach (PrestamoDeportivo::TIPOS_PRODUCTO as $tipo => $nombreTipo) {
            if (TipoProducto::productoEsDeTipo($productoId, $nombreTipo)) {
                return $tipo;
            }
        }

        return null;
    }

    public function situacionPersona(int $personaId): array
    {
        $this->marcarVencidos($personaId);

        return [
            'pendientes' => PrestamoDeportivo::todasLasSedes()
                ->with(['detalles.producto', 'producto', 'sede'])
                ->where('per_id', $personaId)
                ->pendientes()
                ->orderByDesc('id_prd')
                ->get()
                ->unique('tipo_prd')
                ->keyBy('tipo_prd'),
            'sancion_activa' => SancionDeportiva::todasLasSedes()
                ->with('prestamo.detalles.producto')
                ->where('per_id', $personaId)
                ->activas()
                ->orderByDesc('id_snd')
                ->first(),
        ];
    }

    public function productosDisponibles(int $sedeId): Collection
    {
        $oficina = mb_strtolower(TipoProducto::MATERIAL_OFICINA);

        return TipoProducto::productosDeTipos(array_values(PrestamoDeportivo::TIPOS_PRODUCTO))
            ->where('producto.status_id', 1)
            ->orderBy('producto.nombre_pdt')
            ->get(['producto.id_pdt', 'producto.codigo_pdt', 'producto.nombre_pdt', 'tipo_producto.nombre_tpr'])
            ->map(function ($producto) use ($sedeId, $oficina) {
                $producto->tipo = mb_strtolower((string) $producto->nombre_tpr) === $oficina
                    ? PrestamoDeportivo::TIPO_OFICINA
                    : PrestamoDeportivo::TIPO_DEPORTIVO;
                $producto->disponible = $this->disponible((int) $producto->id_pdt, $sedeId);

                return $producto;
            })
            ->filter(fn ($producto) => $producto->disponible >= 1)
            ->values();
    }

    public function disponible(int $productoId, int $sedeId): float
    {
        return (float) $this->inventario->existenciasDisponibles($productoId, $sedeId)
            ->sum(fn ($existencia) => floor((float) $existencia->cantidad_convertida_isl + self::TOLERANCIA));
    }

    public function registrar(array $datos, int $sedeId): PrestamoDeportivo
    {
        $persona = static::buscarPersona($datos['cedula'] ?? null);

        if (! $persona) {
            throw new OperacionDeporteException('No existe ninguna persona registrada con esa cédula. El préstamo solo se otorga a miembros de la universidad.');
        }

        $this->marcarVencidos($persona->id_per);

        $items = $this->agrupar($datos['items'] ?? []);

        if ($items === []) {
            throw new OperacionDeporteException('Agregue al menos un producto con una cantidad mayor que cero.');
        }

        $tipo = $datos['tipo'] ?? static::tipoDeProducto((int) array_key_first($items));

        if (! array_key_exists((string) $tipo, PrestamoDeportivo::TIPOS_PRODUCTO)) {
            throw new OperacionDeporteException('Elija si presta implementos deportivos o material de oficina.');
        }

        foreach (array_keys($items) as $productoId) {
            if (static::tipoDeProducto($productoId) !== $tipo) {
                throw new OperacionDeporteException('Todos los productos de este préstamo deben ser de tipo ' . PrestamoDeportivo::etiquetaTipo($tipo) . '. Para el otro tipo registre un préstamo aparte.');
            }
        }

        $fechaPrestamo = ! empty($datos['fecha_prestamo']) ? Carbon::parse($datos['fecha_prestamo']) : now();
        $fechaEsperada = Carbon::parse($datos['fecha_devolucion_esperada']);

        if ($fechaEsperada->lte($fechaPrestamo)) {
            throw new OperacionDeporteException('La fecha "hasta" debe ser posterior a la fecha "desde".');
        }

        if (empty($datos['epd_id']) && trim((string) ($datos['lugar'] ?? '')) === '') {
            throw new OperacionDeporteException('Indique dónde se usará lo prestado: un espacio deportivo o un lugar.');
        }

        $nombres = DB::table('producto')->whereIn('id_pdt', array_keys($items))->pluck('nombre_pdt', 'id_pdt');

        $prestamo = DB::transaction(function () use ($persona, $datos, $items, $tipo, $fechaPrestamo, $fechaEsperada, $sedeId, $nombres) {
            DB::table('persona')->where('id_per', $persona->id_per)->lockForUpdate()->first();

            $this->exigirElegible($persona->id_per, $tipo);

            $plan = $this->planificar($items, $sedeId, $nombres->all());

            $prestamo = PrestamoDeportivo::create([
                'fecha_prestamo_prd'            => $fechaPrestamo,
                'fecha_devolucion_esperada_prd' => $fechaEsperada,
                'estado_prd'                    => PrestamoDeportivo::ESTADO_ACTIVO,
                'tipo_prd'                      => $tipo,
                'cantidad_prd'                  => (int) round(array_sum($items)),
                'motivo_prd'                    => trim((string) $datos['motivo']),
                'lugar_prd'                     => trim((string) ($datos['lugar'] ?? '')) ?: null,
                'observaciones_prd'             => $datos['observaciones'] ?? null,
                'pdt_id'                        => $plan[0]['pdt_id'],
                'lot_id'                        => $plan[0]['lot_id'],
                'per_id'                        => $persona->id_per,
                'epd_id'                        => $datos['epd_id'] ?? null,
                'sed_id'                        => $sedeId,
                'registrado_por_use_id'         => Auth::id(),
            ]);

            foreach ($plan as $linea) {
                $this->inventario->salidaPorConvertida($linea['lot_id'], $sedeId, $linea['cantidad'], [
                    'mod_id'              => static::moduloDeporteId(),
                    'referencia_type_moi' => self::REFERENCIA_PRESTAMO,
                    'referencia_id_moi'   => $prestamo->id_prd,
                    'observacion_moi'     => 'Salida por préstamo #' . $prestamo->id_prd,
                ]);

                $prestamo->detalles()->create([
                    'pdt_id'       => $linea['pdt_id'],
                    'lot_id'       => $linea['lot_id'],
                    'cantidad_dpd' => $linea['cantidad'],
                ]);
            }

            return $prestamo;
        });

        Bitacora::registrar(
            accion: 'prestamo_registrado',
            modulo: 'deporte',
            tablaAfectada: 'prestamo_deportivo',
            registroId: $prestamo->id_prd,
            datosModificados: [
                'per_id'    => $prestamo->per_id,
                'tipo'      => $tipo,
                'productos' => $items,
                'sed_id'    => $prestamo->sed_id,
            ],
        );

        return $prestamo->fresh(['detalles']);
    }

    public function devolverConEstado(int $prestamoId, string $estadoDevolucion, ?string $observaciones = null): PrestamoDeportivo
    {
        if (! in_array($estadoDevolucion, PrestamoDeportivo::ESTADOS_DEVOLUCION, true)) {
            throw new OperacionDeporteException('El estado de la devolución no es válido.');
        }

        $campo = match ($estadoDevolucion) {
            PrestamoDeportivo::DEVOLUCION_BUENO  => 'devuelta',
            PrestamoDeportivo::DEVOLUCION_DANADO => 'danada',
            default                              => null,
        };

        $lineas = DetallePrestamoDeportivo::where('prd_id', $prestamoId)
            ->get(['id_dpd', 'cantidad_dpd'])
            ->mapWithKeys(fn ($detalle) => [$detalle->id_dpd => $campo ? [$campo => $detalle->cantidad_dpd] : []])
            ->all();

        return $this->devolver($prestamoId, $lineas, $observaciones);
    }

    public function devolver(int $prestamoId, array $lineas, ?string $observaciones = null): PrestamoDeportivo
    {
        [$prestamo, $sancionLevantada, $sancionAplicada, $resumen] = DB::transaction(function () use ($prestamoId, $lineas, $observaciones) {
            $prestamo = PrestamoDeportivo::todasLasSedes()->lockForUpdate()->findOrFail($prestamoId);

            if (! $prestamo->puedeDevolverse()) {
                throw new OperacionDeporteException('Este préstamo ya fue cerrado y no admite otra devolución.');
            }

            $detalles = DetallePrestamoDeportivo::with('producto')->where('prd_id', $prestamo->id_prd)->lockForUpdate()->orderBy('id_dpd')->get();

            if ($detalles->isEmpty()) {
                throw new OperacionDeporteException('El préstamo no tiene productos registrados.');
            }

            $resumen = [];
            $faltantes = [];
            $totalDanada = 0.0;
            $totalPerdida = 0.0;

            foreach ($detalles as $detalle) {
                if (! array_key_exists($detalle->id_dpd, $lineas)) {
                    throw new OperacionDeporteException('Indique qué pasó con cada producto prestado.');
                }

                $nombre = $detalle->producto?->nombre_pdt ?? 'el producto';
                $dato = (array) $lineas[$detalle->id_dpd];
                $devuelta = $this->unidades($dato['devuelta'] ?? 0, $nombre);
                $danada = $this->unidades($dato['danada'] ?? 0, $nombre);
                $consumida = $this->unidades($dato['consumida'] ?? 0, $nombre);
                $prestada = (float) $detalle->cantidad_dpd;

                if ($consumida > 0 && ! $prestamo->esDeOficina()) {
                    throw new OperacionDeporteException('Los implementos deportivos no se gastan: indique si ' . $nombre . ' volvió bien, dañado o si se perdió.');
                }

                if ($devuelta + $danada + $consumida > $prestada + self::TOLERANCIA) {
                    throw new OperacionDeporteException(sprintf(
                        'En %s indicó %s unidades, pero solo se prestaron %s.',
                        $nombre,
                        DetallePrestamoDeportivo::numero($devuelta + $danada + $consumida),
                        DetallePrestamoDeportivo::numero($prestada)
                    ));
                }

                $perdida = round($prestada - $devuelta - $danada - $consumida, 2);

                $detalle->fill([
                    'cantidad_devuelta_dpd'  => $devuelta,
                    'cantidad_danada_dpd'    => $danada,
                    'cantidad_consumida_dpd' => $consumida,
                    'cantidad_perdida_dpd'   => $perdida,
                ]);
                $detalle->save();

                if ($devuelta > 0) {
                    $this->inventario->entrada((int) $detalle->lot_id, (int) $prestamo->sed_id, $this->enPresentacion($prestamo, $detalle, $devuelta), $devuelta, [
                        'mod_id'              => static::moduloDeporteId(),
                        'referencia_type_moi' => self::REFERENCIA_PRESTAMO,
                        'referencia_id_moi'   => $prestamo->id_prd,
                        'observacion_moi'     => 'Devolución del préstamo #' . $prestamo->id_prd,
                    ]);
                }

                $totalDanada += $danada;
                $totalPerdida += $perdida;
                $resumen[$detalle->id_dpd] = compact('devuelta', 'danada', 'consumida', 'perdida');

                if ($danada > 0) {
                    $faltantes[] = DetallePrestamoDeportivo::numero($danada) . ' ' . $nombre . ' dañado(s)';
                }

                if ($perdida > 0) {
                    $faltantes[] = DetallePrestamoDeportivo::numero($perdida) . ' ' . $nombre . ' perdido(s)';
                }
            }

            $anterior = $prestamo->estado_prd;
            $prestamo->fill([
                'fecha_devolucion_real_prd' => now(),
                'estado_devolucion_prd'     => $totalPerdida > 0
                    ? PrestamoDeportivo::DEVOLUCION_PERDIDO
                    : ($totalDanada > 0 ? PrestamoDeportivo::DEVOLUCION_DANADO : PrestamoDeportivo::DEVOLUCION_BUENO),
                'recibido_por_use_id'       => Auth::id(),
                'observaciones_prd'         => $this->anexarObservacion($prestamo->observaciones_prd, $observaciones),
            ]);

            $sancion = SancionDeportiva::todasLasSedes()
                ->where('prd_id', $prestamo->id_prd)
                ->activas()
                ->lockForUpdate()
                ->first();

            if ($totalDanada + $totalPerdida <= self::TOLERANCIA) {
                $prestamo->estado_prd = PrestamoDeportivo::ESTADO_DEVUELTO;
                $prestamo->save();

                if ($sancion) {
                    $sancion->fill([
                        'estado_snd'              => SancionDeportiva::ESTADO_LEVANTADA,
                        'forma_levantamiento_snd' => SancionDeportiva::FORMA_DEVOLUCION,
                        'fecha_levantamiento_snd' => now()->toDateString(),
                        'levantada_por_use_id'    => Auth::id(),
                    ])->save();
                }

                return [$prestamo, $sancion, null, $resumen];
            }

            $prestamo->estado_prd = PrestamoDeportivo::ESTADO_SANCIONADO;
            $prestamo->save();

            $motivo = match (true) {
                $totalDanada > 0 && $totalPerdida > 0 => self::MOTIVO_DANADO_PERDIDO,
                $totalDanada > 0                      => self::MOTIVO_DANADO,
                default                               => self::MOTIVO_PERDIDO,
            };
            $porReponer = 'Por reponer: ' . implode(', ', $faltantes) . '.';

            if ($sancion) {
                $sancion->fill([
                    'motivo_snd'      => $motivo,
                    'descripcion_snd' => $this->anexarObservacion($sancion->descripcion_snd, $porReponer . ' Antes: ' . $sancion->motivo_snd . ' Estado anterior del préstamo: ' . $anterior . '.'),
                ])->save();

                return [$prestamo, null, $sancion, $resumen];
            }

            $sancion = SancionDeportiva::create([
                'motivo_snd'          => $motivo,
                'descripcion_snd'     => $this->anexarObservacion($porReponer, $observaciones),
                'fecha_sancion_snd'   => now()->toDateString(),
                'estado_snd'          => SancionDeportiva::ESTADO_ACTIVA,
                'prd_id'              => $prestamo->id_prd,
                'per_id'              => $prestamo->per_id,
                'aplicada_por_use_id' => Auth::id(),
            ]);

            return [$prestamo, null, $sancion, $resumen];
        });

        Bitacora::registrar(
            accion: 'prestamo_devuelto',
            modulo: 'deporte',
            tablaAfectada: 'prestamo_deportivo',
            registroId: $prestamo->id_prd,
            datosModificados: ['estado' => $prestamo->estado_prd, 'estado_devolucion' => $prestamo->estado_devolucion_prd, 'lineas' => $resumen],
        );

        if ($sancionLevantada) {
            $this->registrarLevantamiento($sancionLevantada);
        }

        if ($sancionAplicada) {
            Bitacora::registrar(
                accion: 'sancion_aplicada',
                modulo: 'deporte',
                tablaAfectada: 'sancion_deportiva',
                registroId: $sancionAplicada->id_snd,
                datosModificados: ['prd_id' => $prestamo->id_prd, 'per_id' => $prestamo->per_id, 'motivo' => $sancionAplicada->motivo_snd],
            );
        }

        return $prestamo->fresh(['detalles']);
    }

    public function reponer(int $sancionId, array $datos = []): SancionDeportiva
    {
        $sancion = DB::transaction(function () use ($sancionId, $datos) {
            $sancion = SancionDeportiva::todasLasSedes()->lockForUpdate()->findOrFail($sancionId);

            if (! $sancion->estaActiva()) {
                throw new OperacionDeporteException('La sanción ya fue levantada.');
            }

            $prestamo = PrestamoDeportivo::todasLasSedes()->lockForUpdate()->findOrFail($sancion->prd_id);

            if (! in_array($prestamo->estado_prd, [PrestamoDeportivo::ESTADO_VENCIDO, PrestamoDeportivo::ESTADO_SANCIONADO], true)) {
                throw new OperacionDeporteException('El préstamo asociado no está pendiente de reposición.');
            }

            $sinDevolver = $prestamo->fecha_devolucion_real_prd === null;
            $detalles = DetallePrestamoDeportivo::where('prd_id', $prestamo->id_prd)->lockForUpdate()->orderBy('id_dpd')->get();
            $usarOriginal = ($datos['lote'] ?? 'nuevo') === 'original';
            $lotesNuevos = [];
            $primerLote = null;

            foreach ($detalles as $detalle) {
                if ($sinDevolver) {
                    $detalle->fill([
                        'cantidad_devuelta_dpd'  => 0,
                        'cantidad_danada_dpd'    => 0,
                        'cantidad_consumida_dpd' => 0,
                        'cantidad_perdida_dpd'   => $detalle->cantidad_dpd,
                    ])->save();
                }

                $cantidad = $detalle->porReponer();

                if ($cantidad <= self::TOLERANCIA) {
                    continue;
                }

                $loteId = $usarOriginal
                    ? (int) $detalle->lot_id
                    : ($lotesNuevos[$detalle->pdt_id] ??= $this->crearLoteReposicion((int) $detalle->pdt_id, $prestamo->id_prd, $datos['codigo_lote'] ?? null));

                $this->inventario->entrada($loteId, (int) $prestamo->sed_id, $this->enPresentacion($prestamo, $detalle, $cantidad), $cantidad, [
                    'mod_id'              => static::moduloDeporteId(),
                    'referencia_type_moi' => self::REFERENCIA_SANCION,
                    'referencia_id_moi'   => $sancion->id_snd,
                    'observacion_moi'     => 'Reposición por la sanción #' . $sancion->id_snd,
                ]);

                $primerLote ??= $loteId;
            }

            if ($primerLote === null) {
                throw new OperacionDeporteException('El préstamo no tiene nada pendiente de reponer.');
            }

            $sancion->fill([
                'estado_snd'              => SancionDeportiva::ESTADO_LEVANTADA,
                'forma_levantamiento_snd' => SancionDeportiva::FORMA_REPOSICION,
                'fecha_levantamiento_snd' => now()->toDateString(),
                'levantada_por_use_id'    => Auth::id(),
                'lot_id'                  => $primerLote,
                'descripcion_snd'         => $this->anexarObservacion($sancion->descripcion_snd, $datos['observaciones'] ?? null),
            ])->save();

            $prestamo->fill([
                'estado_prd'                => PrestamoDeportivo::ESTADO_REPUESTO,
                'estado_devolucion_prd'     => $prestamo->estado_devolucion_prd ?? PrestamoDeportivo::DEVOLUCION_PERDIDO,
                'fecha_devolucion_real_prd' => $prestamo->fecha_devolucion_real_prd ?? now(),
                'recibido_por_use_id'       => Auth::id(),
            ])->save();

            return $sancion;
        });

        $this->registrarLevantamiento($sancion);

        return $sancion->fresh();
    }

    public function marcarVencidos(?int $personaId = null): int
    {
        $ids = PrestamoDeportivo::todasLasSedes()
            ->where('estado_prd', PrestamoDeportivo::ESTADO_ACTIVO)
            ->where('fecha_devolucion_esperada_prd', '<', now())
            ->when($personaId !== null, fn ($consulta) => $consulta->where('per_id', $personaId))
            ->pluck('id_prd');

        $marcados = 0;

        foreach ($ids as $id) {
            $sancion = DB::transaction(function () use ($id) {
                $prestamo = PrestamoDeportivo::todasLasSedes()->lockForUpdate()->find($id);

                if (! $prestamo || $prestamo->estado_prd !== PrestamoDeportivo::ESTADO_ACTIVO || $prestamo->fecha_devolucion_esperada_prd->gte(now())) {
                    return null;
                }

                $prestamo->estado_prd = PrestamoDeportivo::ESTADO_VENCIDO;
                $prestamo->save();

                $existente = SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->activas()->first();

                return $existente ?? SancionDeportiva::create([
                    'motivo_snd'          => self::MOTIVO_VENCIDO,
                    'descripcion_snd'     => 'Fecha de devolución esperada: ' . $prestamo->fecha_devolucion_esperada_prd->format('d/m/Y H:i') . '.',
                    'fecha_sancion_snd'   => now()->toDateString(),
                    'estado_snd'          => SancionDeportiva::ESTADO_ACTIVA,
                    'prd_id'              => $prestamo->id_prd,
                    'per_id'              => $prestamo->per_id,
                    'aplicada_por_use_id' => null,
                ]);
            });

            if (! $sancion) {
                continue;
            }

            $marcados++;

            Bitacora::registrar(
                accion: 'prestamo_vencido',
                modulo: 'deporte',
                tablaAfectada: 'sancion_deportiva',
                registroId: $sancion->id_snd,
                datosModificados: ['prd_id' => (int) $id, 'per_id' => $sancion->per_id],
                observacion: 'Sanción aplicada automáticamente por vencimiento del préstamo #' . $id,
            );
        }

        return $marcados;
    }

    private function exigirElegible(int $personaId, string $tipo): void
    {
        $sancion = SancionDeportiva::todasLasSedes()->where('per_id', $personaId)->activas()->first();

        if ($sancion) {
            throw new OperacionDeporteException('La persona tiene una sanción activa (' . $sancion->motivo_snd . ') y no puede recibir préstamos hasta que se levante por devolución o reposición de lo prestado.');
        }

        $pendiente = PrestamoDeportivo::todasLasSedes()
            ->where('per_id', $personaId)
            ->where('tipo_prd', $tipo)
            ->pendientes()
            ->first();

        if ($pendiente) {
            throw new OperacionDeporteException(sprintf(
                'La persona ya tiene un préstamo de %s pendiente (#%d). Solo se permite uno de cada tipo a la vez (uno de implementos deportivos y uno de material de oficina): debe devolverlo antes de recibir otro.',
                mb_strtolower(PrestamoDeportivo::etiquetaTipo($tipo)),
                $pendiente->id_prd
            ));
        }
    }

    private function planificar(array $items, int $sedeId, array $nombres): array
    {
        $plan = [];

        foreach ($items as $productoId => $cantidad) {
            $existencias = $this->inventario->existenciasDisponibles($productoId, $sedeId);
            $disponible = (float) $existencias->sum(fn ($existencia) => floor((float) $existencia->cantidad_convertida_isl + self::TOLERANCIA));

            if ($disponible + self::TOLERANCIA < $cantidad) {
                throw new OperacionDeporteException(sprintf(
                    'No hay suficiente %s en esta sede: hay %s y pidió %s.',
                    $nombres[$productoId] ?? 'producto',
                    DetallePrestamoDeportivo::numero($disponible),
                    DetallePrestamoDeportivo::numero($cantidad)
                ));
            }

            $restante = $cantidad;

            foreach ($existencias as $existencia) {
                if ($restante <= self::TOLERANCIA) {
                    break;
                }

                $tomar = min(floor((float) $existencia->cantidad_convertida_isl + self::TOLERANCIA), $restante);

                if ($tomar <= 0) {
                    continue;
                }

                $plan[] = ['pdt_id' => $productoId, 'lot_id' => (int) $existencia->lot_id, 'cantidad' => (float) $tomar];
                $restante -= $tomar;
            }
        }

        return $plan;
    }

    private function agrupar(array $items): array
    {
        $agrupados = [];

        foreach ($items as $item) {
            $productoId = (int) ($item['pdt_id'] ?? 0);
            $cantidad = (float) ($item['cantidad'] ?? 0);

            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            if (abs($cantidad - round($cantidad)) > self::TOLERANCIA) {
                throw new OperacionDeporteException('Las cantidades se cuentan por unidad: use números enteros.');
            }

            $agrupados[$productoId] = ($agrupados[$productoId] ?? 0) + round($cantidad);
        }

        return $agrupados;
    }

    private function unidades(mixed $valor, string $nombre): float
    {
        $cantidad = (float) ($valor === null || $valor === '' ? 0 : $valor);

        if ($cantidad < 0 || abs($cantidad - round($cantidad)) > self::TOLERANCIA) {
            throw new OperacionDeporteException('Las cantidades de ' . $nombre . ' deben ser números enteros, cero o mayores.');
        }

        return round($cantidad);
    }

    private function enPresentacion(PrestamoDeportivo $prestamo, DetallePrestamoDeportivo $detalle, float $unidades): float
    {
        $salida = DB::table('movimiento_inventario')
            ->where('referencia_type_moi', self::REFERENCIA_PRESTAMO)
            ->where('referencia_id_moi', $prestamo->id_prd)
            ->where('tipo_movimiento_moi', ServicioInventario::SALIDA)
            ->where('lot_id', $detalle->lot_id)
            ->orderBy('id_moi')
            ->first(['cantidad_moi', 'cantidad_convertida_moi']);

        if (! $salida || (float) $salida->cantidad_convertida_moi <= 0 || (float) $salida->cantidad_moi <= 0) {
            return $unidades;
        }

        return $unidades * (float) $salida->cantidad_moi / (float) $salida->cantidad_convertida_moi;
    }

    private function crearLoteReposicion(int $productoId, int $prestamoId, ?string $codigo): int
    {
        $base = trim((string) $codigo) ?: 'REP-' . now()->format('Ymd') . '-' . $prestamoId;
        $codigo = $base;
        $contador = 1;

        while (Lote::withTrashed()->where('codigo_lote_lot', $codigo)->exists()) {
            $codigo = $base . '-' . $contador++;
        }

        return (int) Lote::create([
            'codigo_lote_lot'      => $codigo,
            'fecha_entrada_lot'    => now()->toDateString(),
            'cantidad_inicial_lot' => 0,
            'cantidad_actual_lot'  => 0,
            'pdt_id'               => $productoId,
            'status_id'            => Lote::STATUS_ACTIVO,
        ])->id_lot;
    }

    private function registrarLevantamiento(SancionDeportiva $sancion): void
    {
        Bitacora::registrar(
            accion: 'sancion_levantada',
            modulo: 'deporte',
            tablaAfectada: 'sancion_deportiva',
            registroId: $sancion->id_snd,
            datosModificados: [
                'forma'  => $sancion->forma_levantamiento_snd,
                'prd_id' => $sancion->prd_id,
                'lot_id' => $sancion->lot_id,
            ],
        );
    }

    private function anexarObservacion(?string $actual, ?string $nueva): ?string
    {
        $nueva = trim((string) $nueva);

        if ($nueva === '') {
            return $actual;
        }

        return trim(($actual ? $actual . "\n" : '') . $nueva);
    }
}
