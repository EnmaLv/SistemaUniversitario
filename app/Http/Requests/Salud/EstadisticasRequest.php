<?php

namespace App\Http\Requests\Salud;

use App\Models\salud\Consulta;
use App\Services\Salud\ListadoSaludService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Filtros y formato de exportación de estadísticas de salud.
 */
class EstadisticasRequest extends FormRequest
{
    public const FORMATOS = ['json', 'pdf', 'word', 'excel'];

    /** Formatos en los que se puede descargar un listado */
    public const FORMATOS_LISTADO = ['pdf', 'excel'];

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
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
            'consultorio_id'   => ['nullable', 'integer', 'exists:consultorios,id'],
            'medico_id'        => ['nullable', 'integer', 'exists:persona,id_persona'],
            'enfermedad_ids'   => ['nullable', 'array', 'max:50'],
            'enfermedad_ids.*' => ['integer', 'min:1'],
            'estado_receta'    => ['nullable', 'in:' . implode(',', Consulta::ESTADOS_REPORTE)],
            'perfil_academico' => ['nullable', 'string', 'max:100'],
            'pnf'              => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Excel solo existe para los listados.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->formato() === 'excel' && !in_array($this->tipoReporte(), ListadoSaludService::TIPOS, true)) {
                $validator->errors()->add('format', 'El formato Excel solo está disponible para los listados.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'format.in'               => 'Formato no soportado.',
            'report_type.regex'       => 'El tipo de reporte no es válido.',
            'estado_receta.in'        => 'El estado de receta no es válido.',
            'end_date.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ];
    }

    public function formato(): string
    {
        return (string) $this->input('format', 'json');
    }

    public function tipoReporte(): string
    {
        return (string) $this->input('report_type', 'completo');
    }

    public function periodo(): string
    {
        return (string) $this->input('periodo', 'mensual');
    }

    /**
     * Indica si se pidió un listado (consultas o medicamentos dispensados) en PDF o Excel.
     */
    public function esListado(): bool
    {
        return in_array($this->tipoReporte(), ListadoSaludService::TIPOS, true)
            && in_array($this->formato(), self::FORMATOS_LISTADO, true);
    }
}
