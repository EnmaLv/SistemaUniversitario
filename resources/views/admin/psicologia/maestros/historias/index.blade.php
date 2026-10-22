<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)] flex flex-col">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col w-full">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Historial Clínico
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Gestiona la evolución y expedientes de tus pacientes atendidos.
                    </p>
                </div>
                <div x-data="{ openModal: false }" class="w-full sm:w-auto">
                    <form action="{{ route('admin.psicologia.maestros.historias.index') }}" method="GET"
                        class="flex flex-col sm:flex-row sm:items-center gap-3 w-full" id="search-form">
                        
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            @if (auth()->user()->puedeRuta('admin.psicologia.maestros.historias.exportar.pdf') || auth()->user()->puedeRuta('admin.psicologia.maestros.historias.exportar.excel'))
                            <div x-data="{ open: false }" class="relative flex-1 sm:flex-initial">
                                <button @click="open = !open" @click.away="open = false" type="button"
                                    class="w-full sm:w-auto justify-center group flex items-center gap-2 px-4 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-bold shadow-md shadow-indigo-200 dark:shadow-indigo-900/30 transition-all">
                                    <svg class="w-5 h-5 transition-transform group-hover:-translate-y-0.5"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Reportes
                                </button>

                                <style>[x-cloak] { display: none !important; }</style>
                                <div x-show="open" x-transition x-cloak
                                    style="background-color: var(--bg-card); border-color: var(--border-color);"
                                    class="absolute left-0 sm:right-0 sm:left-auto mt-2 w-48 rounded-2xl shadow-xl border overflow-hidden z-20">
                                    @canRoute('admin.psicologia.maestros.historias.exportar.pdf')
                                    <a href="{{ route('admin.psicologia.maestros.historias.exportar.pdf', ['search' => request('search'), 'pnf' => request('pnf'), 'edad' => request('edad'), 'fecha_desde' => request('fecha_desde'), 'fecha_hasta' => request('fecha_hasta'), 'enfermedad_id' => request('enfermedad_id'), 'prioridad' => request('prioridad'), 'avance_id' => request('avance_id'), 'esa_id' => request('esa_id')]) }}"
                                        target="_blank"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-bold" style="color: var(--text-main);">PDF</span>
                                    </a>
                                    @endcanRoute
                                    @canRoute('admin.psicologia.maestros.historias.exportar.excel')
                                    <a href="{{ route('admin.psicologia.maestros.historias.exportar.excel', ['search' => request('search'), 'pnf' => request('pnf'), 'edad' => request('edad'), 'fecha_desde' => request('fecha_desde'), 'fecha_hasta' => request('fecha_hasta'), 'enfermedad_id' => request('enfermedad_id'), 'prioridad' => request('prioridad'), 'avance_id' => request('avance_id'), 'esa_id' => request('esa_id')]) }}"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-bold" style="color: var(--text-main);">Excel</span>
                                    </a>
                                    @endcanRoute
                                </div>
                            </div>
                            @endif

                            <button type="button" @click="openModal = true"
                                class="flex-1 sm:flex-initial justify-center px-4 h-11 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 font-bold text-sm rounded-2xl hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                    </path>
                                </svg>
                                Filtrar
                            </button>
                        </div>

                        <div class="relative w-full sm:w-64 lg:w-80">
                            <input id="search-input" type="text" name="search" value="{{ request('search') }}"
                                placeholder="Buscar paciente..."
                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 pl-10 pr-4 border rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            <svg class="w-5 h-5 absolute left-3 top-3 text-slate-400 dark:text-gray-500"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        <div x-show="openModal"
                             class="fixed inset-0 z-[100] overflow-y-auto"
                             x-cloak>
                            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                                <div x-show="openModal"
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="fixed inset-0 transition-opacity bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm"
                                     @click="openModal = false"></div>

                                <div x-show="openModal"
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                                     style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                     class="relative rounded-2xl shadow-2xl border w-full max-w-md p-6 sm:p-8 z-10 overflow-hidden">
                                    
                                    <div class="flex items-center justify-between mb-8">
                                        <h3 class="text-xl font-extrabold tracking-tight" style="color: var(--text-main);">Filtros Avanzados</h3>
                                        <button type="button" @click="openModal = false"
                                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">PNF/Carrera</label>
                                            <select name="pnf"
                                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-medium transition-all appearance-none">
                                                <option value="" style="background-color: var(--bg-card);">Todas las carreras</option>
                                                @foreach ($pnfs as $key => $label)
                                                    <option value="{{ $key }}" {{ request('pnf') == $key ? 'selected' : '' }} style="background-color: var(--bg-card);">
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Edad</label>
                                            <input type="number" name="edad" value="{{ request('edad') }}"
                                                placeholder="Ej. 25" min="1"
                                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-medium transition-all">
                                        </div>

                                        <div x-data="{ tipoFiltro: '{{ request('tipo_filtro_fecha', 'rango') }}' }">
                                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Filtrar fechas por</label>
                                            <select name="tipo_filtro_fecha" x-model="tipoFiltro"
                                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-medium transition-all mb-5 appearance-none">
                                                <option value="rango" style="background-color: var(--bg-card);">Rango de fechas (cualquier cita)</option>
                                                <option value="primera_cita" style="background-color: var(--bg-card);">Fecha de primera cita realizada</option>
                                                <option value="ultima_cita" style="background-color: var(--bg-card);">Fecha de última cita realizada</option>
                                            </select>

                                            <div class="grid grid-cols-2 gap-4" x-show="tipoFiltro !== ''" x-transition>
                                                <div>
                                                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Fecha desde</label>
                                                    <input type="date" name="fecha_desde"
                                                        value="{{ request('fecha_desde') }}"
                                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                        class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-medium transition-all">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Fecha hasta</label>
                                                    <input type="date" name="fecha_hasta"
                                                        value="{{ request('fecha_hasta') }}"
                                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                        class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-medium transition-all">
                                                </div>
                                            </div>

                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-3 font-medium flex items-center gap-1.5" x-show="tipoFiltro === 'primera_cita'">
                                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Mostrará pacientes cuya primera sesión realizada esté en este rango.
                                            </p>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-3 font-medium flex items-center gap-1.5" x-show="tipoFiltro === 'ultima_cita'">
                                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Mostrará pacientes cuya última sesión realizada esté en este rango.
                                            </p>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-3 font-medium flex items-center gap-1.5" x-show="tipoFiltro === 'rango'">
                                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Mostrará pacientes que tengan cualquier cita dentro de este rango.
                                            </p>
                                        </div>
                                    </div>

                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Enfermedad
                                                (Buscar)</label>
                                            <div x-data="{
                                                query: '{{ $enfermedadSeleccionada ? $enfermedadSeleccionada->nombre_efm : '' }}',
                                                enfermedad_id: '{{ request('enfermedad_id') }}',
                                                results: [],
                                                loading: false,
                                                isOpen: false,
                                                search() {
                                                    if (this.query.length < 2) return this.results = [];
                                                    this.loading = true;
                                                    fetch(`{{ route('admin.enfermedades.api.search') }}?q=${encodeURIComponent(this.query)}`)
                                                        .then(r => r.json()).then(d => { this.results = d;
                                                            this.loading = false;
                                                            this.isOpen = true; });
                                                },
                                                select(item) {
                                                    this.query = item.nombre_efm;
                                                    this.enfermedad_id = item.id_efm;
                                                    this.isOpen = false;
                                                },
                                                clear() {
                                                    this.query = '';
                                                    this.enfermedad_id = '';
                                                    this.results = [];
                                                    this.isOpen = false;
                                                }
                                            }" @click.away="isOpen = false"
                                                class="relative">
                                                <input type="hidden" name="enfermedad_id" x-model="enfermedad_id">
                                                <div class="relative">
                                                    <input type="text" x-model="query"
                                                        @input.debounce.300ms="search()"
                                                        @focus="if(query.length >= 2) isOpen = true"
                                                        placeholder="Ej. Depresión..."
                                                        class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                                                    <button type="button" x-show="query.length > 0" @click="clear()"
                                                        class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-gray-300">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div x-show="isOpen && query.length >= 2" x-cloak
                                                    class="absolute mt-1 w-full bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-slate-100 dark:border-gray-700 p-2 z-50">
                                                    <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                        <template x-if="loading">
                                                            <div class="p-2 text-xs text-slate-400 text-center">
                                                                Buscando...</div>
                                                        </template>
                                                        <template x-for="item in results" :key="item.id_efm">
                                                            <button type="button" @click="select(item)"
                                                                class="w-full text-left p-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors group flex items-center gap-2">
                                                                <div class="w-1.5 h-1.5 rounded-full"
                                                                    :class="item.categoria_efm === 'mental' ? 'bg-indigo-400' :
                                                                        'bg-indigo-400'">
                                                                </div>
                                                                <div class="text-xs font-bold text-slate-700 dark:text-gray-300 group-hover:text-indigo-600"
                                                                    x-text="item.nombre_efm"></div>
                                                            </button>
                                                        </template>
                                                        <template x-if="results.length === 0 && !loading">
                                                            <div class="p-2 text-xs text-slate-400 text-center italic">
                                                                No hay resultados</div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Prioridad
                                                de Atención</label>
                                            <select name="prioridad"
                                                class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                                                <option value="">Todas</option>
                                                @foreach ($prioridades as $prioridad)
                                                    <option value="{{ $prioridad->nombre_pat }}"
                                                        {{ request('prioridad') == $prioridad->nombre_pat ? 'selected' : '' }}>
                                                        {{ $prioridad->nombre_pat }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Avance
                                                de Sesión</label>
                                            <select name="avance_id"
                                                class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                                                <option value="">Todos</option>
                                                @foreach ($avances as $avance)
                                                    <option value="{{ $avance->id_avs }}"
                                                        {{ request('avance_id') == $avance->id_avs ? 'selected' : '' }}>
                                                        {{ $avance->nombre_avs }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-bold text-slate-500 dark:text-gray-400 mb-1">Estado
                                                de Ánimo</label>
                                            <select name="esa_id"
                                                class="w-full py-2.5 px-4 bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-gray-900 dark:text-white">
                                                <option value="">Todos</option>
                                                @foreach ($estadosAnimo as $estado)
                                                    <option value="{{ $estado->id_esa }}"
                                                        {{ request('esa_id') == $estado->id_esa ? 'selected' : '' }}>
                                                        {{ $estado->nombre_esa }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-6 flex justify-end gap-3">
                                                <a href="{{ route('admin.psicologia.maestros.historias.index') }}"
                                                    class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">Limpiar</a>
                                                <button type="submit"
                                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md active:scale-95 transition-all">Aplicar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @php
                $historias = $historias ?? collect();
            @endphp
            {{-- Modificar --}}
            @if ($historias->isEmpty())
                <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);" class="rounded-2xl border shadow-sm p-12 text-center h-fit mb-auto">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500">
                        <i class="fas fa-folder-open text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-1" style="color: var(--text-main);">Sin expedientes activos</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                        Los pacientes aparecerán aquí automáticamente una vez que completes su primera cita.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($historias as $historia)
                        @php
                            $paciente = $historia['paciente'];
                            $photoPath = $paciente->profile_photo_path ?? null;
                            $hasPhoto = !empty($photoPath);
                            $nombreCompleto = $paciente->name ?? '';
                            $partes = explode(' ', trim($nombreCompleto));
                            $primerNombre = $partes[0] ?? '';
                            $primerApellido = $partes[1] ?? '';
                            $iniciales = strtoupper(substr($primerNombre, 0, 1) . substr($primerApellido, 0, 1));
                        @endphp
                        <div class="paciente-card rounded-2xl border shadow-sm overflow-hidden flex flex-col w-full transition-all duration-300 hover:shadow-md group" style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);" data-nombre="{{ strtolower($paciente->name) }}">
                            <div class="p-6 flex-1">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden flex items-center justify-center text-white font-bold text-base flex-shrink-0 bg-{{ $themeColor ?? 'indigo' }}-600">
                                        @if ($hasPhoto)
                                            <img src="{{ route('media.profile_photos', basename($photoPath)) }}" alt="{{ $paciente->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ $iniciales }}
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-base font-bold truncate tracking-tight" style="color: var(--text-main);">
                                            {{ $paciente->name }}
                                        </h3>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-xl text-[10px] font-bold border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mt-1">
                                            <i class="fas fa-circle text-[6px]"></i>
                                            Activo
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-2">
                                    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800" style="background-color: rgba(0,0,0,0.02);">
                                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                                            Sesiones
                                        </p>
                                        <p class="text-base font-extrabold" style="color: var(--text-main);">
                                            {{ $historia['citas_realizadas'] }}
                                        </p>
                                    </div>
                                    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800" style="background-color: rgba(0,0,0,0.02);">
                                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                                            Última
                                        </p>
                                        <p class="text-xs font-bold" style="color: var(--text-main);">
                                            {{ $historia['ultima_sesion'] instanceof \Carbon\Carbon ? $historia['ultima_sesion']->locale('es')->translatedFormat('d F') : \Carbon\Carbon::parse($historia['ultima_sesion'])->locale('es')->translatedFormat('d F') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('admin.psicologia.maestros.historias.show', $historia['id']) }}" class="p-4 text-center border-t text-xs font-bold transition-all flex items-center justify-center gap-2 border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 text-{{ $themeColor ?? 'indigo' }}-600 dark:text-{{ $themeColor ?? 'indigo' }}-400">
                                <span>Abrir Expediente</span>
                                <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div id="no-results-msg" class="hidden rounded-2xl border shadow-sm p-12 text-center h-fit mb-auto" style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500">
                        <i class="fas fa-magnifying-glass text-xl"></i>
                    </div>
                    <h3 class="text-base font-bold mb-1" style="color: var(--text-main);">Sin coincidencias</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">No se encontraron pacientes que coincidan con tu búsqueda.</p>
                </div>

                <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                    {{ $historias->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
