<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Persona;
use App\Models\PersonaPnf;
use Livewire\Attributes\Validate;
use Exception;
use Illuminate\Validation\Rule;

class RegistroPersona extends Component
{

    public $isEdit = false;
    public $onlyShow = false;
    public $cedula;
    public $estadosVeId;
    public $municipiosId;
    public $parroquiaId;
    public $municipiosVE = [];
    public $parroquiasVE = [];

    #[Validate('required')]
    public $fecha_nacimiento;


    public $nombre;
    public $segundo_nombre;
    public $apellido;
    public $segundo_apellido;

    public $genero;
    public $telefono;
    public $email;

    public $pnfId;
    public $sedeId;
    public $semestreId;

    public $calle;
    public $sector;

    public $formHabilitado = false;
    public $showPnf = false;
    public $enabledMunicipio = false;
    public $enabledParroquia = false;

    protected function rules()
    {
        $rules = [
            'cedula' => [
                'required',
                'numeric',
                'min:1000000',
                Rule::unique('persona', 'cedula_persona')
                    ->where(fn($q) => $q->where('estado', 1)),
            ],
            'nombre' => 'required|min:3|max:50',
            'segundo_nombre' => 'nullable',
            'apellido' => 'required|min:3|max:50',
            'segundo_apellido' => 'nullable',
            'fecha_nacimiento' => [
                'required',
                'date',
                'before_or_equal:' . now()->subYears(15)->format('Y-m-d'),
            ],
            'genero' => 'required',
            'telefono' => 'required',
            'email' => 'required|email',
            'calle' => 'required',
            'sector' => 'required',
            'estadosVeId' => 'required|numeric',
            'municipiosId' => 'required|numeric',
            'parroquiaId' => 'required|numeric',
            'sedeId' => 'required|numeric',
            'pnfId' => 'required|numeric',
            'semestreId' => 'required',
        ];

        if ($this->isEdit) {
            $rules['cedula'] = 'required|min:100000|numeric';
        }

        return $rules;
    }


    protected $messages = [
        'estadosVeId.required' => 'Debe seleccionar un estado',
        'cedula.min' => 'La cédula debe tener al menos 7 dígitos',
        'nombre.required' => 'El nombre es obligatorio',
        'apellido.required' => 'El apellido es obligatorio',
        'genero.required' => 'El genero es obligatorio',
        'telefono.required' => 'El telefono es obligatorio',
        'email.required' => 'El email es obligatorio',
        'calle.required' => 'La calle es obligatoria',
        'sector.required' => 'El sector es obligatorio',
        'municipiosId.required' => 'Debe seleccionar un municipio',
        'parroquiaId.required' => 'Debe seleccionar una parroquia',
        'pnfId.required' => 'El PNF es obligatorio',
        'sedeId.required' => 'La Sede es obligatoria',
        'semestreId.required' => 'El Semestre es obligatorio',
    ];


    public function updatedCedula($value)
    {
        if (empty($value)) {
            $this->formHabilitado = false;
            return;
        }

        $persona = Persona::where('cedula_persona', $value)->first();

        if ($persona) {
            if ($persona->estado === 0) {
                // Disparamos el evento con Livewire 3
                $this->dispatch('confirm-reactivate', cedula: $value);
                $this->formHabilitado = false;
                return;
            } elseif ($persona->estado === 1) {
                $this->formHabilitado = false;
                $this->dispatch(
                    'alert',
                    type: 'info',
                    title: 'Estudiante ya activo',
                    text: 'El estudiante ya está activo en el sistema.'
                );
                return;
            }
        } else {
            $this->formHabilitado = true;
        }

        try {
            $this->validateOnly('cedula');
        } catch (Exception $e) {
            $this->formHabilitado = false;
            throw $e;
        }
    }



    public function updatedEstadosVeId($value)
    {
        $this->validateOnly('estadosVeId');

        $this->municipiosVE = DB::table('municipio')
            ->where('esd_id', $value)
            ->where('status_id', 1)
            ->get();

        $this->enabledMunicipio = ($value && $this->formHabilitado);
    }

    public function updatedMunicipiosId($value)
    {
        $this->validateOnly('municipiosId');

        $this->parroquiasVE = DB::table('parroquia')->where('mun_id', $value)->where('status_id', 1)->get();

        $this->enabledParroquia = ($value && $this->formHabilitado);
    }

