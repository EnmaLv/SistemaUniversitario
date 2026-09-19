<!-- LISTADO DE PACIENTES PENDIENTES -->
<div id="pendingListWrapper" class="flex flex-col flex-1 h-full min-h-0 overflow-hidden">
    <div class="flex-1 overflow-y-auto invisible-scrollbar space-y-3 pr-1">
        @if ($citasPendientes->isEmpty() && (!isset($pacientesSinCita) || $pacientesSinCita->isEmpty()))
            <div id="pendingNoResultsMessage"
                class="flex flex-col items-center justify-center p-6 text-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 my-2">
                <svg class="w-8 h-8 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <p class="text-xs font-medium text-slate-400 dark:text-slate-500">Sin pacientes encontrados</p>
            </div>
        @else
            <p id="pendingNoResultsMessage" class="hidden text-xs text-slate-400 dark:text-slate-500 my-2">Sin pacientes
                encontrados.</p>
            <ul id="pendingList" class="space-y-2 mb-3">
                @foreach ($citasPendientes as $cita)
                    @php
                        $prioridadClase = match (strtolower($cita->prioridad ?? 'media')) {
                            'baja'
                                => 'border-emerald-200/80 dark:border-emerald-900/50 hover:border-emerald-300 dark:hover:border-emerald-700 bg-emerald-50/20 dark:bg-emerald-950/10',
                            'media'
                                => 'border-sky-200/80 dark:border-sky-900/50 hover:border-sky-300 dark:hover:border-sky-700 bg-sky-50/20 dark:bg-sky-950/10',
                            'alta'
                                => 'border-amber-200/80 dark:border-amber-900/50 hover:border-amber-300 dark:hover:border-amber-700 bg-amber-50/20 dark:bg-amber-950/10',
                            'crítica'
                                => 'border-rose-200/80 dark:border-rose-900/50 hover:border-rose-300 dark:hover:border-rose-700 bg-rose-50/20 dark:bg-rose-950/10',
                            default
                                => 'border-indigo-200/80 dark:border-indigo-900/50 hover:border-indigo-300 dark:hover:border-indigo-700 bg-indigo-50/20 dark:bg-indigo-950/10',
                        };
                        $puntoClase = match (strtolower($cita->prioridad ?? 'media')) {
                            'baja' => 'bg-emerald-500 ring-emerald-100 dark:ring-emerald-950',
                            'media' => 'bg-sky-500 ring-sky-100 dark:ring-sky-950',
                            'alta' => 'bg-amber-500 ring-amber-100 dark:ring-amber-950',
                            'crítica' => 'bg-rose-500 ring-rose-100 dark:ring-rose-950 animate-pulse',
                            default => 'bg-indigo-500 ring-indigo-100 dark:ring-indigo-950',
                        };
                        $isManual =
                            in_array($cita->motivo, [
                                'Asignado manualmente por psicólogo',
                                'Gestionada por psicólogo',
                            ]) ||
                            str_contains($cita->motivo, 'anualmente') ||
                            str_contains($cita->motivo, 'estionada');
                    @endphp

                    <li class="pending-item {{ $prioridadClase }} rounded-2xl p-3 border bg-white dark:bg-slate-900 shadow-sm hover:shadow transition-all group flex items-center justify-between cursor-grab active:cursor-grabbing draggable-patient"
                        data-patient-name="{{ trim(($cita->user_nombres ?? '') . ' ' . ($cita->user_apellidos ?? '')) ?: ($cita->paciente_short_name ?: 'Paciente') }}"
                        data-patient-cedula="{{ $cita->paciente_cedula ?? '' }}" data-cita-id="{{ $cita->id }}"
                        data-prioridad="{{ $cita->prioridad ?? 'media' }}"
                        data-bloques-sugeridos="{{ preg_replace('/(\d{1,2}:\d{2}):\d{2}/', '$1', $cita->bloques_sugeridos ?? '') }}"
                        data-bloques-propuestos="{{ preg_replace('/(\d{1,2}:\d{2}):\d{2}/', '$1', $cita->bloques_propuestos ?? '') }}"
                        data-bloque-propuesto="{{ $cita->bloque_propuesto }}"
                        data-propuesta-estado="{{ $cita->propuesta_estado ?? '' }}"
                        data-is-manual="{{ $isManual ? '1' : '0' }}" draggable="true">

                        <div class="flex items-center gap-2.5 min-w-0">
                            <!-- Drag Indicator Handle Icon -->
                            <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 dark:group-hover:text-slate-500 shrink-0 transition-colors"
                                fill="currentColor" viewBox="0 0 16 16">
                                <path
                                    d="M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                            </svg>
                            <span class="shrink-0 h-2 w-2 rounded-full ring-4 {{ $puntoClase }}"></span>
                            <span
                                class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                                {{ $cita->paciente_short_name ?: 'Paciente' }}
                            </span>
                        </div>

                        @php
                            $isContrapropuesta = !empty($cita->propuesta_estado);

                            if ($isManual) {
                                $btnClasses =
                                    'text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/80 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60';
                            } elseif ($isContrapropuesta) {
                                $btnClasses =
                                    'text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/80 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 dark:hover:bg-purple-900/60';
                            } else {
                                $btnClasses =
                                    'text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/80 bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/60';
                            }
                        @endphp

                        <button type="button"
                            class="ml-2 shrink-0 detail-btn text-[11px] font-semibold rounded-xl px-2.5 py-1 border transition-all active:scale-95 shadow-2xs {{ $btnClasses }}"
                            data-cita-id="{{ $cita->id }}"
                            data-cita-json-url="{{ route('admin.psicologia.maestros.citas.show.json', $cita->id) }}"
                            data-cita-prioridad-url="{{ route('admin.psicologia.maestros.citas.update.prioridad', $cita->id) }}">
                            Detalles
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        @if (isset($pacientesSinCita) && $pacientesSinCita->isNotEmpty())
            <div
                class="{{ $citasPendientes->isEmpty() ? 'mt-1' : 'mt-3' }} pt-3 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500 mb-2 px-1">
                    Pacientes registrados sin cita</p>
                <ul id="patientsWithoutAppointmentList" class="space-y-2">
                    @foreach ($pacientesSinCita as $pacienteSinCita)
                        @php
                            $nombresArr = array_filter(explode(' ', $pacienteSinCita->persona->nombre_persona  ?? ''));
                            $apellidosArr = array_filter(explode(' ', $pacienteSinCita->persona->apellido_persona  ?? ''));
                            $primerNombre = !empty($nombresArr) ? array_values($nombresArr)[0] : '';
                            $primerApellido = !empty($apellidosArr) ? array_values($apellidosArr)[0] : '';
                            $shortName = trim($primerNombre . ' ' . $primerApellido) ?: 'Paciente';
                        @endphp
                        <li class="pending-item patient-without-appointment bg-amber-50/20 dark:bg-amber-950/10 border border-amber-200/80 dark:border-amber-900/50 hover:border-amber-300 dark:hover:border-amber-700 rounded-2xl p-3 flex items-center justify-between transition-all group"
                            data-patient-name="{{ trim($pacienteSinCita->name ?? '') ?: $shortName }}"
                            data-patient-cedula="{{ $pacienteSinCita->cedula ?? '' }}"
                            data-patient-without-appointment="true">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span
                                    class="shrink-0 h-2 w-2 rounded-full bg-amber-500 ring-4 ring-amber-100 dark:ring-amber-950"></span>
                                <span
                                    class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $shortName }}</span>
                            </div>
                            <button type="button"
                                class="ml-2 shrink-0 agregar-manual-btn text-[11px] font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 rounded-xl px-2.5 py-1 border border-amber-200 dark:border-amber-800/80 hover:bg-amber-100 dark:hover:bg-amber-900/60 transition-all active:scale-95 shadow-2xs"
                                data-paciente-id="{{ $pacienteSinCita->id_usuario ?? ($pacienteSinCita->id ?? $pacienteSinCita->getKey()) }}"
                                data-paciente-name="{{ trim($pacienteSinCita->name ?? '') ?: $shortName }}">
                                Agregar
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @if (isset($citasPendientes) && method_exists($citasPendientes, 'hasPages') && $citasPendientes->hasPages())
        <div class="mt-auto flex justify-center pt-3 shrink-0 border-t border-slate-100 dark:border-slate-800">
            {{ $citasPendientes->appends(request()->query())->links('admin.psicologia.maestros.agenda.partials.pagination') }}
        </div>
    @endif
</div>
