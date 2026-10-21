<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Receta;
use App\Models\DetalleRegistroDiario;
use App\Models\ConsumoDiario;
use App\Models\ConsumoDiarioDetalle;
use App\Models\InventarioSedeLote;
use Illuminate\Support\Facades\DB;
use Exception;

class RegistroComida extends Component
{
    public $desayunos_agregados = [];
    public $showNotification = false;
    public $notification = ['type' => 'success', 'message' => ''];
    public $desayuno_registrado = false;
    public $horarioPermitido = false;
    public $alertInventario = null;
    public $alertLimite = null;
    public $inventarioError = null;
    public $inventarioAdvertencias = [];

    public $tipoComidaActual = null;
    public $tipoComidaLabel = '';
    public $ventanaActual = null;
    public $mensajeHorario = null;
    public $proximoHorario = null;

    protected function getVentanas(): array
    {
        return [
            'desayuno' => ['inicio' => '09:00', 'fin' => '10:00', 'label' => 'Desayuno'],
            'almuerzo' => ['inicio' => '12:00', 'fin' => '14:00', 'label' => 'Almuerzo'],
            'cena'     => ['inicio' => '18:00', 'fin' => '20:00', 'label' => 'Cena'],
        ];
    }

    protected function determinarTipoComidaActual(): ?string
    {
        $hora = now()->format('H:i');
        foreach ($this->getVentanas() as $tipo => $ventana) {
            if ($hora >= $ventana['inicio'] && $hora <= $ventana['fin']) {
                return $tipo;
            }
        }
        return null;
    }

    protected function calcularProximoHorario(): ?array
    {
        $horaActual = now()->format('H:i');
        $ventanas = $this->getVentanas();

        foreach ($ventanas as $tipo => $ventana) {
            if ($horaActual < $ventana['inicio']) {
                return [
                    'tipo'  => $tipo,
                    'label' => $ventana['label'],
                    'hora'  => $ventana['inicio'],
                ];
            }
        }

        $primerTipo = array_key_first($ventanas);
        return [
            'tipo'  => $primerTipo,
            'label' => $ventanas[$primerTipo]['label'],
            'hora'  => $ventanas[$primerTipo]['inicio'],
            'manana' => true,
        ];
    }

    public function updated($property)
    {
        if (str_starts_with($property, 'desayunos_agregados')) {
            $this->validateOnly($property, $this->rulesRealtime());
        }

        if (str_contains($property, 'receta_id')) {
            $ids = array_filter(array_column($this->desayunos_agregados, 'receta_id'));

            if (count($ids) !== count(array_unique($ids))) {
                $this->addError('duplicado', 'No puede seleccionar la misma receta más de una vez.');
            } else {
                $this->resetErrorBag('duplicado');
            }
        }
    }

    protected function rulesRealtime(): array
    {
        $rules = [];
        foreach ($this->desayunos_agregados as $i => $item) {
            $rules["desayunos_agregados.$i.receta_id"] = 'required|exists:recetas,id';
            $rules["desayunos_agregados.$i.cantidad"]  = 'required|numeric|min:1';
        }
        return $rules;
    }

    public function mount()
    {
        $this->checkEstadoActual();
        if (!$this->desayuno_registrado && empty($this->desayunos_agregados)) {
            $this->addDesayuno();
        }
    }

    public function checkEstadoActual()
    {
        $hoy  = now()->toDateString();
        $tipo = $this->determinarTipoComidaActual();
        $this->tipoComidaActual = $tipo;

        $ventanas = $this->getVentanas();

        if ($tipo) {
            $this->tipoComidaLabel  = $ventanas[$tipo]['label'];
            $this->ventanaActual    = $ventanas[$tipo]['inicio'] . ' - ' . $ventanas[$tipo]['fin'];
            $this->mensajeHorario   = null;
            $this->proximoHorario   = null;
            $this->horarioPermitido = true;
        } else {
            $this->tipoComidaLabel  = '';
            $this->ventanaActual    = null;
            $this->mensajeHorario   = 'Actualmente no hay ninguna comida activa.';
            $this->proximoHorario   = $this->calcularProximoHorario();
            $this->horarioPermitido = false;
        }

        $this->desayuno_registrado = false;
        $this->desayunos_agregados = [];

        if ($tipo) {
            $registroActual = DetalleRegistroDiario::whereDate('created_at', $hoy)
                ->where('tipo_comida', $tipo)
                ->exists();

            $this->desayuno_registrado = $registroActual;

            if ($registroActual) {
                $detalles = DetalleRegistroDiario::whereDate('created_at', $hoy)
                    ->where('tipo_comida', $tipo)
                    ->get(['receta_id', 'cantidad_servido']);

                $this->desayunos_agregados = $detalles->map(function ($item) {
                    return ['receta_id' => $item->receta_id, 'cantidad' => $item->cantidad_servido];
                })->toArray();
            }
        }
    }

