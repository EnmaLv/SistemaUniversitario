<?php

namespace App\Http\Requests\Salud;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Búsquedas AJAX (pacientes y enfermedades).
 */
class BusquedaRequest extends FormRequest
{
    public const MIN_CARACTERES = 2;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function termino(): string
    {
        return trim((string) $this->validated('q', ''));
    }

    public function terminoValido(): bool
    {
        return mb_strlen($this->termino()) >= self::MIN_CARACTERES;
    }
}
