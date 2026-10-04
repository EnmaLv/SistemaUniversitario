<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use App\Models\salud\Cita;
use App\Models\Usuario;
use App\Models\salud\Prioridad;
use App\Models\salud\GrupoHorario;
use App\Models\salud\CitaNotaEvolucion;
use App\Models\salud\NotaEvolucionCampo;
use App\Models\salud\PlantillaGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    public function index()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        abort_if(!$user, 403);

        if ($user->tieneRol('psicologo')) {
            $citas = Cita::obtenerCitasGlobales();
            return view('admin.psicologia.maestros.citas.index', compact('citas'));
        }

        if ($user->tieneRol('paciente')) {
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', 'App\Models\Usuario')
                ->whereNull('read_at')
                ->whereIn('type', [
                    'App\Notifications\CitaConfirmedNotification',
                    'App\Notifications\CitaRechazadaNotification',
                    'App\Notifications\CitaCancelledNotification'
                ])
                ->update(['read_at' => now()]);

            $citas = Cita::obtenerPorPaciente($user->id_usuario);
            $prioridades = Prioridad::obtenerParaPsicologo();

            return view('admin.psicologia.maestros.citas.index', compact('citas', 'prioridades'));
        }

        abort(403);
    }

    public function create()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        abort_if(!$user || !$user->tieneRol('paciente'), 403);

        $tieneCitaPendiente = Cita::tieneCitaActiva($user->id_usuario);
        $psicologos = $tieneCitaPendiente ? collect() : $user->obtenerPsicologosDisponibles();

        return view('admin.psicologia.maestros.citas.create', compact('psicologos', 'tieneCitaPendiente'));
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'psicologo_id' => 'required|exists:usuario,id_usuario',
        ]);

        $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo($request->psicologo_id);
        if (!$grupoActivo) {
            return response()->json(['disponibilidad' => []]);
        }

        $horarios = \Illuminate\Support\Facades\DB::table('horarios')
            ->where('grupo_horario_id', $grupoActivo->id)
            ->whereIn('activo', [1, 2])
            ->get();

        $horariosPorDia = [];
        foreach ($horarios as $h) {
            $diaName = $h->dia === 'Miercoles' ? 'Miércoles' : $h->dia;
            $horariosPorDia[$diaName][] = $h;
        }

        foreach ($horariosPorDia as $dia => &$hrs) {
            usort($hrs, fn($a, $b) => strcmp($a->hora_inicio, $b->hora_inicio));
        }

        $startDate = now();
        $endDate = now()->addDays(30);

        $citasConfirmadas = Cita::where('psicologo_id', $request->psicologo_id)
            ->where('estado', 'confirmada')
            ->whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get(['fecha', 'hora']);

        $citasPorFecha = [];
        foreach ($citasConfirmadas as $cita) {
            $fechaKey = $cita->fecha instanceof \Carbon\Carbon 
                ? $cita->fecha->format('Y-m-d') 
                : Carbon::parse($cita->fecha)->format('Y-m-d');
            $citasPorFecha[$fechaKey][] = Carbon::parse($cita->hora);
        }

        $disponibilidad = [];
        $diasLargo = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        for ($i = 0; $i < 30; $i++) {
            $fechaObj = $startDate->copy()->addDays($i);
            $fechaStr = $fechaObj->format('Y-m-d');
            $diaSemana = $diasLargo[$fechaObj->dayOfWeek];

            if (!isset($horariosPorDia[$diaSemana])) continue;

            $citasDelDia = $citasPorFecha[$fechaStr] ?? [];
            $slotsLibres = [];
            $ahora = now();

            foreach ($horariosPorDia[$diaSemana] as $h) {
                $inicio = Carbon::parse($h->hora_inicio);
                $fin = Carbon::parse($h->hora_fin);

                if ($i === 0 && $inicio->format('H:i:s') <= $ahora->format('H:i:s')) {
                    continue;
                }

                $ocupado = false;
                foreach ($citasDelDia as $horaCita) {
                    if ($horaCita->gte($inicio) && $horaCita->lt($fin)) {
                        $ocupado = true;
                        break;
                    }
                }

                if (!$ocupado) {
                    $slotsLibres[] = $inicio->format('g:i A') . ' - ' . $fin->format('g:i A');
                }
            }

            if (!empty($slotsLibres)) {
                $disponibilidad[$fechaStr] = $slotsLibres;
            }
        }

        return response()->json(['disponibilidad' => $disponibilidad]);
    }

    public function store(Request $request)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        abort_if(!$user || !$user->tieneRol('paciente'), 403);

        $validated = $request->validate([
            'psicologo_id' => 'required|exists:usuario,id_usuario',
            'fecha_solicitada' => 'required|date_format:Y-m-d|after_or_equal:today',
            'motivo' => 'required|string|max:100',
            'bloques_sugeridos' => 'required|string|max:1000',
            'prioridad' => 'nullable|string|max:50',
        ]);

        [$isPass, $message, $cita] = Cita::crear($user, $validated);

        if (!$isPass) {
            return back()->withErrors(['bloques_sugeridos' => $message])->withInput();
        }

        return redirect()->route('admin.psicologia.maestros.citas.index')->with('success', $message);
    }

    public function show($citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        $this->authorizeAccess($cita);

        return view('admin.psicologia.maestros.citas.show', compact('cita'));
    }

            private function resolverCategoria(?Request $request = null): string
    {
        $contexto = $request ? ($request->get('tipo') ?? $request->get('tipo_contexto')) : null;

        if ($contexto) {
            return match ($contexto) {
                'psicologia', 'mental'       => 'mental',
                'biopsicosocial'             => 'biopsicosocial',
                'medicina', 'salud', 'fisica' => 'fisica',
                default                      => 'fisica',
            };
        }

        $moduloActivo = session('modulo_activo');

        if ($moduloActivo) {
            return match ($moduloActivo) {
                'psicologia', 'mental'       => 'mental',
                'biopsicosocial'             => 'biopsicosocial',
                'medicina', 'salud', 'fisica' => 'fisica',
                default                      => 'fisica',
            };
        }

        return 'fisica';
    }

    public function editNote($citaId, Request $request)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);

        $avances = \Illuminate\Support\Facades\DB::table('avances_sesion')->orderBy('nombre', 'asc')->get();
        $estadosAnimo = \Illuminate\Support\Facades\DB::table('estado_animos')->orderBy('valor', 'asc')->get();

        $camposGuardadosRaw = CitaNotaEvolucion::obtenerPorCita($citaId);

        $categoriaFiltro = $this->resolverCategoria($request);
        $tipo = $request->get('tipo', $categoriaFiltro);
        $returnTo = $request->get('return_to');
        $editing = $request->get('editing');

        $camposOcultos = [
            'Detalle del Avance',
            'Plan de Tratamiento',
            'Diagnósticos Oficiales',
            'Estado de Ánimo del Paciente',
            'Estado de Evolución',
            'Próxima Cita Recomendada'
        ];

        if ($camposGuardadosRaw->isEmpty()) {
            $camposDefault = NotaEvolucionCampo::obtenerCamposDisponibles(null)->whereNull('psicologo_id');
            $camposGuardados = $camposDefault->filter(fn($campo) => !in_array($campo->titulo, $camposOcultos))
                ->map(fn($campo) => (object)[
                    'campo_id' => $campo->id,
                    'titulo' => $campo->titulo,
                    'contenido' => ''
                ])->values();
        } else {
            $camposGuardados = $camposGuardadosRaw->filter(fn($campo) => !in_array($campo->titulo, $camposOcultos))->values();
        }

        $camposDisponibles = NotaEvolucionCampo::obtenerPorPsicologo($user->id_usuario);

        return view('admin.psicologia.maestros.citas.edit_note', 
        compact('cita', 'avances', 'estadosAnimo', 'camposGuardados', 'camposDisponibles', 'tipo', 'categoriaFiltro', 'returnTo', 'editing'));
    }

    public function downloadPdf($citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        $this->authorizeAccess($cita);

        $pdf = $this->generatePdfContent($cita);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="nota-sesion-' . $cita->id . '.pdf"',
        ]);
    }

    private function generatePdfContent($cita): string
    {
        $paciente = Usuario::with('persona')->find($cita->user_id);
        $psicologo = Usuario::with('persona')->find($cita->psicologo_id);

        $pacienteName = $paciente ? $paciente->persona->nombre_persona : 'Desconocido';
        $psicologoName = $psicologo ? $psicologo->persona->nombre_persona : 'Desconocido';

        $headerLines = [
            'Psico-Guía UPTP',
            'Nota de sesión',
            'Paciente: ' . $pacienteName,
            'Psicólogo: ' . $psicologoName,
            'Fecha de sesión: ' . ($cita->fecha ? Carbon::parse($cita->fecha)->format('d/m/Y') : 'Sin fecha'),
            'Motivo de Solicitud: ' . ($cita->motivo ?: 'No definido'),
            '',
        ];

        $noteLines = [];
        $rawNotas = $cita->notas;

        try {
            $data = json_decode($rawNotas, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $noteLines[] = '--- DETALLES CLINICOS ---';
                $noteLines[] = '1. MOTIVO DE CONSULTA:';
                $noteLines[] = $data['motivo_consulta'] ?? 'No registrado';
                $noteLines[] = '';
                $noteLines[] = '2. OBSERVACIONES CLINICAS:';
                $obs = explode("\n", wordwrap($data['observaciones'] ?? 'No registrado', 80));
                $noteLines = array_merge($noteLines, $obs);
                $noteLines[] = '';
                $noteLines[] = '3. INTERVENCIONES / RESUMEN:';
                $int = explode("\n", wordwrap($data['intervenciones'] ?? 'No registrado', 80));
                $noteLines = array_merge($noteLines, $int);
                $noteLines[] = '';

                if (!empty($data['diagnosticos'])) {
                    $noteLines[] = 'DIAGNOSTICOS (CIE-10):';
                    foreach ($data['diagnosticos'] as $diag) {
                        $noteLines[] = "- " . ($diag['codigo'] ?? '') . " " . ($diag['nombre'] ?? '');
                    }
                    $noteLines[] = '';
                }

                if (!empty($data['avance_estado']) || !empty($data['avance_detalle'])) {
                    $noteLines[] = 'AVANCES DE SESIÓN:';
                    $avanceNombreDisplay = 'N/A';
                    if (!empty($data['avance_estado'])) {
                        $avanceRecord = DB::table('avances_sesion')->where('id', $data['avance_estado'])->first();
                        $avanceNombreDisplay = $avanceRecord ? $avanceRecord->nombre : 'ID: ' . $data['avance_estado'];
                    }
                    $noteLines[] = 'Estado: ' . $avanceNombreDisplay;
                    if (!empty($data['avance_detalle'])) {
                        $det = explode("\n", wordwrap($data['avance_detalle'], 80));
                        $noteLines = array_merge($noteLines, $det);
                    }
                    $noteLines[] = '';
                }

                $noteLines[] = 'PLAN DE TRATAMIENTO:';
                $noteLines[] = $data['plan_tratamiento'] ?? 'No registrado';

                if (!empty($data['proxima_cita_fecha'])) {
                    $noteLines[] = '';
                    $noteLines[] = 'PROXIMA CITA RECOMENDADA:';
                    $noteLines[] = 'Fecha: ' . $data['proxima_cita_fecha'];
                    $noteLines[] = 'Razón: ' . ($data['proxima_cita_razon'] ?? 'N/A');
                }
            } else {
                $noteLines = $rawNotas ? explode("\n", trim($rawNotas)) : ['No se registraron notas para esta sesión.'];
            }
        } catch (\Exception $e) {
            $noteLines = $rawNotas ? explode("\n", trim($rawNotas)) : ['No se registraron notas para esta sesión.'];
        }

        $lines = array_merge($headerLines, $noteLines);

        $content = '';
        $y = 760;
        foreach ($lines as $line) {
            if ($y < 40) break;

            $encodedLine = @iconv('UTF-8', 'CP1252//TRANSLIT', $line);
            if ($encodedLine === false) $encodedLine = $line;

            $encodedLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encodedLine);
            $content .= "BT /F1 12 Tf 45 $y Td (" . $encodedLine . ") Tj ET\n";
            $y -= 18;
        }

        $streamLength = strlen($content);
        $pdfParts = [
            "%PDF-1.4\n",
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n",
            "5 0 obj\n<< /Length $streamLength >>\nstream\n" . $content . "endstream\nendobj\n",
        ];

        $pdf = '';
        $positions = [];
        foreach ($pdfParts as $part) {
            $positions[] = strlen($pdf);
            $pdf .= $part;
        }

        $xrefStart = strlen($pdf);
        $pdf .= "xref\n0 " . (count($pdfParts) + 1) . "\n";
        $pdf .= sprintf("%010d %05d f \n", 0, 65535);
        foreach ($positions as $position) {
            $pdf .= sprintf("%010d %05d n \n", $position, 0);
        }

        $pdf .= "trailer\n<< /Size " . (count($pdfParts) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n$xrefStart\n%%EOF";

        return $pdf;
    }

    public function showJson($citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        $this->authorizeAccess($cita);

        $paciente = $cita->paciente;
        $persona = $paciente?->persona;

        $fotoUrl = null;
        if ($paciente && $paciente->profile_photo_path) {
            if (file_exists(public_path('storage/' . $paciente->profile_photo_path))) {
                $fotoUrl = asset('storage/' . $paciente->profile_photo_path);
            }
        }

        $pacienteShortName = 'Paciente';
        if ($persona && !empty($persona->nombre_persona)) {
            $pacienteNombresStr = explode(' ', trim($persona->nombre_persona))[0];
            $pacienteApellidosStr = !empty($persona->apellido_persona) ? explode(' ', trim($persona->apellido_persona))[0] : '';
            $pacienteShortName = trim($pacienteNombresStr . ' ' . $pacienteApellidosStr) ?: 'Paciente';
        }

        $psicologoShortName = 'Sin asignar';
        if ($cita->psicologo && $cita->psicologo->persona && !empty($cita->psicologo->persona->nombre_persona)) {
            $psicologoNombresStr = explode(' ', trim($cita->psicologo->persona->nombre_persona))[0];
            $psicologoApellidosStr = !empty($cita->psicologo->persona->apellido_persona) ? explode(' ', trim($cita->psicologo->persona->apellido_persona))[0] : '';
            $psicologoShortName = trim($psicologoNombresStr . ' ' . $psicologoApellidosStr) ?: 'Sin asignar';
        }

        return response()->json([
            'id' => $cita->id,
            'paciente' => $pacienteShortName,
            'paciente_foto' => $fotoUrl,
            'psicologo' => $psicologoShortName,
            'fecha_solicitud' => Carbon::parse($cita->created_at)->format('g:i A'),
            'fecha_solicitud_iso' => Carbon::parse($cita->created_at)->toIso8601String(),
            'fecha_confirmada' => $cita->estado === 'pendiente' ? 'Pendiente' : ($cita->fecha ? Carbon::parse($cita->fecha)->format('Y-m-d') : 'Sin fecha'),
            'bloque_confirmado' => $cita->bloque_propuesto ?: null,
            'hora_confirmada' => $cita->confirmado_en ? Carbon::parse($cita->confirmado_en)->format('g:i A') : 'En espera',
            'hora_confirmada_iso' => $cita->confirmado_en ? Carbon::parse($cita->confirmado_en)->toIso8601String() : null,
            'estado' => $cita->estado === 'no_asistio' ? 'Ausente' : ucfirst($cita->estado),
            'prioridad' => $cita->prioridad ?? 'media',
            'motivo' => $cita->motivo ?: 'No especificado',
            'bloques_sugeridos' => $cita->bloques_sugeridos ?? '',
            'bloque_propuesto' => $cita->bloque_propuesto,
            'bloques_propuestos' => $cita->bloques_propuestos ?? '',
            'propuesta_estado' => $cita->propuesta_estado ?? null,
            'propuesta_bloque_seleccionado' => $cita->propuesta_bloque_seleccionado ?? null,
            'motivo_rechazo_propuesta' => $cita->motivo_rechazo_propuesta ?? null,
            'paciente_horario' => $paciente && isset($paciente->horario_path) && $paciente->horario_path ? asset('storage/' . $paciente->horario_path) : null,
            'email' => $persona->email_persona ?? $paciente->email ?? null,
            'telefono' => $persona->telefono_persona ?? null,
            'registrado_en' => $paciente?->created_at ? Carbon::parse($paciente->created_at)->format('d M, Y') : null,
            'cedula' => $persona->cedula_persona ?? null,
            'edad' => $persona->edad_persona ?? ($persona->fecha_nacimiento_persona ? Carbon::parse($persona->fecha_nacimiento_persona)->age : null),
            'genero' => $persona->genero_persona ?? null,
            'nacimiento' => $persona->fecha_nacimiento_persona ?? null,
            'ubicacion' => $paciente->ubicacion ?? null,
            'discapacidad' => $paciente->discapacidad ?? null,
            'hijos' => $paciente->hijos ?? null,
            'civil' => $paciente->estado_civil ?? null,
            'perfil_academico' => $persona->perfil->nombre_perfil ?? null,
            'pnf' => $paciente->pnf ?? null,
            'semestre' => $persona->semestre_persona ?? null,
        ]);
    }

    private function authorizeAccess($cita): void
    {
        /** @var Usuario $user */
        $user = Auth::user();

        abort_if(
            !$user
            || (!$user->tieneRol('paciente')
                && !$user->tieneRol('psicologo')),
            403,
            'No autorizado para ver esta cita.'
        );

        // Comparaciones explícitas con cast a int para evitar mismatch string/int
        $userId          = (int) $user->id_usuario;
        $citaUserId      = (int) $cita->user_id;
        $citaPsicologoId = $cita->psicologo_id ? (int) $cita->psicologo_id : null;

        // Paciente: solo sus propias citas
        if ($user->tieneRol('paciente')) {
            if ($citaUserId !== $userId) {
                abort(403, 'No puedes ver citas de otros pacientes.');
            }
            return;
        }

        // Psicólogo: sus propias citas siempre; citas ajenas solo si están pendientes
        if ($user->tieneRol('psicologo')) {
            $esMismoPsicologo = ($citaPsicologoId === $userId);
            $esPendiente      = ($cita->estado === 'pendiente');

            if (!$esMismoPsicologo && !$esPendiente) {
                abort(403, 'Esta cita no te pertenece.');
            }
            return;
        }

        abort(403);
    }

    public function updatePriority(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);

        $validated = $request->validate([
            'prioridad' => 'required|string|max:50',
        ]);

        list($success, $message) = Cita::actualizarPrioridad($cita, $validated['prioridad']);

        if (!$success) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return back()->withErrors(['prioridad' => $message])->withInput();
        }

        $cita->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'prioridad' => $cita->prioridad,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }

    public function updateNote(Request $request, $citaId)
    {
        $cita = Cita::find($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);
        $marcarRealizada = ($cita->estado === 'confirmada');
        $isManual = ($cita->motivo === 'Nota de Evolución (Manual)');
        $requireFields = $marcarRealizada && !$isManual;

        if ($marcarRealizada) {
            if (!PlantillaGlobal::tienePlantillaGlobal($user->id_usuario)) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Debe activar su Esquema General para el historial clínico antes de completar citas.',
                        'redirect_template' => true
                    ], 400);
                }
                return redirect()->route('plantillas-globales.index')->with('error', 'Debe activar su Esquema General para el historial clínico.');
            }
        }

        if ($request->has('structured')) {
            $rules = [
                'titulo_manual' => 'nullable|string|max:255',
                'diagnosticos' => 'nullable|array',
                'estado_animo_id' => $requireFields ? 'required|integer|exists:estado_animos,id' : 'nullable|integer|exists:estado_animos,id',
                'estado_animo_detalle' => $requireFields ? 'required|string|max:2000' : 'nullable|string|max:2000',
                'avance_estado' => $requireFields ? 'required|integer|exists:avances_sesion,id' : 'nullable|integer|exists:avances_sesion,id',
                'avance_detalle' => $requireFields ? 'required|string|max:2000' : 'nullable|string|max:2000',
                'proxima_cita_fecha' => 'nullable|date',
                'campos_dinamicos' => 'nullable|array',
                'campos_dinamicos.*' => 'nullable|string',
            ];

            $messages = [
                'avance_estado.required' => 'Debe seleccionar un nivel de avance clínico para completar la cita.',
                'avance_estado.exists' => 'El nivel de avance seleccionado no es válido.',
                'estado_animo_id.required' => 'Debe seleccionar un estado de ánimo del paciente para completar la cita.',
                'estado_animo_id.exists' => 'El estado de ánimo seleccionado no es válido.',
                'avance_detalle.required' => 'Debe detallar el avance de la sesión.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $camposDinamicos = $request->input('campos_dinamicos', []);

            if ($isManual) {
                $hasContent = !empty(trim($request->input('titulo_manual')))
                    || $request->input('estado_animo_id')
                    || $request->input('avance_estado')
                    || !empty($request->input('diagnosticos'));

                if (!$hasContent) {
                    foreach ($camposDinamicos as $contenido) {
                        if (!empty(trim($contenido))) {
                            $hasContent = true;
                            break;
                        }
                    }
                }

                if (!$hasContent) {
                    return back()->withInput()->withErrors(['campos_dinamicos' => 'La nota manual no puede estar completamente vacía. Por favor, llena al menos un campo.']);
                }
            }

            $validated = $validator->validated();

            if (isset($validated['campos_dinamicos'])) {
                foreach ($validated['campos_dinamicos'] as $campoId => $contenido) {
                    if (!empty(trim($contenido))) {
                        CitaNotaEvolucion::guardarCampo($cita->id, $campoId, trim($contenido));
                    }
                }
                unset($validated['campos_dinamicos']);
            }

            if (isset($validated['estado_animo_id'])) {
                $cita->update(['estado_animo_id' => $validated['estado_animo_id']]);
            }

            Cita::actualizarNota($cita, json_encode($validated));
        } else {
            $validated = $request->validate([
                'notas' => $marcarRealizada ? 'required|string|max:5000' : 'nullable|string|max:5000',
            ], [
                'notas.required' => 'La nota de evolución clínica es obligatoria para completar la cita.',
            ]);

            Cita::actualizarNota($cita, $validated['notas']);
        }

        if ($marcarRealizada) {
            $resultado = Cita::marcarRealizada($cita->id, $user->id_usuario);
            if (!$resultado[0]) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $resultado[1]], 400);
                }
                return back()->withInput()->withErrors(['error' => $resultado[1]]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'notas' => $cita->notas]);
        }

        $msg = $marcarRealizada
            ? 'La cita se ha completado con éxito y la nota de evolución ha sido registrada.'
            : 'Nota de sesión actualizada correctamente.';

        return redirect()->route('admin.psicologia.maestros.historias.show', ['paciente' => $cita->user_id, 'tab' => 'evolucion'])->with('success', $msg);
    }

    public function storeCampoAjax(Request $request)
    {
        $request->validate(['titulo' => 'required|string|max:100']);

        /** @var Usuario $user */
        $user = Auth::user();
        $psicologoId = $user->id_usuario;

        if (NotaEvolucionCampo::existeTitulo($psicologoId, $request->titulo)) {
            return response()->json(['success' => false, 'message' => 'Ya existe un campo con este título.']);
        }

        $campoId = NotaEvolucionCampo::crearPersonalizado($psicologoId, $request->titulo);

        return response()->json([
            'success' => true,
            'campo' => [
                'id' => $campoId,
                'titulo' => $request->titulo,
                'psicologo_id' => $psicologoId
            ]
        ]);
    }

    public function reject($citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();

        $this->verificarPsicologoDueno($cita);
        $validated = request()->validate([
            'motivo_rechazo' => 'nullable|string|max:1000',
        ]);

        [$isPass, $message] = Cita::rechazar($cita->id, $validated['motivo_rechazo']);

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function cancel(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();

        $this->verificarPsicologoDueno($cita);
        if (
            !$user->tieneRol('psicologo')
            && (int) $cita->user_id !== (int) $user->id_usuario
        ) {
            abort(403, 'No puedes ver esta cita.');
        }

        [$isPass, $message] = Cita::cancelar($cita->id, $user->id_usuario, $request->input('motivo_cancelacion'));

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => $isPass ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->route('admin.psicologia.maestros.citas.index')->with($isPass ? 'success' : 'error', $message);
    }

    public function cancelConfirmedByPsicologo(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();

        $this->verificarPsicologoDueno($cita);
        $validated = $request->validate([
            'motivo_cancelacion' => 'nullable|string|max:1000',
        ]);

        [$isPass, $message] = Cita::cancelar($cita->id, $user->id_usuario, $validated['motivo_cancelacion'] ?? null);

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function posponer(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();

        $this->verificarPsicologoDueno($cita);
        [$isPass, $message] = Cita::posponer($cita->id, $user->id_usuario);

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function proponer(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();

        $this->verificarPsicologoDueno($cita);
        $validated = $request->validate([
            'fecha' => 'required|date',
            'bloque' => 'required|string|max:255',
        ]);

        [$isPass, $message] = Cita::proponer($cita->id, $user->id_usuario, $validated['fecha'], $validated['bloque']);

        $status = is_string($isPass) ? $isPass : ($isPass ? 'success' : 'error');

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function quitarPropuesta(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();

        $this->verificarPsicologoDueno($cita);
        $fecha = $request->input('fecha');
        if (!$fecha) {
            return response()->json(['status' => 'error', 'message' => 'La fecha es obligatoria']);
        }

        [$isPass, $message] = Cita::quitarPropuesta($cita->id, $fecha, $request->input('bloque'));

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function accept(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita); 

        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|string',
            'bloque' => 'required|string|max:255',
        ]);

        [$isPass, $message] = Cita::confirmar($cita->id, $user->id_usuario, $validated);

        $paciente = Usuario::with('persona')->find($cita->user_id);
        $nombrePaciente = $paciente?->persona?->nombre_persona ?? 'Paciente';

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message,
            'paciente' => $nombrePaciente,
            'paciente_id' => $cita->user_id,
            'cita_id' => $cita->id
        ]);
    }

    public function complete(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);
        if ($cita->estado !== 'confirmada') {
            return response()->json([
                'status' => 'error',
                'message' => 'Solo se pueden registrar notas para citas en estado Confirmada.'
            ], 400);
        }

        if (!PlantillaGlobal::tienePlantillaGlobal($user->id_usuario)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debe activar su Esquema General para el historial clínico antes de completar citas.',
                'redirect_template' => true
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Redirigiendo a la creación de la nota de evolución...',
            'paciente_id' => $cita->user_id,
            'redirect_url' => route('admin.psicologia.maestros.citas.edit.note', $cita->id)
        ]);
    }

    public function noAsistio($citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);

        [$isPass, $message] = Cita::marcarNoAsistio($cita->id);

        if (!$isPass) {
            $isWarning = str_contains($message, 'no ha comenzado');
            return response()->json([
                'status' => 'error',
                'is_warning' => $isWarning,
                'message' => $isWarning ? 'No es posible procesar la cita antes de la fecha y hora programadas. Por favor, aguarde al inicio de la sesión para registrar su estado.' : $message
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    public function historyJson(Request $request)
    {
        /** @var Usuario $user */
        $user = Auth::user();

        if (!$user || !$user->tieneRol('paciente')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $startDate = $request->input('start_date', now()->subMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $prioridad = $request->input('prioridad');

        $citas = Cita::obtenerHistorialPaciente($user->id_usuario, 12, $startDate, $endDate, $prioridad);

        $citas->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'psicologo' => $c->psicologo->persona->nombre_persona ?? 'Sin asignar',
            'fecha' => $c->fecha ? Carbon::parse($c->fecha)->format('d/m/Y') : 'S/F',
            'fecha_formateada' => $c->fecha ? Carbon::parse($c->fecha)->translatedFormat('l d \d\e F, Y') : 'S/F',
            'hora' => $c->hora ? Carbon::parse($c->hora)->format('g:i A') : 'S/H',
            'estado' => $c->estado,
            'cancelado_por' => $c->cancelado_por ?? null,
            'motivo' => $c->motivo,
            'notas' => $c->notas
        ]);

        return response()->json($citas);
    }

    private function verificarPsicologoDueno($cita, ?string $mensaje = null): void
    {
        /** @var Usuario $user */
        $user = Auth::user();

        abort_if(
            !$user || !$user->tieneRol('psicologo'),
            403,
            $mensaje ?? 'No autorizado.'
        );

        $userId   = (int) $user->id_usuario;
        $citaPsi  = $cita->psicologo_id ? (int) $cita->psicologo_id : null;

        abort_if(
            $citaPsi !== $userId,
            403,
            $mensaje ?? 'Esta cita no te pertenece.'
        );
    }

    public function enviarPropuesta($citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);
        [$isPass, $message] = Cita::enviarPropuesta($cita->id);

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function responderPropuesta(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        abort_if(!$user || !$user->tieneRol('paciente'), 403, 'No autorizado.');
        abort_if((int) $cita->user_id !== (int) $user->id_usuario, 403, 'Esta cita no te pertenece.');

        $validated = $request->validate([
            'opcion' => 'required|in:cualquier_dia,sugerencia_aceptada,rechazada,aceptada',
            'bloque' => 'nullable|string|max:255',
            'motivo_rechazo' => 'nullable|string|max:500',
            'nuevos_bloques' => 'nullable|string|max:2000',
        ]);

        [$isPass, $message] = Cita::responderPropuesta(
            $cita->id,
            $validated['opcion'],
            $validated['bloque'] ?? null,
            $validated['motivo_rechazo'] ?? null,
            $validated['nuevos_bloques'] ?? null
        );

        return response()->json([
            'status' => $isPass ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function descargarConstanciaPdf($citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);
        abort_if($cita->estado !== 'realizada', 400, 'La constancia solo se puede generar de citas realizadas.');
        abort_if($cita->motivo === 'Nota de Evolución (Manual)', 400, 'No se puede generar constancia de asistencia para notas manuales.');

        $paciente = Usuario::with('persona')->find($cita->user_id);
        $psicologo = Usuario::with('persona')->find($cita->psicologo_id);

        if ($paciente) {
            $paciente->name = $paciente->persona->nombre_persona ?? 'Paciente';
        }
        if ($psicologo) {
            $psicologo->name = $psicologo->persona->nombre_persona ?? 'Psicólogo';
        }

        $pdf = Pdf::loadView('admin.psicologia.maestros.historias.constanciaPDF', compact('cita', 'paciente', 'psicologo'))
            ->setPaper('a4', 'portrait');

        $slugPaciente = $paciente ? Str::slug($paciente->name) : 'paciente';

        return $pdf->stream('Constancia_Asistencia_' . $slugPaciente . '.pdf');
    }

    public function destroy($citaId)
    {
        $cita = Cita::find($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);
        abort_if($cita->motivo !== 'Nota de Evolución (Manual)', 400, 'Solo se pueden eliminar notas de evolución creadas manualmente.');

        $cita->update(['status' => 0]);

        return redirect()->route('admin.psicologia.maestros.historias.show', $cita->user_id)->with('success', 'Nota de evolución manual eliminada correctamente.');
    }

    public function dismissCancelMessage(Request $request, $citaId)
    {
        $cita = Cita::obtenerDetalle($citaId);
        abort_if(!$cita, 404);

        /** @var Usuario $user */
        $user = Auth::user();
        $this->verificarPsicologoDueno($cita);

        [$isPass, $message] = Cita::ocultarMensajeCancelacion($citaId);

        return response()->json([
            'success' => $isPass,
            'message' => $message
        ]);
    }
}
