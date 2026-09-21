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
        $productoId = is_object($productoParam) ? $productoParam->id : $productoParam;

        return [
            'categoria_id' => 'required|exists:categorias,id',
            'codigo' => 'nullable|string|max:255',
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('productos', 'nombre')->ignore($productoId)
            ],

            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'precio_compra' => 'nullable|numeric',
            'stock_minimo' => 'required|integer',
            'stock_maximo' => 'required|integer',
            'peso_contenido'             => ['nullable', 'required_without:presentacion_id', 'numeric', 'min:0'],
            'unidades_por_presentacion'  => ['nullable', 'required_with:presentacion_id', 'numeric', 'min:0.001'],
            'unidad_id' => 'required|exists:unidades,id',
            'estado' => 'nullable|boolean',
            'costo_usd' => 'sometimes|required|numeric|min:0',
            'presentacion_dispensacion' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un producto con este nombre',
        ];
    }
}
