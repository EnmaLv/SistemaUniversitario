<?php

namespace App\Http\Controllers\beca;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Becas\BecaPregunta;
use App\Models\Becas\Beneficio;
use Illuminate\Support\Facades\DB;
use App\Models\Becas\BecaPreguntaOpciones;

class BecaPreguntaController extends Controller
{

    public function index(Request $request)
    {
        $buscar    = $request->get('buscar');
        $beneficio = $request->get('beneficio');
        $tipo      = $request->get('tipo');
        $activo    = $request->get('activo', 1);

        $preguntas = BecaPregunta::with('beca')
            ->when($buscar, function ($q) use ($buscar) {
                $q->where(function ($q) use ($buscar) {
                    $q->where('etiqueta_pgb', 'like', "%{$buscar}%")
                    ->orWhere('codigo_pgb', 'like', "%{$buscar}%");
                });
            })
            ->when($beneficio, fn ($q) => $q->where('bes_id', $beneficio))
            ->when($tipo, fn ($q) => $q->where('tipo_campo_pgb', $tipo))
            ->where('status_id', (int) $activo === 1 ? BecaPregunta::STATUS_ACTIVO : BecaPregunta::STATUS_INACTIVO)
            ->orderBy('bes_id')
            ->orderBy('orden_pgb')
            ->paginate(10)
            ->appends($request->query());

        $beneficios = Beneficio::orderBy('nombre_beneficio_bes')->get();

        $tipos = [
            'text'     => 'Texto',
            'textarea' => 'Texto largo',
            'number'   => 'Número',
            'decimal'  => 'Decimal',
            'email'    => 'Email',
            'date'     => 'Fecha',
            'select'   => 'Selección',
            'radio'    => 'Radio',
            'checkbox' => 'Casilla múltiple',
            'boolean'  => 'Sí / No',
        ];

        return view('admin.becas.preguntas.index', compact('preguntas', 'beneficios', 'tipos'));
    }

    public function create()
    {
        $beneficios = Beneficio::orderBy('nombre_beneficio_bes')->get();

        $tipos = [
            'text'     => 'Texto corto',
            'textarea' => 'Texto largo',
            'number'   => 'Número entero',
            'decimal'  => 'Número decimal',
            'email'    => 'Correo',
            'date'     => 'Fecha',
            'select'   => 'Selección (desplegable)',
            'radio'    => 'Opción única',
            'checkbox' => 'Opción múltiple',
            'boolean'  => 'Sí / No',
        ];

        return view('admin.becas.preguntas.create', compact('beneficios', 'tipos'));
    }

