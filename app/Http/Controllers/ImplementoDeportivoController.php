<?php

namespace App\Http\Controllers;

use App\Models\TipoProducto;

class ImplementoDeportivoController extends ProductoPorTipoController
{
    protected function tipo(): string
    {
        return TipoProducto::IMPLEMENTO_DEPORTIVO;
    }

    protected function config(): array
    {
        return [
            'ruta'        => 'admin.deportes.implementos',
            'url'         => 'admin/deportes/implementos',
            'singular'    => 'Implemento deportivo',
            'plural'      => 'Implementos deportivos',
            'nuevo'       => 'Nuevo implemento',
            'icono'       => 'fa-futbol',
            'descripcion' => 'Registra los balones, raquetas, mallas y demás implementos que se prestan; aquí ves su precio y cuántos hay.',
            'ejemplo'     => 'Ej: Balón de fútbol N.º 5',
            'vacio'       => 'Todavía no hay implementos deportivos registrados. Pulsa "Nuevo implemento" para agregar el primero; luego podrás comprarlo en Compras.',
        ];
    }
}
