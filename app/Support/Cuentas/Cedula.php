<?php

namespace App\Support\Cuentas;

class Cedula
{
    public const MIN_DIGITOS = 6;

    public const MAX_DIGITOS = 10;

    public static function normalizar(?string $valor): ?string
    {
        $valor = mb_strtoupper(trim((string) $valor));

        if ($valor === '') {
            return null;
        }

        $valor = preg_replace('/^[VE]\s*[-.]?\s*/u', '', $valor);
        $valor = preg_replace('/[\s.\-]/u', '', $valor);

        if (! preg_match('/^\d+$/', $valor)) {
            return null;
        }

        $largo = strlen($valor);

        return $largo >= self::MIN_DIGITOS && $largo <= self::MAX_DIGITOS ? $valor : null;
    }

    public static function esCorreo(string $valor): bool
    {
        return str_contains($valor, '@');
    }
}
