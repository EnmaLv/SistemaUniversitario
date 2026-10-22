<?php

namespace App\Http\Controllers;

use App\Models\TipoProducto;

class MaterialOficinaController extends ProductoPorTipoController
{
    protected function tipo(): string
    {
        return TipoProducto::MATERIAL_OFICINA;
    }

    protected function config(): array
    {
        return [
            'ruta'        => 'admin.maestros.material_oficina',
            'url'         => 'admin/maestros/material-oficina',
            'singular'    => 'Material de oficina',
            'plural'      => 'Material de oficina',
            'nuevo'       => 'Nuevo material',
            'icono'       => 'fa-paperclip',
            'descripcion' => 'Registra el papel, los lápices, cuadernos y demás material de oficina que se entrega; aquí ves su precio y cuánto hay.',
            'ejemplo'     => 'Ej: Resma de papel carta',
            'vacio'       => 'Todavía no hay material de oficina registrado. Pulsa "Nuevo material" para agregar el primero; luego podrás comprarlo en Compras.',
        ];
    }
}
