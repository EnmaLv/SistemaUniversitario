<?php

use App\Http\Controllers\salud\AgendaController;
use App\Http\Controllers\salud\HorarioController;
use App\Http\Controllers\salud\AvanceSesionController;
use App\Http\Controllers\salud\ChatController;
use App\Http\Controllers\salud\CitaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\salud\EnfermedadController;
use App\Http\Controllers\salud\EstadoAnimoController;
use App\Http\Controllers\salud\EstadoAnimoDiarioController;
use App\Http\Controllers\salud\GrupoHorarioController;
use App\Http\Controllers\salud\HistoriaController;
use App\Http\Controllers\salud\MediaController;
use App\Http\Controllers\salud\NotaEvolucionCampoController;
use App\Http\Controllers\salud\NotificationController;
use App\Http\Controllers\salud\PlantillaGlobalController;
use App\Http\Controllers\salud\PlantillaSeccionController;
use App\Http\Controllers\salud\PrioridadController;
use App\Http\Controllers\salud\PublicacionController;
use App\Http\Controllers\salud\PublicacionReaccionController;

Route::get('enfermedades', [EnfermedadController::class, 'index'])->name('admin.enfermedades.index');
Route::post('enfermedades/store', [EnfermedadController::class, 'store'])->name('admin.enfermedades.store');
Route::put('enfermedades/{id}', [EnfermedadController::class, 'update'])->name('admin.enfermedades.update');
Route::delete('enfermedades/{id}', [EnfermedadController::class, 'destroy'])->name('admin.enfermedades.destroy');
Route::put('enfermedades/{id}/activar', [EnfermedadController::class, 'activar'])->name('admin.enfermedades.activar');
Route::get('enfermedades/api/search', [EnfermedadController::class, 'search'])->name('admin.enfermedades.api.search');

Route::get('/psicologia/maestros/estado_animos', [EstadoAnimoController::class, 'index'])->name('admin.psicologia.maestros.estado_animos.index');
Route::get('/psicologia/maestros/estado_animos/create', [EstadoAnimoController::class, 'create'])->name('admin.psicologia.maestros.estado_animos.create');
Route::post('/psicologia/maestros/estado_animos/store', [EstadoAnimoController::class, 'store'])->name('admin.psicologia.maestros.estado_animos.store');
Route::get('/psicologia/maestros/estado_animos/{estado_animo}/edit', [EstadoAnimoController::class, 'edit'])->name('admin.psicologia.maestros.estado_animos.edit');
Route::put('/psicologia/maestros/estado_animos/{estado_animo}', [EstadoAnimoController::class, 'update'])->name('admin.psicologia.maestros.estado_animos.update');
Route::delete('/psicologia/maestros/estado_animos/{estado_animo}', [EstadoAnimoController::class, 'destroy'])->name('admin.psicologia.maestros.estado_animos.destroy');
Route::post('/psicologia/maestros/estado_animo_diario', [EstadoAnimoDiarioController::class, 'store'])->name('admin.psicologia.maestros.estado_animo_diario.store');

Route::get('/psicologia/maestros/avances_sesion', [AvanceSesionController::class, 'index'])->name('admin.psicologia.maestros.avances_sesion.index');
Route::get('/psicologia/maestros/avances_sesion/create', [AvanceSesionController::class, 'create'])->name('admin.psicologia.maestros.avances_sesion.create');
Route::post('/psicologia/maestros/avances_sesion/store', [AvanceSesionController::class, 'store'])->name('admin.psicologia.maestros.avances_sesion.store');
Route::get('/psicologia/maestros/avances_sesion/{avance}/edit', [AvanceSesionController::class, 'edit'])->name('admin.psicologia.maestros.avances_sesion.edit');
Route::put('/psicologia/maestros/avances_sesion/{avance}', [AvanceSesionController::class, 'update'])->name('admin.psicologia.maestros.avances_sesion.update');
Route::delete('/psicologia/maestros/avances_sesion/{avance}', [AvanceSesionController::class, 'destroy'])->name('admin.psicologia.maestros.avances_sesion.destroy');


