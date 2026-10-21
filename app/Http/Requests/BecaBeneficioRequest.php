<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BecaBeneficioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $beneficioId = $this->route('beneficio')?->id_bes;

        return [
            'nombre_beneficio' => [
                'required',
                'string',
                'max:255',
                Rule::unique('beneficio_estudiantil', 'nombre_beneficio_bes')->ignore($beneficioId, 'id_bes')->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string'],
            'status'      => ['required', 'boolean'],
        ];
    }
}