<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BecaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
            'beneficios' => ['nullable', 'array'],
            'beneficios.*.id' => ['nullable', 'exists:be_beneficios,id'],
            'beneficios.*.observacion' => ['nullable', 'string'],
            'beneficios.*.activo' => ['nullable'],
            'requiere_tutor' => ['nullable', 'boolean'],
            'tutores' => ['nullable', 'array'],
            'tutores.*.id' => ['nullable', 'exists:be_beca_tutores,id'],
            'tutores.*.rol_id' => ['nullable', 'exists:rol,id_rol'],
            'tutores.*.tutor_id' => ['nullable', 'exists:persona,id_per'],
            'tutores.*.descripcion' => ['nullable', 'string'],
            'asignaciones' => ['nullable', 'array'],
            'asignaciones.*.id' => ['nullable', 'exists:be_beca_trabajo_asignaciones,id'],
            'asignaciones.*.area' => ['nullable', 'string', 'max:255'],
            'asignaciones.*.horario' => ['nullable', 'string', 'max:255'],
            'asignaciones.*.tutor_id' => ['nullable', 'exists:persona,id_per'],
            'asignaciones.*.observaciones' => ['nullable', 'string'],
            'asignaciones.*.activo' => ['nullable', 'boolean'],
            'preguntas' => ['nullable', 'array'],
            'preguntas.*.texto' => ['required', 'string', 'filled'],
            'preguntas.*.tipo' => ['required', 'in:text,number'],
            'preguntas.*.min' => ['nullable', 'numeric'],
            'preguntas.*.max' => ['nullable', 'numeric'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $preguntas = $this->input('preguntas', []);
            foreach ($preguntas as $index => $pregunta) {
                if (($pregunta['tipo'] ?? null) === 'number') {
                    $min = isset($pregunta['min']) && $pregunta['min'] !== '' ? $pregunta['min'] : null;
                    $max = isset($pregunta['max']) && $pregunta['max'] !== '' ? $pregunta['max'] : null;
                    if (!is_null($min) && !is_null($max) && is_numeric($min) && is_numeric($max) && $min > $max) {
                        $validator->errors()->add("preguntas.$index.min", 'El valor mínimo no puede ser mayor que el máximo.');
                    }
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'requiere_tutor' => $this->boolean('requiere_tutor'),
        ];

        // Si el formulario envía explícitamente 'activo', usar ese valor.
        if ($this->has('activo')) {
            $merge['activo'] = $this->boolean('activo');
        }

        // Si es creación (POST) y no se envía 'activo', asumimos activo = true.
        if (!$this->has('activo') && $this->isMethod('post')) {
            $merge['activo'] = true;
        }

        $this->merge($merge);
    }
}
