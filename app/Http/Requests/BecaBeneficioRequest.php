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
        $beneficioId = $this->route('beneficio')?->id;

        return [
            'nombre_beneficio' => [
                'required',
                'string',
                'max:255',
                Rule::unique('be_beneficios', 'nombre_beneficio')->ignore($beneficioId),
            ],
            'descripcion' => ['nullable', 'string'],
            'status'      => ['required', 'boolean'],
        ];
    }
}