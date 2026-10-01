<?php

namespace App\Http\Requests\Salud;

use App\Models\salud\Consulta;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Filtros y formato de exportación de estadísticas de salud.
 */
class EstadisticasRequest extends FormRequest
{
    public const FORMATOS = ['json', 'pdf', 'word'];

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'format'           => ['nullable', 'in:' . implode(',', self::FORMATOS)],
            'report_type'      => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9_\-]+$/i'],
            'periodo'          => ['nullable', 'string', 'max:30'],
            'consultorio_id'   => ['nullable', 'integer', 'exists:consultorios,id'],
            'medico_id'        => ['nullable', 'integer', 'exists:persona,id_persona'],
            'enfermedad_ids'   => ['nullable', 'array', 'max:50'],
            'enfermedad_ids.*' => ['integer', 'min:1'],
            'estado_receta'    => ['nullable', 'in:' . implode(',', Consulta::ESTADOS_REPORTE)],
            'perfil_academico' => ['nullable', 'string', 'max:100'],
            'pnf'              => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'format.in'         => 'Formato no soportado.',
            'report_type.regex' => 'El tipo de reporte no es válido.',
            'estado_receta.in'  => 'El estado de receta no es válido.',
        ];
    }

    public function formato(): string
    {
        return $this->input('format', 'json');
    }

    public function tipoReporte(): string
    {
        return $this->input('report_type', 'completo');
    }

    public function periodo(): string
    {
        return $this->input('periodo', 'mensual');
    }
}
