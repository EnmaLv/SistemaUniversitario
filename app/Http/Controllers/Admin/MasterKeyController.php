<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfiguracionSistema;
use Illuminate\Support\Facades\Auth;
use App\Support\Auditoria\Bitacora;

class MasterKeyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showForm()
    {
        $user = Auth::user();
        abort_unless($user && $user->esSuperAdmin(), 403);

        return view('admin.master_key_manage');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->esSuperAdmin(), 403);

        $request->validate([
            'current_master_key' => 'required|string',
            'new_master_key' => 'required|string|min:6|confirmed',
        ]);

        if (!ConfiguracionSistema::checkMasterKey($request->current_master_key)) {
            Bitacora::registrar('llave_maestra_global_incorrecta', 'administracion', nivel: \App\Models\Bitacora::NIVEL_ADVERTENCIA);

            return back()->withErrors(['current_master_key' => 'Llave maestra actual inválida.']);
        }

        ConfiguracionSistema::updateMasterKey($request->new_master_key);
        Bitacora::registrar('llave_maestra_global_actualizada', 'administracion', 'configuracion_sistema', nivel: \App\Models\Bitacora::NIVEL_CRITICO);
        return back()->with('status', 'Llave maestra actualizada Exitosamente.');
    }
}
