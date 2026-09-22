<?php

namespace App\Http\Controllers;

use App\Http\Requests\BecaBeneficioRequest;
use App\Models\Becas\Beneficio;
use App\Services\BecaBeneficioService;
use Illuminate\Http\Request;

class BecaBeneficioController extends Controller
{
    public function __construct(private BecaBeneficioService $beneficioService)
    {
    }

    public function index(Request $request)
    {
        session(['modulo_activo' => 'beca']);

        $beneficios = $this->beneficioService->listar($request->only(['buscar', 'activo']));

        return view('admin.becas.beneficios.index', compact('beneficios'));
    }

    public function create()
    {
        session(['modulo_activo' => 'beca']);

        return view('admin.becas.beneficios.create');
    }

    public function store(BecaBeneficioRequest $request)
    {
        session(['modulo_activo' => 'beca']);

        $this->beneficioService->crear($request->validated());

        return redirect()
            ->route('admin.becas.beneficios.index')
            ->with('success', 'Beneficio registrado exitosamente.');
    }

    public function edit(Beneficio $beneficio)
    {
        session(['modulo_activo' => 'beca']);

        return view('admin.becas.beneficios.edit', compact('beneficio'));
    }

    public function update(BecaBeneficioRequest $request, Beneficio $beneficio)
    {
        session(['modulo_activo' => 'beca']);

        $this->beneficioService->actualizar($beneficio, $request->validated());

        return redirect()
            ->route('admin.becas.beneficios.index')
            ->with('success', 'Beneficio actualizado exitosamente.');
    }

    public function toggle(Beneficio $beneficio)
    {
        session(['modulo_activo' => 'beca']);

        $beneficio = $this->beneficioService->cambiarEstado($beneficio);
        $mensaje = $beneficio->status ? 'Beneficio activado exitosamente.' : 'Beneficio desactivado exitosamente.';

        return back()->with('success', $mensaje);
    }
}