    public function reactivarEstudiante($cedula)
    {
        try {
            $persona = Persona::where('cedula_persona', $cedula)->first();

            if (!$persona) {
                $this->dispatch(
                    'alert',
                    type: 'error',
                    title: 'Error',
                    text: 'Estudiante no encontrado.'
                );
                return;
            }

            if ($persona->estado === 0) {
                // Reactivamos el estudiante
                $persona->update([
                    'estado' => 1,
                    'updated_at' => now(),
                ]);

                // Cargamos los datos en el formulario
                $this->cedula = $persona->cedula_persona;
                $this->nombre = $persona->nombre_persona;
                $this->segundo_nombre = $persona->segundo_nombre_persona;
                $this->apellido = $persona->apellido_persona;
                $this->segundo_apellido = $persona->segundo_apellido_persona;
                $this->fecha_nacimiento = $persona->fecha_nacimiento_persona;
                $this->genero = $persona->genero_persona;
                $this->telefono = $persona->telefono_persona;
                $this->email = $persona->email_persona;
                $this->semestreId = $persona->semestre_persona;
                $this->sedeId = $persona->sed_id;

                // Cargamos datos de dirección si existen
                $direccion = DB::table('direccion')
                    ->join('parroquia', 'direccion.par_id', '=', 'parroquia.id_par')
                    ->join('municipio', 'parroquia.mun_id', '=', 'municipio.id_mun')
                    ->where('direccion.id_persona', $persona->id_persona)
                    ->select('direccion.*', 'parroquia.mun_id', 'municipio.esd_id')
                    ->first();

                if ($direccion) {
                    $this->estadosVeId = $direccion->esd_id;
                    $this->municipiosId = $direccion->mun_id;
                    $this->parroquiaId = $direccion->par_id;
                    $this->calle = $direccion->calle;
                    $this->sector = $direccion->sector;

                    // Cargamos municipios y parroquias
                    $this->municipiosVE = DB::table('municipio')
                        ->where('esd_id', $this->estadosVeId)
                        ->where('status_id', 1)
                        ->get();

                    $this->parroquiasVE = DB::table('parroquia')
                        ->where('mun_id', $this->municipiosId)
                        ->where('status_id', 1)
                        ->get();

                    $this->enabledMunicipio = true;
                    $this->enabledParroquia = true;
                }

                // Cargamos PNF
                $personaPnf = PersonaPnf::where('id_persona', $persona->id_persona)->first();
                if ($personaPnf) {
                    $this->pnfId = $personaPnf->prg_id;
                    $this->showPnf = true;
                }

                // Habilitamos el formulario
                $this->formHabilitado = false;

                // Alerta de éxito
                $this->dispatch(
                    'alert',
                    type: 'success',
                    title: 'Estudiante reactivado',
                    text: 'El estudiante ha sido reactivado correctamente. Puede editar los datos si es necesario.'
                );
            } else {
                $this->dispatch(
                    'alert',
                    type: 'info',
                    title: 'Estudiante ya activo',
                    text: 'El estudiante ya está activo en el sistema.'
                );
            }
        } catch (Exception $e) {
            $this->dispatch(
                'alert',
                type: 'error',
                title: 'Error',
                text: 'Ocurrió un error al reactivar el estudiante: ' . \App\Support\Seguridad\MensajeError::de($e)
            );
        }
    }


    public function create()
    {
        $data = $this->validate();
        $persona = Persona::where('cedula_persona', $data['cedula'])->first();

        if ($persona) {
            if ($persona->estado === 0) {
                $persona->update([
                    'estado' => 1,
                    'nombre_persona' => $data['nombre'],
                    'apellido_persona' => $data['apellido'],
                    'fecha_nacimiento_persona' => $data['fecha_nacimiento'],
                    'updated_at' => now(),
                ]);

                \App\Support\Cuentas\CuentasComunidad::asegurarCuenta($persona);

                $this->dispatch(
                    'alert',
                    type: 'success',
                    title: 'Estudiante activado',
                    text: 'El estudiante estaba inactivo y ahora ha sido activado.'
                );

                return;
            } else {
                $this->dispatch(
                    'alert',
                    type: 'info',
                    title: 'Estudiante ya activo',
                    text: 'El estudiante ya está activo en el sistema.'
                );
                return;
            }
        } else {
            $finish = Persona::crearPersona($data);

            if ($finish) {
                $nueva = Persona::where('cedula_persona', $data['cedula'])->first();
                if ($nueva) {
                    \App\Support\Cuentas\CuentasComunidad::asegurarCuenta($nueva);
                }

                redirect()->route('admin.configuracion.persona.index')->with('success', 'Estudiante creado exitosamente.');
            } else {
                $this->dispatch(
                    'alert',
                    type: 'error',
                    title: 'Error',
                    text: 'Error al crear el estudiante. Revisa los datos'
                );
            }
        }
    }

