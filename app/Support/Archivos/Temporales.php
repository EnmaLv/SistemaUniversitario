<?php

namespace App\Support\Archivos;

class Temporales
{
    public static function ruta(string $nombre): string
    {
        $directorio = storage_path('app/private/tmp');

        if (! is_dir($directorio)) {
            mkdir($directorio, 0700, true);
        }

        return $directorio . DIRECTORY_SEPARATOR . basename($nombre);
    }
}
