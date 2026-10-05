<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Receta;
use App\Models\DetalleRegistroDiario;
use App\Models\InventarioSedeLote;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Exception;

class RegistroComida extends Component
{
    public $desayunos_agregados = [];
    public $showNotification = false;
    public $notification = ['type' => 'success', 'message' => ''];
    public $desayuno_registrado = false;
    public $horarioPermitido;
    public $alertInventario = null;
    public $alertLimite = null;
    public $inventarioError = null;
    public $inventarioAdvertencias = [];

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
        $this->checkDesayunoStatus();
        if (empty($this->desayunos_agregados)) {
            $this->addDesayuno();
        }
    }

    public function checkDesayunoStatus()
    {
        $hoy = now()->toDateString();
        $registroHoy = DetalleRegistroDiario::whereDate('created_at', $hoy)->exists();

        $hora = now()->format('H:i');
        $this->horarioPermitido = $hora >= '00:00' && $hora <= '23:59';
        $this->desayuno_registrado = $registroHoy;

        if ($this->desayuno_registrado) {
            $detalles = DetalleRegistroDiario::whereDate('created_at', $hoy)->get(['receta_id', 'cantidad_servido']);
            $this->desayunos_agregados = $detalles->map(function ($item) {
                return ['receta_id' => $item->receta_id, 'cantidad' => $item->cantidad_servido];
            })->toArray();
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

    /**
     * Resuelve la cantidad por unidad (en gramos base) de un ingrediente.
     * Intenta varios campos en orden, devuelve 0 si no hay ninguno válido.
     */
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
            $factorAGramo = (float) ($ingrediente->unidad?->factor_a_gramo ?? 0);

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
                $advertencias[] = "El ingrediente «{$producto->nombre}» no tiene cantidad configurada en la receta y no se descontará del inventario. Edita la receta para corregirlo.";
                continue;
            }

            $totalNecesario = $cantidadPorUnidad * $cantidadServido;

            $disponibleTotal = (float) InventarioSedeLote::where('sede_id', $sedeId)
                ->whereHas('lote', function ($q) use ($ingrediente) {
                    $q->where('producto_id', $ingrediente->producto_id)
                        ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
                        ->where('estado', 1);
                })
                ->where('cantidad_convertida', '>', 0)
                ->sum('cantidad_convertida');

            if ($disponibleTotal < $totalNecesario) {
                $faltantes[] = [
                    'producto'   => $producto->nombre,
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

        $hora = now()->format('H:i');
        if (!($hora >= '00:00' && $hora <= '23:59')) {
            $this->addError('hora', 'El registro no está permitido en este horario.');
            return;
        }

        if (DetalleRegistroDiario::whereDate('created_at', now()->toDateString())->exists()) {
            $this->addError('existe', 'El registro de comidas de hoy ya fue guardado.');
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

        $sedeId = 1;
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
            $this->inventarioError = $e->getMessage();
            $this->alertInventario = $e->getMessage();
            $this->dispatch('notify-inventario', message: $e->getMessage());
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($this->desayunos_agregados as $registro) {

                $recetaId = $registro['receta_id'];
                $cantidadServido = $registro['cantidad'];

                DetalleRegistroDiario::create([
                    'receta_id'        => $recetaId,
                    'cantidad_servido' => $cantidadServido,
                    'fecha'            => now(),
                ]);

                $receta = Receta::with('recetaIngredientes.producto')->find($recetaId);

                foreach ($receta->recetaIngredientes as $ingrediente) {

                    $cantidadPorUnidad = $this->resolverCantidadPorUnidad($ingrediente);

                    if ($cantidadPorUnidad <= 0) {
                        continue;
                    }

                    $totalDescontarGramos = $cantidadPorUnidad * $cantidadServido;
                    $pesoUnidad = (float) $ingrediente->producto->peso_contenido;

                    if ($pesoUnidad <= 0) {
                        $pesoUnidad = 1;
                    }

                    $lotes = InventarioSedeLote::where('sede_id', $sedeId)
                        ->whereHas('lote', function ($q) use ($ingrediente) {
                            $q->where('producto_id', $ingrediente->producto_id)
                                ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
                                ->where('estado', 1);
                        })
                        ->where('cantidad_convertida', '>', 0)
                        ->orderBy('lote_id', 'asc')
                        ->get();

                    $pendiente = $totalDescontarGramos;

                    foreach ($lotes as $inv) {

                        if ($pendiente <= 0) break;

                        $dispGramos  = $inv->cantidad_convertida;
                        $tomarGramos = min($pendiente, $dispGramos);

                        $inv->cantidad_convertida -= $tomarGramos;
                        $inv->cantidad = max(0, $inv->cantidad_convertida / $pesoUnidad);
                        $inv->save();

                        $lote = $inv->lote;
                        $lote->cantidad_actual = (float) InventarioSedeLote::where('lote_id', $lote->id)
                            ->sum('cantidad_convertida');
                        $lote->save();

                        $factorUnidad = (float) ($ingrediente->unidad?->factor_a_gramo ?? 0);
                        $cantidadEnUnidad = $factorUnidad > 0
                            ? $tomarGramos / $factorUnidad
                            : $tomarGramos;

                        MovimientoInventario::create([
                            'producto_id'         => $ingrediente->producto_id,
                            'lote_id'             => $lote->id,
                            'sede_id'             => $sedeId,
                            'tipo_movimiento'     => 'SALIDA',
                            'unidad_id'           => $ingrediente->unidad_id,
                            'cantidad'            => $cantidadEnUnidad,
                            'cantidad_convertida' => $tomarGramos,
                            'fecha'               => now(),
                            'observaciones'       => "Consumo por receta {$receta->nombre} ({$cantidadServido} raciones)",
                        ]);

                        $pendiente -= $tomarGramos;
                    }

                    if ($pendiente > 0) {
                        throw new Exception("No hay suficiente inventario para el ingrediente: {$ingrediente->producto->nombre}. Faltan " . round($pendiente, 2) . " gramos.");
                    }
                }
            }

            DB::commit();

            $this->desayuno_registrado = true;
            $this->inventarioError = null;
            $this->inventarioAdvertencias = $advertencias;

            $mensajeExito = 'Los registros de comida del día fueron guardados correctamente.';
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

            $this->alertInventario   = $e->getMessage();
            $this->inventarioError   = $e->getMessage();
            $this->desayuno_registrado = false;
            $this->showNotification  = true;
            $this->dispatch('notify-inventario', message: $e->getMessage());
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
