<x-app-layout>

    <script>
        window._historiaRoutes = {
            desvincular:     @js(route('admin.psicologia.maestros.historias.enfermedad.desvincular')),
            seccionDestroy:  @js(route('admin.psicologia.maestros.historias.secciones.destroy', 'PLACEHOLDER')),
            seccionReorder:  @js(route('admin.psicologia.maestros.historias.secciones.reorder', 'PLACEHOLDER')),
            vincular:        @js(route('admin.psicologia.maestros.historias.enfermedad.vincular')),
            csrf:            document.querySelector('meta[name="csrf-token"]')?.content || '',
            historiaId:      {{ $historia->id }},
        };

        window._historiaConfig = {
            vinculados: @js($enfermedadesVinculadas->mapWithKeys(fn($items, $key) => [
                $key => $items->map(fn($v) => ['link_id' => $v->link_id, 'nombre' => $v->nombre]),
            ])),
            seccionesTitulos: @js($seccionesPersonalizadas->pluck('titulo')->values()->toArray()),
        };

        document.addEventListener('alpine:init', () => {
            Alpine.data('historiaShow', () => ({
                showStats: false,
                isEditing: true,
                hasUnsavedChanges: false,
                showUnsavedModal: false,
                pendingUrl: null,
                vinculados: window._historiaConfig.vinculados,
                searchQuery: '',
                seccionesTitulos: window._historiaConfig.seccionesTitulos,

                matchesSearch(title) {
                    if (!this.searchQuery) return true;
                    const normalize = (str) => str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    return normalize(title).includes(normalize(this.searchQuery));
                },

                hasVisibleSections() {
                    if (!this.searchQuery) return this.seccionesTitulos.length > 0;
                    const normalize = (str) => str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    const q = normalize(this.searchQuery);
                    return this.seccionesTitulos.some(title => normalize(title).includes(q));
                },

                init() {
                    window.addEventListener('beforeunload', (e) => {
                        if (this.hasUnsavedChanges) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });

                    document.addEventListener('click', (e) => {
                        const link = e.target.closest('a');
                        if (link && link.href && !link.href.includes('#') && link.target !== '_blank' && !link.hasAttribute('download')) {
                            if (e.target.closest('[x-show="showUnsavedModal"]')) return;
                            if (this.hasUnsavedChanges) {
                                e.preventDefault();
                                e.stopPropagation();
                                this.pendingUrl = link.href;
                                this.showUnsavedModal = true;
                            }
                        }
                    }, { capture: true });
                },

                confirmLeave() {
                    this.hasUnsavedChanges = false;
                    if (this.pendingUrl) window.location.href = this.pendingUrl;
                },

                desvincular(linkId) {
                    AppModal.confirm('Confirmar', '¿Desvincular esta condición?').then((confirmed) => {
                        if (!confirmed) return;
                        fetch(window._historiaRoutes.desvincular, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': window._historiaRoutes.csrf,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ link_id: linkId })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.hasUnsavedChanges = true;
                                for (const key in this.vinculados) {
                                    this.vinculados[key] = this.vinculados[key].filter(v => v.link_id !== linkId);
                                }
                            }
                        });
                    });
                },

                deleteSection(id) {
                    AppModal.confirm('Atención', '¿Estás seguro de eliminar esta sección? Se perderán todos los segmentos y datos guardados.').then((confirmed) => {
                        if (!confirmed) return;

                        const url = window._historiaRoutes.seccionDestroy.replace('PLACEHOLDER', id);
                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': window._historiaRoutes.csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(async (res) => {
                            if (!res.ok) {
                                const text = await res.text();
                                console.error('[deleteSection] HTTP ' + res.status, text);
                                AppModal.alert('Error', 'No se pudo eliminar (HTTP ' + res.status + '). Revisa la consola.');
                                return;
                            }
                            this.hasUnsavedChanges = false;
                            window.location.reload();
                        })
                        .catch((err) => {
                            console.error('[deleteSection] fetch error:', err);
                            AppModal.alert('Error', 'Error de red al eliminar.');
                        });
                    });
                },

                reorderSection(id, direction) {
                    const url = window._historiaRoutes.seccionReorder.replace('PLACEHOLDER', id);
                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': window._historiaRoutes.csrf,
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ direccion: direction })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const seccionActual = document.getElementById('seccion-' + id);
                            if (seccionActual) {
                                if (direction === 'up' && seccionActual.previousElementSibling?.classList.contains('seccion-dinamica')) {
                                    seccionActual.parentNode.insertBefore(seccionActual, seccionActual.previousElementSibling);
                                } else if (direction === 'down' && seccionActual.nextElementSibling?.classList.contains('seccion-dinamica')) {
                                    seccionActual.parentNode.insertBefore(seccionActual.nextElementSibling, seccionActual);
                                }
                            }
                        } else {
                            AppModal.alert('Error', 'Error al reordenar la sección.');
                        }
                    });
                },

                vincular(enfermedadId, contexto) {
                    fetch(window._historiaRoutes.vincular, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': window._historiaRoutes.csrf,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            historia_clinica_id: window._historiaRoutes.historiaId,
                            enfermedad_id: enfermedadId,
                            contexto: contexto
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (!this.vinculados[contexto]) this.vinculados[contexto] = [];
                            this.vinculados[contexto].push({
                                link_id: data.link_id,
                                nombre: data.nombre
                            });
                            this.hasUnsavedChanges = true;
                            this.$dispatch('linked-' + contexto);
                        } else {
                            AppModal.alert('Error', 'Error al vincular: ' + (data.message || 'Desconocido'));
                        }
                    });
                }
            }));
        });
    </script>
    <style>
        .seccion-dinamica:first-of-type .btn-subir {
            display: none !important;
        }

        .seccion-dinamica:last-of-type .btn-bajar {
            display: none !important;
        }

        .dark .seccion-dinamica textarea {
            color: #e5e7eb !important;
        }

        .dark .seccion-dinamica input {
            color: #e5e7eb !important;
        }
    </style>
    @php $tab = request()->query('tab', 'expediente'); @endphp
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)] overflow-x-hidden" x-data="historiaShow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6">
                <a href="{{ route('admin.psicologia.maestros.historias.index') }}"
                    class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-indigo-600 transition-colors group">
                    <i class="fas fa-arrow-left text-xs transition-transform group-hover:-translate-x-1"></i>
                    <span>Volver al listado</span>
                </a>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                class="rounded-2xl border shadow-sm p-6 sm:p-8 mb-8 overflow-visible relative">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-full -mr-32 -mt-32 pointer-events-none">
                </div>

                <div class="relative flex flex-col md:flex-row md:items-center gap-6">
                    @php
                        $fechaCita = $paciente->primera_cita
                            ? \Carbon\Carbon::parse($paciente->primera_cita)->format('d/m/Y')
                            : 'No disponible';
                        $edad = $paciente->persona->fecha_nacimiento_persona
                            ? \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento_persona)->age
                            : 'No disponible';
                        $nacimiento = $paciente->persona->fecha_nacimiento_persona
                            ? \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento_persona)->format('d/m/Y')
                            : 'No disponible';
                        $nombreCompleto =
                            implode(' ', [
                                $paciente->persona->nombre_persona ?? '',
                                $paciente->persona->apellido_persona ?? '',
                            ]) ?? '';
                        $partes = explode(' ', trim($nombreCompleto));
                        $primerNombre = $partes[0] ?? '';
                        $primerApellido = $partes[1] ?? '';
                        $iniciales = strtoupper(substr($primerNombre, 0, 1) . substr($primerApellido, 0, 1));
                        $photoPath = $paciente->profile_photo_path ?? null;
                        $hasPhoto = !empty($photoPath);
                    @endphp
                    <button type="button"
                        class="open-patient-modal w-20 h-20 sm:w-24 sm:h-24 rounded-2xl flex items-center justify-center text-white font-bold text-2xl sm:text-3xl shadow-lg shadow-indigo-500/10 hover:scale-105 transition-all active:scale-95 overflow-hidden border border-indigo-100 dark:border-indigo-900/50 shrink-0"
                        style="background: linear-gradient(135deg, #4f46e5, #6d28d9)" data-patient-type="user"
                        data-patient-name="{{ $nombreCompleto }}"
                        data-patient-email="{{ $paciente->persona->email_persona ?? 'No disponible' }}"
                        data-patient-phone="{{ $paciente->persona->telefono_persona ?? 'No disponible' }}"
                        data-patient-created="{{ $fechaCita }}"
                        data-patient-cedula="{{ $paciente->persona->cedula_persona ?? 'No disponible' }}"
                        data-patient-genero="{{ $paciente->persona->genero_persona ?? 'No disponible' }}"
                        data-patient-nacimiento="{{ $nacimiento }}"
                        data-patient-perfil-academico="{{ $paciente->perfil_academico ?? 'Sin definir' }}"
                        data-patient-pnf="{{ $paciente->persona->pnf ?? 'No aplica' }}"
                        data-patient-semestre="{{ $paciente->persona->semestre_persona ? $paciente->persona->semestre_persona . '° Semestre' : 'No aplica' }}"
                        data-patient-horario="{{ $paciente->horario_path ? asset('storage/' . $paciente->horario_path) : '' }}"
                        data-patient-edad="{{ $edad }}"
                        data-patient-photo="{{ $hasPhoto ? route('media.profile_photos', basename($photoPath)) : '' }}"
                        title="Ver perfil completo">
                        @if ($hasPhoto)
                            <img src="{{ route('media.profile_photos', basename($photoPath)) }}"
                                alt="{{ $paciente->name }}" class="w-full h-full object-cover">
                        @else
                            {{ $iniciales }}
                        @endif
                    </button>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight"
                                style="color: var(--text-main);">{{ $paciente->name }}</h2>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                <i class="fas fa-circle text-[8px] text-emerald-500"></i>
                                <span>Paciente Activo</span>
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-4 text-xs font-medium">
                            <div
                                class="inline-flex items-center gap-3 bg-indigo-50/70 dark:bg-indigo-950/40 px-4 py-2 rounded-xl border border-indigo-100 dark:border-indigo-900/50">
                                <span class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold"
                                    title="Sesiones completadas con éxito">
                                    <i class="fas fa-calendar-check text-xs"></i>
                                    <span>{{ $stats['realizadas'] }} Sesiones Realizadas</span>
                                </span>
                                <div class="w-px h-4 bg-indigo-200 dark:bg-indigo-800"></div>
                                <button @click="showStats = true"
                                    class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-bold transition-colors group/btn"
                                    title="Ver historial de inasistencias y cancelaciones">
                                    <i
                                        class="fas fa-chart-pie text-xs transition-transform group-hover/btn:rotate-12"></i>
                                    <span class="text-[10px] uppercase tracking-wider">Resumen de Actividad</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div x-data="{ openTopExport: false }" class="relative flex gap-3 self-start md:self-center">
                        <button @click="openTopExport = !openTopExport" @click.away="openTopExport = false"
                            type="button" title="Exportar Expediente Completo"
                            class="p-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl border border-gray-200 dark:border-gray-700 transition shadow-sm inline-flex items-center gap-2 text-xs font-bold active:scale-95">
                            <i class="fas fa-file-export text-sm"></i>
                        </button>
                        <style>
                            [x-cloak] {
                                display: none !important;
                            }
                        </style>
                        <div x-show="openTopExport" x-transition x-cloak
                            class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-30">
                            <a href="{{ route('admin.psicologia.maestros.historias.expedienteCompletoPdf', $paciente->id) }}"
                                target="_blank"
                                class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div
                                    class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/50 flex items-center justify-center text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/40">
                                    <i class="fas fa-file-pdf text-sm"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">PDF</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">Expediente
                                        completo</span>
                                </div>
                            </a>
                            <a href="{{ route('admin.psicologia.maestros.historias.expedienteCompletoWord', $paciente->id) }}"
                                target="_blank"
                                class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div
                                    class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                    <i class="fas fa-file-word text-sm"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">Word</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">Expediente
                                        Completo</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="flex gap-1.5 p-1.5 rounded-2xl shadow-sm border w-fit">
                    <a href="{{ route('admin.psicologia.maestros.historias.show', ['paciente' => $paciente->id, 'tab' => 'expediente']) }}"
                        class="px-6 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 {{ $tab === 'expediente' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        Expediente General
                    </a>
                    <a href="{{ route('admin.psicologia.maestros.historias.show', ['paciente' => $paciente->id, 'tab' => 'evolucion']) }}"
                        class="px-6 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 {{ $tab === 'evolucion' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        Línea de Evolución
                    </a>
                </div>

                @if ($tab === 'expediente')
                    <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                        <div x-data="{ openExport: false }" class="relative z-10 w-full md:w-auto">
                            <button @click="openExport = !openExport" @click.away="openExport = false" type="button"
                                class="w-full md:w-auto group flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-600 text-indigo-600 dark:text-indigo-400 hover:text-white rounded-xl text-xs font-bold transition-all border border-indigo-100 dark:border-indigo-900/50 shadow-sm active:scale-95 whitespace-nowrap">
                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 3v6a1 1 0 001 1h6"></path>
                                </svg>
                                Reportes
                            </button>

                            <div x-show="openExport" x-transition x-cloak
                                class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <a href="{{ route('admin.psicologia.maestros.historias.reportePdf', $paciente->id) }}"
                                    target="_blank"
                                    class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/50 flex items-center justify-center text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/40">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">PDF</span>
                                </a>
                                <a href="{{ route('admin.psicologia.maestros.historias.reporteWord', $paciente->id) }}"
                                    target="_blank"
                                    class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">Word</span>
                                </a>
                            </div>
                        </div>

                        <div class="relative w-full md:w-80">
                            <span
                                class="absolute inset-y-0 left-0 flex items-center rounded-full pl-3.5 text-gray-400 dark:text-gray-500 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input type="text" id="buscador-secciones" x-model="searchQuery"
                                @input="searchQuery = $event.target.value" placeholder="Buscar sección..."
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full border rounded-xl py-2.5 pl-10 pr-4 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-sm placeholder-gray-400 dark:placeholder-gray-500">
                            <span x-show="searchQuery" x-cloak
                                class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <button type="button" @click="searchQuery = ''"
                                    class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            @if ($tab === 'expediente')
                <div>
                    <form action="{{ route('admin.psicologia.maestros.historias.update', $paciente->id) }}"
                        method="POST" @input="hasUnsavedChanges = true" @submit="hasUnsavedChanges = false">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="from" value="{{ request('from') }}">

                        <div class="space-y-6">
                            @foreach ($seccionesPersonalizadas as $indexKey => $seccion)
                                <div id="seccion-{{ $seccion->id }}"
                                    style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                    class="rounded-2xl border shadow-sm p-6 sm:p-8 relative group seccion-dinamica overflow-hidden"
                                    x-show="matchesSearch('{{ addslashes($seccion->titulo) }}')" x-transition>
                                    <div
                                        class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                                        <div>
                                            <h3 class="text-lg sm:text-xl font-extrabold tracking-tight"
                                                style="color: var(--text-main);">{{ $seccion->titulo }}</h3>
                                            <p
                                                class="mt-0.5 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                                {{ $seccion->descripcion_general ?? 'Sección Personalizada' }}</p>
                                        </div>
                                        <div x-show="isEditing" class="flex items-center gap-1">
                                            <button type="button"
                                                @click.stop.prevent="reorderSection({{ $seccion->id }}, 'up')"
                                                class="btn-subir p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition-all active:scale-95"
                                                title="Subir sección">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            </button>
                                            <button type="button"
                                                @click.stop.prevent="reorderSection({{ $seccion->id }}, 'down')"
                                                class="btn-bajar p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition-all active:scale-95"
                                                title="Bajar sección">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <button type="button"
                                                @click.stop.prevent="deleteSection({{ $seccion->id }})"
                                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-xl transition-all active:scale-95"
                                                title="Eliminar sección">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        class="grid grid-cols-1 {{ $seccion->segmentos->count() > 1 ? 'md:grid-cols-2' : '' }} gap-6">
                                        @foreach ($seccion->segmentos as $segmento)
                                            <div class="space-y-3">
                                                <div>
                                                    <div x-show="isEditing" x-cloak>
                                                        <input type="text"
                                                            name="segmentos_metadata[{{ $segmento->id }}][titulo]"
                                                            value="{{ $segmento->titulo }}"
                                                            style="color: var(--text-main);"
                                                            class="w-full bg-transparent border-b border-dashed border-gray-300 dark:border-gray-700 pb-1 text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 focus:outline-none focus:border-indigo-500"
                                                            placeholder="Título del segmento">
                                                    </div>
                                                    <div x-show="!isEditing" x-cloak>
                                                        <label
                                                            class="block text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-1">{{ $segmento->titulo ?? 'Información' }}</label>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap gap-1.5 mb-2">
                                                    <template
                                                        x-for="vinculo in (vinculados['seg_{{ $segmento->id }}'] || [])"
                                                        :key="vinculo.link_id">
                                                        <span
                                                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold tracking-wider rounded-xl border border-indigo-100 dark:border-indigo-900/50 group/tag">
                                                            <span x-text="vinculo.nombre"></span>
                                                            <button type="button"
                                                                @click="desvincular(vinculo.link_id)"
                                                                x-show="isEditing"
                                                                class="hover:text-indigo-800 dark:hover:text-indigo-200 transition-colors">
                                                                <svg class="w-3 h-3" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="3"
                                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </span>
                                                    </template>
                                                </div>

                                                <textarea name="segmentos_extra[{{ $segmento->id }}]" rows="4" :readonly="!isEditing"
                                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                    :class="isEditing ?
                                                        'border-indigo-300 dark:border-indigo-700 focus:ring-2 focus:ring-indigo-500/20' :
                                                        'border-transparent pointer-events-none opacity-90'"
                                                    class="w-full border rounded-xl p-4 text-xs font-medium transition-all focus:outline-none placeholder-gray-400 dark:placeholder-gray-500">{{ $segmento->contenido }}</textarea>

                                                <div class="flex justify-end -mt-1 pr-1" x-show="isEditing"
                                                    x-transition x-data="{
                                                        isOpen: false,
                                                        query: '',
                                                        results: [],
                                                        loading: false,
                                                        search() {
                                                            if (this.query.length < 2) return this.results = [];
                                                            this.loading = true;
                                                            fetch(`{{ route('admin.enfermedades.api.search') }}?q=${encodeURIComponent(this.query)}`)
                                                                .then(r => r.json()).then(d => { this.results = d;
                                                                    this.loading = false; });
                                                        }
                                                    }"
                                                    @linked-seg-{{ $segmento->id }}.window="query = ''; results = []; isOpen = false"
                                                    @click.away="isOpen = false">

                                                    <div class="flex items-center gap-2">
                                                        <div x-show="isOpen"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 scale-95 -translate-x-4"
                                                            x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                                                            class="relative">
                                                            <input type="text" x-model="query"
                                                                @input.debounce.300ms="search()"
                                                                @keydown.enter.prevent="if(results.length > 0) vincular(results[0].id, 'seg_{{ $segmento->id }}')"
                                                                placeholder="Buscar..."
                                                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                                class="w-40 border rounded-xl py-1.5 px-3 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-sm placeholder-gray-400 dark:placeholder-gray-500">

                                                            <div x-show="query.length >= 2" x-cloak
                                                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                                class="absolute bottom-full right-0 mb-3 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border p-2 z-30">
                                                                <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                                    <template x-if="loading">
                                                                        <div
                                                                            class="p-2 text-[10px] text-gray-400 dark:text-gray-500 text-center font-medium">
                                                                            Buscando...</div>
                                                                    </template>
                                                                    <template x-for="item in results"
                                                                        :key="item.id">
                                                                        <button type="button"
                                                                            class="w-full text-left p-2 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition-colors group"
                                                                            @click="vincular(item.id, 'seg_{{ $segmento->id }}')">
                                                                            <div class="flex items-center gap-2">
                                                                                <div class="w-1.5 h-1.5 rounded-full"
                                                                                    :class="item.categoria === 'mental' ?
                                                                                        'bg-indigo-400' :
                                                                                        'bg-indigo-400'">
                                                                                </div>
                                                                                <div class="text-[10px] font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400"
                                                                                    x-text="item.nombre"></div>
                                                                            </div>
                                                                        </button>
                                                                    </template>
                                                                    <template x-if="results.length === 0 && !loading">
                                                                        <div
                                                                            class="p-2 text-[10px] text-gray-400 dark:text-gray-500 text-center italic font-medium">
                                                                            No hay resultados</div>
                                                                    </template>
                                                                </div>
                                                                <div
                                                                    class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                                                    <a onclick="abrirModalCrearEnfermedad('modal-create-edit')"
                                                                        class="block text-center text-[9px] font-black text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 uppercase tracking-widest">
                                                                        ¿No aparece? Crear nueva
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <button type="button" @click="isOpen = !isOpen"
                                                            class="w-8 h-8 rounded-full flex items-center justify-center shadow-lg transition-all transform hover:scale-110 active:scale-95"
                                                            :class="isOpen ? 'bg-slate-100 dark:bg-gray-700 text-slate-400' :
                                                                'bg-indigo-600 text-white shadow-indigo-100 dark:shadow-indigo-900/30'"
                                                            title="Añadir condición">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <div x-show="!hasVisibleSections()" x-transition
                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="rounded-2xl border-2 border-dashed p-12 text-center shadow-sm w-full">
                                <h3 class="text-lg font-extrabold tracking-tight mb-1"
                                    style="color: var(--text-main);">Sin historia encontrada</h3>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 max-w-sm mx-auto">No se
                                    encontraron secciones en el expediente general que coincidan con tu búsqueda.</p>
                            </div>
                        </div>

                        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                            class="fixed bottom-6 right-6 z-30 flex items-center gap-3 p-2.5 rounded-2xl shadow-xl border backdrop-blur-md">
                            <button type="button" x-show="isEditing" x-transition
                                @click="$dispatch('open-modal-seccion')"
                                class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-600 text-emerald-600 dark:text-emerald-400 hover:text-white rounded-xl flex items-center justify-center transition-all shadow-sm border border-emerald-100 dark:border-emerald-900/50 active:scale-95 focus:outline-none"
                                title="Añadir Anexo Clínico">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>

                            <button type="submit" x-show="isEditing" x-transition
                                class="w-12 h-12 bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-600 text-indigo-600 dark:text-indigo-400 hover:text-white rounded-xl flex items-center justify-center transition-all shadow-sm border border-indigo-100 dark:border-indigo-900/50 active:scale-95 focus:outline-none"
                                title="Actualizar Expediente">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if ($tab === 'evolucion')
                <div x-data="{
                    showExportModal: false,
                    showDeleteModal: false,
                    deleteTarget: null,
                    exportFormat: 'pdf',
                    modoDescarga: 'unificado',
                    selectAll: true,
                    selectedNotes: @js($citasPaciente->pluck('id')->toArray()),
                    allNoteIds: @js($citasPaciente->pluck('id')->toArray()),
                    toggleAll() {
                        if (this.selectAll) {
                            this.selectedNotes = [...this.allNoteIds];
                        } else {
                            this.selectedNotes = [];
                        }
                    },
                    toggleNote(id) {
                        const idx = this.selectedNotes.indexOf(id);
                        if (idx > -1) {
                            this.selectedNotes.splice(idx, 1);
                        } else {
                            this.selectedNotes.push(id);
                        }
                        this.selectAll = this.selectedNotes.length === this.allNoteIds.length;
                    },
                    openExport(format) {
                        this.exportFormat = format;
                        this.selectAll = true;
                        this.selectedNotes = [...this.allNoteIds];
                        this.showExportModal = true;
                    },
                    submitExport() {
                        const form = this.$refs.exportForm;
                        form.action = this.exportFormat === 'pdf' ?
                            '{{ route('admin.psicologia.maestros.historias.evolucion.pdf', $paciente->id) }}' :
                            '{{ route('admin.psicologia.maestros.historias.evolucion.word', $paciente->id) }}';
                        form.submit();
                    }
                }">
                    <div class="mb-6 flex items-center justify-end gap-3">
                        @if (!$citasPaciente->isEmpty())
                            <div class="relative group" x-data="{ dropOpen: false }" @mouseenter="dropOpen = true"
                                @mouseleave="dropOpen = false">
                                <button type="button"
                                    class="flex items-center gap-2 px-5 py-2.5 bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-600 text-indigo-600 dark:text-indigo-400 hover:text-white rounded-xl text-xs font-bold transition-all shadow-sm border border-indigo-100 dark:border-indigo-900/50 active:scale-95 whitespace-nowrap">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Reportes
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="dropOpen ? 'rotate-180' : ''"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="dropOpen" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-1"
                                    style="background-color: var(--bg-card); border-color: var(--border-color);"
                                    class="absolute right-0 mt-2 w-56 rounded-xl shadow-xl border z-30 overflow-hidden"
                                    x-cloak>
                                    <div class="p-2 space-y-1">
                                        <p
                                            class="px-3 py-1.5 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                            Tipo de archivo</p>
                                        <button type="button" @click="openExport('pdf'); dropOpen = false"
                                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                            <div
                                                class="w-8 h-8 bg-red-50 dark:bg-red-950/50 rounded-lg flex items-center justify-center text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/40">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="text-left">
                                                <span
                                                    class="text-xs font-bold text-gray-800 dark:text-gray-200 block">PDF</span>
                                                <span
                                                    class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">Documento
                                                    portable</span>
                                            </div>
                                        </button>
                                        <button type="button" @click="openExport('word'); dropOpen = false"
                                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                            <div
                                                class="w-8 h-8 bg-blue-50 dark:bg-blue-950/50 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="text-left">
                                                <span
                                                    class="text-xs font-bold text-gray-800 dark:text-gray-200 block">Word</span>
                                                <span
                                                    class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">Documento
                                                    editable (.docx)</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form
                            action="{{ route('admin.psicologia.maestros.historias.evolucion.store', $paciente->id) }}"
                            method="POST">
                            @csrf
                            <button type="submit"
                                class="group flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md active:scale-95 transition-all">
                                <svg class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </form>
                    </div>

                    @if ($citasPaciente->isEmpty())
                        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                            class="rounded-2xl border-2 border-dashed p-12 text-center shadow-sm w-full">
                            <h3 class="text-lg font-extrabold tracking-tight mb-1" style="color: var(--text-main);">No
                                hay sesiones aún</h3>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 max-w-sm mx-auto">Las
                                sesiones aparecerán aquí como una línea de tiempo a medida que se completen.</p>
                        </div>
                    @else
                        <div
                            class="space-y-8 relative before:absolute before:inset-y-0 before:left-4 md:before:left-1/2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-800 before:-translate-x-1/2">
                            @foreach ($citasPaciente as $index => $cita)
                                <div
                                    class="relative flex flex-col md:flex-row gap-8 md:gap-0 items-start md:items-center">
                                    <div
                                        class="absolute left-4 md:left-1/2 w-7 h-7 rounded-full z-10 -translate-x-1/2 border-4 {{ $cita->is_manual ? 'border-amber-500 bg-amber-50 dark:bg-amber-950' : 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950' }} shadow-sm">
                                    </div>
                                    <div
                                        class="w-full md:w-[45%] {{ $index % 2 == 0 ? 'md:mr-auto md:pr-12 text-left md:text-right' : 'md:ml-auto md:pl-12 order-last md:order-none' }}">
                                        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                            class="rounded-2xl border shadow-sm p-6 hover:shadow-md transition-all group">
                                            <div
                                                class="flex {{ $index % 2 == 0 ? 'md:flex-row-reverse' : '' }} items-center gap-3 mb-3">
                                                <span
                                                    class="text-[10px] font-black {{ $cita->is_manual ? 'text-amber-600 dark:text-amber-400' : 'text-indigo-600 dark:text-indigo-400' }} uppercase tracking-wider">{{ $cita->fecha?->translatedFormat('d M, Y') }}</span>
                                                @if (!$cita->is_manual)
                                                    <span
                                                        class="w-1 h-1 bg-gray-300 dark:bg-gray-700 rounded-full"></span>
                                                    <span
                                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sesión
                                                        #{{ $cita->session_number }}</span>
                                                @else
                                                    <span
                                                        class="px-2.5 py-0.5 bg-amber-50/70 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 text-[9px] font-black uppercase tracking-wider rounded-xl border border-amber-100 dark:border-amber-900/50">Manual</span>
                                                @endif
                                            </div>
                                            <h4 class="text-base sm:text-lg font-extrabold tracking-tight mb-2"
                                                style="color: var(--text-main);">
                                                {{ $cita->display_title ?? 'Consulta General' }}</h4>
                                            <p
                                                class="text-xs font-medium text-gray-500 dark:text-gray-400 leading-relaxed italic mb-4">
                                                "{{ Str::limit($cita->notas_limpias, 150) ?: 'No se registraron notas específicas.' }}"
                                            </p>
                                            <div
                                                class="flex {{ $index % 2 == 0 ? 'md:flex-row-reverse' : '' }} flex-wrap gap-2">
                                                <a href="{{ route('admin.psicologia.maestros.citas.edit.note', $cita->id) }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl text-[10px] font-bold uppercase tracking-wider border border-indigo-100 dark:border-indigo-900/50 hover:bg-indigo-600 hover:text-white transition-all active:scale-95"
                                                    title="Editar Nota">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                        </path>
                                                    </svg>
                                                    Editar
                                                </a>
                                                @if (!$cita->is_manual)
                                                    <a href="{{ route('admin.psicologia.maestros.citas.constancia.pdf', $cita->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl text-[10px] font-bold uppercase tracking-wider border border-emerald-100 dark:border-emerald-900/50 hover:bg-emerald-600 hover:text-white transition-all active:scale-95"
                                                        title="Descargar Constancia">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2.5"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                            </path>
                                                        </svg>
                                                        Constancia
                                                    </a>
                                                @endif
                                                @if ($cita->is_manual)
                                                    <button type="button"
                                                        @click="deleteTarget = {{ $cita->id }}; showDeleteModal = true"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50/70 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-xl text-[10px] font-bold uppercase tracking-wider border border-red-100 dark:border-red-900/50 hover:bg-red-600 hover:text-white transition-all active:scale-95"
                                                        title="Eliminar Nota Manual">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2.5"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                        Eliminar
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($citasPaciente->hasPages())
                            <div class="mt-8">
                                {{ $citasPaciente->appends(['tab' => 'evolucion'])->links('admin.psicologia.maestros.historias.partials.pagination') }}
                            </div>
                        @endif
                    @endif

                    <div x-show="showExportModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div x-show="showExportModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm"
                                @click="showExportModal = false"></div>
                            <div x-show="showExportModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="relative rounded-2xl shadow-2xl w-full max-w-md p-0 overflow-hidden border z-10">
                                <div
                                    class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800/60">
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="showExportModal = false"
                                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 19l-7-7 7-7"></path>
                                            </svg>
                                        </button>
                                        <h3 class="text-lg font-extrabold tracking-tight"
                                            style="color: var(--text-main);">Reportes</h3>
                                    </div>
                                    <button type="button" @click="showExportModal = false"
                                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="p-6 space-y-5">
                                    <div>
                                        <label
                                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Tipo
                                            de archivo</label>
                                        <div class="flex items-center gap-2 p-3 rounded-xl border"
                                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center border transition-colors"
                                                :class="exportFormat === 'pdf' ?
                                                    'bg-red-50 dark:bg-red-950/50 border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400' :
                                                    'bg-blue-50 dark:bg-blue-950/50 border-blue-100 dark:border-blue-900/40 text-blue-600 dark:text-blue-400'">
                                                <svg x-show="exportFormat === 'pdf'" class="w-4 h-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                <svg x-show="exportFormat === 'word'" class="w-4 h-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <span class="text-xs font-bold" style="color: var(--text-main);"
                                                    x-text="exportFormat === 'pdf' ? 'PDF' : 'Word (.docx)'"></span>
                                            </div>
                                            <div class="flex gap-1">
                                                <button type="button" @click="exportFormat = 'pdf'"
                                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                                                    :class="exportFormat === 'pdf' ?
                                                        'bg-indigo-600 text-white shadow-sm active:scale-95' :
                                                        'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800'">PDF</button>
                                                <button type="button" @click="exportFormat = 'word'"
                                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                                                    :class="exportFormat === 'word' ?
                                                        'bg-indigo-600 text-white shadow-sm active:scale-95' :
                                                        'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800'">Word</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Modo
                                            de descarga</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button type="button" @click="modoDescarga = 'unificado'"
                                                class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all active:scale-95"
                                                :class="modoDescarga === 'unificado' ?
                                                    'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800/60' :
                                                    'hover:bg-gray-50 dark:hover:bg-gray-800/50'"
                                                style="border-color: var(--border-color);">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                                    :class="modoDescarga === 'unificado' ?
                                                        'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400' :
                                                        'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div class="text-center">
                                                    <span class="text-xs font-bold block"
                                                        :class="modoDescarga === 'unificado' ?
                                                            'text-indigo-600 dark:text-indigo-400' :
                                                            'text-gray-600 dark:text-gray-300'">Un
                                                        solo documento</span>
                                                    <span class="text-[9px] text-gray-400 font-medium">Todas en 1
                                                        archivo</span>
                                                </div>
                                            </button>
                                            <button type="button" @click="modoDescarga = 'individuales'"
                                                class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all active:scale-95"
                                                :class="modoDescarga === 'individuales' ?
                                                    'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800/60' :
                                                    'hover:bg-gray-50 dark:hover:bg-gray-800/50'"
                                                style="border-color: var(--border-color);">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                                    :class="modoDescarga === 'individuales' ?
                                                        'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400' :
                                                        'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div class="text-center">
                                                    <span class="text-xs font-bold block"
                                                        :class="modoDescarga === 'individuales' ?
                                                            'text-indigo-600 dark:text-indigo-400' :
                                                            'text-gray-600 dark:text-gray-300'">Archivos
                                                        individuales</span>
                                                    <span class="text-[9px] text-gray-400 font-medium">Carpeta
                                                        ZIP</span>
                                                </div>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Selecciona
                                            notas</label>
                                        <label
                                            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors mb-3"
                                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                                class="w-4 h-4 rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                            <div>
                                                <span class="text-xs font-bold" style="color: var(--text-main);">Todas
                                                    las notas</span>
                                                <span
                                                    class="text-xs text-gray-400 font-medium ml-1">({{ $citasPaciente->total() }})</span>
                                            </div>
                                        </label>

                                        <div class="max-h-52 overflow-y-auto space-y-1.5 pr-1 scrollbar-thin">
                                            @foreach ($citasPaciente as $index => $cita)
                                                <label
                                                    class="flex items-center gap-3 p-2.5 rounded-xl cursor-pointer transition-colors"
                                                    :class="selectedNotes.includes({{ $cita->id }}) ?
                                                        'bg-indigo-50/50 dark:bg-indigo-950/30' :
                                                        'hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                                                    <input type="checkbox" value="{{ $cita->id }}"
                                                        :checked="selectedNotes.includes({{ $cita->id }})"
                                                        @change="toggleNote({{ $cita->id }})"
                                                        class="w-4 h-4 rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-xs font-black text-indigo-600 dark:text-indigo-400 shrink-0">Nota
                                                                {{ $citasPaciente->total() - ($citasPaciente->currentPage() - 1) * $citasPaciente->perPage() - $index }}</span>
                                                            <span
                                                                class="text-[10px] text-gray-400 font-medium truncate">{{ $cita->fecha?->translatedFormat('d M, Y') }}</span>
                                                        </div>
                                                        <p
                                                            class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                                            {{ $cita->display_title ?? 'Consulta General' }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between text-xs font-medium text-gray-500 dark:text-gray-400 rounded-xl px-4 py-2.5 border"
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);">
                                        <span>Notas seleccionadas</span>
                                        <span class="font-black text-indigo-600 dark:text-indigo-400"
                                            x-text="selectedNotes.length + ' de ' + allNoteIds.length"></span>
                                    </div>

                                    <form x-ref="exportForm" method="POST" action="" target="_blank">
                                        @csrf
                                        <input type="hidden" name="modo_descarga" :value="modoDescarga">
                                        <template x-for="noteId in selectedNotes" :key="noteId">
                                            <input type="hidden" name="citas_ids[]" :value="noteId">
                                        </template>
                                        <button type="button" @click="submitExport()"
                                            :disabled="selectedNotes.length === 0"
                                            class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-200 dark:disabled:bg-gray-800 text-white disabled:text-gray-400 dark:disabled:text-gray-600 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-md active:scale-95 disabled:active:scale-100 disabled:shadow-none">
                                            Descargar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm"
                            @click="showDeleteModal = false"></div>
                        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                            style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                            class="relative rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 text-center border z-10">
                            <div
                                class="mx-auto flex items-center justify-center h-14 w-14 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-100 dark:border-rose-900/40 text-rose-500 dark:text-rose-400 mb-5 shadow-sm">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-extrabold tracking-tight mb-2" style="color: var(--text-main);">
                                ¿Eliminar nota manual?</h3>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                                Esta acción eliminará permanentemente la nota de evolución manual y no podrá deshacerse.
                            </p>
                            <div class="flex items-center justify-center gap-3">
                                <button type="button" @click="showDeleteModal = false; deleteTarget = null"
                                    class="flex-1 px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all active:scale-95">
                                    Cancelar
                                </button>
                                <form :action="'/citas/' + deleteTarget" method="POST" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        @endif

        <div x-data="{
            isOpen: false,
            search: '',
            descripcion: '',
            numCampos: 1,
            segmentos: [''],
            mostrarMensaje: false,
        
            actualizarSegmentos() {
                let n = parseInt(this.numCampos);
                if (n < 1) n = 1;
                if (n > 4) n = 4;
                this.numCampos = n;
        
                while (this.segmentos.length < n) {
                    this.segmentos.push('');
                }
                while (this.segmentos.length > n) {
                    this.segmentos.pop();
                }
        
                if (n >= 4) {
                    this.mostrarMensaje = true;
                    setTimeout(() => {
                        this.mostrarMensaje = false;
                    }, 5000);
                }
            },
        
            selectTemplate(t) {
                this.search = t.titulo;
            }
        }" @open-modal-seccion.window="isOpen = true" x-show="isOpen"
            class="fixed inset-0 z-[110] overflow-y-auto" x-cloak>

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm"
                    @click="isOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                    class="inline-block px-6 pt-6 pb-6 overflow-hidden text-left align-bottom transition-all transform rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-xl sm:w-full sm:p-8 border z-10">

                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold tracking-tight" style="color: var(--text-main);">
                                    Añadir Anexo Clínico</h3>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Configuración del
                                    Historial</p>
                            </div>
                        </div>
                        <button type="button" @click="isOpen = false"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form x-ref="formSeccion"
                        action="{{ route('admin.psicologia.maestros.historias.secciones.store', $paciente->id) }}"
                        method="POST">
                        @csrf
                        <div class="space-y-6 max-h-[60vh] overflow-y-auto px-1 custom-scrollbar">
                            <div>
                                <label
                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Título
                                    del Anexo Clínico <span class="text-red-500">*</span></label>
                                <input type="text" name="titulo" x-model="search" required
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-medium transition-all"
                                    placeholder="Ej: Prueba de Inteligencia">
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Descripción
                                    General (Opcional)</label>
                                <input type="text" name="descripcion_general" x-model="descripcion"
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-medium transition-all"
                                    placeholder="Ej: Evaluación cognitiva detallada">
                            </div>

                            <hr class="border-t border-gray-100 dark:border-gray-800">

                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <label
                                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400">¿Cuántos
                                        campos (segmentos)?</label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="numCampos--; actualizarSegmentos()"
                                            class="w-8 h-8 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 flex items-center justify-center transition-all">-</button>
                                        <span class="text-sm font-extrabold w-4 text-center"
                                            style="color: var(--text-main);" x-text="numCampos"></span>
                                        <button type="button" @click="numCampos++; actualizarSegmentos()"
                                            :class="{ 'opacity-50 cursor-not-allowed': numCampos >= 4 }"
                                            class="w-8 h-8 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 flex items-center justify-center transition-all">+</button>
                                    </div>
                                </div>

                                <template x-if="mostrarMensaje">
                                    <div x-show="mostrarMensaje" x-transition:enter="transition ease-out duration-500"
                                        x-transition:enter-start="opacity-0 transform translate-y-4"
                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                        x-transition:leave="transition ease-in duration-1000"
                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                        x-transition:leave-end="opacity-0 transform translate-y-4"
                                        class="bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 px-4 py-3 rounded-xl relative mb-4 text-xs font-medium"
                                        role="alert">
                                        <span class="block sm:inline">No puedes crear más de 4 segmentos por cada anexo
                                            clínico.</span>
                                    </div>
                                </template>

                                <div class="space-y-3">
                                    <template x-for="(seg, index) in segmentos" :key="index">
                                        <div class="flex items-center gap-3 animate-fade-in-up">
                                            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black"
                                                x-text="index + 1"></div>
                                            <input type="text" :name="'segmentos_titulos[]'"
                                                x-model="segmentos[index]" required
                                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                class="flex-1 h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs font-bold transition-all"
                                                :placeholder="'Título del campo ' + (index + 1)">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            @if ($plantillas->count() > 0)
                                <div class="pt-2" x-data="{
                                    queryPlantilla: '',
                                    plantillasData: @js(
    $plantillas
        ->map(function ($p) {
            return [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'descripcion_general' => $p->descripcion_general ?? '',
                'segmentos' => $p->segmentos ?? '[]',
            ];
        })
        ->values()
        ->toArray(),
),
                                    openDropdown: false,
                                    get filteredPlantillas() {
                                        if (this.queryPlantilla === '') {
                                            return this.plantillasData.slice(0, 5);
                                        }
                                        return this.plantillasData.filter(p => p.titulo.toLowerCase().includes(this.queryPlantilla.toLowerCase()));
                                    }
                                }">
                                    <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-3">
                                        Reutilizar títulos de mis plantillas</h4>
                                    <div class="relative" @click.away="openDropdown = false">
                                        <input type="text" x-model="queryPlantilla" @focus="openDropdown = true"
                                            placeholder="Escriba para buscar o haga clic para ver recientes..."
                                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                            class="w-full h-11 px-4 border rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all mb-2">

                                        <div x-show="openDropdown"
                                            style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                            class="absolute bottom-full mb-1 z-50 w-full border rounded-2xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar"
                                            x-cloak>

                                            <div
                                                class="px-4 py-2.5 bg-gray-50/50 dark:bg-gray-900/50 text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                                                <span x-show="queryPlantilla === ''">Últimas disponibles
                                                    agregadas:</span>
                                                <span x-show="queryPlantilla !== ''">Resultados encontrados:</span>
                                            </div>

                                            <template x-if="filteredPlantillas.length === 0">
                                                <div class="p-4 text-xs font-bold text-rose-500">No hay resultados
                                                    encontrados.</div>
                                            </template>

                                            <template x-for="plantilla in filteredPlantillas" :key="plantilla.id">
                                                <button type="button"
                                                    @click="
                                                    search = plantilla.titulo;
                                                    descripcion = plantilla.descripcion_general;
                                                    let segs = [];
                                                    try { segs = JSON.parse(plantilla.segmentos || '[]'); } catch(e) {}
                                                    segmentos = segs;
                                                    numCampos = segs.length > 0 ? segs.length : 1;
                                                    if (segmentos.length === 0) segmentos = [''];
                                                    
                                                    openDropdown = false;
                                                    queryPlantilla = '';
                                                "
                                                    class="w-full text-left px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 last:border-0 transition-colors flex flex-col gap-1">
                                                    <span x-text="plantilla.titulo"
                                                        style="color: var(--text-main);"></span>
                                                    <span x-show="plantilla.descripcion_general"
                                                        class="text-xs font-normal text-gray-400"
                                                        x-text="plantilla.descripcion_general"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div
                            class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                            <button type="button" @click="isOpen = false"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all active:scale-95">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Anexar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="showStats" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div x-show="showStats" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm"
                    @click="showStats = false"></div>

                <div x-show="showStats" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                    style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                    class="relative rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8 border z-10">

                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold tracking-tight" style="color: var(--text-main);">
                                    Resumen de Actividad</h3>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Historial del paciente
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showStats = false"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);"
                        class="rounded-2xl p-5 mb-4 border relative overflow-hidden flex items-center justify-between shadow-sm">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total de
                                actividades</p>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Todas las
                                interacciones registradas</p>
                        </div>
                        <span
                            class="text-4xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $stats['total'] }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);"
                            class="rounded-xl p-3.5 border shadow-sm flex flex-col justify-center">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Sesiones
                                    logradas</p>
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-extrabold"
                                style="color: var(--text-main);">{{ $stats['realizadas'] }}</span>
                        </div>

                        <div style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);"
                            class="rounded-xl p-3.5 border shadow-sm flex flex-col justify-center">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Inasistencias
                                </p>
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-extrabold"
                                style="color: var(--text-main);">{{ $stats['inasistencias'] }}</span>
                        </div>

                        <div style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);"
                            class="rounded-xl p-3.5 border shadow-sm flex flex-col justify-center">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Canc. paciente
                                </p>
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 8v4m0 4h.01"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-extrabold"
                                style="color: var(--text-main);">{{ $stats['paciente_cancel_post'] }}</span>
                        </div>

                        <div style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);"
                            class="rounded-xl p-3.5 border shadow-sm flex flex-col justify-center">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Canc.
                                    psicólogo</p>
                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-2xl font-extrabold"
                                style="color: var(--text-main);">{{ $stats['psicologo_cancel'] }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);"
                            class="flex-1 rounded-xl p-3.5 border shadow-sm flex flex-col justify-center">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Sin
                                    horario</span>
                            </div>
                            <span class="text-xl font-extrabold"
                                style="color: var(--text-main);">{{ $stats['paciente_cancel_pre'] }}</span>
                        </div>
                        <div style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);"
                            class="flex-1 rounded-xl p-3.5 border shadow-sm flex flex-col justify-center">
                            <div class="flex items-center justify-between mb-1">
                                <span
                                    class="text-[10px] font-black uppercase tracking-wider text-gray-400">Rechazadas</span>
                            </div>
                            <span class="text-xl font-extrabold"
                                style="color: var(--text-main);">{{ $stats['rechazadas'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showUnsavedModal" class="fixed inset-0 z-[9999] overflow-y-auto" style="z-index: 9999;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div x-show="showUnsavedModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm"
                    @click="showUnsavedModal = false"></div>

                <div x-show="showUnsavedModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                    style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                    class="relative inline-block w-full max-w-sm p-6 sm:p-8 overflow-hidden text-center transition-all transform rounded-2xl shadow-2xl border z-10">

                    <div
                        class="mx-auto flex items-center justify-center h-14 w-14 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-100 dark:border-amber-900/40 text-amber-500 dark:text-amber-400 mb-5 shadow-sm">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-extrabold tracking-tight mb-2" style="color: var(--text-main);">¿Estás
                        seguro que deseas salir?</h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">Hay
                        información aún no guardada. Si sales ahora, perderás los cambios realizados.</p>

                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="showUnsavedModal = false"
                            class="flex-1 px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all active:scale-95">
                            Cancelar
                        </button>
                        <button type="button" @click="confirmLeave()"
                            class="flex-1 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                            Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('pacientes.partials.modal')
    @include('admin.enfermedades.modal-create-edit', [
        'modalId' => 'modal-create-edit',
        'tipo' => $tipo,
        'returnTo' => $returnTo,
        'editing' => $editing,
        'categoriaTexto' => $categoriaTexto,
    ])
</x-app-layout>
