<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use App\Models\salud\Cita;
use App\Models\salud\GrupoHorario;
use App\Models\salud\Horario;
use App\Models\salud\Prioridad;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AgendaController extends Controller
{
    private function verificarAcceso(): Usuario
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user || !$user->tieneRol(['psicologo', 'administrador', 'admin'])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->verificarAcceso();

        $data = Cita::obtenerDataAgenda($request, $user);

        $data['avances'] = DB::table('avances_sesion')->orderBy('nombre', 'asc')->get();
        $data['estados_animo'] = DB::table('estado_animos')->orderBy('valor', 'asc')->get();
        $data['prioridades'] = Prioridad::obtenerParaPsicologo($user->id_usuario);

        return view('admin.psicologia.maestros.agenda.index', $data);
    }

    public function pendingList(Request $request)
    {
        $user = $this->verificarAcceso();
        $psicologoId = $user->id_usuario;

        if ($user->tieneRol(['administrador', 'admin']) && $request->has('psicologo_id')) {
            $psicologoId = $request->input('psicologo_id');
        }

        $prioridadFilter = $request->input('prioridad');
        $q = trim((string) $request->input('q', ''));

        $citasPendientes = Cita::obtenerPendientes($psicologoId, $prioridadFilter, $q);

        $pacientesSinCita = Usuario::obtenerPacientesSinCita($q);

        return view('admin.psicologia.maestros.agenda.components.pending-list', compact('citasPendientes', 'pacientesSinCita'));
    }

    public function crearCitaManual(Request $request)
    {
        $user = $this->verificarAcceso();

        $validated = $request->validate([
            'paciente_id'   => 'required|exists:usuario,id_usuario',
            'fecha'         => 'required|date_format:Y-m-d|after_or_equal:today',
            'hora'          => 'required|date_format:H:i',
            'bloque_inicio' => 'required|date_format:H:i',
            'bloque_fin'    => 'required|date_format:H:i',
            'prioridad'     => 'nullable|string|in:baja,media,alta,crítica,critica',
        ]);

        $pacienteId   = $validated['paciente_id'];
        $fecha        = $validated['fecha'];
        $hora         = $validated['hora'];
        $bloqueInicio = $validated['bloque_inicio'];
        $bloqueFin    = $validated['bloque_fin'];
        $prioridad    = $validated['prioridad'] ?? 'media';

        if ($prioridad === 'critica') $prioridad = 'crítica';

        // Validación: paciente sin cita pendiente/confirmada
        $existe = Cita::where('user_id', $pacienteId)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'El paciente ya tiene una cita pendiente o confirmada.'
            ]);
        }

        $paciente = Usuario::find($pacienteId);
        if (!$paciente) {
            return response()->json(['success' => false, 'message' => 'Paciente no válido.']);
        }

        // Construir el label del bloque (ej: "Lunes 07:00 - 08:15")
        $diasMap = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
            4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'
        ];
        $diaSemana   = $diasMap[\Carbon\Carbon::parse($fecha)->dayOfWeek];
        $bloqueLabel = "{$diaSemana} {$bloqueInicio} - {$bloqueFin}";

        // Verificar que el bloque esté libre
        $bloqueOcupado = Cita::where('psicologo_id', $user->id_usuario)
            ->where('fecha', $fecha)
            ->whereIn('estado', ['confirmada', 'realizada'])
            ->whereNotNull('bloque_propuesto')
            ->get()
            ->first(function ($c) use ($bloqueLabel) {
                $bloqueDecrypted = $c->bloque_propuesto;
                try {
                    $try = decrypt($bloqueDecrypted);
                    if (is_array($try) || is_object($try)) {
                        $try = json_encode($try);
                    }
                    $bloqueDecrypted = $try;
                } catch (\Exception $e) {
                    try {
                        $bloqueDecrypted = Crypt::decryptString($c->bloque_propuesto);
                    } catch (\Exception $e2) {
                        // Se queda el valor crudo
                    }
                }
                return $bloqueDecrypted
                    && Cita::normalizarBloque($bloqueDecrypted) === Cita::normalizarBloque($bloqueLabel);
            });

        if ($bloqueOcupado) {
            return response()->json([
                'success' => false,
                'message' => 'Este bloque horario ya tiene una cita confirmada. Elige otro bloque.'
            ]);
        }

        // Crear directamente como CONFIRMADA con bloque asignado
        $cita = Cita::create([
            'user_id'          => $pacienteId,
            'psicologo_id'     => $user->id_usuario,
            'fecha'            => $fecha,
            'hora'             => $hora,
            'estado'           => 'confirmada',
            'prioridad'        => $prioridad,
            'motivo'           => Crypt::encryptString('Gestionada por psicólogo'),
            'bloque_propuesto' => Crypt::encryptString($bloqueLabel),
            'confirmado_en'    => now(),
        ]);

        // Notificación in-app
        DB::table('notifications')->insert([
            'id'              => Str::uuid()->toString(),
            'type'            => 'App\Notifications\NuevaCitaNotification',
            'notifiable_type' => Usuario::class,
            'notifiable_id'   => $pacienteId,
            'data'            => json_encode([
                'type_id' => 'cita_confirmed',
                'body'    => 'El psicólogo ha agendado una cita para ti el ' .
                            \Carbon\Carbon::parse($fecha)->translatedFormat('d/m/Y') .
                            " de {$bloqueInicio} a {$bloqueFin}.",
                'url'     => route('admin.psicologia.maestros.citas.index'),
            ]),
            'read_at'    => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Correo al paciente
        try {
            $citaData = (object) [
                'id'       => $cita->id,
                'paciente' => $paciente,
                'fecha'    => $fecha,
                'hora'     => $hora,
                'bloque'   => $bloqueLabel,
            ];
            Mail::to($paciente->persona->email_persona ?? $paciente->username)
                ->queue(new \App\Mail\CitaAsignadaManualMail($citaData, $user));
        } catch (\Exception $e) {
            Log::error('Error enviando correo de cita manual: ' . $e->getMessage());
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Cita agendada correctamente.',
            'cita_id'  => $cita->id,
            'bloque'   => $bloqueLabel,
        ]);
    }

    public function dailyCitas(Request $request)
    {
        $psicologoId = $request->input('psicologo_id', Auth::id());
        $fecha = $request->input('fecha');

        return response()->json(Cita::obtenerCitasDiariasJson($psicologoId, $fecha));
    }

    public function estadisticas(Request $request)
    {
        $user = $this->verificarAcceso();

        $psicologoId = $request->input('psicologo_id', $user->id_usuario);

        if ($user->tieneRol(['psicologo']) && !$user->tieneRol(['administrador', 'admin']) && $user->id_usuario != $psicologoId) {
            abort(403);
        }

        $fechaInicio = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $fechaFin = $request->input('end_date', Carbon::now()->toDateString());
        $estado = $request->input('estado');
        $avanceId = $request->input('avance_id');
        $estadoAnimoId = $request->input('estado_animo_id');
        $prioridad = $request->input('prioridad');
        $perfilAcademico = $request->input('perfil_academico');
        $pnf = $request->input('pnf');
        $format = $request->input('format', 'pdf');
        $reportType = $request->input('report_type', 'completo');

        $citas = Cita::obtenerEstadisticas($psicologoId, $fechaInicio, $fechaFin, $estado, $avanceId, $estadoAnimoId, $prioridad, $perfilAcademico, $pnf);
        $resumen = Cita::obtenerResumenEstadistico($citas, $fechaInicio, $fechaFin, $psicologoId);
        $psicologo = Usuario::find($psicologoId);

        $avanceNombre = $avanceId ? DB::table('avances_sesion')->where('id', $avanceId)->value('nombre') : null;

        $estadoAnimoNombre = null;
        if ($estadoAnimoId) {
            $animo = DB::table('estado_animos')->where('id', $estadoAnimoId)->first();
            $estadoAnimoNombre = $animo ? "{$animo->valor} - {$animo->nombre}" : null;
        }

        if ($format === 'json') {
            return response()->json([
                'citas' => $citas,
                'resumen' => $resumen,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'psicologo' => $psicologo
            ]);
        }

        if ($format === 'html') {
            return view('admin.psicologia.maestros.agenda.estadisticas-dashboard', [
                'psicologoId' => $psicologoId,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'avances' => DB::table('avances_sesion')->orderBy('nombre', 'asc')->where('status', 1)->get(),
                'estados_animo' => DB::table('estado_animos')->orderBy('valor', 'asc')->where('status', 1)->get(),
                'prioridades' => Prioridad::obtenerParaPsicologo($psicologoId)
            ]);
        }

        if ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Agenda\EstadisticasExport($citas, $fechaInicio, $fechaFin, $estado, $psicologo, $avanceNombre, $resumen, $estadoAnimoNombre, $prioridad),
                'estadisticas_citas.xlsx'
            );
        }

        if ($format === 'word') {
            $periodo = $request->input('periodo', 'mensual');
            $tempFile = \App\Exports\Agenda\EstadisticasWordExport::generate($citas, $resumen, $fechaInicio, $fechaFin, $estado, $avanceNombre, $estadoAnimoNombre, $prioridad, $psicologo, $periodo);
            return response()->download($tempFile)->deleteFileAfterSend(true);
        }

        ini_set('memory_limit', '512M');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.psicologia.maestros.agenda.estadisticas-pdf', [
            'citas' => $citas,
            'resumen' => $resumen,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'estado' => $estado,
            'avance_id' => $avanceId,
            'avance_nombre' => $avanceNombre,
            'estado_animo_id' => $estadoAnimoId,
            'estado_animo_nombre' => $estadoAnimoNombre,
            'prioridad' => $prioridad,
            'perfil_academico' => $perfilAcademico,
            'pnf' => $pnf,
            'psicologo' => $psicologo,
            'periodo' => $request->input('periodo', 'mensual'),
            'reportType' => $reportType
        ]);

        return $pdf->stream('estadisticas_citas.pdf');
    }

    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');

        $user = $this->verificarAcceso();

        $psicologoId = $user->id_usuario;
        $psicologo = $user;

        if ($user->tieneRol(['administrador']) && $request->has('psicologo_id')) {
            $psicologoId = $request->input('psicologo_id');
            $psicologo = Usuario::find($psicologoId);

            if (!$psicologo) {
                abort(404, 'Psicólogo no encontrado');
            }
        } elseif (!$user->tieneRol(['psicologo', 'administrador'])) {
            abort(403, 'Solo los psicólogos pueden exportar su agenda.');
        }

        $viewType = $request->input('view', 'week');
        $dateStr = $request->input('date');
        $baseDate = $dateStr ? Carbon::parse($dateStr) : Carbon::now();

        $numSemanas = ($viewType === 'month') ? 4 : 1;
        $semanasInfo = [];

        for ($i = 0; $i < $numSemanas; $i++) {
            $currentDate = $baseDate->copy()->addWeeks($i);
            $inicioSemana = $currentDate->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            $finSemana = $currentDate->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

            $citas = Cita::with('paciente')
                ->where('psicologo_id', $psicologoId)
                ->where('estado', 'confirmada')
                ->whereBetween('fecha', [$inicioSemana, $finSemana])
                ->get();

            $citasCalendario = $citas->map(function($cita) {
                $nombres = $cita->paciente->persona->nombre_persona ?? $cita->nombres ?? '';
                $apellidos = $cita->paciente->persona->apellido_persona ?? $cita->apellidos ?? '';

                $cita->paciente_nombre = trim("{$nombres} {$apellidos}");

                $pNombre = explode(' ', trim($nombres))[0] ?? '';
                $pApellido = explode(' ', trim($apellidos))[0] ?? '';
                $cita->paciente_short_name = trim("{$pNombre} {$pApellido}") ?: 'Paciente';

                $cita->fecha = Carbon::parse($cita->fecha);
                return $cita;
            });

            $semanasInfo[] = [
                'currentDate' => $currentDate,
                'citasCalendario' => $citasCalendario,
            ];
        }

        $dias = Horario::diasSemana();

        $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo($psicologoId);
        $horarios = Horario::obtenerPorFiltros($psicologoId, $grupoActivo ? $grupoActivo->id : null, null);

        $horariosPorDia = [];
        foreach ($dias as $dia) {
            $horariosPorDia[$dia] = $horarios->where('dia', $dia);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.psicologia.maestros.agenda.pdf',
            compact('semanasInfo', 'dias', 'psicologo', 'horarios', 'horariosPorDia')
        )->setPaper('a4', 'landscape');

        return $pdf->stream('Agenda_Semanal_' . Str::slug($psicologo->username ?? 'Psicologo') . '.pdf');
    }
}
