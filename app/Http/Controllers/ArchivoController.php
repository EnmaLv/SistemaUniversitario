<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    public function index()
    {
        return view('admin.configuracion.archivos.index');
    }

    public function ver(Archivo $archivo)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario->puede('archivos', 'exportar') || $usuario->puede('archivos', 'ver'), 403, 'No tienes permiso para descargar este archivo.');
        abort_unless($archivo->ruta_archivo && Storage::disk('local')->exists($archivo->ruta_archivo), 404);

        return Storage::disk('local')->download(
            $archivo->ruta_archivo,
            $archivo->nombre_original ?: basename($archivo->ruta_archivo),
            ['X-Content-Type-Options' => 'nosniff']
        );
    }
}
