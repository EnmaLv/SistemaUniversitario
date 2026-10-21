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
        $beneficios = Beneficio::activos()->get();
        $lapsos     = Lapso::all();

        $criteriosPorBeneficio = $this->mapaCriteriosPorBeneficio();

        return view('admin.becas.jornada.create', compact('beneficios', 'lapsos', 'criteriosPorBeneficio'));
    }

    private function mapaCriteriosPorBeneficio(): array
    {
        return BeneficioCriterio::with('pregunta')
            ->get()
            ->groupBy('bes_id')
            ->map(fn ($items) => $items->map(fn ($c) => [
                'id_pregunta'     => $c->pgb_id,
                'pregunta'        => $c->pregunta?->etiqueta_pgb,
                'codigo'          => $c->pregunta?->codigo_pgb,
                'operador'        => $c->operador_crb,
                'valor_esperado'  => $c->valor_esperado_crb,
                'es_eliminatoria' => (bool) $c->es_eliminatoria_crb,
                'peso'            => $c->peso_crb,
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
            return redirect()->back()->withInput()->with('error', 'Error al crear la jornada: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function edit(int $id)
    {
        $jornada    = JornadaBeca::with('criterios.pregunta')->findOrFail($id);
        $beneficios = Beneficio::activos()->get();
        $lapsos     = Lapso::all();

        $criteriosPorBeneficio = $this->mapaCriteriosPorBeneficio();

        $criteriosActuales = $jornada->criterios->map(fn ($c) => [
            'id_pregunta'     => $c->pgb_id,
            'pregunta'        => $c->pregunta?->etiqueta_pgb,
            'codigo'          => $c->pregunta?->codigo_pgb,
            'operador'        => $c->operador_cjb,
            'valor_esperado'  => $c->valor_esperado_cjb,
            'es_eliminatoria' => (bool) $c->es_eliminatoria_cjb,
            'peso'            => $c->peso_cjb,
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
            return redirect()->back()->withInput()->with('error', 'Error al actualizar la jornada: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->JornadaServices->desactivarJornada($id);
            $this->JornadaServices->obtenerJornadaActiva();

            return redirect()->route('admin.becas.jornada.index')->with('success', 'Jornada inactivada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.becas.jornada.index')->with('error', 'Error al inactivar la jornada: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function activar($id)
    {
        try {
            $this->JornadaServices->activarJornada($id);
            $this->JornadaServices->obtenerJornadaActiva();

            return redirect()->route('admin.becas.jornada.index')->with('success', 'Jornada activada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.becas.jornada.index')->with('error', 'Error al activar la jornada: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }
}
