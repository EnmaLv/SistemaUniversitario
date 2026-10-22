<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\salud\EstadoAnimoDiario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstadoAnimoDiarioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'valor' => 'required|integer|min:1|max:10',
        ]);

        $yaRegistrado = EstadoAnimoDiario::getTodayForUser(Auth::id());

        if ($yaRegistrado) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Ya has registrado tu estado de ánimo hoy.'], 400);
            }
            return redirect()->back()->with('error', 'Ya has registrado tu estado de ánimo hoy.');
        }

        DB::table('estado_animo_diario')->insert([
            'paciente_use_id' => Auth::id(),
            'valor_ead' => $request->valor,
            'fecha_ead' => Carbon::today(),
            'created_id' => Auth::id(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => '¡Gracias por compartir cómo te sientes hoy!']);
        }

        return redirect()->back()->with('success', '¡Gracias por compartir cómo te sientes hoy!');
    }
}
