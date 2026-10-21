<?php

namespace App\Http\Controllers;

use App\Models\SobranteComedor;
use App\Models\Receta;

class DetalleRegistroDiarioController extends Controller
{
    public function index()
    {
        $comidas = Receta::orderBy('id_rct', 'desc')->where('status_id', Receta::STATUS_ACTIVO)->get();
        $sobrantes = SobranteComedor::orderByDesc('id_sbc')->paginate(10)->withQueryString();
        return view('admin.movimientos.registro_comida.index', compact('comidas', 'sobrantes'));
    }
}
