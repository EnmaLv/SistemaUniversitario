<?php

namespace App\Http\Requests\Salud;

use App\Services\Salud\RecetaService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Paso 2: guardar / actualizar la receta médica.
 */
class RecetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'fecha'       => ['required', 'date'],
            'vigencia'    => ['nullable', 'date', 'after_or_equal:fecha'],
            'descripcion' => ['nullable', 'string', 'max:500'],

            'detalles'                   => ['required', 'array', 'min:1', 'max:50'],
            'detalles.*.producto_id'     => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.cantidad'        => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'detalles.*.unidad_id'       => ['required', 'integer', 'exists:unidades,id'],
            'detalles.*.frecuencia'      => ['required', 'string', 'max:255'],
            'detalles.*.equivalencia_ml' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'detalles.*.fecha_inicio'    => ['nullable', 'date', 'after_or_equal:fecha'],
            'detalles.*.fecha_fin'       => ['nullable', 'date', 'after_or_equal:fecha'],
            'detalles.*.observaciones'   => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Validaciones que dependen de varios campos o de la BD.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $detalles = $this->input('detalles', []);

            // 1. Solo productos activos del tipo salud
            $idsSolicitados = collect($detalles)->pluck('producto_id')->map(fn($id) => (int) $id)->unique();
            $idsValidos = app(RecetaService::class)
                ->productosSaludQuery()
                ->whereIn('id', $idsSolicitados)
                ->pluck('id')
                ->map(fn($id) => (int) $id);

            foreach ($detalles as $i => $item) {
                $n = is_numeric($i) ? (int) $i + 1 : $i;

                if (!$idsValidos->contains((int) $item['producto_id'])) {
                    $validator->errors()->add(
                        "detalles.{$i}.producto_id",
                        "Medicamento #{$n}: el producto no está activo o no es de tipo salud."
                    );
                }

                // 2. fecha_fin >= fecha_inicio dentro del mismo ítem
                if (!empty($item['fecha_inicio']) && !empty($item['fecha_fin'])
                    && Carbon::parse($item['fecha_fin'])->lt(Carbon::parse($item['fecha_inicio']))) {
                    $validator->errors()->add(
                        "detalles.{$i}.fecha_fin",
                        "Medicamento #{$n}: la fecha fin no puede ser anterior a la fecha de inicio."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'vigencia.after_or_equal'            => 'La vigencia no puede ser anterior a la fecha de la receta.',
            'detalles.required'                  => 'Debe agregar al menos un medicamento.',
            'detalles.min'                       => 'Debe agregar al menos un medicamento.',
            'detalles.*.producto_id.required'    => 'Seleccione el medicamento.',
            'detalles.*.producto_id.exists'      => 'El medicamento seleccionado no existe.',
            'detalles.*.cantidad.min'            => 'La cantidad debe ser mayor a 0.',
            'detalles.*.unidad_id.required'      => 'Seleccione la unidad.',
            'detalles.*.frecuencia.required'     => 'Indique la frecuencia.',
            'detalles.*.fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a la fecha de la receta.',
            'detalles.*.fecha_fin.after_or_equal'    => 'La fecha fin no puede ser anterior a la fecha de la receta.',
        ];
    }

    public function attributes(): array
    {
        return [
            'fecha'                      => 'fecha',
            'vigencia'                   => 'vigencia',
            'descripcion'                => 'descripción',
            'detalles.*.producto_id'     => 'medicamento',
            'detalles.*.cantidad'        => 'cantidad',
            'detalles.*.unidad_id'       => 'unidad',
            'detalles.*.frecuencia'      => 'frecuencia',
            'detalles.*.equivalencia_ml' => 'equivalencia (ml)',
            'detalles.*.fecha_inicio'    => 'fecha de inicio',
            'detalles.*.fecha_fin'       => 'fecha fin',
            'detalles.*.observaciones'   => 'observaciones',
        ];
    }
}
