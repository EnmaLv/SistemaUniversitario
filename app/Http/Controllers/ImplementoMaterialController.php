<?php

namespace App\Http\Controllers;

use App\Models\TipoProducto;
use Illuminate\Http\Request;

class ImplementoMaterialController extends ProductoPorTipoController
{
    public const DEPORTIVO = 'deportivo';

    public const OFICINA = 'oficina';

    protected function tipos(): array
    {
        return [
            self::DEPORTIVO => [
                'nombre'   => TipoProducto::IMPLEMENTO_DEPORTIVO,
                'etiqueta' => 'Implemento deportivo',
                'filtro'   => 'Deportivo',
                'icono'    => 'fa-futbol',
                'ayuda'    => 'Balones, raquetas, mallas, petos, conos…',
                'ejemplo'  => 'Ej: Balón de fútbol N.º 5',
            ],
            self::OFICINA => [
                'nombre'   => TipoProducto::MATERIAL_OFICINA,
                'etiqueta' => 'Material de oficina',
                'filtro'   => 'Oficina',
                'icono'    => 'fa-paperclip',
                'ayuda'    => 'Lápices, marcadores, cuadernos, resmas de papel…',
                'ejemplo'  => 'Ej: Marcador acrílico azul',
            ],
        ];
    }

    protected function config(): array
    {
        return [
            'ruta'        => 'admin.deportes.implementos',
            'url'         => 'admin/deportes/implementos',
            'plural'      => 'Implementos y materiales',
            'nuevo'       => 'Nuevo implemento o material',
            'icono'       => 'fa-boxes',
            'descripcion' => 'Registra los implementos deportivos y el material de oficina que se prestan; aquí ves su precio y cuántos hay.',
            'vacio'       => 'Todavía no hay implementos ni materiales registrados. Pulsa "Nuevo implemento o material" para agregar el primero; luego podrás comprarlo en Compras.',
        ];
    }

    public function redirigirMaterialOficina(Request $request, $producto = null)
    {
        if ($producto !== null) {
            return redirect()->route('admin.deportes.implementos.edit', (int) $producto);
        }

        $accion = $request->routeIs('admin.maestros.material_oficina.create') ? 'create' : 'index';

        return redirect()->route('admin.deportes.implementos.' . $accion, array_merge($request->query(), ['tipo' => self::OFICINA]));
    }
}
