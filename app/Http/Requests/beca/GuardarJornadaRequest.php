<?php

namespace App\Http\Requests\beca;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class GuardarJornadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'activa' => $this->has('activa') ? 1 : 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre_jornada' => 'required|string|max:255',
            'descripcion_jornada' => 'nullable|string',
            'beneficio_id' => 'required|exists:be_beneficios,id',
            'lapsos_id' => 'required|exists:be_lapsos,id',
            'fecha_inicio_solicitud' => 'required|date',
            'fecha_fin_solicitud' => 'required|date|after_or_equal:fecha_inicio_solicitud',
            'cupos_maximos' => 'required|integer|min:1',
            'activa' => 'required|boolean',

            'criterios'                  => 'nullable|array',
            'criterios.*.id_pregunta'    => 'required|exists:be_beca_preguntas,id',
            'criterios.*.operador'       => 'nullable|in:=,!=,>,>=,<,<=,in,not_in,between',
            'criterios.*.valor_esperado' => 'nullable|string|max:255',
            'criterios.*.es_eliminatoria'=> 'nullable|boolean',
            'criterios.*.peso'           => 'nullable|numeric|min:0',
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'nombre_jornada.required' => 'El nombre de la jornada es obligatorio.',
            'beneficio_id.required' => 'Debe seleccionar un beneficio.',
            'lapsos_id.required' => 'Debe seleccionar un lapso académico.',
            'fecha_inicio_solicitud.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin_solicitud.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin_solicitud.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'cupos_maximos.required' => 'El número de cupos máximos es obligatorio.',
            'cupos_maximos.min' => 'El número de cupos máximos debe ser al menos 1.',
        ];
    }
}
