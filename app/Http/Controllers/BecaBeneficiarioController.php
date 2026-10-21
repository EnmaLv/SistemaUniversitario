<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Models\Becas\Beca;
use App\Models\Becas\BecaBeneficiario;
use App\Models\Persona;
use Illuminate\Http\Request as HttpRequest;

class BecaBeneficiarioController extends Controller
{
    public function store(HttpRequest $request, Beca $beca)
    {
        $data = $request->validate([
            'persona_id' => ['required', 'exists:persona,id_per'],
            'area' => ['nullable', 'string', 'max:255'],
            'horario' => ['nullable', 'string', 'max:255'],
            'tutor_id' => ['nullable', 'exists:persona,id_per'],
            'observaciones' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:activo,suspendido,finalizado'],
            'activo' => ['nullable', 'boolean'],
            'motivo_suspension' => ['nullable', 'string', 'max:500'],
        ]);

        $data['activo'] = $request->boolean('activo');
        $data['tutor_id'] = $request->input('tutor_id') ?: null;
        $data['estado'] = $data['estado'] ?? 'activo';
        $data['motivo_suspension'] = $data['estado'] === 'suspendido' ? ($data['motivo_suspension'] ?? null) : null;

        $beneficiario = BecaBeneficiario::updateOrCreate([
            'beca_id' => $beca->id,
            'persona_id' => $data['persona_id'],
        ], $data);

        return redirect()->route('admin.becas.edit', $beca)->with('success', 'Beneficiario agregado.');
    }

    public function update(HttpRequest $request, Beca $beca, BecaBeneficiario $beneficiario)
    {
        $data = $request->validate([
            'area' => ['nullable', 'string', 'max:255'],
            'horario' => ['nullable', 'string', 'max:255'],
            'tutor_id' => ['nullable', 'exists:persona,id_per'],
            'observaciones' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:activo,suspendido,finalizado'],
            'activo' => ['nullable', 'boolean'],
            'motivo_suspension' => ['nullable', 'string', 'max:500'],
        ]);

        $data['activo'] = $request->boolean('activo');
        $data['tutor_id'] = $request->input('tutor_id') ?: null;
        $data['motivo_suspension'] = $data['estado'] === 'suspendido' ? ($data['motivo_suspension'] ?? null) : null;

        $beneficiario->update($data);

        return redirect()->route('admin.becas.edit', $beca)->with('success', 'Beneficiario actualizado.');
    }

    public function destroy(Beca $beca, BecaBeneficiario $beneficiario)
    {
        $beneficiario->delete();

        return redirect()->route('admin.becas.edit', $beca)->with('success', 'Beneficiario eliminado.');
    }
}
