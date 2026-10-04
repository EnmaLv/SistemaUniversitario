<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use App\Models\salud\Cita;
use App\Models\salud\Enfermedad;
use App\Models\salud\AvanceSesion;
use App\Models\salud\EstadoAnimo;
use App\Models\salud\Prioridad;
use App\Models\salud\HistoriaClinica;
use App\Models\salud\SeccionPersonalizada;
use App\Models\salud\SegmentoPersonalizado;
use App\Models\salud\PlantillaSeccion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use Exception;
use Illuminate\Support\Facades\DB;

class HistoriaController extends Controller
{
    public function buscarPaciente(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return response()->json([]);
        }

        $pacientes = HistoriaClinica::buscarPacientes($query, Auth::id());

        return response()->json($pacientes);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $filters = $this->extraerFiltros($request);
        $historias = HistoriaClinica::obtenerListado(Auth::id(), $search, $filters);

        $page = $request->get('page', 1);
        $cantidad = 9;

        $paginator = new LengthAwarePaginator(
            $historias->slice(($page - 1) * $cantidad, $cantidad),
            $historias->count(),
            $cantidad,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $enfermedades = Enfermedad::obtenerTodasActivas();
        $avances = AvanceSesion::obtenerPorPsicologo(Auth::id());
        $estadosAnimo = EstadoAnimo::obtenerActivos();
        $prioridades = Prioridad::obtenerParaPsicologo(Auth::id());
        $pnfs = [
            'Administracion' => 'Administración',
            'Mecanica' => 'Mecánica',
            'Mantenimiento' => 'Mantenimiento',
            'Electricidad' => 'Electricidad',
            'Veterinaria' => 'Veterinaria',
            'Informatica' => 'Informática',
            'PDA' => 'PDA',
            'Distribucion_Logistica' => 'Distribución y Logística',
            'Agroalimentacion' => 'Agroalimentación',
            'Seguridad_Alimentaria_Nutricional' => 'Seguridad alimentaria y Cultura Nutricional'
        ];

        $enfermedadSeleccionada = !empty($filters['enfermedad_id'])
            ? Enfermedad::obtenerPorId($filters['enfermedad_id'])
            : null;

        return view('admin.psicologia.maestros.historias.index', [
            'historias' => $paginator,
            'search' => $search,
            'filters' => $filters,
            'enfermedadSeleccionada' => $enfermedadSeleccionada,
            'enfermedades' => $enfermedades,
            'avances' => $avances,
            'estadosAnimo' => $estadosAnimo,
            'prioridades' => $prioridades,
            'pnfs' => $pnfs
        ]);
    }

    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $search = $request->input('search');
        $filters = $this->extraerFiltros($request);
        $historias = HistoriaClinica::obtenerListado(Auth::id(), $search, $filters);
        $filterNames = $this->obtenerNombresFiltros($filters);