Route::get('/psicologia/maestros/horarios', [HorarioController::class, 'index'])->name('admin.psicologia.maestros.horarios.index');
Route::get('/psicologia/maestros/horarios/create', [HorarioController::class, 'create'])->name('admin.psicologia.maestros.horarios.create');
Route::post('/psicologia/maestros/horarios/store', [HorarioController::class, 'store'])->name('admin.psicologia.maestros.horarios.store');
Route::get('/psicologia/maestros/horarios/{horario}/edit', [HorarioController::class, 'edit'])->name('admin.psicologia.maestros.horarios.edit');
Route::put('/psicologia/maestros/horarios/{horario}', [HorarioController::class, 'update'])->name('admin.psicologia.maestros.horarios.update');
Route::delete('/psicologia/maestros/horarios/{horario}', [HorarioController::class, 'destroy'])->name('admin.psicologia.maestros.horarios.destroy');
Route::get('/psicologia/maestros/horarios/exportar-pdf', [HorarioController::class, 'exportarPdf'])->name('admin.psicologia.maestros.horarios.exportarPdf');
Route::patch('/psicologia/maestros/horarios/{horario}/activate', [HorarioController::class, 'activate'])->name('admin.psicologia.maestros.horarios.activate');
Route::patch('/psicologia/maestros/horarios/{horario}/deactivate', [HorarioController::class, 'deactivate'])->name('admin.psicologia.maestros.horarios.deactivate');


Route::get('/psicologia/maestros/grupos_horarios', [GrupoHorarioController::class, 'index'])->name('admin.psicologia.maestros.grupos_horarios.index');
Route::get('/psicologia/maestros/grupos_horarios/create', [GrupoHorarioController::class, 'create'])->name('admin.psicologia.maestros.grupos_horarios.create');
Route::post('/psicologia/maestros/grupos_horarios/store', [GrupoHorarioController::class, 'store'])->name('admin.psicologia.maestros.grupos_horarios.store');
Route::get('/psicologia/maestros/grupos_horarios/{grupo_horario}/edit', [GrupoHorarioController::class, 'edit'])->name('admin.psicologia.maestros.grupos_horarios.edit');
Route::put('/psicologia/maestros/grupos_horarios/{grupo_horario}', [GrupoHorarioController::class, 'update'])->name('admin.psicologia.maestros.grupos_horarios.update');
Route::delete('/psicologia/maestros/grupos_horarios/{grupo_horario}', [GrupoHorarioController::class, 'destroy'])->name('admin.psicologia.maestros.grupos_horarios.destroy');
Route::post('/psicologia/maestros/grupos_horarios/store-from-horarios', [GrupoHorarioController::class, 'storeFromHorarios'])->name('admin.psicologia.maestros.grupos_horarios.store_from_horarios');
Route::patch('/psicologia/maestros/grupos_horarios/{id}/activate', [GrupoHorarioController::class, 'activate'])->name('admin.psicologia.maestros.grupos_horarios.activate');
Route::patch('/psicologia/maestros/grupos_horarios/{id}/deactivate', [GrupoHorarioController::class, 'deactivate'])->name('admin.psicologia.maestros.grupos_horarios.deactivate');


Route::get('/psicologia/maestros/plantillas_globales', [PlantillaGlobalController::class, 'index'])->name('admin.psicologia.maestros.plantillas_globales.index');
Route::get('/psicologia/maestros/plantillas_globales/create', [PlantillaGlobalController::class, 'create'])->name('admin.psicologia.maestros.plantillas_globales.create');
Route::post('/psicologia/maestros/plantillas_globales/store', [PlantillaGlobalController::class, 'store'])->name('admin.psicologia.maestros.plantillas_globales.store');
Route::get('/psicologia/maestros/plantillas_globales/{avance}/edit', [PlantillaGlobalController::class, 'edit'])->name('admin.psicologia.maestros.plantillas_globales.edit');
Route::put('/psicologia/maestros/plantillas_globales/{avance}', [PlantillaGlobalController::class, 'update'])->name('admin.psicologia.maestros.plantillas_globales.update');
Route::delete('/psicologia/maestros/plantillas_globales/{avance}', [PlantillaGlobalController::class, 'destroy'])->name('admin.psicologia.maestros.plantillas_globales.destroy');
Route::post('/psicologia/maestros/plantillas_globales/aplicar', [PlantillaGlobalController::class, 'apply'])->name('admin.psicologia.maestros.plantillas_globales.apply');


Route::get('/psicologia/maestros/plantillas', [PlantillaSeccionController::class, 'index'])->name('admin.psicologia.maestros.plantillas.index');
Route::get('/psicologia/maestros/plantillas/create', [PlantillaSeccionController::class, 'create'])->name('admin.psicologia.maestros.plantillas.create');
Route::post('/psicologia/maestros/plantillas/store', [PlantillaSeccionController::class, 'store'])->name('admin.psicologia.maestros.plantillas.store');
Route::get('/psicologia/maestros/plantillas/{avance}/edit', [PlantillaSeccionController::class, 'edit'])->name('admin.psicologia.maestros.plantillas.edit');
Route::put('/psicologia/maestros/plantillas/{avance}', [PlantillaSeccionController::class, 'update'])->name('admin.psicologia.maestros.plantillas.update');
Route::delete('/psicologia/maestros/plantillas/{avance}', [PlantillaSeccionController::class, 'destroy'])->name('admin.psicologia.maestros.plantillas.destroy');


