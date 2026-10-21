<?php

namespace App\Http\Controllers;

use App\Support\Auditoria\Bitacora;
use App\Support\Sedes\SedeActiva;
use Illuminate\Http\Request;

class SedeActivaController extends Controller
{
    public function cambiar(Request $request)
    {
        abort_unless(SedeActiva::puedeElegir(), 403, 'Solo los administradores pueden cambiar de sede.');

        $datos = $request->validate([
            'sed_id' => ['nullable', 'integer', 'exists:sede,id_sed'],
        ]);

        $anterior = SedeActiva::id();
        $nueva = isset($datos['sed_id']) ? (int) $datos['sed_id'] : null;

        SedeActiva::fijar($nueva);

        Bitacora::registrar(
            accion: 'sede_activa_cambiada',
            modulo: 'administracion',
            datosAnteriores: ['sede_activa' => $anterior ?? 'todas'],
            datosModificados: ['sede_activa' => $nueva ?? 'todas'],
        );

        return back()->with('success', $nueva === null ? 'Ahora ves todas las sedes.' : 'Sede activa actualizada.');
    }
}
