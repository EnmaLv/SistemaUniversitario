<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Modulo;

class ModuloController extends Controller
{
    public function seleccionarForm()
    {
        session()->forget(['modulo_activo']);

        if (is_null(session('modulos_permitidos'))) {
            $user = Auth::user();
            (new \App\AdminLTE\Filters\ModuleFilter)
                ->inicializarSesion($user->id_usuario ?? $user->id);
        }

        return redirect()->route('home');
    }

    public function cambiar(Request $request)
    {
        $request->validate(['modulo' => 'required|string']);

        $moduloKey = $request->input('modulo');

        $modulo = Modulo::where('key', $moduloKey)->where('activo', true)->first();
        if (! $modulo) {
            return redirect()->back()->with('error', 'Módulo no válido.');
        }

        $permitidos = session('modulos_permitidos', []);
        if (! in_array($moduloKey, $permitidos)) {
            return redirect()->back()->with('error', 'No tienes acceso a ese módulo.');
        }

        session(['modulo_activo' => $modulo->key]);

        return redirect()->route('home')
            ->with('success', 'Módulo cambiado a: ' . $modulo->nombre);
    }
}