    public function addDesayuno()
    {
        if ($this->desayuno_registrado) return;

        $this->desayunos_agregados[] = [
            'receta_id' => null,
            'cantidad'  => null,
        ];

        $this->resetErrorBag();
        $this->inventarioError = null;
        $this->inventarioAdvertencias = [];
    }

    public function removeDesayuno($index)
    {
        if ($this->desayuno_registrado) return;

        unset($this->desayunos_agregados[$index]);
        $this->desayunos_agregados = array_values($this->desayunos_agregados);

        $this->resetErrorBag();
        $this->inventarioError = null;
        $this->inventarioAdvertencias = [];
    }

    protected function resolverCantidadPorUnidad($ingrediente): float
    {
        $candidatos = [
            'cantidad_gramos',
            'cantidad_convertida',
            'cantidad_convertida_base',
            'cantidad',
        ];

        foreach ($candidatos as $campo) {
            if (isset($ingrediente->{$campo})) {
                $valor = (float) $ingrediente->{$campo};
                if ($valor > 0) {
                    return $valor;
                }
            }
        }

        if (isset($ingrediente->cantidad_porcion)) {
            $cantidadPorcion = (float) $ingrediente->cantidad_porcion;
            $factorAGramo = (float) ($ingrediente->unidad?->factor_conversion_unm ?? 0);

            if ($cantidadPorcion > 0 && $factorAGramo > 0) {
                return $cantidadPorcion * $factorAGramo;
            }
        }

        return 0.0;
    }

    protected function verificarInventario(int $recetaId, int $cantidadServido, int $sedeId): array
    {
        $receta = Receta::with('recetaIngredientes.producto')->find($recetaId);

        if (!$receta) {
            throw new Exception("La receta seleccionada no existe.");
        }

        if ($receta->recetaIngredientes->isEmpty()) {
            throw new Exception(
                "La receta «{$receta->nombre}» no tiene ingredientes registrados. " .
                    "Agrega sus ingredientes antes de poder registrarla."
            );
        }

        $faltantes    = [];
        $advertencias = [];

        foreach ($receta->recetaIngredientes as $ingrediente) {

            $producto = $ingrediente->producto;

            if (!$producto) {
                $advertencias[] = "Un ingrediente de «{$receta->nombre}» no tiene producto asociado y fue omitido.";
                continue;
            }

            $cantidadPorUnidad = $this->resolverCantidadPorUnidad($ingrediente);

            if ($cantidadPorUnidad <= 0) {
                $advertencias[] = "El ingrediente «{$producto->nombre_pdt}» no tiene cantidad configurada en la receta y no se descontará del inventario. Edita la receta para corregirlo.";
                continue;
            }

            $totalNecesario = $cantidadPorUnidad * $cantidadServido;

            $disponibleTotal = (float) InventarioSedeLote::where('sed_id', $sedeId)
                ->whereHas('lote', function ($q) use ($ingrediente) {
                    $q->where('pdt_id', $ingrediente->pdt_id)
                        ->where(function ($vencimiento) {
                            $vencimiento->whereNull('fecha_vencimiento_lot')
                                ->orWhereDate('fecha_vencimiento_lot', '>=', now()->toDateString());
                        })
                        ->where('status_id', 1);
                })
                ->where('cantidad_convertida_isl', '>', 0)
                ->sum('cantidad_convertida_isl');

            if ($disponibleTotal < $totalNecesario) {
                $faltantes[] = [
                    'producto'   => $producto->nombre_pdt,
                    'necesario'  => round($totalNecesario, 2),
                    'disponible' => round($disponibleTotal, 2),
                    'faltante'   => round($totalNecesario - $disponibleTotal, 2),
                ];
            }
        }

        if (!empty($faltantes)) {
            $lineas = [];
            foreach ($faltantes as $f) {
                $lineas[] = "• {$f['producto']}: necesita " . number_format($f['necesario'], 2)
                    . " g · disponible " . number_format($f['disponible'], 2)
                    . " g · faltan " . number_format($f['faltante'], 2) . " g";
            }

            throw new Exception(
                "Inventario insuficiente para la receta «{$receta->nombre}»:"
                    . PHP_EOL . PHP_EOL
                    . implode(PHP_EOL, $lineas)
            );
        }

        return $advertencias;
    }