Route::get('/psicologia/maestros/campos_evolucion', [NotaEvolucionCampoController::class, 'index'])->name('admin.psicologia.maestros.campos_evolucion.index');
Route::get('/psicologia/maestros/campos_evolucion/create', [NotaEvolucionCampoController::class, 'create'])->name('admin.psicologia.maestros.campos_evolucion.create');
Route::post('/psicologia/maestros/campos_evolucion/store', [NotaEvolucionCampoController::class, 'store'])->name('admin.psicologia.maestros.campos_evolucion.store');
Route::get('/psicologia/maestros/campos_evolucion/{campo}/edit', [NotaEvolucionCampoController::class, 'edit'])->name('admin.psicologia.maestros.campos_evolucion.edit');
Route::patch('/psicologia/maestros/campos_evolucion/{campo}', [NotaEvolucionCampoController::class, 'update'])->name('admin.psicologia.maestros.campos_evolucion.update');
Route::delete('/psicologia/maestros/campos_evolucion/{campo}', [NotaEvolucionCampoController::class, 'destroy'])->name('admin.psicologia.maestros.campos_evolucion.destroy');


Route::get('/psicologia/maestros/prioridades', [PrioridadController::class, 'index'])->name('admin.psicologia.maestros.prioridades.index');
Route::get('/psicologia/maestros/prioridades/create', [PrioridadController::class, 'create'])->name('admin.psicologia.maestros.prioridades.create');
Route::post('/psicologia/maestros/prioridades/store', [PrioridadController::class, 'store'])->name('admin.psicologia.maestros.prioridades.store');
Route::get('/psicologia/maestros/prioridades/{prioridad}/edit', [PrioridadController::class, 'edit'])->name('admin.psicologia.maestros.prioridades.edit');
Route::put('/psicologia/maestros/prioridades/{prioridad}', [PrioridadController::class, 'update'])->name('admin.psicologia.maestros.prioridades.update');
Route::delete('/psicologia/maestros/prioridades/{prioridad}', [PrioridadController::class, 'destroy'])->name('admin.psicologia.maestros.prioridades.destroy');


Route::get('/psicologia/maestros/publicaciones/mural', [PublicacionController::class, 'mural'])->name('admin.psicologia.maestros.publicaciones.mural');
Route::get('/psicologia/maestros/publicaciones', [PublicacionController::class, 'index'])->name('admin.psicologia.maestros.publicaciones.index');
Route::get('/psicologia/maestros/publicaciones/create', [PublicacionController::class, 'create'])->name('admin.psicologia.maestros.publicaciones.create');
Route::post('/psicologia/maestros/publicaciones', [PublicacionController::class, 'store'])->name('admin.psicologia.maestros.publicaciones.store');
Route::get('/psicologia/maestros/publicaciones/{id}/edit', [PublicacionController::class, 'edit'])->name('admin.psicologia.maestros.publicaciones.edit');
Route::put('/psicologia/maestros/publicaciones/{id}', [PublicacionController::class, 'update'])->name('admin.psicologia.maestros.publicaciones.update');
Route::delete('/psicologia/maestros/publicaciones/{id}', [PublicacionController::class, 'destroy'])->name('admin.psicologia.maestros.publicaciones.destroy');
Route::post('/psicologia/maestros/publicaciones/{id}/reaccionar', [PublicacionReaccionController::class, 'toggle'])->name('admin.psicologia.maestros.publicaciones.reaccionar');

Route::get('/media/profile-photos/{filename}', [MediaController::class, 'showProfilePhoto'])->name('media.profile_photos');
Route::get('/media/publicaciones/{filename}', [MediaController::class, 'showPublicacionMedia'])->name('media.publicaciones');

