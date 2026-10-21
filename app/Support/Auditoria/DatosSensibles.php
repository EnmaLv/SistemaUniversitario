<?php

namespace App\Support\Auditoria;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DatosSensibles
{
    public static function esSensible(string $campo): bool
    {
        $campo = mb_strtolower($campo);

        if (in_array($campo, config('seguridad.campos_sensibles', []), true)) {
            return true;
        }

        foreach (config('seguridad.patrones_sensibles', []) as $patron) {
            if (str_contains($campo, $patron)) {
                return true;
            }
        }

        return false;
    }

    public static function limpiar(?array $datos): ?array
    {
        if ($datos === null) {
            return null;
        }

        $limite = (int) config('seguridad.bitacora.max_caracteres_valor', 1000);
        $limpios = [];

        foreach ($datos as $campo => $valor) {
            if (is_string($campo) && static::esSensible($campo)) {
                continue;
            }

            if (is_array($valor)) {
                $limpios[$campo] = static::limpiar($valor);
                continue;
            }

            if ($valor instanceof \DateTimeInterface) {
                $limpios[$campo] = $valor->format('Y-m-d H:i:s');
                continue;
            }

            if ($valor instanceof \BackedEnum) {
                $valor = $valor->value;
            }

            if (is_object($valor)) {
                $valor = method_exists($valor, '__toString') ? (string) $valor : get_class($valor);
            }

            if (is_string($valor)) {
                $valor = mb_scrub($valor, 'UTF-8');
                if (mb_strlen($valor) > $limite) {
                    $valor = mb_substr($valor, 0, $limite) . '…[truncado]';
                }
            }

            $limpios[$campo] = $valor;
        }

        return $limpios;
    }

    public static function camposSensiblesEn(array $campos): array
    {
        return array_values(array_filter($campos, fn ($campo) => is_string($campo) && static::esSensible($campo)));
    }

    public static function urlSegura(Request $request): string
    {
        $query = $request->query();

        foreach (array_keys($query) as $clave) {
            if (static::esSensible((string) $clave)) {
                unset($query[$clave]);
            }
        }

        $url = $request->url() . ($query ? '?' . http_build_query($query) : '');

        return Str::limit($url, 2040, '');
    }
}
