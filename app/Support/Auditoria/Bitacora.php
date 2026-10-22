<?php

namespace App\Support\Auditoria;

use App\Models\Bitacora as BitacoraModelo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Bitacora
{
    protected static bool $tablaConfirmada = false;

    public static function registrar(
        string $accion,
        ?string $modulo = null,
        ?string $tablaAfectada = null,
        int|string|null $registroId = null,
        ?array $datosAnteriores = null,
        ?array $datosModificados = null,
        string $nivel = BitacoraModelo::NIVEL_INFO,
        ?string $observacion = null,
        ?Usuario $usuario = null,
        ?string $nombreUsuario = null,
        ?int $codigoRespuesta = null,
    ): ?BitacoraModelo {
        if (! config('seguridad.bitacora.habilitada', true)) {
            return null;
        }

        try {
            if (! static::tablaDisponible()) {
                return null;
            }

            $request = static::peticionActual();
            $usuario ??= static::usuarioActual();
            $agente = $request ? AgenteUsuario::analizar($request->userAgent()) : ['navegador' => 'Consola', 'sistema_operativo_bis' => PHP_OS_FAMILY];

            if ($registroId !== null && ! ctype_digit((string) $registroId)) {
                $observacion = trim(($observacion ?? '') . ' [clave: ' . $registroId . ']');
                $registroId = null;
            }

            return BitacoraModelo::create([
                'modulo_bis'            => Str::limit($modulo ?? FiltroPeticiones::moduloDe($request), 100, ''),
                'tipo_evento_bis'       => Str::limit($accion, 100, ''),
                'tabla_afectada_bis'    => $tablaAfectada,
                'registro_id_bis'       => $registroId,
                'use_id'                => $usuario?->getKey(),
                'created_id'            => $usuario?->getKey(),
                'nombre_usuario_bis'    => Str::limit($nombreUsuario ?? $usuario?->username_use ?? '', 255, '') ?: null,
                'datos_anteriores_bis'  => DatosSensibles::limpiar($datosAnteriores),
                'datos_nuevos_bis'      => DatosSensibles::limpiar($datosModificados),
                'direccion_ip_bis'      => $request?->ip(),
                'navegador_bis'         => Str::limit($agente['navegador'], 255, ''),
                'sistema_operativo_bis' => Str::limit($agente['sistema_operativo'], 100, ''),
                'metodo_http_bis'       => $request?->method(),
                'ruta_bis'              => $request ? Str::limit($request->route()?->getName() ?? $request->path(), 191, '') : null,
                'url_bis'               => $request ? DatosSensibles::urlSegura($request) : null,
                'codigo_respuesta_bis'  => $codigoRespuesta,
                'nivel_bis'             => $nivel,
                'session_id_bis'        => $request && $request->hasSession() ? hash('sha256', $request->session()->getId()) : null,
                'observacion_bis'       => $observacion !== null ? Str::limit($observacion, 2000) : ($request ? null : 'consola'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar el evento en la bitácora.', [
                'accion' => $accion,
                'error'  => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected static function peticionActual(): ?Request
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }

        return app()->bound('request') ? app('request') : null;
    }

    protected static function usuarioActual(): ?Usuario
    {
        try {
            $usuario = Auth::user();
        } catch (\Throwable $e) {
            return null;
        }

        return $usuario instanceof Usuario ? $usuario : null;
    }

    protected static function tablaDisponible(): bool
    {
        if (static::$tablaConfirmada) {
            return true;
        }

        return static::$tablaConfirmada = Schema::hasTable('bitacora_sistema');
    }
}
