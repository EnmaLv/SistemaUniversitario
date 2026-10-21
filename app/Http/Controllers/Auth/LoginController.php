<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Auditoria\Bitacora;
use App\Support\Seguridad\Bloqueos;
use App\Support\Seguridad\ProteccionLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as AuthFacade;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        logout as protected cerrarSesion;
    }

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $resultado = ProteccionLogin::intentar(
            (string) $request->input('email'),
            (string) $request->input('password'),
            (string) $request->ip()
        );

        $usuario = $resultado['usuario'];
        if (! $usuario) {
            return back()->withInput($request->only('email'))->withErrors(['email' => $resultado['mensaje']]);
        }

        $perfil = $usuario->perfil()->first();
        if ($perfil && $perfil->nombre_perfil === 'Administrador') {
            $request->session()->regenerate();
            session(['pending_admin_id' => $usuario->id_usuario]);

            Bitacora::registrar(
                accion: 'login_credenciales_validas',
                modulo: 'autenticacion',
                observacion: 'Contraseña correcta; pendiente de llave maestra',
                usuario: $usuario,
            );

            return redirect()->route('admin.master_key.form');
        }

        $request->session()->forget('pending_admin_id');
        AuthFacade::login($usuario);
        $request->session()->regenerate();
        session(['ultima_actividad' => time()]);

        Bloqueos::registrarLoginExitoso($usuario, $request);
        \App\Support\Sedes\SedeActiva::inicializar($usuario);
        session(['sede_inicializada_para' => (int) $usuario->getKey()]);
        Bitacora::registrar(accion: 'login_exitoso', modulo: 'autenticacion', usuario: $usuario);

        $destino = (new \App\AdminLTE\Filters\ModuleFilter())->resolveInitialRoute($usuario->id_usuario ?? $usuario->id);
        return redirect()->intended($destino);
    }

    public function logout(Request $request)
    {
        $usuario = $request->user();

        if ($usuario) {
            Bitacora::registrar(accion: 'logout', modulo: 'autenticacion', usuario: $usuario);
        }

        return $this->cerrarSesion($request);
    }
}
