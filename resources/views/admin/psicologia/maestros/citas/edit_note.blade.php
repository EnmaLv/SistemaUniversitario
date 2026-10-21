<x-app-layout>
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" x-data="clinicalNoteEditor()">
        <div>
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @php
                            $paciente = $cita->paciente;
                            $nombreCompleto =
                                implode(' ', [
                                    $paciente->persona->nombres_per ?? '',
                                    $paciente->persona->apellidos_per ?? '',
                                ]) ?? '';
                            $partes = explode(' ', trim($nombreCompleto));
                            $primerNombre = $partes[0] ?? '';
                            $primerApellido = $partes[1] ?? '';
                            $iniciales = strtoupper(substr($primerNombre, 0, 1) . substr($primerApellido, 0, 1));
                            $isManual = $cita->motivo === 'Nota de Evolución (Manual)';
                            $fechaCita =
                                $cita->confirmado_en ?? null
                                    ? \Carbon\Carbon::parse($cita->confirmado_en)->format('d/m/Y')
                                    : 'No disponible';
                            $edad =
                                $paciente->persona->fecha_nacimiento_per ?? null
                                    ? \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento_per)->age
                                    : 'No disponible';
                            $nacimiento =
                                $paciente->persona->fecha_nacimiento_per ?? null
                                    ? \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento_per)->format(
                                        'd/m/Y',
                                    )
                                    : 'No disponible';
                        @endphp

                        <button type="button"
                            class="open-patient-modal shrink-0 w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-md shadow-indigo-500/20 hover:scale-105 active:scale-95 transition-all cursor-pointer"
                            data-patient-type="user" data-patient-name="{{ $nombreCompleto }}"
                            data-patient-email="{{ $paciente->persona->correo_per ?? 'No disponible' }}"
                            data-patient-phone="{{ $paciente->persona->telefono_per ?? 'No disponible' }}"
                            data-patient-created="{{ $fechaCita }}"
                            data-patient-cedula="{{ $paciente->persona->cedula_per ?? 'No disponible' }}"
                            data-patient-genero="{{ $paciente->persona->genero_per ?? 'No disponible' }}"
                            data-patient-nacimiento="{{ $nacimiento }}"
                            data-patient-semestre="{{ $paciente->persona->semestre_per ?? null ? $paciente->persona->semestre . '° Semestre' : 'No aplica' }}"
                            data-patient-edad="{{ $edad }}" title="Ver perfil del paciente">
                            <span class="text-lg font-black tracking-wider">{{ $iniciales }}</span>
                        </button>

                        <div class="flex-1 min-w-0">
                            <h1
                                class="text-xl sm:text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white flex flex-wrap items-center gap-2 leading-tight">
                                Nota de Sesión: {{ $cita->paciente->name }}
                            </h1>
                            <p
                                class="mt-0.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $cita->fecha?->translatedFormat('d M, Y') ?? 'S/F' }}
                                ({{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('g:i A') : 'S/H' }})
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.psicologia.maestros.historias.show', $cita->user_id) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold border border-gray-200 dark:border-gray-700 shadow-sm transition-all active:scale-95 group">
                            <i class="fas fa-arrow-left text-xs transition-transform group-hover:-translate-x-1"></i>
                            <span>Volver al Historial</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div
                    class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60 rounded-2xl p-5 mb-8 flex gap-4 items-start shadow-sm animate-in fade-in slide-in-from-top-4 duration-200">
                    <div
                        class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                        <i class="fas fa-circle-exclamation text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-rose-800 dark:text-rose-300 uppercase tracking-widest mb-1">
                            Nota Clínica Obligatoria</h4>
                        <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 leading-relaxed">
                            {{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.psicologia.maestros.citas.update.notas', $cita->id) }}" method="POST"
                id="form-notas-evolucion" class="relative">
                @csrf
                @method('PATCH')
                <input type="hidden" name="structured" value="1">
                <input type="hidden" name="is_manual" value="{{ $isManual ? '1' : '0' }}">
                <input type="hidden" name="titulo_manual" x-model="data.titulo_manual">
                <input type="hidden" name="from" value="{{ request('from') }}">


                <div class="space-y-8">
                    <div class="space-y-6" id="campos-dinamicos-container">
                        @if ($cita->motivo === 'Nota de Evolución (Manual)')
                            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="rounded-2xl border shadow-sm p-6 sm:p-8">
                                <div class="flex items-center gap-2 mb-4">
                                    <i class="fas fa-pen-to-square text-sky-500"></i>
                                    <label for="titulo_manual_input"
                                        class="text-[10px] font-black uppercase tracking-wider text-gray-400">Título de
                                        la Nota (Opcional)</label>
                                </div>
                                <input type="text" id="titulo_manual_input" name="titulo_manual"
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-medium transition-all"
                                    x-model="data.titulo_manual" placeholder="Ej: Seguimiento Mensual...">
                            </div>
                        @endif

                        <template x-for="(campo, index) in data.campos_dinamicos" :key="campo.campo_id">
                            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="en-card rounded-2xl border shadow-sm p-6 sm:p-8 relative overflow-hidden group">
                                <div class="flex items-start justify-between gap-2 mb-3 sm:mb-4">
                                    <div class="flex items-start gap-2 flex-1 min-w-0 mt-1 sm:mt-0 sm:items-center">
                                        <i class="fas fa-align-left text-indigo-500 shrink-0 text-sm"></i>
                                        <h4
                                            class="text-[10px] font-black uppercase tracking-wider text-gray-400 leading-tight break-words">
                                            <span x-text="index + 1"></span>. <span x-text="campo.titulo"></span> <span
                                                x-show="!isManual && campo.campo_id <= 3" class="text-red-500">*</span>
                                        </h4>
                                    </div>
                                    <div
                                        class="shrink-0 flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all">
                                        <button type="button" @click="moveCampoUp(index)" x-show="index > 0"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                                            title="Subir campo">
                                            <i class="fas fa-chevron-up text-xs"></i>
                                        </button>
                                        <button type="button" @click="moveCampoDown(index)"
                                            x-show="index < data.campos_dinamicos.length - 1"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                                            title="Bajar campo">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </button>
                                        <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                                        <button type="button" @click="removeCampoDinamico(index)"
                                            x-show="isManual || campo.campo_id > 3"
                                            class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors"
                                            title="Quitar campo">
                                            <i class="fas fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <textarea :name="'campos_dinamicos[' + campo.campo_id + ']'" rows="4"
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full border rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none font-medium leading-relaxed"
                                    x-model="campo.contenido" :required="!isManual && campo.campo_id <= 3" placeholder="Escribe los detalles aquí..."></textarea>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                            class="en-card rounded-2xl border shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-file-medical text-indigo-500"></i>
                                    <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                                        Diagnósticos Oficiales</h4>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-4">
                                <template x-for="(diag, index) in data.diagnosticos" :key="index">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold uppercase tracking-wider rounded-xl border border-indigo-100 dark:border-indigo-800/60">
                                        <span x-text="diag.nombre"></span>
                                        <button type="button" @click="removeDiagnostico(index)"
                                            class="hover:text-red-500 transition-colors ml-1">
                                            <i class="fas fa-xmark text-xs"></i>
                                        </button>
                                    </span>
                                </template>
                                <template x-if="data.diagnosticos.length === 0">
                                    <span
                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest italic">Sin
                                        diagnósticos asociados</span>
                                </template>
                            </div>

                            <div class="relative" x-data="{ search: '', results: [], loading: false, open: false }">
                                <div
                                    class="flex items-center px-4 border border-gray-200 dark:border-gray-700 rounded-xl focus-within:ring-2 focus-within:ring-indigo-500 transition-all shadow-sm">
                                    <i class="fas fa-magnifying-glass text-gray-400 text-xs mr-2"></i>
                                    <input type="text" x-model="search"
                                        @input.debounce.300ms="
                                                if(search.length < 2) { results = []; open = false; return; }
                                                loading = true;
                                                fetch(`{{ route('admin.enfermedades.api.search') }}?q=${encodeURIComponent(search)}`)
                                                    .then(r => r.json()).then(d => { results = d; loading = false; open = true; });
                                           "
                                        class="w-full border-none bg-transparent text-xs font-bold text-gray-700 dark:text-white focus:ring-0 placeholder-gray-400 py-3"
                                        placeholder="Buscar diagnóstico o condición...">
                                </div>

                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-2">
                                    <div class="max-h-48 overflow-y-auto">
                                        <template x-if="loading">
                                            <div
                                                class="p-3 text-[10px] text-gray-400 text-center font-bold uppercase tracking-widest animate-pulse">
                                                Buscando...</div>
                                        </template>
                                        <template x-for="res in results" :key="res.id">
                                            <button type="button"
                                                @click="addDiagnostico(res); open = false; search = ''"
                                                class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 dark:hover:bg-gray-700 rounded-xl border-b border-gray-50 dark:border-gray-700/50 last:border-none transition-colors group">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-2 h-2 rounded-full bg-indigo-500 group-hover:scale-110 transition-transform">
                                                    </div>
                                                    <span
                                                        class="text-[10px] font-bold text-gray-700 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400"
                                                        x-text="res.nombre"></span>
                                                    <span
                                                        class="text-[9px] font-mono text-gray-400 dark:text-gray-500 ml-auto"
                                                        x-text="res.codigo"></span>
                                                </div>
                                            </button>
                                        </template>
                                        <template x-if="results.length === 0 && !loading">
                                            <div class="p-3 text-[10px] text-gray-400 text-center italic font-bold">No
                                                se encontraron resultados</div>
                                        </template>
                                    </div>

                                    <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                        <a onclick="abrirModalCrearEnfermedad('modal-create-edit')"
                                            class="block text-center text-[9px] font-black text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 uppercase tracking-widest">
                                            ¿No aparece? Crear nueva
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                            class="en-card rounded-2xl border shadow-sm p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-chart-line text-emerald-500"></i>
                                <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400">Avances y
                                    Estado del Paciente</h4>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">Estado
                                        de Ánimo del Paciente @if (!$isManual)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    <select name="estado_animo_id" x-model="data.estado_animo_id"
                                        @if (!$isManual) required @endif
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full border rounded-xl h-11 px-3 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                                        <option value="">Seleccionar estado de ánimo...</option>
                                        @foreach ($estadosAnimo as $animo)
                                            <option value="{{ $animo->id }}">{{ $animo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="estado_animo_detalle" rows="2" @if (!$isManual) required @endif
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full mt-2 border rounded-xl p-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none font-medium"
                                        x-model="data.estado_animo_detalle" placeholder="Describe observaciones sobre su estado de ánimo..."></textarea>
                                </div>

                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">Estado
                                        de Evolución @if (!$isManual)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    <select name="avance_estado" x-model="data.avance_estado"
                                        @if (!$isManual) required @endif
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full border rounded-xl h-11 px-3 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                                        <option value="">Seleccionar estado de avance...</option>
                                        @foreach ($avances as $avance)
                                            <option value="{{ $avance->id }}">{{ $avance->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="avance_detalle" rows="2" @if (!$isManual) required @endif
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full mt-2 border rounded-xl p-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none font-medium"
                                        x-model="data.avance_detalle" placeholder="Describe los avances o retrocesos observados..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                            class="en-card rounded-2xl border shadow-sm p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-list-check text-indigo-500"></i>
                                <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400">Plan de
                                    Tratamiento</h4>
                            </div>
                            <textarea name="plan_tratamiento" rows="4"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full border rounded-xl p-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none font-medium"
                                x-model="data.plan_tratamiento" placeholder="Asignar tareas para la casa..."></textarea>
                        </div>

                        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                            class="en-card rounded-2xl border shadow-sm p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-calendar-plus text-indigo-500"></i>
                                <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400">Próxima Cita
                                    Recomendada</h4>
                            </div>
                            <input type="date" name="proxima_cita_fecha"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 border rounded-xl p-3 text-xs font-bold mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                x-model="data.proxima_cita_fecha">
                            <textarea name="proxima_cita_razon" rows="2"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full border rounded-xl p-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none font-medium"
                                x-model="data.proxima_cita_razon" placeholder="Razón de la próxima cita..."></textarea>
                        </div>
                    </div>
                </div>

                <div
                    class="fixed bottom-6 left-1/2 -translate-x-1/2 sm:left-auto sm:translate-x-0 sm:bottom-8 sm:right-8 z-30 flex items-center gap-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-2.5 rounded-full shadow-2xl border border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.psicologia.maestros.historias.show', $cita->user_id) }}" title="Volver"
                        class="w-12 h-12 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full flex items-center justify-center transition-all active:scale-95 border border-gray-100 dark:border-gray-700">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>

                    <button type="button" @click="showModalCampos = true" title="Añadir Campo"
                        class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-600 hover:text-white text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center transition-all active:scale-95 border border-indigo-100 dark:border-indigo-800/50">
                        <i class="fas fa-plus text-lg"></i>
                    </button>

                    <button type="submit" @click="syncStructured($event)" title="Guardar Nota"
                        class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-600 hover:text-white text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center transition-all active:scale-95 border border-emerald-100 dark:border-emerald-800/50">
                        <i class="fas fa-check text-lg"></i>
                    </button>
                </div>

                <template x-for="(diag, index) in data.diagnosticos" :key="'hidden-' + index">
                    <input type="hidden" :name="'diagnosticos[' + index + '][id]'" :value="diag.id">
                </template>
                <template x-for="(diag, index) in data.diagnosticos" :key="'hidden-cod-' + index">
                    <input type="hidden" :name="'diagnosticos[' + index + '][codigo]'" :value="diag.codigo">
                </template>
                <template x-for="(diag, index) in data.diagnosticos" :key="'hidden-nom-' + index">
                    <input type="hidden" :name="'diagnosticos[' + index + '][nombre]'" :value="diag.nombre">
                </template>
            </form>

            <div x-show="showUnsavedModal" class="fixed inset-0 overflow-y-auto" style="z-index: 9999;" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 text-center">
                    <div x-show="showUnsavedModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 transition-opacity bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm"
                        @click="showUnsavedModal = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div x-show="showUnsavedModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                        class="inline-block border shadow-2xl rounded-2xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full overflow-hidden text-center align-bottom transition-all transform p-6 sm:p-8">

                        <div
                            class="mx-auto flex items-center justify-center h-14 w-14 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 mb-5 text-amber-500">
                            <i class="fas fa-triangle-exclamation text-xl"></i>
                        </div>

                        <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight mb-2"
                            style="color: var(--text-main);">
                            ¿Estás seguro que deseas salir?
                        </h3>
                        <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-6">
                            Hay información aún no guardada. Si sales ahora, perderás los cambios realizados.
                        </p>

                        <div
                            class="pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                            <button type="button" @click="showUnsavedModal = false"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                                Cancelar
                            </button>
                            <button type="button" @click="confirmLeave()"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                                <i class="fas fa-right-from-bracket text-xs"></i>
                                <span>Salir</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="showModalCampos" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 overflow-y-auto" style="z-index: 9999;"
                x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 text-center">
                    <div x-show="showModalCampos" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 transition-opacity bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm"
                        @click="showModalCampos = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <style>
                        [x-cloak] {
                            display: none !important;
                        }
                    </style>
                    <div x-show="showModalCampos" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                        class="inline-block border shadow-2xl rounded-2xl sm:my-8 sm:align-middle sm:max-w-xl sm:w-full overflow-hidden text-left align-bottom transition-all transform p-6 sm:p-8">

                        <div
                            class="flex items-start justify-between pb-6 mb-6 border-b border-gray-100 dark:border-gray-800 gap-4">
                            <div>
                                <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight"
                                    style="color: var(--text-main);">
                                    Añadir Campo a la Sesión
                                </h3>
                                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Configuración de la Nota
                                </p>
                            </div>
                            <button type="button" @click="showModalCampos = false"
                                class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 transition-all active:scale-95 shrink-0"
                                aria-label="Cerrar">
                                <i class="fas fa-xmark text-sm"></i>
                            </button>
                        </div>

                        <form id="formNuevoCampo"
                            action="{{ route('admin.psicologia.maestros.citas.campos.store.ajax') }}" method="POST"
                            @submit.prevent="submitNuevoCampo">
                            @csrf
                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                        Título del Campo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="titulo" required
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full h-11 border rounded-xl px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                                        placeholder="Ej: Antecedentes Familiares">
                                </div>

                                <hr class="border-gray-100 dark:border-gray-800">

                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                        Reutilizar campos existentes
                                    </label>
                                    <div class="relative" x-data="{ openDropdownCampos: false }"
                                        @click.away="openDropdownCampos = false">
                                        <div class="flex items-center px-4 border rounded-xl focus-within:ring-2 focus-within:ring-indigo-500 transition-all"
                                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);">
                                            <i class="fas fa-magnifying-glass text-gray-400 text-xs mr-2 shrink-0"></i>
                                            <input type="text" x-model="searchCampo"
                                                @focus="openDropdownCampos = true"
                                                class="w-full h-11 border-none bg-transparent text-sm font-medium focus:ring-0 placeholder-gray-400 p-0"
                                                style="color: var(--text-main);"
                                                placeholder="Escriba para buscar o haga clic para ver disponibles...">
                                        </div>

                                        <div x-show="openDropdownCampos"
                                            style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                            class="absolute z-50 w-full mt-2 border rounded-2xl shadow-xl max-h-60 overflow-y-auto p-2"
                                            x-cloak>

                                            <div
                                                class="px-3 py-2 text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800 mb-1">
                                                <span x-show="searchCampo === ''">Campos disponibles:</span>
                                                <span x-show="searchCampo !== ''">Resultados encontrados:</span>
                                            </div>

                                            <template x-if="camposFiltrados.length === 0">
                                                <div class="p-3 text-xs font-bold text-red-500">No hay resultados
                                                    encontrados.</div>
                                            </template>

                                            <template x-for="campo in camposFiltrados" :key="campo.id">
                                                <button type="button"
                                                    @click="
                                                    addCampoFromModal(campo.id, campo.titulo);
                                                    openDropdownCampos = false;
                                                    searchCampo = '';
                                                "
                                                    class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800/60 rounded-xl transition-colors flex items-center justify-between group"
                                                    style="color: var(--text-main);">
                                                    <div class="flex flex-col gap-0.5">
                                                        <span x-text="campo.titulo"></span>
                                                        <span
                                                            class="text-[9px] font-black text-gray-400 uppercase tracking-wider"
                                                            x-text="campo.psicologo_id ? 'Personalizado' : 'Sistema'"></span>
                                                    </div>
                                                    <i
                                                        class="fas fa-plus text-xs text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 shrink-0"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                                <button type="button" @click="showModalCampos = false"
                                    class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                                    <i class="fas fa-plus text-xs"></i>
                                    <span>Añadir Campo</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.enfermedades.modal-create-edit', [
        'modalId' => 'modal-create-edit',
        'tipo' => $tipo,
        'returnTo' => $returnTo,
        'editing' => $editing,
        'categoriaTexto' => $categoriaTexto,
    ])

    <script>
        function clinicalNoteEditor() {
            let initialData = {
                titulo_manual: '',
                diagnosticos: [],
                estado_animo_id: '',
                estado_animo_detalle: '',
                avance_estado: '',
                avance_detalle: '',
                plan_tratamiento: '',
                proxima_cita_fecha: '',
                proxima_cita_razon: '',
                campos_dinamicos: @json($camposGuardados)
            };

            const rawNotas = @json($cita->notas);
            try {
                const parsed = JSON.parse(rawNotas);
                if (typeof parsed === 'object' && parsed !== null) {
                    initialData = {
                        ...initialData,
                        ...parsed
                    };
                }
            } catch (e) {}

            const initialSnapshot = JSON.stringify(initialData);
            const isRealizada = @json($cita->estado === 'realizada');

            return {
                data: initialData,
                isManual: @json($isManual),
                hasUnsavedChanges: !isRealizada,
                showUnsavedModal: false,
                showModalCampos: false,
                searchCampo: '',
                camposDisponibles: @json($camposDisponibles),
                pendingUrl: '',
                isSubmitting: false,

                async submitNuevoCampo(e) {
                    const form = e.target;
                    const btn = form.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = 'Guardando...';
                    btn.disabled = true;

                    const formData = new FormData(form);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.camposDisponibles.push(data.campo);
                            this.addCampoFromModal(data.campo.id, data.campo.titulo);

                            form.reset();
                            this.showModalCampos = false;

                            let t = document.createElement('div');
                            t.innerHTML = `<div id="toast" class="fixed top-6 right-6 z-50">
                                <div class="max-w-sm w-full bg-emerald-600 text-white shadow-lg rounded-2xl border border-emerald-700 px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-bold text-xs uppercase tracking-wider">¡Listo!</p>
                                            <p class="text-xs mt-1 font-medium">Anexo guardado exitosamente.</p>
                                        </div>
                                        <button onclick="document.getElementById('toast')?.remove()" class="text-white opacity-70 hover:opacity-100 font-black">✕</button>
                                    </div>
                                </div>
                            </div>`;
                            document.body.appendChild(t);
                            setTimeout(() => {
                                document.getElementById('toast')?.remove()
                            }, 4000);
                        } else {
                            AppModal.alert('Error', data.message || 'Error al guardar el campo');
                        }
                    } catch (error) {
                        AppModal.alert('Error', 'Error de conexión');
                    } finally {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                },

                get camposFiltrados() {
                    if (this.searchCampo.length === 0) return this.camposDisponibles;
                    return this.camposDisponibles.filter(c => c.titulo.toLowerCase().includes(this.searchCampo
                        .toLowerCase()));
                },

                init() {
                    this.$watch('data', (value) => {
                        this.hasUnsavedChanges = !isRealizada || JSON.stringify(value) !== initialSnapshot;
                    });

                    window.addEventListener('beforeunload', (e) => {
                        if (this.hasUnsavedChanges && !this.isSubmitting) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });

                    document.addEventListener('click', (e) => {
                        let link = e.target.closest('a');
                        if (link && link.href && !link.href.includes('#') && link.target !== '_blank' && !link
                            .hasAttribute('download')) {
                            if (e.target.closest('[x-show="showUnsavedModal"]')) return;

                            if (this.hasUnsavedChanges && !this.isSubmitting) {
                                e.preventDefault();
                                e.stopPropagation();
                                this.pendingUrl = link.href;
                                this.showUnsavedModal = true;
                            }
                        }
                    }, {
                        capture: true
                    });
                },

                markAsChanged() {
                    this.hasUnsavedChanges = true;
                },

                confirmLeave() {
                    this.hasUnsavedChanges = false;
                    if (this.pendingUrl) {
                        window.location.href = this.pendingUrl;
                    }
                },

                addDiagnostico(item) {
                    if (!this.data.diagnosticos.some(d => d.id === item.id)) {
                        this.data.diagnosticos.push(item);
                        this.hasUnsavedChanges = JSON.stringify(this.data) !== initialSnapshot;
                    }
                },

                removeDiagnostico(index) {
                    this.data.diagnosticos.splice(index, 1);
                    this.hasUnsavedChanges = JSON.stringify(this.data) !== initialSnapshot;
                },

                removeCampoDinamico(index) {
                    this.data.campos_dinamicos.splice(index, 1);
                    this.hasUnsavedChanges = true;
                },

                moveCampoUp(index) {
                    if (index > 0) {
                        const temp = this.data.campos_dinamicos[index];
                        this.data.campos_dinamicos[index] = this.data.campos_dinamicos[index - 1];
                        this.data.campos_dinamicos[index - 1] = temp;
                        this.hasUnsavedChanges = true;
                    }
                },

                moveCampoDown(index) {
                    if (index < this.data.campos_dinamicos.length - 1) {
                        const temp = this.data.campos_dinamicos[index];
                        this.data.campos_dinamicos[index] = this.data.campos_dinamicos[index + 1];
                        this.data.campos_dinamicos[index + 1] = temp;
                        this.hasUnsavedChanges = true;
                    }
                },

                addCampoFromModal(campoId, titulo) {
                    const exists = this.data.campos_dinamicos.find(c => c.campo_id == campoId);
                    if (exists) {
                        AppModal.alert('Acción no permitida', 'Este campo ya está en la nota de evolución.');
                        return;
                    }
                    this.data.campos_dinamicos.push({
                        campo_id: campoId,
                        titulo: titulo,
                        contenido: ''
                    });
                    this.hasUnsavedChanges = true;
                    this.showModalCampos = false;
                },

                syncStructured(e) {
                    const evt = e || window.event;
                    const form = evt && evt.target ? evt.target.closest('form') : null;
                    if (form && !form.checkValidity()) {
                        return;
                    }
                    this.isSubmitting = true;
                    this.hasUnsavedChanges = false;
                }
            };
        }
    </script>
    @include('pacientes.partials.modal')
</x-app-layout>
