<?php

namespace App\Support\Permisos;

use Illuminate\Support\Str;

class ResolutorAccion
{
    public static function desdeRuta(?string $nombreRuta, string $metodo): string
    {
        $metodo = strtoupper($metodo);

        if ($nombreRuta) {
            $forzadas = config('permisos.rutas_accion', []);
            if (isset($forzadas[$nombreRuta])) {
                return $forzadas[$nombreRuta];
            }

            $segmentos = array_slice(array_reverse(explode('.', $nombreRuta)), 0, 2);

            foreach ($segmentos as $segmento) {
                $segmento = Str::snake($segmento);
                foreach (config('permisos.sufijos', []) as $accion => $patrones) {
                    if (Str::is($patrones, $segmento)) {
                        return $accion;
                    }
                }
            }
        }

        if ($metodo === 'DELETE') {
            return 'eliminar';
        }

        return in_array($metodo, ['GET', 'HEAD', 'OPTIONS'], true) ? 'ver' : 'editar';
    }

    public static function acciones(): array
    {
        return array_keys(config('permisos.acciones', []));
    }

    public static function etiqueta(string $accion): string
    {
        return config("permisos.acciones.{$accion}.label", ucfirst($accion));
    }
}
