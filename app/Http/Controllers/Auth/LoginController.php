<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use App\Services\AuthService;
use Illuminate\Support\Facades\Auth as AuthFacade;


class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('email');
        $password = $request->input('password');

        $authService = new AuthService();
        $usuario = $authService->validateCredentials($username, $password);
        if (!$usuario) {
            return back()->withErrors(['email' => 'Credenciales inválidas.']);
        }

        $perfil = $usuario->perfil()->first();
        if ($perfil && $perfil->nombre_perfil === 'Administrador') {
            session(['pending_admin_id' => $usuario->id_usuario]);
            return redirect()->route('admin.configuracion.master_key.form');
        }

        $request->session()->forget('pending_admin_id');
        AuthFacade::login($usuario);
        $request->session()->regenerate();
        $destino = (new \App\AdminLTE\Filters\ModuleFilter())->resolveInitialRoute($usuario->id_usuario ?? $usuario->id);
        return redirect()->intended($destino);
    }
}
