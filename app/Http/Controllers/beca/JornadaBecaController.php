<?php

namespace App\Http\Controllers\beca;

use App\Http\Requests\beca\GuardarJornadaRequest;

use App\Http\Controllers\Controller;
use App\Models\Becas\JornadaBeca;
use Illuminate\Http\Request;
use App\Services\becas\JornadaBecasServices;
use App\Models\Becas\Lapso;
use App\Models\Becas\Beneficio;
use App\Models\Becas\BeneficioCriterio;

class JornadaBecaController extends Controller
{
    protected JornadaBecasServices $JornadaServices;
    
    public function __construct(JornadaBecasServices $Jornadaserices)
    {
        $this->JornadaServices = $Jornadaserices;
    }

    public function index()
    {
        return $this->JornadaServices->index();
    }

    public function create()
    {
        $beneficios = Beneficio::where('status', 1)->get();
        $lapsos     = Lapso::all();

        $criteriosPorBeneficio = $this->mapaCriteriosPorBeneficio();

        return view('admin.becas.jornada.create', compact('beneficios', 'lapsos', 'criteriosPorBeneficio'));
    }

    private function mapaCriteriosPorBeneficio(): array
    {
        return BeneficioCriterio::with('pregunta')
            ->get()
            ->groupBy('id_be_beneficio')
            ->map(fn ($items) => $items->map(fn ($c) => [
                'id_pregunta'     => $c->id_pregunta,
                'pregunta'        => $c->pregunta?->etiqueta,
                'codigo'          => $c->pregunta?->codigo,
                'operador'        => $c->operador,
                'valor_esperado'  => $c->valor_esperado,
                'es_eliminatoria' => (bool) $c->es_eliminatoria,
                'peso'            => $c->peso,
            ])->values()->all())
            ->toArray();
    }

    public function store(GuardarJornadaRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->JornadaServices->crearJornada($validated);

            $this->JornadaServices->obtenerJornadaActiva();
            return redirect()->route('admin.becas.jornada.index')->with('success', 'Jornada creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al crear la jornada: ' . $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        $jornada    = JornadaBeca::with('criterios.pregunta')->findOrFail($id);
        $beneficios = Beneficio::where('status', 1)->get();
        $lapsos     = Lapso::all();

        $criteriosPorBeneficio = $this->mapaCriteriosPorBeneficio();

        $criteriosActuales = $jornada->criterios->map(fn ($c) => [
            'id_pregunta'     => $c->id_pregunta,
            'pregunta'        => $c->pregunta?->etiqueta,
            'codigo'          => $c->pregunta?->codigo,
            'operador'        => $c->operador,
            'valor_esperado'  => $c->valor_esperado,
            'es_eliminatoria' => (bool) $c->es_eliminatoria,
            'peso'            => $c->peso,
        ])->values()->all();

        return view('admin.becas.jornada.edit',
            compact('jornada', 'beneficios', 'lapsos', 'criteriosPorBeneficio', 'criteriosActuales'));
    }

    public function update(GuardarJornadaRequest $request,int $id)
    {
        $validated = $request->validated();

        try {
            $this->JornadaServices->actualizarJornada($id, $validated);
            $this->JornadaServices->obtenerJornadaActiva();

            return redirect()->route('admin.becas.jornada.index')->with('success', 'Jornada actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar la jornada: ' . $e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->JornadaServices->desactivarJornada($id);
            $this->JornadaServices->obtenerJornadaActiva();

            return redirect()->route('admin.becas.jornada.index')->with('success', 'Jornada inactivada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.becas.jornada.index')->with('error', 'Error al inactivar la jornada: ' . $e->getMessage());
        }
    }

    public function activar($id)
    {
        try {
            $this->JornadaServices->activarJornada($id);
            $this->JornadaServices->obtenerJornadaActiva();

            return redirect()->route('admin.becas.jornada.index')->with('success', 'Jornada activada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.becas.jornada.index')->with('error', 'Error al activar la jornada: ' . $e->getMessage());
        }
    }
}
