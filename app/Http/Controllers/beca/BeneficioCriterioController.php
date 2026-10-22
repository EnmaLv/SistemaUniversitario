<?php

namespace App\Http\Controllers\beca;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Becas\Beneficio;
use App\Models\Becas\BecaPregunta;
use App\Models\Becas\BeneficioCriterio;
use Illuminate\Support\Facades\DB;

class BeneficioCriterioController extends Controller
{
    private function operadores(): array
    {
        return [
            '='       => 'Igual a',
            '!='      => 'Distinto de',
            '>'       => 'Mayor que',
            '>='      => 'Mayor o igual que',
            '<'       => 'Menor que',
            '<='      => 'Menor o igual que',
            'in'      => 'Está en la lista',
            'not_in'  => 'No está en la lista',
            'between' => 'Está entre',
        ];
    }

    public function index(Request $request)
    {
        $buscar       = $request->get('buscar');
        $beneficio    = $request->get('beneficio');
        $eliminatoria = $request->get('eliminatoria');

        $criterios = BeneficioCriterio::with(['beneficio', 'pregunta'])
            ->when($buscar, function ($q) use ($buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->whereHas('pregunta', fn ($p) => $p->where('etiqueta_pgb', 'like', "%{$buscar}%")
                                                            ->orWhere('codigo_pgb', 'like', "%{$buscar}%"))
                      ->orWhereHas('beneficio', fn ($b) => $b->where('nombre_beneficio_bes', 'like', "%{$buscar}%"));
                });
            })
            ->when($beneficio, fn ($q) => $q->where('bes_id', $beneficio))
            ->when($eliminatoria !== null && $eliminatoria !== '', function ($q) use ($eliminatoria) {
                $q->where('es_eliminatoria_crb', (bool) (int) $eliminatoria);
            })
            ->orderBy('bes_id')
            ->orderBy('id_crb')
            ->paginate(10)
            ->appends($request->query());

        $beneficios = Beneficio::orderBy('nombre_beneficio_bes')->get();
        $operadores = $this->operadores();

        return view('admin.becas.criterios.index', compact('criterios', 'beneficios', 'operadores'));
    }

    public function create()
    {
        $beneficios = Beneficio::orderBy('nombre_beneficio_bes')->get();

        $preguntas = BecaPregunta::activos()
            ->orderBy('bes_id')
            ->orderBy('orden_pgb')
            ->get();

        $operadores = $this->operadores();

        return view('admin.becas.criterios.create', compact('beneficios', 'preguntas', 'operadores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_be_beneficio' => 'required|exists:beneficio_estudiantil,id_bes',
            'id_pregunta'     => 'required|exists:pregunta_beneficio,id_pgb',
            'es_eliminatoria' => 'nullable|boolean',
            'operador'        => 'nullable|in:=,!=,>,>=,<,<=,in,not_in,between',
            'valor_esperado'  => 'nullable|string|max:255',
            'peso'            => 'nullable|numeric|min:0',
        ]);

        $preguntaValida = BecaPregunta::where('id_pgb', $data['id_pregunta'])
            ->where('bes_id', $data['id_be_beneficio'])
            ->exists();

        if (!$preguntaValida) {
            return back()->withInput()
                ->withErrors(['id_pregunta' => 'La pregunta seleccionada no pertenece a este beneficio.']);
        }

        $duplicado = BeneficioCriterio::withTrashed()
            ->where('bes_id', $data['id_be_beneficio'])
            ->where('pgb_id', $data['id_pregunta'])
            ->exists();

        if ($duplicado) {
            return back()->withInput()
                ->withErrors(['id_pregunta' => 'Ya existe un criterio para esta pregunta en este beneficio.']);
        }

        if ($request->boolean('es_eliminatoria') && !$request->filled('operador')) {
            return back()->withInput()
                ->withErrors(['operador' => 'Un criterio eliminatorio necesita un operador.']);
        }

        BeneficioCriterio::create([
            'bes_id'              => $data['id_be_beneficio'],
            'pgb_id'              => $data['id_pregunta'],
            'es_eliminatoria_crb' => $request->boolean('es_eliminatoria'),
            'operador_crb'        => $request->filled('operador') ? $data['operador'] : null,
            'valor_esperado_crb'  => $request->filled('valor_esperado') ? $data['valor_esperado'] : null,
            'peso_crb'            => $data['peso'] ?? 0,
        ]);

        return redirect()
            ->route('admin.becas.criterios.index')
            ->with('success', 'Criterio creado correctamente.');
    }

    public function edit(int $id)
    {
        $criterio = BeneficioCriterio::with(['beneficio', 'pregunta'])->findOrFail($id);

        $beneficios = Beneficio::orderBy('nombre_beneficio_bes')->get();

        $preguntas = BecaPregunta::activos()
            ->orderBy('bes_id')
            ->orderBy('orden_pgb')
            ->get();

        $operadores = $this->operadores();

        return view('admin.becas.criterios.edit', compact('criterio', 'beneficios', 'preguntas', 'operadores'));
    }

    public function update(Request $request, int $id)
    {
        $criterio = BeneficioCriterio::findOrFail($id);

        $data = $request->validate([
            'id_be_beneficio' => 'required|exists:beneficio_estudiantil,id_bes',
            'id_pregunta'     => 'required|exists:pregunta_beneficio,id_pgb',
            'es_eliminatoria' => 'nullable|boolean',
            'operador'        => 'nullable|in:=,!=,>,>=,<,<=,in,not_in,between',
            'valor_esperado'  => 'nullable|string|max:255',
            'peso'            => 'nullable|numeric|min:0',
        ]);

        $preguntaValida = BecaPregunta::where('id_pgb', $data['id_pregunta'])
            ->where('bes_id', $data['id_be_beneficio'])
            ->exists();

        if (!$preguntaValida) {
            return back()->withInput()
                ->withErrors(['id_pregunta' => 'La pregunta seleccionada no pertenece a este beneficio.']);
        }

        $duplicado = BeneficioCriterio::withTrashed()
            ->where('bes_id', $data['id_be_beneficio'])
            ->where('pgb_id', $data['id_pregunta'])
            ->where('id_crb', '!=', $id)
            ->exists();

        if ($duplicado) {
            return back()->withInput()
                ->withErrors(['id_pregunta' => 'Ya existe otro criterio para esta pregunta en este beneficio.']);
        }

        if ($request->boolean('es_eliminatoria') && !$request->filled('operador')) {
            return back()->withInput()
                ->withErrors(['operador' => 'Un criterio eliminatorio necesita un operador.']);
        }

        $criterio->update([
            'bes_id'              => $data['id_be_beneficio'],
            'pgb_id'              => $data['id_pregunta'],
            'es_eliminatoria_crb' => $request->boolean('es_eliminatoria'),
            'operador_crb'        => $request->filled('operador') ? $data['operador'] : null,
            'valor_esperado_crb'  => $request->filled('valor_esperado') ? $data['valor_esperado'] : null,
            'peso_crb'            => $data['peso'] ?? 0,
        ]);

        return redirect()
            ->route('admin.becas.criterios.index')
            ->with('success', 'Criterio actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $criterio = BeneficioCriterio::findOrFail($id);
        $criterio->forceDelete();

        return redirect()
            ->route('admin.becas.criterios.index')
            ->with('success', 'Criterio eliminado correctamente.');
    }
}