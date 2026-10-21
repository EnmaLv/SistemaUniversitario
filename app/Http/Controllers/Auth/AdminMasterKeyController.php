<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Bitacora as BitacoraModelo;
use App\Support\Auditoria\Bitacora;
use App\Support\Seguridad\Bloqueos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class AdminMasterKeyController extends Controller
{
    public function showForm()
    {
        if (!session('pending_admin_id')) {
            return redirect()->route('login');
        }

        return view('auth.admin_master_key');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'master_key' => ['required', 'string'],
        ]);

        $id = session('pending_admin_id');
        if (!$id) {
            return redirect()->route('login')->withErrors(['email' => 'La sesión expiró. Inicia sesión de nuevo.']);
        }

        $user = Usuario::find($id);
        if (!$user) {
            session()->forget('pending_admin_id');
            return redirect()->route('login')->withErrors(['email' => Bloqueos::MENSAJE_GENERICO]);
        }

        if ($estado = Bloqueos::estado($user)) {
            $this->descartarSesion($request);
            return redirect()->route('login')->withErrors(['email' => Bloqueos::mensaje($estado)]);
        }

        if ($user->verifyMasterKey($request->input('master_key'))) {
            session()->forget('pending_admin_id');
            Auth::loginUsingId($user->id_usuario ?? $user->id);
            $request->session()->regenerate();
            session(['ultima_actividad' => time()]);

            Bloqueos::registrarLoginExitoso($user, $request);
            \App\Support\Sedes\SedeActiva::inicializar($user);
            session(['sede_inicializada_para' => (int) $user->getKey()]);
            Bitacora::registrar(accion: 'llave_maestra_correcta', modulo: 'autenticacion', observacion: 'Inicio de sesión de administrador', usuario: $user);
            Bitacora::registrar(accion: 'login_exitoso', modulo: 'autenticacion', usuario: $user);

            $destino = (new \App\AdminLTE\Filters\ModuleFilter())->resolveInitialRoute($user->id_usuario ?? $user->id);
            return redirect()->intended($destino);
        }

        Bitacora::registrar(
            accion: 'llave_maestra_incorrecta',
            modulo: 'autenticacion',
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: sprintf('Inicio de sesión de administrador. Intento %d de %d', (int) $user->intentos_llave_fallidos + 1, (int) config('seguridad.max_intentos_llave', 3)),
            usuario: $user,
        );

        if ($estado = Bloqueos::registrarFalloLlave($user)) {
            $this->descartarSesion($request);
            return redirect()->route('login')->withErrors(['email' => Bloqueos::mensaje($estado)]);
        }

        return back()->withErrors(['master_key' => 'Llave maestra incorrecta.']);
    }

    private function descartarSesion(Request $request): void
    {
        $request->session()->forget('pending_admin_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
