<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Support\Seguridad\Bloqueos;
use Illuminate\Http\Request;

class SeguridadBloqueosController extends Controller
{
    public const MOTIVOS = [
        Bloqueos::MOTIVO_CREDENCIALES => 'Contraseñas incorrectas',
        Bloqueos::MOTIVO_LLAVE        => 'Llave maestra incorrecta',
        Bloqueos::MOTIVO_RECUPERACION => 'Recuperación fallida',
        Bloqueos::MOTIVO_TEMPORALES   => 'Bloqueos temporales repetidos',
        Bloqueos::MOTIVO_ACCESOS      => 'Accesos restringidos repetidos',
    ];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\RequireMasterKey::class);
    }

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $tipo = $request->input('tipo');

        $usuarios = Usuario::with('persona')
            ->where(function ($q) use ($tipo) {
                if ($tipo !== 'temporal') {
                    $q->orWhere('bloqueado', true);
                }
                if ($tipo !== 'permanente') {
                    $q->orWhere(fn ($sub) => $sub->where('bloqueado', false)->where('bloqueado_hasta', '>', now()));
                }
            })
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->where(function ($sub) use ($busqueda) {
                    $sub->where('username', 'like', "%{$busqueda}%")
                        ->orWhereHas('persona', fn ($p) => $p->where('nombres_per', 'like', "%{$busqueda}%")
                            ->orWhere('apellidos_per', 'like', "%{$busqueda}%"));
                });
            })
            ->orderByDesc('bloqueado_en')
            ->paginate(10)
            ->withQueryString();

        $motivos = self::MOTIVOS;

        return view('admin.configuracion.seguridad.bloqueos.index', compact('usuarios', 'motivos', 'busqueda', 'tipo'));
    }

    public function desbloquear(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $responsable = $request->user();

        if ((int) $usuario->id_usuario === (int) $responsable->id_usuario) {
            return redirect()
                ->route('admin.configuracion.seguridad.bloqueos.index')
                ->with('error', 'No puedes desbloquear tu propia cuenta. Pide a otro administrador que lo haga.');
        }

        if (! Bloqueos::estaBloqueado($usuario) && (int) $usuario->bloqueos_temporales === 0 && (int) $usuario->intentos_fallidos === 0) {
            return redirect()
                ->route('admin.configuracion.seguridad.bloqueos.index')
                ->with('info', 'El usuario no tiene bloqueos activos.');
        }

        Bloqueos::desbloquear($usuario, $responsable);

        return redirect()
            ->route('admin.configuracion.seguridad.bloqueos.index')
            ->with('success', "Usuario {$usuario->username} desbloqueado exitosamente.");
    }
}
