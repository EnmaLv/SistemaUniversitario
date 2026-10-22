<?php

namespace App\Http\Requests\Salud;

use App\Models\salud\Consulta;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Throwable;

/**
 * Paso 1: alta y edición de consultas.
 */
class ConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'per_id'     => ['required', 'integer', 'exists:persona,id_per'],
            'medico_id'      => ['required', 'integer', 'different:per_id', 'exists:persona,id_per'],
            'consultorio_id' => ['required', 'integer', 'exists:consultorio,id_csr'],
            'fecha'          => ['required', 'date', 'before_or_equal:today'],
            'motivo'         => ['required', 'string', 'min:3', 'max:1000'],
            'diagnostico'    => ['required', 'string', 'min:3', 'max:2000'],
            'observaciones'  => ['nullable', 'string', 'max:2000'],
            'motivo_registro_tardio' => ['nullable', 'string', 'max:1000'],
            'enfermedades'   => ['required', 'array', 'min:1', 'max:20'],
            'enfermedades.*' => ['required', 'integer', 'distinct', 'exists:enfermedad,id_efm'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('fecha') || ! $this->esRegistroTardio()) {
                return;
            }

            if (mb_strlen(trim((string) $this->input('motivo_registro_tardio'))) < 5) {
                $validator->errors()->add(
                    'motivo_registro_tardio',
                    'La fecha de atención es distinta a la fecha de registro (' . Carbon::parse($this->fechaRegistro())->format('d/m/Y') . '). Indique el motivo del registro tardío (mínimo 5 caracteres).'
                );
            }
        });
    }

    public function fechaRegistro(): string
    {
        $consulta = $this->route('consulta');

        return $consulta instanceof Consulta && $consulta->exists
            ? $consulta->fechaRegistro()
            : now()->toDateString();
    }

    public function esRegistroTardio(): bool
    {
        $fecha = $this->input('fecha');

        if (blank($fecha) || ! is_string($fecha)) {
            return false;
        }

        try {
            return Carbon::parse($fecha)->toDateString() !== $this->fechaRegistro();
        } catch (Throwable) {
            return false;
        }
    }

    public function messages(): array
    {
        return [
            'medico_id.different'       => 'El médico no puede ser el mismo paciente.',
            'fecha.before_or_equal'     => 'La fecha de atención no puede ser futura.',
            'enfermedades.required'     => 'Debe seleccionar al menos una enfermedad.',
            'enfermedades.min'          => 'Debe seleccionar al menos una enfermedad.',
            'enfermedades.*.distinct'   => 'Hay enfermedades repetidas.',
            'enfermedades.*.exists'     => 'Una de las enfermedades seleccionadas no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'per_id'     => 'paciente',
            'medico_id'      => 'médico',
            'consultorio_id' => 'consultorio',
            'fecha'          => 'fecha de atención',
            'motivo'         => 'motivo',
            'diagnostico'    => 'diagnóstico',
            'observaciones'  => 'observaciones',
            'motivo_registro_tardio' => 'motivo del registro tardío',
            'enfermedades'   => 'enfermedades',
        ];
    }

    /**
     * Datos de la consulta (sin enfermedades) listos para persistir.
     */
    public function datosConsulta(): array
    {
        $datos = $this->validated();

        return [
            'paciente_per_id'   => $datos['per_id'],
            'medico_per_id'     => $datos['medico_id'],
            'csr_id'            => $datos['consultorio_id'],
            'fecha_cme'         => $datos['fecha'],
            'motivo_cme'        => $datos['motivo'],
            'diagnostico_cme'   => $datos['diagnostico'],
            'observaciones_cme' => $datos['observaciones'] ?? '',
            'motivo_registro_tardio_cme' => $this->esRegistroTardio()
                ? trim((string) ($datos['motivo_registro_tardio'] ?? ''))
                : null,
        ];
    }

    public function enfermedadesIds(): array
    {
        return array_map('intval', $this->validated('enfermedades'));
    }
}
