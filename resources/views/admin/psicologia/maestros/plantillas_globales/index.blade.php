<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            @if (session('error'))
                <div
                    class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span><strong
                            class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Encabezado de Página --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Esquema General del Expediente
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Define la estructura base para el módulo de <strong
                            class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">Salud Mental</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.psicologia.maestros.plantillas.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold text-xs shadow-sm transition-all">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Volver a Plantillas</span>
                    </a>
                </div>
            </div>

            {{-- Alerta informativa --}}
            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border p-5 mb-8 flex flex-col sm:flex-row sm:items-start gap-4 shadow-sm">
                <div class="flex items-center justify-between sm:justify-start gap-3 w-full sm:w-auto">
                    <div
                        class="w-10 h-10 bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-info-circle text-lg"></i>
                    </div>
                    <div class="sm:hidden">
                        @if (($plantilla?->estado_phc ?? null) == 1)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                ACTIVA
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                INACTIVA / PREDETERMINADA
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-bold" style="color: var(--text-main);">
                        ¿Qué es el esquema general del expediente?
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                        Este te permite estandarizar las secciones y campos fundamentales <strong>para todos los
                            pacientes.</strong> Edita la estructura a continuación y luego asegúrate de guardar los
                        cambios para activarla.
                    </p>
                </div>
                <div class="hidden sm:flex shrink-0 items-center pt-1">
                    @if (($plantilla?->estado_phc ?? null) == 1)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            ACTIVA
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            INACTIVA / PREDETERMINADA
                        </span>
                    @endif
                </div>
            </div>

            @php
                $seccionesData = $plantilla?->secciones_decoded ?? [];
                $seccionesAlpine = [];
                foreach ($seccionesData as $seccion) {
                    $segs = $seccion['segmentos'] ?? [''];
                    if (empty($segs)) {
                        $segs = [''];
                    }
                    $seccionesAlpine[] = [
                        'titulo' => $seccion['titulo'] ?? '',
                        'descripcion_general' => $seccion['descripcion_general'] ?? '',
                        'numCampos' => count($segs),
                        'segmentos' => $segs,
                    ];
                }
                if (empty($seccionesAlpine)) {
                    $seccionesAlpine = [
                        ['titulo' => '', 'descripcion_general' => '', 'numCampos' => 1, 'segmentos' => ['']],
                    ];
                }
            @endphp

            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                class="rounded-2xl border shadow-sm p-6 sm:p-8 pb-24" x-data="{
                    secciones: @js($seccionesAlpine),
                    isEditing: {{ ($plantilla?->estado_phc ?? 2) == 2 ? 'true' : 'false' }},
                    showModal: false,
                    search: '',
                
                    agregarSeccion() {
                        if (!this.isEditing) return;
                        this.secciones.push({
                            titulo: '',
                            descripcion_general: '',
                            numCampos: 1,
                            segmentos: ['']
                        });
                    },
                
                    eliminarSeccion(index) {
                        if (!this.isEditing) return;
                        if (this.secciones.length > 1) {
                            this.secciones.splice(index, 1);
                        }
                    },
                
                    moverSeccion(index, direccion) {
                        if (!this.isEditing) return;
                        if (direccion === -1 && index > 0) {
                            let temp = this.secciones[index];
                            this.secciones[index] = this.secciones[index - 1];
                            this.secciones[index - 1] = temp;
                        } else if (direccion === 1 && index < this.secciones.length - 1) {
                            let temp = this.secciones[index];
                            this.secciones[index] = this.secciones[index + 1];
                            this.secciones[index + 1] = temp;
                        }
                    },
                
                    showToast: false,
                    toastMessage: '',
                    toastSecIndex: -1,
                    triggerToast(secIndex) {
                        this.toastSecIndex = secIndex;
                        this.showToast = true;
                        setTimeout(() => { this.showToast = false; }, 3000);
                    },
                
                    actualizarSegmentos(seccion) {
                        if (!this.isEditing) return;
                        let n = parseInt(seccion.numCampos);
                        if (n < 1) n = 1;
                        if (n > 4) {
                            n = 4;
                        }
                        seccion.numCampos = n;
                
                        while (seccion.segmentos.length < n) {
                            seccion.segmentos.push('');
                        }
                        if (seccion.segmentos.length > n) {
                            seccion.segmentos = seccion.segmentos.slice(0, n);
                        }
                    },
                
                    guardar() {
                        if ('{{ $plantilla?->estado_phc ?? 2 }}' == '1') {
                            this.showModal = true;
                        } else {
                            document.getElementById('formPlantilla').submit();
                        }
                    },
                
                    submitForm(aplicarTodos) {
                        document.getElementById('aplicar_a_todos_input').value = aplicarTodos ? '1' : '0';
                        document.getElementById('formPlantilla').submit();
                    }
                }">

                <form id="formPlantilla"
                    action="{{ route('admin.psicologia.maestros.plantillas_globales.update', $plantilla->id_phc) }}"
                    method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        {{-- Nombre de la Plantilla --}}
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Nombre de la Plantilla <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="titulo" required x-bind:readonly="!isEditing"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-bold transition-all"
                                :class="!isEditing ? 'border-transparent bg-transparent px-0 focus:ring-0' : ''"
                                placeholder="Ej: Evaluación Psicológica Integral"
                                value="{{ old('titulo', $plantilla?->titulo_phc ?? '') }}">
                            @error('titulo')
                                <p class="mt-2 text-xs text-rose-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Descripción General --}}
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Descripción General (Opcional)
                            </label>
                            <input type="text" name="descripcion" x-bind:readonly="!isEditing"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                :class="!isEditing ? 'border-transparent bg-transparent px-0 focus:ring-0' : ''"
                                placeholder="Ej: Plantilla estándar para evaluación inicial"
                                value="{{ old('descripcion', $plantilla?->descripcion_phc ?? '') }}">
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80">
                            {{-- Título de secciones y Buscador --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                                <div>
                                    <h3 class="text-base font-extrabold tracking-tight"
                                        style="color: var(--text-main);">
                                        Secciones del Historial
                                    </h3>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                                        Cada sección contiene campos (segmentos) editables
                                    </p>
                                </div>
                                <div class="relative w-full sm:w-64 shrink-0">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-search text-xs"></i>
                                    </div>
                                    <input type="text" x-model="search"
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full pl-9 pr-4 py-2 rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all"
                                        placeholder="Buscar sección...">
                                </div>
                            </div>

                            {{-- Lista de secciones --}}
                            <div class="space-y-6">
                                <template x-for="(seccion, secIndex) in secciones" :key="secIndex">
                                    <div x-show="search === '' || seccion.titulo.toLowerCase().includes(search.toLowerCase())"
                                        style="background-color: rgba(0,0,0,0.015); border-color: var(--border-color);"
                                        class="rounded-2xl p-5 border relative">

                                        {{-- Header de la sección --}}
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center gap-2.5">
                                                <span
                                                    class="w-7 h-7 bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 rounded-lg flex items-center justify-center font-black text-xs shadow-sm"
                                                    x-text="secIndex + 1"></span>
                                                <span
                                                    class="text-[10px] font-black uppercase tracking-wider text-gray-400">Sección</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="moverSeccion(secIndex, -1)"
                                                    x-show="isEditing && secIndex > 0"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-{{ $themeColor }}-600 dark:hover:text-{{ $themeColor }}-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                                    title="Mover arriba">
                                                    <i class="fas fa-chevron-up text-xs"></i>
                                                </button>
                                                <button type="button" @click="moverSeccion(secIndex, 1)"
                                                    x-show="isEditing && secIndex < secciones.length - 1"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-{{ $themeColor }}-600 dark:hover:text-{{ $themeColor }}-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                                    title="Mover abajo">
                                                    <i class="fas fa-chevron-down text-xs"></i>
                                                </button>
                                                <button type="button" @click="eliminarSeccion(secIndex)"
                                                    x-show="isEditing && secciones.length > 1"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors"
                                                    title="Eliminar sección">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Título de la sección --}}
                                        <div class="mb-4">
                                            <label
                                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                                                Título de la Sección <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" :name="'secciones_estructura[' + secIndex + '][titulo]'"
                                                x-model="seccion.titulo" required x-bind:readonly="!isEditing"
                                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                class="w-full h-10 px-3.5 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-bold transition-all"
                                                :class="!isEditing ? 'border-transparent bg-transparent px-0 focus:ring-0' : ''"
                                                placeholder="Ej: Antecedentes Personales">
                                        </div>

                                        {{-- Descripción de la sección --}}
                                        <div class="mb-4">
                                            <label
                                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                                                Descripción (Opcional)
                                            </label>
                                            <input type="text"
                                                :name="'secciones_estructura[' + secIndex + '][descripcion_general]'"
                                                x-model="seccion.descripcion_general" x-bind:readonly="!isEditing"
                                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                class="w-full h-10 px-3.5 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                                :class="!isEditing ? 'border-transparent bg-transparent px-0 focus:ring-0' : ''"
                                                placeholder="Ej: Historial médico y psicológico del paciente">
                                        </div>

                                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800/80">
                                            {{-- Campos (Segmentos) --}}
                                            <div>
                                                <div class="flex items-center justify-between mb-3">
                                                    <label
                                                        class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                                                        Campos (Segmentos)
                                                    </label>
                                                    <div class="flex items-center gap-2">
                                                        <div x-show="showToast && toastSecIndex === secIndex"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 transform translate-x-4"
                                                            x-transition:enter-end="opacity-100 transform translate-x-0"
                                                            x-transition:leave="transition ease-in duration-300"
                                                            x-transition:leave-start="opacity-100 transform translate-x-0"
                                                            x-transition:leave-end="opacity-0 transform translate-x-4"
                                                            style="display: none;"
                                                            class="bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 px-3 py-1 rounded-xl flex items-center gap-1.5">
                                                            <i class="fas fa-exclamation-triangle text-xs"></i>
                                                            <span class="text-[10px] font-bold">Máx. 4 campos</span>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800 p-1 rounded-xl border border-gray-200 dark:border-gray-700">
                                                            <button type="button" x-show="isEditing"
                                                                @click="if(seccion.numCampos > 1) { seccion.numCampos--; actualizarSegmentos(seccion); }"
                                                                class="w-6 h-6 flex items-center justify-center rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-xs font-bold">-</button>
                                                            <span
                                                                class="w-6 text-center text-xs font-black text-gray-700 dark:text-gray-200"
                                                                x-text="seccion.numCampos"></span>
                                                            <button type="button" x-show="isEditing"
                                                                @click="if(seccion.numCampos < 4) { seccion.numCampos++; actualizarSegmentos(seccion); } else { triggerToast(secIndex); }"
                                                                class="w-6 h-6 flex items-center justify-center rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-xs font-bold">+</button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="space-y-2">
                                                    <template x-for="(seg, segIndex) in seccion.segmentos"
                                                        :key="segIndex">
                                                        <div class="flex items-center gap-2.5">
                                                            <div class="w-6 h-6 rounded-lg bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 flex items-center justify-center font-bold text-[10px] shrink-0"
                                                                x-text="segIndex + 1"></div>
                                                            <input type="text"
                                                                x-model="seccion.segmentos[segIndex]"
                                                                :name="'secciones_estructura[' + secIndex + '][segmentos][' +
                                                                    segIndex + ']'"
                                                                required x-bind:readonly="!isEditing"
                                                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                                class="w-full h-9 px-3 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-xs font-medium transition-all"
                                                                :class="!isEditing ?
                                                                    'border-transparent bg-transparent px-0 focus:ring-0' :
                                                                    ''"
                                                                placeholder="Título del campo">
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Modal Confirmación --}}
                        <div x-show="showModal" style="display: none;"
                            class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
                            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="rounded-2xl p-6 shadow-2xl max-w-md w-full border"
                                @click.away="showModal = false">
                                <h3 class="text-base font-extrabold tracking-tight mb-2"
                                    style="color: var(--text-main);">
                                    ¿Estás seguro de guardar estos cambios?
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                                    Al hacerlo, los cambios podrán aplicarse en todas las historias clínicas ya
                                    existentes. Sin embargo, si no lo deseas, los cambios solo se aplicarán para los
                                    siguientes expedientes clínicos futuros.
                                </p>
                                <input type="hidden" name="aplicar_a_todos" id="aplicar_a_todos_input"
                                    value="0">

                                <div class="flex flex-col gap-2.5">
                                    <button type="button" @click="submitForm(true)"
                                        class="w-full py-2.5 px-4 rounded-xl {{ $btnClass }} text-white font-bold text-xs shadow-md active:scale-95 transition-all">
                                        Sí, aplicar a todos
                                    </button>
                                    <button type="button" @click="submitForm(false)"
                                        class="w-full py-2.5 px-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs transition-all">
                                        Solo aplicar a expedientes futuros
                                    </button>
                                    <button type="button" @click="showModal = false"
                                        class="mt-1 text-[10px] font-black uppercase tracking-wider text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-center w-full transition-colors">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Floating Action Buttons --}}
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="fixed bottom-8 right-8 z-30 flex items-center gap-3 p-2 rounded-full shadow-2xl border">
                        <template x-if="isEditing">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="agregarSeccion()" title="Agregar Sección"
                                    class="flex items-center gap-2 px-4 h-11 rounded-full bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 hover:bg-{{ $themeColor }}-600 hover:text-white transition-all shadow-sm text-xs font-bold">
                                    <i class="fas fa-plus text-xs"></i>
                                    <span>Agregar Sección</span>
                                </button>
                                <div class="w-px h-6 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                                <button type="button"
                                    @click="isEditing = false; if('{{ $plantilla?->estado_phc ?? 2 }}' == '2') isEditing = true;"
                                    title="Cancelar"
                                    class="w-11 h-11 flex items-center justify-center rounded-full bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm text-xs font-bold"
                                    x-show="'{{ $plantilla?->estado_phc ?? 2 }}' == '1'">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button type="button" @click="guardar()"
                                    title="{{ ($plantilla?->estado_phc ?? 2) == 1 ? 'Guardar' : 'Guardar y Activar' }}"
                                    class="w-11 h-11 flex items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm text-xs font-bold">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="!isEditing">
                            <button type="button" @click="isEditing = true" title="Editar Esquema"
                                class="w-11 h-11 flex items-center justify-center rounded-full bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 hover:bg-{{ $themeColor }}-600 hover:text-white transition-all shadow-sm text-xs font-bold">
                                <i class="fas fa-pen"></i>
                            </button>
                        </template>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
