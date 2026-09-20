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
                    $q->where('etiqueta', 'like', "%{$buscar}%")
                    ->orWhere('codigo', 'like', "%{$buscar}%");
                });
            })
            ->when($beneficio, fn ($q) => $q->where('id_be_beneficio', $beneficio))
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->where('activo', $activo)
            ->orderBy('id_be_beneficio')
            ->orderBy('orden')
            ->paginate(10)
            ->appends($request->query());

        $beneficios = Beneficio::orderBy('nombre_beneficio')->get();

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
        $beneficios = Beneficio::orderBy('nombre_beneficio')->get();

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
            'id_be_beneficio'      => 'required|exists:be_beneficios,id',
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

        $existe = BecaPregunta::where('id_be_beneficio', $data['id_be_beneficio'])
            ->where('codigo', $data['codigo'])
            ->exists();

        if ($existe) {
            return back()->withInput()
                ->withErrors(['codigo' => 'Ya existe una pregunta con este código para este beneficio.']);
        }

        DB::transaction(function () use ($data, $request, $tiposConOpciones) {
            $pregunta = BecaPregunta::create([
                'id_be_beneficio' => $data['id_be_beneficio'],
                'codigo'          => $data['codigo'],
                'etiqueta'        => $data['etiqueta'],
                'placeholder'     => $data['placeholder'] ?? null,
                'tipo'            => $data['tipo'],
                'obligatoria'     => $request->boolean('obligatoria'),
                'valor_min'       => $data['valor_min'] ?? null,
                'valor_max'       => $data['valor_max'] ?? null,
                'min_length'      => $data['min_length'] ?? null,
                'max_length'      => $data['max_length'] ?? null,
                'regex'           => $data['regex'] ?? null,
                'orden'           => $data['orden'] ?? 0,
                'activo'          => $request->boolean('activo', true),
            ]);

            if (in_array($data['tipo'], $tiposConOpciones) && !empty($data['opciones'])) {
                foreach ($data['opciones'] as $i => $op) {
                    if (empty($op['etiqueta']) || !isset($op['valor']) || $op['valor'] === '') {
                        continue;
                    }
                    $pregunta->opciones()->create([
                        'etiqueta' => $op['etiqueta'],
                        'valor'    => $op['valor'],
                        'orden'    => $i,
                        'activo'   => true,
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

        $beneficios = Beneficio::orderBy('nombre_beneficio')->get();

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
            'id_be_beneficio'      => 'required|exists:be_beneficios,id',
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

        $duplicado = BecaPregunta::where('id_be_beneficio', $data['id_be_beneficio'])
            ->where('codigo', $data['codigo'])
            ->where('id', '!=', $id)
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
                'id_be_beneficio' => $data['id_be_beneficio'],
                'codigo'          => $data['codigo'],
                'etiqueta'        => $data['etiqueta'],
                'placeholder'     => $data['placeholder'] ?? null,
                'tipo'            => $data['tipo'],
                'obligatoria'     => $request->boolean('obligatoria'),
                'valor_min'       => $data['valor_min'] ?? null,
                'valor_max'       => $data['valor_max'] ?? null,
                'min_length'      => $data['min_length'] ?? null,
                'max_length'      => $data['max_length'] ?? null,
                'regex'           => $data['regex'] ?? null,
                'orden'           => $data['orden'] ?? 0,
                'activo'          => $request->boolean('activo', true),
            ]);

            $pregunta->opciones()->delete();

            if (in_array($data['tipo'], $tiposConOpciones) && !empty($data['opciones'])) {
                foreach ($data['opciones'] as $i => $op) {
                    if (empty($op['etiqueta']) || !isset($op['valor']) || $op['valor'] === '') {
                        continue;
                    }
                    $pregunta->opciones()->create([
                        'etiqueta' => $op['etiqueta'],
                        'valor'    => $op['valor'],
                        'orden'    => $i,
                        'activo'   => true,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.becas.preguntas.index')
            ->with('success', 'Pregunta actualizada correctamente.');
    }
    
}
