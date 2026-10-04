<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            return redirect()->route('login')->withErrors(['email' => 'Session expired, please login again.']);
        }

        $user = Usuario::find($id);
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        if ($user->verifyMasterKey($request->input('master_key'))) {
            session()->forget('pending_admin_id');
            Auth::loginUsingId($user->id_usuario ?? $user->id);
            $request->session()->regenerate();
            $destino = (new \App\AdminLTE\Filters\ModuleFilter())->resolveInitialRoute($user->id_usuario ?? $user->id);
            return redirect()->intended($destino);
        }

        return back()->withErrors(['master_key' => 'Llave maestra incorrecta.']);
    }
}