Route::get('/psicologia/maestros/citas', [CitaController::class, 'index'])->name('admin.psicologia.maestros.citas.index');
Route::get('/psicologia/maestros/citas/create', [CitaController::class, 'create'])->name('admin.psicologia.maestros.citas.create');
Route::post('/psicologia/maestros/citas', [CitaController::class, 'store'])->name('admin.psicologia.maestros.citas.store');
Route::get('/psicologia/maestros/citas/{id}/edit', [CitaController::class, 'edit'])->name('admin.psicologia.maestros.citas.edit');
Route::put('/psicologia/maestros/citas/{id}', [CitaController::class, 'update'])->name('admin.psicologia.maestros.citas.update');
Route::get('/psicologia/maestros/citas/{cita}/constancia-pdf', [CitaController::class, 'descargarConstanciaPdf'])->name('admin.psicologia.maestros.citas.constancia.pdf');
Route::delete('/psicologia/maestros/citas/{cita}', [CitaController::class, 'destroy'])->name('admin.psicologia.maestros.citas.destroy');
Route::patch('/psicologia/maestros/citas/{cita}/cancelar-psicologo', [CitaController::class, 'cancelConfirmedByPsicologo'])->name('admin.psicologia.maestros.citas.cancel.psicologo');
Route::get('/psicologia/maestros/citas/available_slots', [CitaController::class, 'getAvailableSlots'])->name('admin.psicologia.maestros.citas.available_slots');
Route::get('/psicologia/maestros/citas/historial-json', [CitaController::class, 'historyJson'])->name('admin.psicologia.maestros.citas.history.json');
Route::patch('/psicologia/maestros/citas/{cita}/cancel', [CitaController::class, 'cancel'])->name('admin.psicologia.maestros.citas.cancel');
Route::patch('/psicologia/maestros/citas/{cita}/rechazar', [CitaController::class, 'reject'])->name('admin.psicologia.maestros.citas.reject');
Route::patch('/psicologia/maestros/citas/{cita}/proponer', [CitaController::class, 'proponer'])->name('admin.psicologia.maestros.citas.proponer');
Route::patch('/psicologia/maestros/citas/{cita}/quitar-propuesta', [CitaController::class, 'quitarPropuesta'])->name('admin.psicologia.maestros.citas.quitar_propuesta');
Route::patch('/psicologia/maestros/citas/{cita}/enviar-propuesta', [CitaController::class, 'enviarPropuesta'])->name('admin.psicologia.maestros.citas.enviar_propuesta');
Route::patch('/psicologia/maestros/citas/{cita}/responder-propuesta', [CitaController::class, 'responderPropuesta'])->name('admin.psicologia.maestros.citas.responder_propuesta');
Route::patch('/psicologia/maestros/citas/{cita}/aceptar', [CitaController::class, 'accept'])->name('admin.psicologia.maestros.citas.accept');
Route::patch('/psicologia/maestros/citas/{cita}/posponer', [CitaController::class, 'posponer'])->name('admin.psicologia.maestros.citas.posponer');
Route::patch('/psicologia/maestros/citas/{cita}/realizar', [CitaController::class, 'complete'])->name('admin.psicologia.maestros.citas.realizar');
Route::patch('/psicologia/maestros/citas/{cita}/no-asistio', [CitaController::class, 'noAsistio'])->name('admin.psicologia.maestros.citas.no_asistio');
Route::patch('/psicologia/maestros/citas/{cita}/dismiss-cancel', [CitaController::class, 'dismissCancelMessage'])->name('admin.psicologia.maestros.citas.dismiss_cancel');
Route::get('/psicologia/maestros/citas/{cita}/editar-nota', [CitaController::class, 'editNote'])->name('admin.psicologia.maestros.citas.edit.note');
Route::get('/psicologia/maestros/citas/{cita}/descargar-pdf', [CitaController::class, 'downloadPdf'])->name('admin.psicologia.maestros.citas.download.pdf');
Route::patch('/psicologia/maestros/citas/{cita}/notas', [CitaController::class, 'updateNote'])->name('admin.psicologia.maestros.citas.update.notas');
Route::post('/psicologia/maestros/citas/campos-ajax', [CitaController::class, 'storeCampoAjax'])->name('admin.psicologia.maestros.citas.campos.store.ajax');
Route::get('/psicologia/maestros/citas/{cita}/json', [CitaController::class, 'showJson'])->name('admin.psicologia.maestros.citas.show.json');
Route::patch('/psicologia/maestros/citas/{cita}/prioridad', [CitaController::class, 'updatePriority'])->name('admin.psicologia.maestros.citas.update.prioridad');


