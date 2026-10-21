<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $proveedores = Proveedor::listarProveedores(
            $request->input('buscar'),
            $request->input('estado', 1)
        );

        return view('admin.maestros.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('admin.maestros.proveedores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'empresa'   => 'required|string|max:255',
            'rif'       => 'nullable|string|max:30',
            'direccion' => 'required|string|max:255',
            'nombre'    => 'required|string|max:255',
            'telefono'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:proveedor,email_prv',
        ]);

        $fromidreuse = Proveedor::crearProveedor($validated);

        $from = $request->input('from');

        if ($from) {
            return redirect($from . '?proveedor_id=' . $fromidreuse)
                ->with('success', 'Proveedor creado exitosamente.');
        } else {
            return redirect()->route('admin.maestros.proveedores.index')
                ->with('success', 'Proveedor creado exitosamente.');
        }
    }

    public function show($id)
    {
        $proveedor = Proveedor::obtenerProveedorConCompras($id);

        if (!$proveedor) {
            return redirect()
                ->route('admin.maestros.proveedores.index')
                ->with('error', 'Proveedor no encontrado.')
                ->with('icono', 'error');
        }

        return view('admin.maestros.proveedores.show', compact('proveedor'));
    }

    public function edit($id)
    {
        $proveedor = Proveedor::obtenerProveedor($id);

        if (!$proveedor) {
            return redirect()
                ->route('admin.maestros.proveedores.index')
                ->with('error', 'Proveedor no encontrado.')
                ->with('icono', 'error');
        }

        return view('admin.maestros.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'empresa'   => 'required|string|max:255',
            'rif'       => 'nullable|string|max:30',
            'direccion' => 'required|string|max:255',
            'nombre'    => 'required|string|max:255',
            'telefono'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:proveedor,email_prv,' . $id . ',id_prv',
        ]);

        Proveedor::actualizarProveedor($id, $validated);

        return redirect()
            ->route('admin.maestros.proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy($id)
    {
        if (Proveedor::tieneCompras($id)) {
            return redirect()
                ->route('admin.maestros.proveedores.index')
                ->with('error', 'No se puede eliminar el proveedor porque tiene compras asociadas.');
        }

        Proveedor::eliminarProveedor($id);

        return redirect()
            ->route('admin.maestros.proveedores.index')->with('success', 'Proveedor eliminado exitosamente.');
    }

    public function activar($id)
    {
        Proveedor::activarProveedor($id);
        return redirect()->route('admin.maestros.proveedores.index')->with('success', 'Proveedor activado exitosamente.');
    }
}