    public function store(Request $request)
    {
        $tipos = ['text','textarea','number','decimal','email','date','select','radio','checkbox','boolean'];
        $tiposConOpciones = ['select', 'radio', 'checkbox'];

        $data = $request->validate([
            'id_be_beneficio'      => 'required|exists:beneficio_estudiantil,id_bes',
            'codigo'               => 'required|string|max:60|regex:/^[a-z0-9_]+$/',
            'etiqueta'             => 'required|string|max:255',
            'placeholder'          => 'nullable|string|max:255',
            'tipo'                 => 'required|in:' . implode(',', $tipos),
            'obligatoria'          => 'nullable|boolean',
            'activo'               => 'nullable|boolean',
            'orden'                => 'nullable|integer|min:0',
            'valor_min'            => 'nullable|numeric',
            'valor_max'            => 'nullable|numeric|gte:valor_min',
            'min_length'           => 'nullable|integer|min:0',
            'max_length'           => 'nullable|integer|min:0|gte:min_length',
            'regex'                => 'nullable|string|max:255',
            'opciones'             => 'nullable|array',
            'opciones.*.etiqueta'  => 'nullable|string|max:255',
            'opciones.*.valor'     => 'nullable|string|max:100',
        ], [
            'codigo.regex'   => 'El código solo puede contener letras minúsculas, números y guion bajo.',
            'valor_max.gte'  => 'El valor máximo debe ser mayor o igual al mínimo.',
            'max_length.gte' => 'El largo máximo debe ser mayor o igual al mínimo.',
        ]);

        $existe = BecaPregunta::withTrashed()
            ->where('bes_id', $data['id_be_beneficio'])
            ->where('codigo_pgb', $data['codigo'])
            ->exists();

        if ($existe) {
            return back()->withInput()
                ->withErrors(['codigo' => 'Ya existe una pregunta con este código para este beneficio.']);
        }

        DB::transaction(function () use ($data, $request, $tiposConOpciones) {
            $pregunta = BecaPregunta::create([
                'bes_id'          => $data['id_be_beneficio'],
                'codigo_pgb'      => $data['codigo'],
                'etiqueta_pgb'    => $data['etiqueta'],
                'placeholder_pgb' => $data['placeholder'] ?? null,
                'tipo_campo_pgb'  => $data['tipo'],
                'obligatoria_pgb' => $request->boolean('obligatoria'),
                'valor_min_pgb'   => $data['valor_min'] ?? null,
                'valor_max_pgb'   => $data['valor_max'] ?? null,
                'min_length_pgb'  => $data['min_length'] ?? null,
                'max_length_pgb'  => $data['max_length'] ?? null,
                'regex_pgb'       => $data['regex'] ?? null,
                'orden_pgb'       => $data['orden'] ?? 0,
                'status_id'       => $request->boolean('activo', true) ? BecaPregunta::STATUS_ACTIVO : BecaPregunta::STATUS_INACTIVO,
            ]);

            if (in_array($data['tipo'], $tiposConOpciones) && !empty($data['opciones'])) {
                foreach ($data['opciones'] as $i => $op) {
                    if (empty($op['etiqueta']) || !isset($op['valor']) || $op['valor'] === '') {
                        continue;
                    }
                    $pregunta->opciones()->create([
                        'etiqueta_opb' => $op['etiqueta'],
                        'valor_opb'    => $op['valor'],
                        'orden_opb'    => $i,
                        'status_id'    => BecaPreguntaOpciones::STATUS_ACTIVO,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.becas.preguntas.index')
            ->with('success', 'Pregunta creada correctamente.');
    }

    public function edit(int $id)
    {
        $pregunta = BecaPregunta::with('opciones')->findOrFail($id);

        $beneficios = Beneficio::orderBy('nombre_beneficio_bes')->get();

        $tipos = [
            'text'     => 'Texto corto',
            'textarea' => 'Texto largo',
            'number'   => 'Número entero',
            'decimal'  => 'Número decimal',
            'email'    => 'Correo',
            'date'     => 'Fecha',
            'select'   => 'Selección (desplegable)',
            'radio'    => 'Opción única',
            'checkbox' => 'Opción múltiple',
            'boolean'  => 'Sí / No',
        ];

        return view('admin.becas.preguntas.edit', compact('pregunta', 'beneficios', 'tipos'));
    }

    public function update(Request $request, int $id)
    {
        $pregunta = BecaPregunta::findOrFail($id);

        $tipos = ['text','textarea','number','decimal','email','date','select','radio','checkbox','boolean'];
        $tiposConOpciones = ['select', 'radio', 'checkbox'];

        $data = $request->validate([
            'id_be_beneficio'      => 'required|exists:beneficio_estudiantil,id_bes',
            'codigo'               => 'required|string|max:60|regex:/^[a-z0-9_]+$/',
            'etiqueta'             => 'required|string|max:255',
            'placeholder'          => 'nullable|string|max:255',
            'tipo'                 => 'required|in:' . implode(',', $tipos),
            'obligatoria'          => 'nullable|boolean',
            'activo'               => 'nullable|boolean',
            'orden'                => 'nullable|integer|min:0',
            'valor_min'            => 'nullable|numeric',
            'valor_max'            => 'nullable|numeric|gte:valor_min',
            'min_length'           => 'nullable|integer|min:0',
            'max_length'           => 'nullable|integer|min:0|gte:min_length',
            'regex'                => 'nullable|string|max:255',
            'opciones'             => 'nullable|array',
            'opciones.*.etiqueta'  => 'nullable|string|max:255',
            'opciones.*.valor'     => 'nullable|string|max:100',
        ], [
            'codigo.regex'   => 'El código solo puede contener letras minúsculas, números y guion bajo.',
            'valor_max.gte'  => 'El valor máximo debe ser mayor o igual al mínimo.',
            'max_length.gte' => 'El largo máximo debe ser mayor o igual al mínimo.',
        ]);

        $duplicado = BecaPregunta::withTrashed()
            ->where('bes_id', $data['id_be_beneficio'])
            ->where('codigo_pgb', $data['codigo'])
            ->where('id_pgb', '!=', $id)
            ->exists();

        if ($duplicado) {
            return back()->withInput()
                ->withErrors(['codigo' => 'Ya existe otra pregunta con este código para este beneficio.']);
        }

        if (in_array($data['tipo'], $tiposConOpciones)) {
            $validas = collect($request->opciones ?? [])
                ->filter(fn ($o) => !empty($o['etiqueta']) && isset($o['valor']) && $o['valor'] !== '')
                ->count();

            if ($validas === 0) {
                return back()->withInput()
                    ->withErrors(['opciones' => 'Debes agregar al menos una opción para este tipo de pregunta.']);
            }
        }

        DB::transaction(function () use ($pregunta, $data, $request, $tiposConOpciones) {
            $pregunta->update([
                'bes_id'          => $data['id_be_beneficio'],
                'codigo_pgb'      => $data['codigo'],
                'etiqueta_pgb'    => $data['etiqueta'],
                'placeholder_pgb' => $data['placeholder'] ?? null,
                'tipo_campo_pgb'  => $data['tipo'],
                'obligatoria_pgb' => $request->boolean('obligatoria'),
                'valor_min_pgb'   => $data['valor_min'] ?? null,
                'valor_max_pgb'   => $data['valor_max'] ?? null,
                'min_length_pgb'  => $data['min_length'] ?? null,
                'max_length_pgb'  => $data['max_length'] ?? null,
                'regex_pgb'       => $data['regex'] ?? null,
                'orden_pgb'       => $data['orden'] ?? 0,
                'status_id'       => $request->boolean('activo', true) ? BecaPregunta::STATUS_ACTIVO : BecaPregunta::STATUS_INACTIVO,
            ]);

            $pregunta->opciones()->forceDelete();

            if (in_array($data['tipo'], $tiposConOpciones) && !empty($data['opciones'])) {
                foreach ($data['opciones'] as $i => $op) {
                    if (empty($op['etiqueta']) || !isset($op['valor']) || $op['valor'] === '') {
                        continue;
                    }
                    $pregunta->opciones()->create([
                        'etiqueta_opb' => $op['etiqueta'],
                        'valor_opb'    => $op['valor'],
                        'orden_opb'    => $i,
                        'status_id'    => BecaPreguntaOpciones::STATUS_ACTIVO,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.becas.preguntas.index')
            ->with('success', 'Pregunta actualizada correctamente.');
    }
    
}
