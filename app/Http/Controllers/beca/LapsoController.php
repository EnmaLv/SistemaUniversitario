<?php

namespace App\Http\Controllers\beca;

use App\Http\Controllers\Controller;
use App\Models\Becas\Lapso;
use Illuminate\Http\Request;

class LapsoController extends Controller
{
    public function avanzar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:be_lapsos,codigo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'codigo.required' => 'El código del lapso es obligatorio.',
            'codigo.unique' => 'Este código de lapso ya existe en el historial.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de finalización es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de finalización debe ser igual o posterior a la fecha de inicio.',
        ]);

        // Desactivar el lapso actual
        Lapso::where('es_actual', 1)->update(['es_actual' => 0]);

        // Crear el nuevo lapso y establecerlo como actual
        Lapso::create([
            'codigo' => trim($request->codigo),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'es_actual' => 1,
            'permite_solicitudes' => 1, // Por defecto permitir solicitudes en el nuevo lapso
        ]);

        return redirect()->back()->with('success', 'El lapso académico ha sido avanzado exitosamente. Ahora el sistema opera bajo el lapso ' . $request->codigo . '.');
    }
}
