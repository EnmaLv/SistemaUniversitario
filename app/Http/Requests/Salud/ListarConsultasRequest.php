<?php

namespace App\Http\Requests\Salud;

use App\Models\salud\Consulta;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Filtros del listado de consultas.
 */
class ListarConsultasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'buscar'         => ['nullable', 'string', 'max:100'],
            'consultorio_id' => ['nullable', 'integer', 'min:1'],
            'medico_id'      => ['nullable', 'integer', 'min:1'],
            'rango_fechas'   => ['nullable', 'string', 'max:30'],
            'estado'         => ['nullable', 'in:' . Consulta::FILTRO_PENDIENTE . ',' . Consulta::FILTRO_COMPLETADO],
        ];
    }

    /**
     * Valida el formato "Y-m-d" o "Y-m-d to Y-m-d" del rango.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rango = $this->input('rango_fechas');

            if (!$rango || $validator->errors()->has('rango_fechas')) {
                return;
            }

            $fechas = explode(' to ', $rango);

            if (count($fechas) > 2) {
                $validator->errors()->add('rango_fechas', 'El rango de fechas no tiene un formato válido.');
                return;
            }

            foreach ($fechas as $fecha) {
                $parsed = \DateTime::createFromFormat('Y-m-d', $fecha);
                if (!$parsed || $parsed->format('Y-m-d') !== $fecha) {
                    $validator->errors()->add('rango_fechas', 'El rango de fechas no tiene un formato válido.');
                    return;
                }
            }

            if (count($fechas) === 2 && Carbon::parse($fechas[0])->gt(Carbon::parse($fechas[1]))) {
                $validator->errors()->add('rango_fechas', 'La fecha inicial no puede ser mayor que la final.');
            }
        });
    }

    public function filtro(string $campo): mixed
    {
        $valor = $this->validated($campo);

        return in_array($campo, ['consultorio_id', 'medico_id'], true) && $valor !== null
            ? (int) $valor
            : $valor;
    }
}