    public function saveDesayuno()
    {
        $this->inventarioError = null;
        $this->inventarioAdvertencias = [];
        $this->resetErrorBag();

        $tipo = $this->determinarTipoComidaActual();

        if (!$tipo) {
            $this->addError('horario', $this->mensajeHorario ?? 'Actualmente no hay ninguna comida activa.');
            return;
        }

        $this->tipoComidaActual = $tipo;
        $this->tipoComidaLabel  = $this->getVentanas()[$tipo]['label'];

        $yaRegistrado = DetalleRegistroDiario::whereDate('created_at', now()->toDateString())
            ->where('tipo_comida', $tipo)
            ->exists();

        if ($yaRegistrado) {
            $this->addError('existe', "El registro de {$this->tipoComidaLabel} de hoy ya fue guardado.");
            return;
        }

        if (empty(array_filter($this->desayunos_agregados, fn($d) => $d['receta_id'] !== null))) {
            $this->addError('general', 'Debe seleccionar al menos una comida con su cantidad.');
            return;
        }

        $rules = [];
        $messages = [];

        foreach ($this->desayunos_agregados as $i => $registro) {
            $rules['desayunos_agregados.' . $i . '.receta_id'] = 'required|numeric|exists:recetas,id';
            $rules['desayunos_agregados.' . $i . '.cantidad']  = 'required|numeric|min:1';

            $messages['desayunos_agregados.' . $i . '.receta_id.required'] = "Seleccione una opción para el registro #" . ($i + 1);
            $messages['desayunos_agregados.' . $i . '.cantidad.required']  = "Ingrese la cantidad para el registro #" . ($i + 1);
            $messages['desayunos_agregados.' . $i . '.cantidad.min']       = "La cantidad debe ser 1 o superior para el registro #" . ($i + 1);
        }

        $recetaIds = array_filter(array_column($this->desayunos_agregados, 'receta_id'));

        if (!empty($recetaIds) && count($recetaIds) !== count(array_unique($recetaIds))) {
            $this->addError('duplicado', 'No puede seleccionar la misma receta más de una vez. Por favor, elimine el registro duplicado.');
            return;
        }

        $this->validate($rules, $messages);

        $sedeId = \App\Support\Sedes\SedeActiva::idParaCrear();
        $advertencias = [];

        try {
            foreach ($this->desayunos_agregados as $registro) {
                $adv = $this->verificarInventario(
                    (int) $registro['receta_id'],
                    (int) $registro['cantidad'],
                    $sedeId
                );
                $advertencias = array_merge($advertencias, $adv);
            }
        } catch (Exception $e) {
            $this->inventarioError = \App\Support\Seguridad\MensajeError::de($e);
            $this->alertInventario = \App\Support\Seguridad\MensajeError::de($e);
            $this->dispatch('notify-inventario', message: \App\Support\Seguridad\MensajeError::de($e));
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($this->desayunos_agregados as $registro) {

                $recetaId = $registro['receta_id'];
                $cantidadServido = $registro['cantidad'];

                DetalleRegistroDiario::create([
                    'receta_id'        => $recetaId,
                    'tipo_comida'      => $tipo,
                    'cantidad_servido' => $cantidadServido,
                    'fecha'            => now(),
                ]);

                $receta = Receta::with('recetaIngredientes.producto')->find($recetaId);

                $consumo = ConsumoDiario::create([
                    'receta_id'            => $recetaId,
                    'sed_id'              => $sedeId,
                    'fecha_consumo_diario' => now()->toDateString(),
                    'porciones_servidas'   => $cantidadServido,
                ]);

                foreach ($receta->recetaIngredientes as $ingrediente) {

                    $cantidadPorUnidad = $this->resolverCantidadPorUnidad($ingrediente);

                    if ($cantidadPorUnidad <= 0) {
                        continue;
                    }

                    $pendiente = $cantidadPorUnidad * $cantidadServido;
                    $totalIngrediente = $pendiente;

                    $servicio = app(\App\Support\Inventario\ServicioInventario::class);

                    foreach ($servicio->existenciasDisponibles((int) $ingrediente->pdt_id, (int) $sedeId) as $inv) {

                        if ($pendiente <= 0) break;

                        $tomarGramos = min($pendiente, (float) $inv->cantidad_convertida_isl);

                        $servicio->salidaPorConvertida((int) $inv->lot_id, (int) $sedeId, $tomarGramos, [
                            'unm_id'              => $ingrediente->unm_id,
                            'referencia_type_moi' => 'consumo_diario',
                            'referencia_id_moi'   => $consumo->id,
                            'observacion_moi'     => "Consumo por receta {$receta->nombre} ({$cantidadServido} raciones) · " . ucfirst($tipo),
                        ]);

                        $pendiente -= $tomarGramos;
                    }

                    if ($pendiente > 0) {
                        throw new Exception("No hay suficiente inventario para el ingrediente: {$ingrediente->producto->nombre_pdt}. Faltan " . round($pendiente, 2) . " gramos.");
                    }

                    ConsumoDiarioDetalle::create([
                        'consumo_diario_id' => $consumo->id,
                        'pdt_id'            => $ingrediente->pdt_id,
                        'cantidad_total'    => round($totalIngrediente, 2),
                        'unm_id'            => $ingrediente->unm_id ?? $ingrediente->producto->unm_id,
                    ]);
                }
            }

            DB::commit();

            $this->desayuno_registrado = true;
            $this->inventarioError = null;
            $this->inventarioAdvertencias = $advertencias;

            $mensajeExito = "El registro de {$this->tipoComidaLabel} fue guardado correctamente.";
            if (!empty($advertencias)) {
                $mensajeExito .= ' Algunos ingredientes no se descontaron (ver avisos).';
            }

            $this->dispatch('swal', [
                'title' => '¡Éxito!',
                'text'  => $mensajeExito,
                'icon'  => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            $this->alertInventario   = \App\Support\Seguridad\MensajeError::de($e);
            $this->inventarioError   = \App\Support\Seguridad\MensajeError::de($e);
            $this->desayuno_registrado = false;
            $this->showNotification  = true;
            $this->dispatch('notify-inventario', message: \App\Support\Seguridad\MensajeError::de($e));
            return;
        }
    }

    protected function validationAttributes(): array
    {
        $attributes = [];
        foreach ($this->desayunos_agregados as $i => $registro) {
            $attributes["desayunos_agregados.$i.receta_id"] = 'registro #' . ($i + 1);
            $attributes["desayunos_agregados.$i.cantidad"]  = 'cantidad del registro #' . ($i + 1);
        }
        return $attributes;
    }

    public function showNotification()
    {
        $this->showNotification = true;
    }

    public function render()
    {
        $buscar = request()->input('buscar');

        $query = DetalleRegistroDiario::with('receta')->latest();

        if ($buscar) {
            $query->whereHas('receta', function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            });
        }

        $data = $query->paginate(10);
        $comidas = Receta::orderBy('id', 'desc')->where('estado', true)->get();

        return view('livewire.registro-comida', [
            'data'                => $data,
            'buscar'              => $buscar,
            'comidas'             => $comidas,
            'desayuno_registrado' => $this->desayuno_registrado,
        ]);
    }
}