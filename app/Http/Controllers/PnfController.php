<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pnf;
use App\Models\PersonaPnf;

class PnfController extends Controller
{
    public function index(Request $request)
    {

        $pnfs = Pnf::getPnfs($request);
        return view('admin.maestros.pnf.index', compact('pnfs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
        ]);

        $fromidreuse = Pnf::createPnf($request);

        $from = $request->input('from');

        if ($from) {
            return redirect($from . '?pnf_id=' . $fromidreuse->id)
                ->with('success', 'PNF creado exitosamente');
        } else {
            return redirect()->route('admin.maestros.pnf.index')
                ->with('success', 'PNF creado exitosamente');
        }
    }

    public function edit($id)
    {
        $pnf = Pnf::where('id_pnf', $id)->first();
        return view('admin.maestros.pnf.edit', compact('pnf'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'id_estatus' => 'required|numeric',
        ]);

        Pnf::updatePnf($request);

        return redirect()->route('admin.maestros.pnf.index')->with('success', 'PNF actualizado Exitosamente');
    }

    public function destroy($id)
    {
        $personaPnf = PersonaPnf::where('id_pnf', $id)->first();
        if ($personaPnf) {
            return redirect()->route('admin.maestros.pnf.index')->with('error', 'El PNF tiene personas asociadas. No se puede desactivar.');
        }

        $resultado = Pnf::activarPnf($id);

        if ($resultado) {
            return redirect()
                ->route('admin.maestros.pnf.index')
                ->with('success', 'Estado del PNF actualizado Exitosamente');
        }
        return redirect()
            ->route('admin.maestros.pnf.index')
            ->with('error', 'No se pudo actualizar el estado del PNF');
    }

    public function activar($id)
    {
        Pnf::activarPnf($id);

        return redirect()->route('admin.maestros.pnf.index')->with('success', 'PNF activado Exitosamente');
    }
}
