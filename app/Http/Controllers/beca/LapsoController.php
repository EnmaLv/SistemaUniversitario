<?php

namespace App\Http\Controllers\beca;

use App\Http\Controllers\Controller;
use App\Models\Becas\Lapso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LapsoController extends Controller
{
    public function avanzar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:lapso_academico,codigo_laa',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'codigo.required' => 'El código del lapso es obligatorio.',
            'codigo.unique' => 'Este código de lapso ya existe en el historial.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de finalización es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de finalización debe ser igual o posterior a la fecha de inicio.',
        ]);

        DB::transaction(function () use ($request) {
            Lapso::where('es_actual_laa', true)->update(['es_actual_laa' => false]);

            Lapso::create([
                'codigo_laa'              => trim($request->codigo),
                'fecha_inicio_laa'        => $request->fecha_inicio,
                'fecha_fin_laa'           => $request->fecha_fin,
                'es_actual_laa'           => true,
                'permite_solicitudes_laa' => true,
            ]);
        });

        return redirect()->back()->with('success', 'El lapso académico ha sido avanzado exitosamente. Ahora el sistema opera bajo el lapso ' . $request->codigo . '.');
    }
}
