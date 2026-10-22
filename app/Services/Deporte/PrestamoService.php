<?php

namespace App\Services\Deporte;

use App\Exceptions\Deporte\OperacionDeporteException;
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

    public const MOTIVO_VENCIDO = 'Préstamo vencido sin devolver el implemento.';

    public const MOTIVO_DANADO = 'Implemento devuelto dañado: debe reponerlo.';

    public const MOTIVO_PERDIDO = 'Implemento perdido: debe reponerlo.';

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

    public function situacionPersona(int $personaId): array
    {
        $this->marcarVencidos($personaId);

        return [
            'prestamo_pendiente' => PrestamoDeportivo::todasLasSedes()
                ->with(['producto', 'sede'])
                ->where('per_id', $personaId)
                ->pendientes()
                ->orderByDesc('id_prd')
                ->first(),
            'sancion_activa' => SancionDeportiva::todasLasSedes()
                ->with('prestamo.producto')
                ->where('per_id', $personaId)
                ->activas()
                ->orderByDesc('id_snd')
                ->first(),
        ];
    }

    public function implementosDisponibles(int $sedeId): Collection
    {
        $productos = TipoProducto::productosDeTipos([TipoProducto::IMPLEMENTO_DEPORTIVO])
            ->where('producto.status_id', 1)
            ->orderBy('producto.nombre_pdt')
            ->get(['producto.id_pdt', 'producto.codigo_pdt', 'producto.nombre_pdt']);

        return $productos->map(function ($producto) use ($sedeId) {
            $producto->lotes = $this->inventario->existenciasDisponibles((int) $producto->id_pdt, $sedeId)
                ->filter(fn ($existencia) => (float) $existencia->cantidad_isl >= 1)
                ->values();

            return $producto;
        })->filter(fn ($producto) => $producto->lotes->isNotEmpty())->values();
    }

    public function registrar(array $datos, int $sedeId): PrestamoDeportivo
    {
        $persona = static::buscarPersona($datos['cedula'] ?? null);

        if (! $persona) {
            throw new OperacionDeporteException('No existe ninguna persona registrada con esa cédula. El préstamo solo se otorga a miembros de la universidad.');
        }

        $this->marcarVencidos($persona->id_per);

        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));
        $loteId = (int) $datos['lot_id'];
        $productoId = (int) ($datos['pdt_id'] ?? 0) ?: (int) Lote::where('id_lot', $loteId)->value('pdt_id');
        $fechaPrestamo = isset($datos['fecha_prestamo']) ? Carbon::parse($datos['fecha_prestamo']) : now();
        $fechaEsperada = Carbon::parse($datos['fecha_devolucion_esperada']);

        if ($fechaEsperada->lte($fechaPrestamo)) {
            throw new OperacionDeporteException('La fecha de devolución esperada debe ser posterior a la fecha de entrega.');
        }

        if (! TipoProducto::productoEsDeTipo($productoId, TipoProducto::IMPLEMENTO_DEPORTIVO)) {
            throw new OperacionDeporteException('Solo se pueden prestar productos de tipo Implemento deportivo.');
        }

        if (! Lote::where('id_lot', $loteId)->where('pdt_id', $productoId)->exists()) {
            throw new OperacionDeporteException('El lote seleccionado no corresponde al implemento.');
        }

        if (empty($datos['epd_id']) && trim((string) ($datos['lugar'] ?? '')) === '') {
            throw new OperacionDeporteException('Indique el espacio deportivo o el lugar donde se usará el implemento.');
        }

        $prestamo = DB::transaction(function () use ($persona, $datos, $cantidad, $productoId, $loteId, $fechaPrestamo, $fechaEsperada, $sedeId) {
            DB::table('persona')->where('id_per', $persona->id_per)->lockForUpdate()->first();

            $this->exigirElegible($persona->id_per);

            $prestamo = PrestamoDeportivo::create([
                'fecha_prestamo_prd'            => $fechaPrestamo,
                'fecha_devolucion_esperada_prd' => $fechaEsperada,
                'estado_prd'                    => PrestamoDeportivo::ESTADO_ACTIVO,
                'cantidad_prd'                  => $cantidad,
                'motivo_prd'                    => trim((string) $datos['motivo']),
                'lugar_prd'                     => trim((string) ($datos['lugar'] ?? '')) ?: null,
                'observaciones_prd'             => $datos['observaciones'] ?? null,
                'pdt_id'                        => $productoId,
                'lot_id'                        => $loteId,
                'per_id'                        => $persona->id_per,
                'epd_id'                        => $datos['epd_id'] ?? null,
                'sed_id'                        => $sedeId,
                'registrado_por_use_id'         => Auth::id(),
            ]);

            $this->inventario->salida($loteId, $sedeId, $cantidad, null, [
                'mod_id'              => static::moduloDeporteId(),
                'referencia_type_moi' => self::REFERENCIA_PRESTAMO,
                'referencia_id_moi'   => $prestamo->id_prd,
                'observacion_moi'     => 'Salida por préstamo deportivo #' . $prestamo->id_prd,
            ]);

            return $prestamo;
        });

        Bitacora::registrar(
            accion: 'prestamo_registrado',
            modulo: 'deporte',
            tablaAfectada: 'prestamo_deportivo',
            registroId: $prestamo->id_prd,
            datosModificados: [
                'per_id'   => $prestamo->per_id,
                'pdt_id'   => $prestamo->pdt_id,
                'lot_id'   => $prestamo->lot_id,
                'cantidad' => $prestamo->cantidad_prd,
                'sed_id'   => $prestamo->sed_id,
            ],
        );

        return $prestamo;
    }

    public function devolver(int $prestamoId, string $estadoDevolucion, ?string $observaciones = null): PrestamoDeportivo
    {
        if (! in_array($estadoDevolucion, PrestamoDeportivo::ESTADOS_DEVOLUCION, true)) {
            throw new OperacionDeporteException('El estado de la devolución no es válido.');
        }

        [$prestamo, $sancionLevantada, $sancionAplicada] = DB::transaction(function () use ($prestamoId, $estadoDevolucion, $observaciones) {
            $prestamo = PrestamoDeportivo::todasLasSedes()->lockForUpdate()->findOrFail($prestamoId);

            if (! $prestamo->puedeDevolverse()) {
                throw new OperacionDeporteException('Este préstamo ya fue cerrado y no admite otra devolución.');
            }

            $anterior = $prestamo->estado_prd;
            $prestamo->fill([
                'fecha_devolucion_real_prd' => now(),
                'estado_devolucion_prd'     => $estadoDevolucion,
                'recibido_por_use_id'       => Auth::id(),
                'observaciones_prd'         => $this->anexarObservacion($prestamo->observaciones_prd, $observaciones),
            ]);

            $sancion = SancionDeportiva::todasLasSedes()
                ->where('prd_id', $prestamo->id_prd)
                ->activas()
                ->lockForUpdate()
                ->first();

            if ($estadoDevolucion === PrestamoDeportivo::DEVOLUCION_BUENO) {
                $prestamo->estado_prd = PrestamoDeportivo::ESTADO_DEVUELTO;
                $prestamo->save();

                $this->inventario->entrada((int) $prestamo->lot_id, (int) $prestamo->sed_id, (float) $prestamo->cantidad_prd, $this->convertidaDeSalida($prestamo), [
                    'mod_id'              => static::moduloDeporteId(),
                    'referencia_type_moi' => self::REFERENCIA_PRESTAMO,
                    'referencia_id_moi'   => $prestamo->id_prd,
                    'observacion_moi'     => 'Devolución del préstamo deportivo #' . $prestamo->id_prd,
                ]);

                if ($sancion) {
                    $sancion->fill([
                        'estado_snd'              => SancionDeportiva::ESTADO_LEVANTADA,
                        'forma_levantamiento_snd' => SancionDeportiva::FORMA_DEVOLUCION,
                        'fecha_levantamiento_snd' => now()->toDateString(),
                        'levantada_por_use_id'    => Auth::id(),
                    ])->save();
                }

                return [$prestamo, $sancion, null];
            }

            $prestamo->estado_prd = PrestamoDeportivo::ESTADO_SANCIONADO;
            $prestamo->save();

            $motivo = $estadoDevolucion === PrestamoDeportivo::DEVOLUCION_DANADO ? self::MOTIVO_DANADO : self::MOTIVO_PERDIDO;

            if ($sancion) {
                $sancion->fill([
                    'motivo_snd'      => $motivo,
                    'descripcion_snd' => $this->anexarObservacion($sancion->descripcion_snd, 'Antes: ' . $sancion->motivo_snd . ' Estado anterior del préstamo: ' . $anterior . '.'),
                ])->save();

                return [$prestamo, null, $sancion];
            }

            $sancion = SancionDeportiva::create([
                'motivo_snd'          => $motivo,
                'descripcion_snd'     => $observaciones,
                'fecha_sancion_snd'   => now()->toDateString(),
                'estado_snd'          => SancionDeportiva::ESTADO_ACTIVA,
                'prd_id'              => $prestamo->id_prd,
                'per_id'              => $prestamo->per_id,
                'aplicada_por_use_id' => Auth::id(),
            ]);

            return [$prestamo, null, $sancion];
        });

        Bitacora::registrar(
            accion: 'prestamo_devuelto',
            modulo: 'deporte',
            tablaAfectada: 'prestamo_deportivo',
            registroId: $prestamo->id_prd,
            datosModificados: ['estado' => $prestamo->estado_prd, 'estado_devolucion' => $estadoDevolucion],
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

        return $prestamo->fresh();
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

            $loteId = $this->loteParaReposicion($prestamo, $datos);
            $cantidad = (float) $prestamo->cantidad_prd;

            $this->inventario->entrada($loteId, (int) $prestamo->sed_id, $cantidad, null, [
                'mod_id'              => static::moduloDeporteId(),
                'referencia_type_moi' => self::REFERENCIA_SANCION,
                'referencia_id_moi'   => $sancion->id_snd,
                'observacion_moi'     => 'Reposición del implemento por la sanción deportiva #' . $sancion->id_snd,
            ]);

            $sancion->fill([
                'estado_snd'              => SancionDeportiva::ESTADO_LEVANTADA,
                'forma_levantamiento_snd' => SancionDeportiva::FORMA_REPOSICION,
                'fecha_levantamiento_snd' => now()->toDateString(),
                'levantada_por_use_id'    => Auth::id(),
                'lot_id'                  => $loteId,
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

    private function exigirElegible(int $personaId): void
    {
        $sancion = SancionDeportiva::todasLasSedes()->where('per_id', $personaId)->activas()->first();

        if ($sancion) {
            throw new OperacionDeporteException('La persona tiene una sanción activa (' . $sancion->motivo_snd . ') y no puede recibir préstamos hasta que se levante por devolución o reposición del implemento.');
        }

        $pendiente = PrestamoDeportivo::todasLasSedes()->where('per_id', $personaId)->pendientes()->first();

        if ($pendiente) {
            throw new OperacionDeporteException('La persona ya tiene un préstamo pendiente (#' . $pendiente->id_prd . '). Solo se permite un préstamo a la vez: debe devolverlo antes de recibir otro.');
        }
    }

    private function loteParaReposicion(PrestamoDeportivo $prestamo, array $datos): int
    {
        if (($datos['lote'] ?? 'nuevo') === 'original') {
            return (int) $prestamo->lot_id;
        }

        $codigo = trim((string) ($datos['codigo_lote'] ?? '')) ?: 'REP-' . now()->format('Ymd') . '-' . $prestamo->id_prd;
        $base = $codigo;
        $contador = 1;

        while (Lote::withTrashed()->where('codigo_lote_lot', $codigo)->exists()) {
            $codigo = $base . '-' . $contador++;
        }

        return (int) Lote::create([
            'codigo_lote_lot'      => $codigo,
            'fecha_entrada_lot'    => now()->toDateString(),
            'cantidad_inicial_lot' => 0,
            'cantidad_actual_lot'  => 0,
            'pdt_id'               => $prestamo->pdt_id,
            'status_id'            => Lote::STATUS_ACTIVO,
        ])->id_lot;
    }

    private function convertidaDeSalida(PrestamoDeportivo $prestamo): ?float
    {
        $convertida = DB::table('movimiento_inventario')
            ->where('referencia_type_moi', self::REFERENCIA_PRESTAMO)
            ->where('referencia_id_moi', $prestamo->id_prd)
            ->where('tipo_movimiento_moi', ServicioInventario::SALIDA)
            ->orderBy('id_moi')
            ->value('cantidad_convertida_moi');

        return $convertida === null ? null : (float) $convertida;
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
