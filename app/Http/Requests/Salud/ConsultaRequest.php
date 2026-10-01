<?php

namespace App\Http\Requests\Salud;

use Illuminate\Foundation\Http\FormRequest;

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
            'id_persona'     => ['required', 'integer', 'exists:persona,id_persona'],
            'medico_id'      => ['required', 'integer', 'different:id_persona', 'exists:persona,id_persona'],
            'consultorio_id' => ['required', 'integer', 'exists:consultorios,id'],
            'fecha'          => ['required', 'date', 'before_or_equal:today'],
            'motivo'         => ['required', 'string', 'min:3', 'max:1000'],
            'diagnostico'    => ['required', 'string', 'min:3', 'max:2000'],
            'observaciones'  => ['nullable', 'string', 'max:2000'],
            'enfermedades'   => ['required', 'array', 'min:1', 'max:20'],
            'enfermedades.*' => ['required', 'integer', 'distinct', 'exists:enfermedades,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'medico_id.different'       => 'El médico no puede ser el mismo paciente.',
            'fecha.before_or_equal'     => 'La fecha de la consulta no puede ser futura.',
            'enfermedades.required'     => 'Debe seleccionar al menos una enfermedad.',
            'enfermedades.min'          => 'Debe seleccionar al menos una enfermedad.',
            'enfermedades.*.distinct'   => 'Hay enfermedades repetidas.',
            'enfermedades.*.exists'     => 'Una de las enfermedades seleccionadas no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_persona'     => 'paciente',
            'medico_id'      => 'médico',
            'consultorio_id' => 'consultorio',
            'fecha'          => 'fecha',
            'motivo'         => 'motivo',
            'diagnostico'    => 'diagnóstico',
            'observaciones'  => 'observaciones',
            'enfermedades'   => 'enfermedades',
        ];
    }

    /**
     * Datos de la consulta (sin enfermedades) listos para persistir.
     */
    public function datosConsulta(): array
    {
        $datos = collect($this->validated())->except('enfermedades')->all();
        $datos['observaciones'] = $datos['observaciones'] ?? '';

        return $datos;
    }

    public function enfermedadesIds(): array
    {
        return array_map('intval', $this->validated('enfermedades'));
    }
}