Route::get('/psicologia/maestros/agenda', [AgendaController::class, 'index'])->name('admin.psicologia.maestros.agenda.index');
Route::get('/psicologia/maestros/agenda/pending-list', [AgendaController::class, 'pendingList'])->name('admin.psicologia.maestros.agenda.pending.list');
Route::post('/psicologia/maestros/agenda/crear_cita_manual', [AgendaController::class, 'crearCitaManual'])->name('admin.psicologia.maestros.agenda.crear_cita_manual');
Route::get('/psicologia/maestros/agenda/daily-citas', [AgendaController::class, 'dailyCitas'])->name('admin.psicologia.maestros.agenda.daily_citas');
Route::get('/psicologia/maestros/agenda/exportar-pdf', [AgendaController::class, 'exportarPdf'])->name('admin.psicologia.maestros.agenda.exportarPdf');
Route::get('/psicologia/maestros/agenda/estadisticas', [AgendaController::class, 'estadisticas'])->name('admin.psicologia.maestros.agenda.estadisticas');
Route::resource('/psicologia/maestros/agenda/prioridades', PrioridadController::class)->names('admin.psicologia.maestros.agenda.prioridades')->except(['show', 'edit', 'update']);
Route::resource('/psicologia/maestros/agenda/estado-animos', EstadoAnimoController::class)->names('admin.psicologia.maestros.agenda.estado_animos')->except(['show']);


Route::get('/psicologia/maestros/historias', [HistoriaController::class, 'index'])->name('admin.psicologia.maestros.historias.index');
Route::get('/psicologia/maestros/historias/{paciente}', [HistoriaController::class, 'show'])->name('admin.psicologia.maestros.historias.show');
Route::get('/psicologia/maestros/historias/{paciente}/download-zip', [HistoriaController::class, 'downloadZip'])->name('admin.psicologia.maestros.historias.downloadZip');
Route::get('/psicologia/maestros/historias/buscar/paciente', [HistoriaController::class, 'buscarPaciente'])->name('admin.psicologia.maestros.historias.buscar');
Route::get('/psicologia/maestros/historias/exportar/pdf', [HistoriaController::class, 'exportarPdf'])->name('admin.psicologia.maestros.historias.exportar.pdf');
Route::get('/psicologia/maestros/historias/exportar/excel', [HistoriaController::class, 'exportarExcel'])->name('admin.psicologia.maestros.historias.exportar.excel');
Route::patch('/psicologia/maestros/historias/{paciente}', [HistoriaController::class, 'update'])->name('admin.psicologia.maestros.historias.update');
Route::get('/psicologia/maestros/historias/{paciente}/reporte-pdf', [HistoriaController::class, 'reportePdf'])->name('admin.psicologia.maestros.historias.reportePdf');
Route::get('/psicologia/maestros/historias/{paciente}/reporte-word', [HistoriaController::class, 'reporteWord'])->name('admin.psicologia.maestros.historias.reporteWord');
Route::get('/psicologia/maestros/historias/{paciente}/expediente-completo-pdf', [HistoriaController::class, 'expedienteCompletoPdf'])->name('admin.psicologia.maestros.historias.expedienteCompletoPdf');
Route::get('/psicologia/maestros/historias/{paciente}/expediente-completo-word', [HistoriaController::class, 'expedienteCompletoWord'])->name('admin.psicologia.maestros.historias.expedienteCompletoWord');
Route::post('/psicologia/maestros/historias/enfermedad/vincular', [HistoriaController::class, 'vincularEnfermedad'])->name('admin.psicologia.maestros.historias.enfermedad.vincular');
Route::delete('/psicologia/maestros/historias/enfermedad/desvincular', [HistoriaController::class, 'desvincularEnfermedad'])->name('admin.psicologia.maestros.historias.enfermedad.desvincular');
Route::post('/psicologia/maestros/historias/{paciente}/secciones', [HistoriaController::class, 'storeSeccion'])->name('admin.psicologia.maestros.historias.secciones.store');
Route::delete('/psicologia/maestros/historias/secciones/{seccion}', [HistoriaController::class, 'destroySeccion'])->name('admin.psicologia.maestros.historias.secciones.destroy');
Route::patch('/psicologia/maestros/historias/secciones/{seccion}/reorder', [HistoriaController::class, 'reorderSeccion'])->name('admin.psicologia.maestros.historias.secciones.reorder');
Route::post('/psicologia/maestros/historias/{paciente}/evolucion', [HistoriaController::class, 'storeEvolucion'])->name('admin.psicologia.maestros.historias.evolucion.store');
Route::post('/psicologia/maestros/historias/{paciente}/evolucion-pdf', [HistoriaController::class, 'evolucionPdf'])->name('admin.psicologia.maestros.historias.evolucion.pdf');
Route::post('/psicologia/maestros/historias/{paciente}/evolucion-word', [HistoriaController::class, 'evolucionWord'])->name('admin.psicologia.maestros.historias.evolucion.word');


Route::get('/notificaciones/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('/notificaciones/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
