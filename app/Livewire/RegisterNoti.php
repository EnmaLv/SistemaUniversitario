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

use function Symfony\Component\Clock\now;

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
    public $horarioPermitido;
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
        $desayunoDelDia = DetalleRegistroDiario::where('fecha', now()->format('Y-m-d'))->get();

        if ($desayunoDelDia->isEmpty()) {
            $this->sobrante = 0;
            return;
        }

        $desayunoTotal = $desayunoDelDia->sum('cantidad_servido');

        $this->sobrante = $desayunoTotal - $registradosHoy;
    }

    public function openModal()
    {
        $this->dispatch('openModal');
    }


    public function save()
    {
        $detalleHoy = DetalleRegistroDiario::whereDate('created_at', now())->first();

        if (!$detalleHoy) {
            $this->notification = [
                'type'    => 'danger',
                'message' => 'Debe registrar el desayuno y la cantidad servida antes de registrar estudiantes.',
            ];
            $this->showNotification = true;
            return;
        }

        $registradosHoy = Registro_diario::where('fecha_regis_diario_c', date('Y-m-d'))->count();

        if ($registradosHoy >= $detalleHoy->cantidad_servido) {
            $this->limiteAlcanzado = "Ya se alcanzó el límite de {$detalleHoy->cantidad_servido} raciones. No se pueden registrar más estudiantes.";
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
                ->exists();

            if ($is_register) {
                $this->notification = [
                    'type'    => 'danger',
                    'message' => "El estudiante {$persona->nombre_persona} {$persona->apellido_persona} ya se registró hoy.",
                ];

                $DatosHistorial['nombre']      = $persona->nombre_persona;
                $DatosHistorial['estado']      = 'Rechazado';
                $DatosHistorial['observacion'] = 'El estudiante ya se registró hoy';

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
                    'fecha_regis_diario_c' => date('Y-m-d'),
                    'hora'                 => date('H:i:s'),
                ]);

                DB::commit();

                $this->recalcularSobrante();

                if ($this->sobrante == 0) {
                    $this->showBtnFinalizar = false;
                    $this->enableInput      = false;
                    $this->dispatch('swal', [
                        'title' => '¡Límite alcanzado!',
                        'text'  => 'Se alcanzó el límite de raciones para hoy.',
                        'icon'  => 'success',
                    ]);
                }

                $this->notification = [
                    'type'    => 'success',
                    'message' => "El estudiante {$persona->nombre_persona} {$persona->apellido_persona} se registró exitosamente.",
                ];

                $DatosHistorial['nombre']      = $persona->nombre_persona;
                $DatosHistorial['estado']      = 'Aprobado';
                $DatosHistorial['observacion'] = 'Registro exitoso';

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

        if ($cierreHoy || $this->sobrante == 0) {
            $this->enableInput = false;
            $this->showBtnFinalizar = false;
            return;
        }

        $this->fecha = now()->format('Y-m-d');

        if ($this->sobrante > 0) {
            $this->showBtnFinalizar = true;
        }
    }

    public function render()
    {
        $receta_diario = DetalleRegistroDiario::whereDate('created_at', now())->exists();

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