    public $personaId;
    public function update()
    {
        try {
            $data = $this->validate();

            [$finish, $error] = Persona::actualizarPersona($data, $this->personaId);

            if ($finish) {

                redirect()->route('admin.configuracion.persona.index')->with('success', 'Estudiante actualizado exitosamente.');
            } else {
                $this->dispatch(
                    'alert',
                    type: 'error',
                    title: 'Error',
                    text: $error ?? 'Error al actualizar el estudiante. Revisa los datos'
                );
            }
        } catch (Exception $e) {
            $this->dispatch(
                'alert',
                type: 'error',
                title: 'Error',
                text: \App\Support\Seguridad\MensajeError::de($e) ?: 'Ocurrió un error al procesar la solicitud'
            );
        }
    }

    public $perfil = [];
    public $pnfs = [];
    public $estadosVE = [];
    public $sede = [];
    public $data = [];

    public function mount()
    {
        if ($this->isEdit) {
            $this->formHabilitado = true;
            $this->enabledMunicipio = true;
            $this->enabledParroquia = true;

            $id = request("id");
            $this->personaId = $id;
            $persona = Persona::where('id_persona', $id)->where('estado', true)->first();
            $personaPnf = PersonaPnf::with('pnf')->where('id_persona', $id)->first();
            $direccion = DB::table('direccion')
                ->join('parroquia', 'direccion.par_id', '=', 'parroquia.id_par')
                ->join('municipio', 'parroquia.mun_id', '=', 'municipio.id_mun')
                ->join('estado', 'municipio.esd_id', '=', 'estado.id_esd')
                ->where('direccion.id_persona', $id)
                ->select('direccion.*', 'parroquia.*', 'municipio.*', 'estado.*')
                ->first();

            $this->cedula = $persona->cedula_persona;
            $this->perfil = DB::table('perfil')->get();
            $this->fecha_nacimiento = $persona->fecha_nacimiento_persona;
            $this->nombre = $persona->nombre_persona;
            $this->segundo_nombre = $persona->segundo_nombre_persona;
            $this->apellido = $persona->apellido_persona;
            $this->segundo_apellido = $persona->segundo_apellido_persona;
            $this->genero = $persona->genero_persona;
            $this->telefono = $persona->telefono_persona;
            $this->email = $persona->email_persona;
            $this->pnfs = DB::table('programa')->where('status_id', 1)->get();
            $this->sede = DB::table('sede')->get();
            $this->semestreId = $persona->semestre_persona;
            $this->showPnf = true;
            $this->pnfId = $personaPnf->prg_id;
            $this->sedeId = $persona->sed_id;

            if (!$direccion) {
                $this->enabledMunicipio = false;
                $this->enabledParroquia = false;
                $this->estadosVE = DB::table('estado')->get();

                $this->dispatch(
                    'alert',
                    type: 'warning',
                    title: 'Usuario sin Dirección',
                    text: 'El usuario no tiene una dirección registrada. Por favor, complete los datos de dirección.'
                );
                return;
            }
            $this->estadosVeId = $direccion->esd_id;
            $this->municipiosVE = DB::table('municipio')
                ->where('esd_id', $this->estadosVeId)
                ->get();

            $this->municipiosId = $direccion->mun_id;

            $this->parroquiasVE = DB::table('parroquia')
                ->where('mun_id', $this->municipiosId)
                ->get();

            $this->parroquiaId = $direccion->par_id;
            $this->calle = $direccion->calle;
            $this->sector = $direccion->sector;
        }

        $this->pnfs = DB::table('programa')->where('status_id', 1)->get();
        $this->estadosVE = DB::table('estado')->where('status_id', 1)->get();
        $this->sede = DB::table('sede')->where('status_id', 1)->get();
    }

    public function render()
    {
        return view('livewire.registro-persona');
    }
}