        $pdf = PDF::loadView('admin.psicologia.maestros.historias.listadoPDF', compact('historias', 'search', 'filterNames'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Listado_Pacientes_' . date('Y_m_d') . '.pdf');
    }

    public function exportarExcel(Request $request)
    {
        $search = $request->input('search');
        $filters = $this->extraerFiltros($request);
        $filterNames = $this->obtenerNombresFiltros($filters);

        return Excel::download(new \App\Exports\Historias\PacientesExport(Auth::id(), $search, $filters, $filterNames), 'Listado_Pacientes_' . date('Y_m_d') . '.xlsx');
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

    public function show($pacienteId, Request $request)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);

        $categoriaFiltro = $this->resolverCategoria($request);
        $tipo = $request->get('tipo', $categoriaFiltro);
        $returnTo = $request->get('return_to');
        $editing = $request->get('editing');

        $paciente->name = trim(($paciente->nombre_persona ?? '') . ' ' . ($paciente->apellido_persona ?? ''));
        $paciente->primera_cita = Cita::obtenerFechaPrimeraCita($paciente->id);

        $historia = HistoriaClinica::iniciarHistoria($paciente->id, Auth::id());

        $citasRaw = Cita::obtenerCitasPsicologoPacienteRaw($paciente->id, Auth::id());
        foreach ($citasRaw as $c) {
            $cDecrypted = Cita::obtenerDetalle($c->id);
            if ($cDecrypted && $cDecrypted->motivo === 'Nota de Evolución (Manual)') {
                $isEmpty = true;
                if ($cDecrypted->notas) {
                    $data = json_decode($cDecrypted->notas, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                        $hasContent = false;
                        $fieldsToCheck = ['motivo_consulta', 'observaciones', 'intervenciones', 'plan_tratamiento', 'proxima_cita_razon', 'titulo_manual', 'estado_animo_id', 'avance_estado', 'diagnosticos'];
                        foreach ($fieldsToCheck as $field) {
                            if (!empty($data[$field])) {
                                $hasContent = true;
                                break;
                            }
                        }
                        if ($hasContent) {
                            $isEmpty = false;
                        }
                    } else if (trim($cDecrypted->notas) !== '') {
                        $isEmpty = false;
                    }
                }
                if ($isEmpty) {
                    Cita::eliminarFisicamente($c->id);
                }
            }
        }

        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, Auth::id());
        $citasPaciente = Cita::obtenerCitasRealizadas($paciente->id, Auth::id());
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);
        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $plantillas = PlantillaSeccion::obtenerPorPsicologo(Auth::id());

        return view('admin.psicologia.maestros.historias.show', [
            'paciente' => $paciente,
            'historia' => $historia,
            'citasPaciente' => $citasPaciente,
            'stats' => $stats,
            'enfermedadesVinculadas' => $enfermedadesVinculadas,
            'seccionesPersonalizadas' => $seccionesPersonalizadas,
            'plantillas' => $plantillas,
            'tipo'            => $tipo,
            'categoriaFiltro' => $categoriaFiltro,
            'returnTo'        => $returnTo,
            'editing'         => $editing
        ]);
    }

    public function vincularEnfermedad(Request $request)
    {
        $request->validate([
            'historia_clinica_id' => 'required|exists:historia_clinicas,id',
            'enfermedad_id' => 'required|exists:enfermedades,id',
            'contexto' => 'required|string',
        ]);

        $result = HistoriaClinica::vincularEnfermedad($request->historia_clinica_id, $request->enfermedad_id, $request->contexto);
        return response()->json($result);
    }

    public function desvincularEnfermedad(Request $request)
    {
        $request->validate([
            'link_id' => 'required'
        ]);

        HistoriaClinica::desvincularEnfermedad($request->link_id);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $pacienteId)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);
        $historia = HistoriaClinica::obtenerPorPaciente($paciente->id);
        abort_if(!$historia, 404);

        SegmentoPersonalizado::actualizarSegmentosExtra($request->segmentos_extra);
        SegmentoPersonalizado::actualizarMetadata($request->segmentos_metadata);

        return back()->with('success', 'Historia clínica actualizada correctamente.');
    }

    public function storeSeccion(Request $request, $pacienteId)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion_general' => 'nullable|string|max:255',
            'segmentos_titulos' => 'required|array|min:1|max:4',
            'segmentos_titulos.*' => 'required|string|max:255',
        ]);

        $historia = HistoriaClinica::obtenerPorPacienteOrFail($pacienteId);

        SeccionPersonalizada::crear($historia, $request->all());

        return back()->with('success', "Sección \"{$request->titulo}\" creada.");
    }

    public function destroySeccion($seccionId)
    {
        $seccion = SeccionPersonalizada::obtenerPorId($seccionId);
        if (!$seccion) {
            abort(404);
        }

        $historia = HistoriaClinica::obtenerPorId($seccion->historia_clinica_id);
        if (!$historia) {
            abort(404, 'Historia clínica no encontrada.');
        }

        $userId = (int) Auth::id();
        $esDueno = (int) $historia->psicologo_id === $userId;

        $tieneCitas = DB::table('citas')
            ->where('psicologo_id', $userId)
            ->where('user_id', $historia->user_id)
            ->exists();

        if (!$esDueno && !$tieneCitas) {
            abort(403, 'No tienes permiso para eliminar esta sección.');
        }

        $titulo = $seccion->titulo;

        DB::transaction(function () use ($seccionId) {
            // 1) Borrar primero los segmentos hijos
            DB::table('historia_segmentos_personalizados')
                ->where('seccion_id', $seccionId)
                ->delete();

            // 2) Ahora la sección
            DB::table('historia_secciones_personalizadas')
                ->where('id', $seccionId)
                ->delete();
        });

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sección \"{$titulo}\" eliminada.",
            ]);
        }

        return back()->with('success', "Sección \"{$titulo}\" eliminada.");
    }

    public function reorderSeccion(Request $request, $seccionId)
    {
        $request->validate([
            'direccion' => 'required|in:up,down',
        ]);

        $seccion = SeccionPersonalizada::obtenerPorId($seccionId);
        if (!$seccion) {
            abort(404);
        }

        $historia = HistoriaClinica::obtenerPorId($seccion->historia_clinica_id);
        if (!$historia || $historia->psicologo_id != Auth::id()) {
            abort(403);
        }

        SeccionPersonalizada::reordenar($seccionId, $request->input('direccion'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Sección reordenada correctamente.');
    }

    public function storeEvolucion($pacienteId)
    {
        $historia = HistoriaClinica::verificarAcceso($pacienteId, Auth::id());

        if (!$historia) {
            abort(403);
        }

        $cita = Cita::crearNotaManual($pacienteId, Auth::id());

        return redirect()->route('admin.psicologia.maestros.citas.edit.note', $cita->id);
    }

    public function downloadZip($pacienteId)
    {
        $userId = Auth::id();
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);

        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $citasPaciente = Cita::obtenerCitasRealizadas($paciente->id, $userId);

        $zip = new ZipArchive();
        $zipFileName = 'Expedientes_' . Str::slug($paciente->name) . '_' . time() . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $zip->addFromString('Expediente_General_' . Str::slug($paciente->name) . '.pdf', $this->generateGeneralPdfContent($paciente, $historia));

            foreach ($citasPaciente as $index => $cita) {
                $numeroSesion = $citasPaciente->count() - $index;
                $fechaStr = $cita->fecha ? $cita->fecha->format('Y-m-d') : 'SinFecha';
                $fileName = "Sesion_{$numeroSesion}_{$fechaStr}.pdf";
                $zip->addFromString($fileName, $this->generateSesionPdfContent($cita));
            }

            $zip->close();
        } else {
            abort(500, 'No se pudo crear el archivo ZIP.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function reportePdf($pacienteId)
    {
        ini_set('memory_limit', '512M');
        $userId = Auth::id();
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));

        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);

        $pdf = PDF::loadView('admin.psicologia.maestros.historias.reportePDF', compact('paciente', 'historia', 'seccionesPersonalizadas', 'stats', 'enfermedadesVinculadas'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Expediente_General_' . Str::slug($paciente->name) . '.pdf');
    }

    public function reporteWord($pacienteId)
    {
        $userId = Auth::id();
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);

        $tempFile = \App\Exports\Historias\WordExport::generateExpedienteGeneral($paciente, $seccionesPersonalizadas, $enfermedadesVinculadas);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function expedienteCompletoPdf($pacienteId)
    {
        ini_set('memory_limit', '512M');
        $userId = Auth::id();
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);

        $citasSeleccionadas = Cita::obtenerCitasRealizadas($paciente->id, $userId);
        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);

        $pdf = PDF::loadView('admin.psicologia.maestros.historias.expedienteCompletoPDF', compact('paciente', 'historia', 'seccionesPersonalizadas', 'enfermedadesVinculadas', 'citasSeleccionadas', 'stats'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Expediente_Completo_' . Str::slug($paciente->name) . '.pdf');
    }

    public function expedienteCompletoWord($pacienteId)
    {
        $userId = Auth::id();
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));
        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);

        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $seccionesPersonalizadas = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        $enfermedadesVinculadas = HistoriaClinica::obtenerEnfermedadesVinculadas($historia->id);
        $citasSeleccionadas = Cita::obtenerCitasRealizadas($paciente->id, $userId);
        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);

        $psicologo = $user->obtenerUsuarioPorId($userId);
        $psicologoName = trim(($psicologo->nombres ?? '') . ' ' . ($psicologo->apellidos ?? ''));

        $tempFile = \App\Exports\Historias\WordExport::generateExpedienteCompleto($paciente, $historia, $seccionesPersonalizadas, $enfermedadesVinculadas, $citasSeleccionadas, $stats, $psicologoName);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function evolucionPdf($pacienteId)
    {
        ini_set('memory_limit', '512M');
        $request = request();
        $userId = Auth::id();
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));

        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);
        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $citasIds = $request->input('citas_ids', []);
        $todasLasCitas = Cita::obtenerCitasRealizadas($paciente->id, $userId);

        $citasSeleccionadas = !empty($citasIds)
            ? $todasLasCitas->filter(fn($c) => in_array($c->id, $citasIds))
            : $todasLasCitas;

        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);
        $modoDescarga = $request->input('modo_descarga', 'unificado');

        if ($modoDescarga === 'individuales') {
            $zip = new ZipArchive();
            $zipFileName = 'Evolucion_Individuales_' . Str::slug($paciente->name) . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            if (!file_exists(storage_path('app/public'))) {
                mkdir(storage_path('app/public'), 0755, true);
            }

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $count = 1;
                foreach ($citasSeleccionadas as $cita) {
                    $citasUna = collect([$cita]);
                    $pdf = PDF::loadView('admin.psicologia.maestros.historias.evolucionPDF', ['paciente' => $paciente, 'historia' => $historia, 'citasSeleccionadas' => $citasUna, 'stats' => $stats])->setPaper('a4', 'portrait');
                    $fecha = $cita->fecha ? $cita->fecha->format('Y-m-d') : 'SinFecha';
                    $pdfContent = $pdf->output();
                    $zip->addFromString("Nota_{$count}_{$fecha}.pdf", $pdfContent);
                    $count++;
                }
                $zip->close();
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            return back()->with('error', 'Error al crear el archivo ZIP.');
        }

        $pdf = PDF::loadView('admin.psicologia.maestros.historias.evolucionPDF', compact('paciente', 'historia', 'citasSeleccionadas', 'stats'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Evolucion_' . Str::slug($paciente->name) . '.pdf');
    }

    public function evolucionWord($pacienteId)
    {
        $request = request();
        $userId = Auth::id();
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($pacienteId);
        abort_if(!$paciente, 404);

        $paciente->name = trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellidos ?? ''));

        $historia = HistoriaClinica::verificarAcceso($paciente->id, $userId);
        if (!$historia) {
            abort(403, 'No tienes acceso a este expediente.');
        }

        $citasIds = $request->input('citas_ids', []);
        $todasLasCitas = Cita::obtenerCitasRealizadas($paciente->id, $userId);

        $citasSeleccionadas = !empty($citasIds)
            ? $todasLasCitas->filter(fn($c) => in_array($c->id, $citasIds))
            : $todasLasCitas;

        $stats = Cita::obtenerEstadisticasPaciente($paciente->id, $userId);

        $psicologo = $user->obtenerUsuarioPorId($userId);
        $psicologoName = trim(($psicologo->nombres ?? '') . ' ' . ($psicologo->apellidos ?? ''));

        $modoDescarga = $request->input('modo_descarga', 'unificado');

        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        if ($modoDescarga === 'individuales') {
            $zip = new ZipArchive();
            $zipFileName = 'Evolucion_Individuales_' . Str::slug($paciente->name) . '.zip';
            $zipPath = storage_path('app/public/' . $zipFileName);

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $count = 1;
                $tempFiles = [];
                foreach ($citasSeleccionadas as $cita) {
                    $tempDocPath = storage_path('app/public/temp_doc_' . $count . '.docx');
                    \App\Exports\Historias\WordExport::generateEvolucion(collect([$cita]), $paciente, $historia, $stats, $psicologoName, $tempDocPath);
                    $fecha = $cita->fecha ? $cita->fecha->format('Y-m-d') : 'SinFecha';
                    $zip->addFile($tempDocPath, "Nota_{$count}_{$fecha}.docx");
                    $tempFiles[] = $tempDocPath;
                    $count++;
                }
                $zip->close();
                foreach ($tempFiles as $f) @unlink($f);
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            return back()->with('error', 'Error al crear el archivo ZIP.');
        }

        $fileName = 'Evolucion_' . Str::slug($paciente->name) . '.docx';
        $tempPath = storage_path('app/public/' . $fileName);

        \App\Exports\Historias\WordExport::generateEvolucion($citasSeleccionadas, $paciente, $historia, $stats, $psicologoName, $tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    private function extraerFiltros(Request $request): array
    {
        return [
            'pnf' => $request->input('pnf'),
            'edad' => $request->input('edad'),
            'tipo_filtro_fecha' => $request->input('tipo_filtro_fecha', 'rango'),
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
            'enfermedad_id' => $request->input('enfermedad_id'),
            'prioridad' => $request->input('prioridad'),
            'avance_id' => $request->input('avance_id'),
            'estado_animo_id' => $request->input('estado_animo_id')
        ];
    }

    private function obtenerNombresFiltros(array $filters): array
    {
        return [
            'pnf' => $filters['pnf'],
            'edad' => $filters['edad'],
            'fecha_desde' => $filters['fecha_desde'],
            'fecha_hasta' => $filters['fecha_hasta'],
            'prioridad' => $filters['prioridad'],
            'enfermedad' => !empty($filters['enfermedad_id']) ? Enfermedad::obtenerNombrePorId($filters['enfermedad_id']) : null,
            'avance' => !empty($filters['avance_id']) ? AvanceSesion::obtenerNombrePorId($filters['avance_id']) : null,
            'estado_animo' => !empty($filters['estado_animo_id']) ? EstadoAnimo::obtenerNombrePorId($filters['estado_animo_id']) : null,
        ];
    }

    private function generateGeneralPdfContent($paciente, $historia): string
    {
        $headerLines = [
            'Psico-Guia UPTP',
            'Expediente General',
            'Paciente: ' . $paciente->name,
            'Email: ' . $paciente->email,
            'Generado el: ' . now()->format('d/m/Y H:i A'),
            '',
            '=== SECCIONES CLINICAS ===',
            '',
        ];

        $secciones = HistoriaClinica::obtenerSeccionesConSegmentos($historia->id);
        foreach ($secciones as $seccion) {
            $headerLines[] = '=== ' . mb_strtoupper($seccion->titulo) . ' ===';
            if (!empty($seccion->descripcion_general)) {
                $headerLines[] = '(' . $seccion->descripcion_general . ')';
            }
            $headerLines[] = '';

            foreach ($seccion->segmentos as $segmento) {
                $headerLines[] = '- ' . $segmento->titulo . ':';
                $contenido = $segmento->contenido ?: 'Sin registro.';
                $lineasContenido = explode("\n", trim($contenido));
                foreach ($lineasContenido as $linea) {
                    $headerLines[] = '  ' . $linea;
                }
                $headerLines[] = '';
            }
        }

        return $this->buildRawPdf($headerLines);
    }

    private function generateSesionPdfContent($cita): string
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $paciente = $user->obtenerUsuarioPorId($cita->user_id);
        $psicologo = $user->obtenerUsuarioPorId($cita->psicologo_id);
        $pacienteName = $paciente ? $paciente->name : 'Desconocido';
        $psicologoName = $psicologo ? $psicologo->name : 'Desconocido';

        $headerLines = [
            'Psico-Guía UPTP',
            'Nota de sesión',
            'Paciente: ' . ($pacienteName ?: 'Desconocido'),
            'Psicólogo: ' . ($psicologoName ?: 'Desconocido'),
            'Fecha de sesión: ' . ($cita->fecha ? $cita->fecha->format('d/m/Y') : 'Sin fecha'),
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
                    $noteLines[] = 'Estado: ' . ucfirst(str_replace('_', ' ', $data['avance_estado'] ?? 'N/A'));
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
        } catch (Exception $e) {
            $noteLines = $rawNotas ? explode("\n", trim($rawNotas)) : ['No se registraron notas para esta sesión.'];
        }

        $lines = array_merge($headerLines, $noteLines);

        return $this->buildRawPdf($lines);
    }

    private function buildRawPdf(array $lines): string
    {
        $content = '';
        $y = 760;
        foreach ($lines as $line) {
            if ($y < 40) {
                break;
            }

            $encodedLine = @iconv('UTF-8', 'CP1252//TRANSLIT', $line);
            if ($encodedLine === false) {
                $encodedLine = $line;
            }

            $encodedLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encodedLine);
            $content .= "BT /F1 12 Tf 45 $y Td (" . $encodedLine . ") Tj ET\n";
            $y -= 18;
        }

        $streamLength = strlen($content);
        $pdfParts = [];
        $pdfParts[] = "%PDF-1.4\n";
        $pdfParts[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdfParts[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdfParts[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $pdfParts[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";
        $pdfParts[] = "5 0 obj\n<< /Length $streamLength >>\nstream\n" . $content . "endstream\nendobj\n";

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
}
