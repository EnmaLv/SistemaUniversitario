<?php

namespace App\Http\Requests\Salud;

use App\Models\salud\Consulta;
use App\Services\Salud\DispensacionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Paso 3: registrar la dispensación de medicamentos.
 */
class DispensacionRequest extends FormRequest
{
    private const VALORES_CHECKBOX = '0,1,on,off,true,false';

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'items'                 => ['nullable', 'array'],
            'items.*'               => ['array'],
            'items.*.dispensar'     => ['nullable', 'in:' . self::VALORES_CHECKBOX],
            'items.*.cantidad'      => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'items.*.observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Valida los ítems contra los detalles reales de la receta.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Consulta|null $consulta */
            $consulta = $this->route('consulta');
            $receta   = $consulta?->receta;

            // Sin receta: el controlador responde con su propio mensaje
            if (!$receta) {
                return;
            }

            $detalles = $receta->detalles()->with('producto')->get()->keyBy('id');

            foreach ($this->input('items', []) as $detalleId => $item) {
                $detalle = $detalles->get((int) $detalleId);

                if (!$detalle) {
                    $validator->errors()->add(
                        "items.{$detalleId}",
                        'Uno de los medicamentos enviados no pertenece a esta receta.'
                    );
                    continue;
                }

                if (!DispensacionService::marcadoParaDispensar($item)) {
                    continue;
                }

                $nombre    = $detalle->producto->nombre ?? 'medicamento';
                $cantidad  = (float) ($item['cantidad'] ?? 0);
                $pendiente = (float) $detalle->cantidad_pendiente;

                if ($cantidad <= 0) {
                    $validator->errors()->add(
                        "items.{$detalleId}.cantidad",
                        "Indique una cantidad mayor a 0 para {$nombre}."
                    );
                    continue;
                }

                if ($pendiente <= 0) {
                    $validator->errors()->add(
                        "items.{$detalleId}.cantidad",
                        "{$nombre} ya fue entregado en su totalidad."
                    );
                    continue;
                }

                if ($cantidad > $pendiente) {
                    $validator->errors()->add(
                        "items.{$detalleId}.cantidad",
                        "La cantidad de {$nombre} ({$cantidad}) supera lo pendiente ({$pendiente})."
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'items.*.dispensar'     => 'dispensar',
            'items.*.cantidad'      => 'cantidad',
            'items.*.observaciones' => 'observaciones',
        ];
    }

    /**
     * Ítems indexados por ID de detalle.
     */
    public function itemsDispensacion(): array
    {
        return $this->validated('items') ?? [];
    }
}
