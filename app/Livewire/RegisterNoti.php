<?php

namespace App\Livewire;

use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Persona;
use App\Models\PersonaPnf;
use App\Models\Registro_diario;
use App\Models\DetalleRegistroDiario;
use App\Models\Receta;
use App\Models\Lote;
use App\Models\InventarioSedeLote;
use App\Models\SobranteComedor;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Exception;
use Livewire\WithPagination;

class RegisterNoti extends Component
{
    use WithPagination;

    protected $queryString = [
        'fecha_desde' => ['except' => ''],
        'fecha_hasta' => ['except' => ''],
        'buscar'      => ['except' => ''],
    ];

    #[Validate('required|numeric|min:7', message: ['required' => 'La cédula es requerida', 'numeric' => 'La cédula debe ser un número', 'min' => 'La cédula debe tener al menos 7 dígitos'])]
    public $cedula = '';

    public $showNotification = false;

    public $notification = [
        'type' => 'success',
        'message' => ''
    ];

    public $receta_id = null;
    public $cantidad_servido = null;
    public $desayuno_registrado = false;
    public $desayuno_del_dia = null;
    public $horarioPermitido = false;
    public $limiteAlcanzado = null;

    public $enableInput = true;
    public $showBtnFinalizar = true;

    public $alertInventario = null;
    public $alertLimite = null;

    public $buscar = '';
    public $fecha_desde;
    public $fecha_hasta;

    public $fecha;
    public $sobrante;
    public $motivo;
    public $accion;
    public $registradosHoy;

    public $tipoComidaActual = null;
    public $tipoComidaLabel = '';
    public $ventanaActual = null;
    public $mensajeHorario = null;
    public $proximoHorario = null;
    public $racionesDisponibles = 0;
    public $racionesServidas = 0;

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
                    'manana' => false,
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

