<?php

namespace App\Http\Requests;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\salud\EnvasePrimario;
use Illuminate\Foundation\Http\FormRequest;

class MedicamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['required', Categoria::reglaDeTipos($this->tiposMedicamento())],
            'codigo' => 'nullable|string|max:255',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'precio_compra' => 'nullable|numeric',
            'stock_minimo_pdt' => 'required|numeric',
            'stock_maximo_pdt' => 'required|numeric',
            'unidad_id' => 'required|exists:unidad_medida,id_unm',
            'envase_primario_id' => ['nullable', EnvasePrimario::reglaDeTipos($this->tiposMedicamento())],
            'presentacion_id' => ['nullable', EnvasePrimario::reglaDeTipos($this->tiposMedicamento())],
            'unidades_presentacion_pdt' => 'nullable|numeric|min:0.001',
            'presentacion_dispensacion_pdt' => 'nullable|string|max:50',
            'estado' => 'nullable|boolean',
            'costo_usd_ppr' => 'required|numeric|min:0',
            'margen' => 'nullable|numeric|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un producto con este nombre',
            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.exists' => 'Elige una categoría de medicamentos de la lista.',
            'presentacion_id.exists' => 'Elige un envase de medicamentos de la lista.',
            'envase_primario_id.exists' => 'Elige un envase de medicamentos de la lista.',
            'costo_usd_ppr.required' => 'El costo en USD es obligatorio.'
        ];
    }

    private function tiposMedicamento(): array
    {
        return array_filter([TipoProducto::idPorNombre(TipoProducto::MEDICAMENTO)]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $nombre = $this->input('nombre');
            $id = $this->route('medicamento') ?? $this->route('producto'); // Captura el ID según la ruta

            if ($this->isMethod('post')) {
                $exists = Producto::where('nombre_pdt', $nombre)->exists();
                if ($exists) {
                    $validator->errors()->add('nombre', 'Ya existe un producto con este nombre');
                }
            } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
                $exists = Producto::where('nombre_pdt', $nombre)->where('id_pdt', '!=', $id)->exists();
                if ($exists) {
                    $validator->errors()->add('nombre', 'Ya existe un producto con este nombre');
                }
            }
        });
    }
}    