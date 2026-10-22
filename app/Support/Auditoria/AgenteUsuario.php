<?php

namespace App\Support\Auditoria;

class AgenteUsuario
{
    private const NAVEGADORES = [
        ['Edge', '/Edg(?:e|A|iOS)?\/([\d.]+)/'],
        ['Opera', '/(?:OPR|Opera)\/([\d.]+)/'],
        ['Samsung Internet', '/SamsungBrowser\/([\d.]+)/'],
        ['Vivaldi', '/Vivaldi\/([\d.]+)/'],
        ['Brave', '/Brave\/([\d.]+)/'],
        ['Firefox', '/(?:Firefox|FxiOS)\/([\d.]+)/'],
        ['Chrome', '/(?:Chrome|CriOS)\/([\d.]+)/'],
        ['Safari', '/Version\/([\d.]+).*Safari\//'],
        ['Internet Explorer', '/(?:MSIE |Trident\/.*rv:)([\d.]+)/'],
        ['Dart', '/Dart\/([\d.]+)/'],
        ['okhttp', '/okhttp\/([\d.]+)/'],
        ['curl', '/curl\/([\d.]+)/'],
        ['Postman', '/PostmanRuntime\/([\d.]+)/'],
        ['Symfony', '/Symfony/'],
    ];

    private const WINDOWS = [
        '10.0' => '10/11',
        '6.3'  => '8.1',
        '6.2'  => '8',
        '6.1'  => '7',
        '6.0'  => 'Vista',
        '5.1'  => 'XP',
    ];

    public static function analizar(?string $agente): array
    {
        $agente = (string) $agente;

        return [
            'navegador'         => static::navegador($agente),
            'sistema_operativo' => static::sistemaOperativo($agente),
        ];
    }

    public static function navegador(string $agente): string
    {
        if ($agente === '') {
            return 'Desconocido';
        }

        foreach (self::NAVEGADORES as [$nombre, $patron]) {
            if (preg_match($patron, $agente, $m)) {
                return isset($m[1]) ? $nombre . ' ' . static::versionCorta($m[1]) : $nombre;
            }
        }

        return 'Otro';
    }

    public static function sistemaOperativo(string $agente): string
    {
        if ($agente === '') {
            return 'Desconocido';
        }

        if (preg_match('/Windows NT ([\d.]+)/', $agente, $m)) {
            return 'Windows ' . (self::WINDOWS[$m[1]] ?? $m[1]);
        }

        if (preg_match('/Android ([\d.]+)/', $agente, $m)) {
            return 'Android ' . $m[1];
        }

        if (str_contains($agente, 'Android')) {
            return 'Android';
        }

        if (preg_match('/(?:iPhone|iPad|iPod).*? OS ([\d_]+)/', $agente, $m)) {
            return 'iOS ' . str_replace('_', '.', $m[1]);
        }

        if (preg_match('/Mac OS X ([\d_.]+)/', $agente, $m)) {
            return 'macOS ' . str_replace('_', '.', $m[1]);
        }

        if (str_contains($agente, 'CrOS')) {
            return 'ChromeOS';
        }

        foreach (['Ubuntu', 'Fedora', 'Debian', 'Arch', 'CachyOS'] as $distro) {
            if (stripos($agente, $distro) !== false) {
                return 'Linux (' . $distro . ')';
            }
        }

        if (str_contains($agente, 'Linux') || str_contains($agente, 'X11')) {
            return 'Linux';
        }

        return 'Otro';
    }

    private static function versionCorta(string $version): string
    {
        $partes = explode('.', $version);

        return implode('.', array_slice($partes, 0, 2));
    }
}