    public function finalizarDia()
    {
        $validated = $this->validate([
            'fecha'    => 'required|date',
            'sobrante' => 'required|numeric',
            'motivo'   => 'required|string',
            'accion'   => 'required|string',
        ], [
            'fecha.required'    => 'La fecha es requerida',
            'fecha.date'        => 'La fecha no es válida',
            'sobrante.required' => 'La cantidad sobrante es requerida',
            'sobrante.numeric'  => 'La cantidad sobrante debe ser un número',
            'motivo.required'   => 'El motivo es requerido',
            'accion.required'   => 'La acción es requerida',
        ]);

        SobranteComedor::create([
            'fecha'             => $validated['fecha'],
            'cantidad_sobrante' => $validated['sobrante'],
            'motivo'            => $validated['motivo'],
            'accion_tomada'     => $validated['accion'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $this->enableInput      = false;
        $this->showBtnFinalizar = false;

        $this->dispatch('finalizar-dia-guardado', [
            'icon'  => 'success',
            'title' => '¡Cierre registrado!',
            'text'  => 'El cierre de jornada se ha guardado correctamente.',
        ]);
    }

    private function recalcularSobrante()
    {
        $registradosHoy = Registro_diario::where('fecha_regis_diario_c', date('Y-m-d'))->count();
        $desayunoDelDia = DetalleRegistroDiario::whereDate('fecha', now()->format('Y-m-d'))->get();

        if ($desayunoDelDia->isEmpty()) {
            $this->sobrante = 0;
            return;
        }

        $desayunoTotal = $desayunoDelDia->sum('cantidad_servido');

        $this->sobrante = $desayunoTotal - $registradosHoy;
    }

    private function calcularRacionesDeComida(string $tipo): array
    {
        $servidas = (int) DetalleRegistroDiario::whereDate('created_at', now()->toDateString())
            ->where('tipo_comida', $tipo)
            ->sum('cantidad_servido');

        $registradas = Registro_diario::where('fecha_regis_diario_c', now()->toDateString())
            ->where('tipo_comida', $tipo)
            ->count();

        return [
            'servidas'    => $servidas,
            'registradas' => $registradas,
            'disponibles' => max(0, $servidas - $registradas),
        ];
    }

    public function openModal()
    {
        $this->dispatch('openModal');
    }

    public function save()
    {
        $tipo = $this->determinarTipoComidaActual();

        if (!$tipo) {
            $this->notification = [
                'type'    => 'danger',
                'message' => $this->mensajeHorario ?? 'Actualmente no hay ninguna comida activa.',
            ];
            $this->showNotification = true;
            return;
        }

        $this->tipoComidaActual = $tipo;
        $this->tipoComidaLabel  = $this->getVentanas()[$tipo]['label'];

        $detalleComida = DetalleRegistroDiario::whereDate('created_at', now()->toDateString())
            ->where('tipo_comida', $tipo)
            ->exists();

        if (!$detalleComida) {
            $this->notification = [
                'type'    => 'danger',
                'message' => "Debe registrar las recetas de {$this->tipoComidaLabel} antes de pasar lista de estudiantes.",
            ];
            $this->showNotification = true;
            return;
        }

        $raciones = $this->calcularRacionesDeComida($tipo);
        $this->racionesDisponibles = $raciones['disponibles'];
        $this->racionesServidas    = $raciones['servidas'];

        if ($raciones['disponibles'] <= 0) {
            $this->limiteAlcanzado = "Ya se alcanzó el límite de {$raciones['servidas']} raciones de {$this->tipoComidaLabel} para hoy. No se pueden registrar más estudiantes.";
            $this->dispatch('notify-limite', message: $this->limiteAlcanzado);
            return;
        }

        $this->validate();

        $DatosHistorial = [
            'cedula' => $this->cedula,
            'fecha'  => date('Y-m-d'),
            'hora'   => date('H:i:s'),
        ];

        $persona = Persona::where('cedula_persona', $this->cedula)
            ->where('estado', true)
            ->where('id_perfil', 2)
            ->first();

        if ($persona) {

            $is_register = Registro_diario::where('id_persona', $persona->id_persona)
                ->where('fecha_regis_diario_c', date('Y-m-d'))
                ->where('tipo_comida', $tipo)
                ->exists();

            if ($is_register) {
                $this->notification = [
                    'type'    => 'danger',
                    'message' => "El estudiante {$persona->nombre_persona} {$persona->apellido_persona} ya se registró en {$this->tipoComidaLabel} hoy.",
                ];

                $DatosHistorial['nombre']      = $persona->nombre_persona;
                $DatosHistorial['estado']      = 'Rechazado';
                $DatosHistorial['observacion'] = "Ya registrado en {$this->tipoComidaLabel} hoy";

                $this->showNotification = true;
                $this->cedula = '';
                $this->dispatch('cedula-validada', datos: $DatosHistorial);
                return;
            }

            try {
                DB::beginTransaction();

                $personaPnf = PersonaPnf::where('id_persona', $persona->id_persona)->first();

                if (!$personaPnf) {
                    throw new Exception('El estudiante no tiene un PNF asignado.');
                }

                DB::table('registro_diario_c')->insert([
                    'id_persona'           => $persona->id_persona,
                    'id_persona_pnf'       => $personaPnf->id_persona_pnf,
                    'tipo_comida'          => $tipo,
                    'fecha_regis_diario_c' => date('Y-m-d'),
                    'hora'                 => date('H:i:s'),
                ]);

                DB::commit();

                $raciones = $this->calcularRacionesDeComida($tipo);
                $this->racionesDisponibles = $raciones['disponibles'];

                if ($raciones['disponibles'] <= 0) {
                    $this->enableInput = false;
                    $this->dispatch('swal', [
                        'title' => '¡Límite alcanzado!',
                        'text'  => "Se alcanzó el límite de raciones de {$this->tipoComidaLabel} para hoy.",
                        'icon'  => 'success',
                    ]);
                }

                $this->notification = [
                    'type'    => 'success',
                    'message' => "El estudiante {$persona->nombre_persona} {$persona->apellido_persona} se registró exitosamente en {$this->tipoComidaLabel}.",
                ];

                $DatosHistorial['nombre']      = $persona->nombre_persona;
                $DatosHistorial['estado']      = 'Aprobado';
                $DatosHistorial['observacion'] = "Registro exitoso en {$this->tipoComidaLabel}";

                $this->dispatch('cedula-validada', datos: $DatosHistorial);
            } catch (Exception $e) {

                DB::rollBack();

                $this->notification = [
                    'type'    => 'danger',
                    'message' => "No se pudo registrar al estudiante: " . $e->getMessage(),
                ];

                $DatosHistorial['nombre']      = $persona->nombre_persona ?? 'Sin nombre';
                $DatosHistorial['estado']      = 'Rechazado';
                $DatosHistorial['observacion'] = $e->getMessage();

                $this->dispatch('cedula-validada', datos: $DatosHistorial);
            }
        } else {

            $this->notification = [
                'type'    => 'danger',
                'message' => 'No se encontró un registro para la cédula: ' . $this->cedula,
            ];
        }

        $this->showNotification = true;
        $this->cedula = '';
        $this->limiteAlcanzado = null;
    }

    public function showNotification()
    {
        $this->showNotification = true;
    }

    public function mount()
    {
        $hoy = now()->format('Y-m-d');

        $this->fecha_desde = empty($this->fecha_desde) ? $hoy : $this->fecha_desde;
        $this->fecha_hasta = empty($this->fecha_hasta) ? $hoy : $this->fecha_hasta;

        $cierreHoy = DB::table('sobrantes_comedor')
            ->whereDate('fecha', now()->format('Y-m-d'))
            ->exists();

        $this->recalcularSobrante();
        $this->checkEstadoActual();

        if ($cierreHoy) {
            $this->enableInput = false;
            $this->showBtnFinalizar = false;
            return;
        }

        $this->fecha = now()->format('Y-m-d');

        if ($this->sobrante > 0 && $this->tipoComidaActual !== null) {
            $this->showBtnFinalizar = true;
        } else {
            $this->showBtnFinalizar = false;
        }
    }

    public function checkEstadoActual()
    {
        $tipo = $this->determinarTipoComidaActual();
        $this->tipoComidaActual = $tipo;

        $ventanas = $this->getVentanas();

        if ($tipo) {
            $this->tipoComidaLabel  = $ventanas[$tipo]['label'];
            $this->ventanaActual    = $ventanas[$tipo]['inicio'] . ' - ' . $ventanas[$tipo]['fin'];
            $this->mensajeHorario   = null;
            $this->proximoHorario   = null;
            $this->horarioPermitido = true;

            $raciones = $this->calcularRacionesDeComida($tipo);
            $this->racionesDisponibles = $raciones['disponibles'];
            $this->racionesServidas    = $raciones['servidas'];

            if ($raciones['servidas'] === 0) {
                $this->enableInput = false;
            } elseif ($raciones['disponibles'] <= 0) {
                $this->enableInput = false;
            } else {
                $this->enableInput = true;
            }
        } else {
            $this->tipoComidaLabel  = '';
            $this->ventanaActual    = null;
            $this->mensajeHorario   = 'Actualmente no hay ninguna comida activa.';
            $this->proximoHorario   = $this->calcularProximoHorario();
            $this->horarioPermitido = false;
            $this->enableInput      = false;
        }
    }

    public function render()
    {
        $receta_diario = false;
        $tipo = $this->tipoComidaActual;

        if ($tipo) {
            $receta_diario = DetalleRegistroDiario::whereDate('created_at', now()->toDateString())
                ->where('tipo_comida', $tipo)
                ->exists();
        }

        $filter = [
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'buscar'      => $this->buscar,
        ];

        $data = Registro_diario::showData($filter);
        $comidas = Receta::orderBy('id', 'desc')
            ->where('estado', true)
            ->get();

        return view('livewire.register-noti', [
            'data'   => $data,
            'comidas' => $comidas,
            'receta_diario' => $receta_diario,
        ]);
    }
}