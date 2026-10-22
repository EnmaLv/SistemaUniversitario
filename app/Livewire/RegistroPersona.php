<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Perfil;
use App\Models\Persona;
use App\Models\PersonaPnf;
use App\Support\Cuentas\CuentasComunidad;
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

    public $perfilId;
    public $perfiles = [];
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
                Rule::unique('persona', 'cedula_per')
                    ->where(fn($q) => $q->where('status_id', 1)),
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
            'perfilId' => ['required', 'integer', Rule::in(array_column($this->perfiles, 'id'))],
            'pnfId' => $this->esEstudiante ? 'required|numeric' : 'nullable',
            'semestreId' => $this->esEstudiante ? 'required' : 'nullable',
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
        'perfilId.required' => 'Debe seleccionar un perfil',
        'perfilId.in' => 'El perfil seleccionado no es válido',
    ];

    public function getEsEstudianteProperty(): bool
    {
        return Perfil::esEstudiante($this->perfilId);
    }

    public function updatedPerfilId(): void
    {
        if (! $this->esEstudiante) {
            $this->resetValidation(['pnfId', 'semestreId']);
        }
    }

    private function cargarPerfiles(?int $perfilActual = null): void
    {
        $this->perfiles = Perfil::comunidad()
            ->orderBy('id_perfil')
            ->get(['id_perfil', 'nombre_perfil'])
            ->map(fn ($perfil) => ['id' => (int) $perfil->id_perfil, 'nombre' => $perfil->nombre_perfil])
            ->all();

        if ($perfilActual && ! in_array($perfilActual, array_column($this->perfiles, 'id'), true)) {
            $nombre = DB::table('perfil')->where('id_perfil', $perfilActual)->value('nombre_perfil');

            if ($nombre) {
                $this->perfiles[] = ['id' => $perfilActual, 'nombre' => $nombre];
            }
        }
    }


    public function updatedCedula($value)
    {
        if (empty($value)) {
            $this->formHabilitado = false;
            return;
        }

        $persona = Persona::where('cedula_per', $value)->first();

        if ($persona) {
            if ((int) $persona->status_id === 2) {
                // Disparamos el evento con Livewire 3
                $this->dispatch('confirm-reactivate', cedula: $value);
                $this->formHabilitado = false;
                return;
            } elseif (! $this->isEdit && (int) $persona->status_id === 1) {
                $this->formHabilitado = false;
                $this->dispatch(
                    'alert',
                    type: 'info',
                    title: 'Persona ya activa',
                    text: 'La persona ya está activa en el sistema.'
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

    public function reactivarPersona($cedula)
    {
        try {
            $persona = Persona::where('cedula_per', $cedula)->first();

            if (!$persona) {
                $this->dispatch(
                    'alert',
                    type: 'error',
                    title: 'Error',
                    text: 'Persona no encontrada.'
                );
                return;
            }

            if ((int) $persona->status_id === 2) {
                $persona->update([
                    'status_id' => 1,
                    'updated_at' => now(),
                ]);

                // Cargamos los datos en el formulario
                $this->cedula = $persona->cedula_per;
                $this->nombre = $persona->nombres_per;
                $this->segundo_nombre = $persona->segundo_nombre_per;
                $this->apellido = $persona->apellidos_per;
                $this->segundo_apellido = $persona->segundo_apellido_per;
                $this->fecha_nacimiento = $persona->fecha_nacimiento_per;
                $this->genero = $persona->genero_per;
                $this->telefono = $persona->telefono_per;
                $this->email = $persona->correo_per;
                $this->semestreId = $persona->semestre_per;
                $this->sedeId = $persona->sed_id;
                $this->perfilId = $persona->id_perfil;
                $this->cargarPerfiles((int) $persona->id_perfil);

                $direccion = DB::table('direccion')
                    ->join('parroquia', 'direccion.par_id', '=', 'parroquia.id_par')
                    ->join('municipio', 'parroquia.mun_id', '=', 'municipio.id_mun')
                    ->where('direccion.per_id', $persona->id_per)
                    ->select('direccion.*', 'parroquia.mun_id', 'municipio.esd_id')
                    ->first();

                if ($direccion) {
                    $this->estadosVeId = $direccion->esd_id;
                    $this->municipiosId = $direccion->mun_id;
                    $this->parroquiaId = $direccion->par_id;
                    $this->calle = $direccion->calle_dir;
                    $this->sector = $direccion->sector_dir;

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
                $personaPnf = PersonaPnf::where('per_id', $persona->id_per)->first();
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
                    title: 'Persona reactivada',
                    text: 'La persona ha sido reactivada correctamente. Puede editar los datos si es necesario.'
                );
            } else {
                $this->dispatch(
                    'alert',
                    type: 'info',
                    title: 'Persona ya activa',
                    text: 'La persona ya está activa en el sistema.'
                );
            }
        } catch (Exception $e) {
            $this->dispatch(
                'alert',
                type: 'error',
                title: 'Error',
                text: 'Ocurrió un error al reactivar la persona: ' . \App\Support\Seguridad\MensajeError::de($e)
            );
        }
    }


    public function create()
    {
        $data = $this->validate();
        $persona = Persona::where('cedula_per', $data['cedula'])->first();

        if ($persona) {
            if ((int) $persona->status_id === 2) {
                $persona->update([
                    'status_id' => 1,
                    'nombres_per' => $data['nombre'],
                    'apellidos_per' => $data['apellido'],
                    'fecha_nacimiento_per' => $data['fecha_nacimiento'],
                    'id_perfil' => $data['perfilId'],
                    'updated_at' => now(),
                ]);

                CuentasComunidad::asegurarCuenta($persona);

                $this->dispatch(
                    'alert',
                    type: 'success',
                    title: 'Persona activada',
                    text: 'La persona estaba inactiva y ahora ha sido activada.'
                );

                return;
            } else {
                $this->dispatch(
                    'alert',
                    type: 'info',
                    title: 'Persona ya activa',
                    text: 'La persona ya está activa en el sistema.'
                );
                return;
            }
        } else {
            $finish = Persona::crearPersona($data);

            if ($finish) {
                $nueva = Persona::where('cedula_per', $data['cedula'])->first();
                if ($nueva) {
                    CuentasComunidad::asegurarCuenta($nueva);
                }

                redirect()->route('admin.configuracion.persona.index')->with('success', 'Persona registrada exitosamente.');
            } else {
                $this->dispatch(
                    'alert',
                    type: 'error',
                    title: 'Error',
                    text: 'Error al registrar la persona. Revisa los datos'
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

                redirect()->route('admin.configuracion.persona.index')->with('success', 'Persona actualizada exitosamente.');
            } else {
                $this->dispatch(
                    'alert',
                    type: 'error',
                    title: 'Error',
                    text: $error ?? 'Error al actualizar la persona. Revisa los datos'
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
            $persona = Persona::where('id_per', $id)->where('status_id', 1)->first();
            $personaPnf = PersonaPnf::with('pnf')->where('per_id', $id)->first();
            $direccion = DB::table('direccion')
                ->join('parroquia', 'direccion.par_id', '=', 'parroquia.id_par')
                ->join('municipio', 'parroquia.mun_id', '=', 'municipio.id_mun')
                ->join('estado', 'municipio.esd_id', '=', 'estado.id_esd')
                ->where('direccion.per_id', $id)
                ->select('direccion.*', 'parroquia.*', 'municipio.*', 'estado.*')
                ->first();

            $this->cedula = $persona->cedula_per;
            $this->perfilId = $persona->id_perfil;
            $this->cargarPerfiles((int) $persona->id_perfil);
            $this->fecha_nacimiento = $persona->fecha_nacimiento_per;
            $this->nombre = $persona->nombres_per;
            $this->segundo_nombre = $persona->segundo_nombre_per;
            $this->apellido = $persona->apellidos_per;
            $this->segundo_apellido = $persona->segundo_apellido_per;
            $this->genero = $persona->genero_per;
            $this->telefono = $persona->telefono_per;
            $this->email = $persona->correo_per;
            $this->pnfs = DB::table('programa')->where('status_id', 1)->get();
            $this->sede = DB::table('sede')->get();
            $this->semestreId = $persona->semestre_per;
            $this->showPnf = Perfil::esEstudiante($persona->id_perfil);
            $this->pnfId = $personaPnf?->prg_id;
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
            $this->calle = $direccion->calle_dir;
            $this->sector = $direccion->sector_dir;
        }

        if (! $this->isEdit) {
            $this->perfilId = Perfil::idEstudiante();
            $this->cargarPerfiles();
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
