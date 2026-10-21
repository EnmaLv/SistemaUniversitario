<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productoParam = $this->route('producto') ?? $this->route('id');
        $productoId = is_object($productoParam) ? $productoParam->id_pdt : $productoParam;

        return [
            'categoria_id' => 'required|exists:categoria_producto,id_cap',
            'codigo' => 'nullable|string|max:255',
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('producto', 'nombre_pdt')->ignore($productoId, 'id_pdt')
            ],

            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'precio_compra' => 'nullable|numeric',
            'stock_minimo_pdt' => 'required|integer',
            'stock_maximo_pdt' => 'required|integer',
            'peso_contenido_pdt'             => ['nullable', 'required_without:presentacion_id', 'numeric', 'min:0'],
            'unidades_presentacion_pdt'  => ['nullable', 'required_with:presentacion_id', 'numeric', 'min:0.001'],
            'unidad_id' => 'required|exists:unidad_medida,id_unm',
            'presentacion_id' => 'nullable|exists:envase_primario,id_enp',
            'estado' => 'nullable|boolean',
            'costo_usd_ppr' => 'sometimes|required|numeric|min:0',
            'presentacion_dispensacion_pdt' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un producto con este nombre',
        ];
    }
}
