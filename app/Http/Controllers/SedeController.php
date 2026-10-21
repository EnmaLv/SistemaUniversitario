<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SedeController extends Controller
{
    public function index(Request $request)
    {
        $sedes = Sede::listarSedes(
            $request->input('buscar'),
            $request->input('activo', 1)
        );
        return view('admin.maestros.sedes.index', compact('sedes'));
    }

    public function create()
    {
        return view('admin.maestros.sedes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                'unique:sede,nombre_sed'
            ],
            'direccion' => 'required|string|max:255',
            'telefono'  => 'required|string|max:20',
        ], [
            'nombre.unique' => 'Ya existe una sede con este nombre',
        ]);

        $fromidreuse = Sede::crearSede($validated);

        $from = $request->input('from');

        if ($from) {
            return redirect($from . '?sed_id=' . $fromidreuse)
                ->with('success', 'Sede creada exitosamente.');
        } else {
            return redirect()->route('admin.maestros.sedes.index')
                ->with('success', 'Sede creada exitosamente.');
        }
    }

    public function show($id)
    {
        $sede = Sede::obtenerSedeConInventario($id);

        if (!$sede) {
            return redirect()
                ->route('admin.maestros.sedes.index')
                ->with('error', 'Sede no encontrada.')
                ->with('icono', 'error');
        }

        $estadisticas = Sede::obtenerEstadisticas($id);
        return view('admin.maestros.sedes.show', compact('sede', 'estadisticas'));
    }

    public function edit($id)
    {
        $sede = Sede::obtenerSede($id);
        if (!$sede) {
            return redirect()
                ->route('admin.maestros.sedes.index')
                ->with('error', 'Sede no encontrada.')
                ->with('icono', 'error');
        }
        return view('admin.maestros.sedes.edit', compact('sede'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sede', 'nombre_sed')->ignore($id, 'id_sed'),
            ],
            'direccion' => 'required|string|max:255',
            'telefono'  => 'required|string|max:20',
        ], [
            'nombre.unique' => 'Ya existe una sede con este nombre',
        ]);

        Sede::actualizarSede($id, $validated);

        return redirect()
            ->route('admin.maestros.sedes.index')
            ->with('success', 'Sede actualizada exitosamente.')
            ->with('icono', 'success');
    }

    public function destroy($id)
    {
        if (Sede::tieneInventario($id)) {
            return redirect()
                ->route('admin.maestros.sedes.index')
                ->with('error', 'No se puede eliminar la sede porque tiene inventario asociado.')
                ->with('icono', 'error');
        }

        if (Sede::tieneMovimientos($id)) {
            return redirect()
                ->route('admin.maestros.sedes.index')
                ->with('error', 'No se puede eliminar la sede porque tiene movimientos de inventario.')
                ->with('icono', 'error');
        }

        Sede::eliminarSede($id);

        return redirect()
            ->route('admin.maestros.sedes.index')
            ->with('success', 'Sede eliminada exitosamente.')
            ->with('icono', 'success');
    }

    public function activar($id)
    {
        Sede::activarSede($id);
        return redirect()->route('admin.maestros.sedes.index')->with('success', 'Sede activada exitosamente.');
    }

    public function exportCsv(Request $request)
    {
        $rows = Sede::exportarCSV(
            $request->input('buscar'),
            $request->input('activo')
        );

        $filename = 'sedes_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Nombre', 'Dirección', 'Teléfono', 'Estado']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->id_sed,
                    $row->nombre_sed,
                    $row->direccion_sed,
                    $row->telefono_sed,
                    (int) $row->status_id === 1 ? 'Activo' : 'Inactivo'
                ]);
            }

            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }
}