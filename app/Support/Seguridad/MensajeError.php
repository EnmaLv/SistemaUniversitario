<?php

namespace App\Support\Seguridad;

use Illuminate\Database\QueryException;
use PDOException;
use Throwable;

class MensajeError
{
    public const GENERICO = 'Ocurrió un error inesperado. Intenta de nuevo o contacta al administrador.';

    public static function de(Throwable $e): string
    {
        if ($e instanceof QueryException || $e instanceof PDOException || $e instanceof \Error || trim($e->getMessage()) === '') {
            report($e);

            return self::GENERICO;
        }

        return $e->getMessage();
    }
}
