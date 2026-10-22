<?php

namespace App\Http\Controllers\Deporte;

use App\Exceptions\Deporte\OperacionDeporteException;
use App\Http\Controllers\Controller;
use App\Models\Deporte\SancionDeportiva;
use App\Services\Deporte\PrestamoService;
use App\Support\Seguridad\MensajeError;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SancionDeporteController extends Controller
{
    public function __construct(private PrestamoService $servicio)
    {
    }

    public function index(Request $request)
    {
        $this->servicio->marcarVencidos();

        $buscar = trim((string) $request->query('q', ''));
        $estado = $request->query('estado', SancionDeportiva::ESTADO_ACTIVA);

        $sanciones = SancionDeportiva::with(['persona', 'prestamo.producto', 'levantadaPor.persona'])
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $patron = '%' . mb_strtolower($buscar) . '%';

                $consulta->whereHas('persona', function ($persona) use ($patron) {
                    $persona->where(fn ($filtro) => $filtro
                        ->whereRaw('LOWER(cedula_per) LIKE ?', [$patron])
                        ->orWhereRaw('LOWER(nombres_per) LIKE ?', [$patron])
                        ->orWhereRaw('LOWER(apellidos_per) LIKE ?', [$patron]));
                });
            })
            ->when(in_array($estado, [SancionDeportiva::ESTADO_ACTIVA, SancionDeportiva::ESTADO_LEVANTADA], true), fn ($consulta) => $consulta->where('estado_snd', $estado))
            ->orderByDesc('id_snd')
            ->paginate(10)
            ->withQueryString();

        return view('admin.deportes.sanciones.index', compact('sanciones', 'buscar', 'estado'));
    }

    public function show($id)
    {
        $sancion = SancionDeportiva::with(['persona', 'prestamo.producto', 'prestamo.lote', 'prestamo.sede', 'aplicadaPor.persona', 'levantadaPor.persona', 'loteReposicion'])
            ->findOrFail($id);

        return view('admin.deportes.sanciones.show', compact('sancion'));
    }

    public function updateReposicion(Request $request, $id)
    {
        $sancion = SancionDeportiva::findOrFail($id);

        $datos = $request->validate([
            'lote'          => ['required', Rule::in(['nuevo', 'original'])],
            'codigo_lote'   => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->servicio->reponer($sancion->id_snd, $datos);
        } catch (OperacionDeporteException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', MensajeError::de($e));
        }

        return redirect()->route('admin.deportes.sanciones.show', $sancion->id_snd)->with('success', 'Reposición registrada: la sanción quedó levantada y el implemento entró al inventario.');
    }
}
