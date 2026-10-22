<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <a href="{{ route('admin.psicologia.maestros.plantillas_globales.index') }}"
                class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-{{ $themeColor }}-600 mb-6 transition-colors group">
                <i class="fas fa-arrow-left text-xs transition-transform group-hover:-translate-x-1"></i>
                <span>Volver a Esquemas Globales</span>
            </a>

            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                    Editar Esquema General
                </h1>
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    Modificando el esquema: <strong class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">{{ $plantilla->titulo_phc }}</strong>
                </p>
            </div>

            @include('components.alert')

            @if (session('error'))
                <div
                    class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span><strong
                            class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
                </div>
            @endif

            @php
                $seccionesData = $plantilla->secciones_decoded ?? [];
                $seccionesAlpine = [];
                foreach ($seccionesData as $seccion) {
                    $segs = $seccion['segmentos'] ?? [''];
                    if (empty($segs)) $segs = [''];
                    $seccionesAlpine[] = [
                        'titulo' => $seccion['titulo'] ?? '',
                        'descripcion_general' => $seccion['descripcion_general'] ?? '',
                        'numCampos' => count($segs),
                        'segmentos' => $segs,
                    ];
                }
                if (empty($seccionesAlpine)) {
                    $seccionesAlpine = [['titulo' => '', 'descripcion_general' => '', 'numCampos' => 1, 'segmentos' => ['']]];
                }
            @endphp

            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                class="rounded-2xl border shadow-sm overflow-hidden p-6 sm:p-8"
                x-data="{
                    secciones: @js($seccionesAlpine),

                    agregarSeccion() {
                        this.secciones.push({
                            titulo: '',
                            descripcion_general: '',
                            numCampos: 1,
                            segmentos: ['']
                        });
                    },

                    eliminarSeccion(index) {
                        if (this.secciones.length > 1) {
                            this.secciones.splice(index, 1);
                        }
                    },

                    actualizarSegmentos(seccion) {
                        let n = parseInt(seccion.numCampos);
                        if (n < 1) n = 1;
                        if (n > 10) n = 10;
                        seccion.numCampos = n;

                        while (seccion.segmentos.length < n) {
                            seccion.segmentos.push('');
                        }
                        if (seccion.segmentos.length > n) {
                            seccion.segmentos = seccion.segmentos.slice(0, n);
                        }
                    }
                }">

                <form action="{{ route('admin.psicologia.maestros.plantillas_globales.update', $plantilla->id_phc) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">

                        {{-- Nombre de la Plantilla --}}
                        <div>
                            <label for="titulo"
                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Nombre de la Plantilla <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $plantilla->titulo_phc) }}" required
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                placeholder="Ej: Evaluación Psicológica Integral">
                            @error('titulo')
                                <p class="mt-2 text-xs text-red-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Descripción General --}}
                        <div>
                            <label for="descripcion"
                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Descripción General (Opcional)
                            </label>
                            <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $plantilla->descripcion_phc) }}"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                placeholder="Ej: Plantilla estándar para evaluación inicial">
                        </div>

                        {{-- Bloque de Secciones --}}
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80">
                            
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-base font-extrabold tracking-tight" style="color: var(--text-main);">
                                        Secciones del Historial
                                    </h3>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                                        Cada sección contiene campos (segmentos) editables
                                    </p>
                                </div>
                                <button type="button" @click="agregarSeccion()"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 transition-all">
                                    <i class="fas fa-plus text-xs"></i>
                                    <span>Agregar Sección</span>
                                </button>
                            </div>

                            <div class="space-y-6">
                                <template x-for="(seccion, secIndex) in secciones" :key="secIndex">
                                    <div style="background-color: rgba(0,0,0,0.015); border-color: var(--border-color);"
                                        class="rounded-2xl p-5 border relative">

                                        {{-- Header de la sección --}}
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-7 h-7 bg-{{ $themeColor }}-100 dark:bg-{{ $themeColor }}-900/40 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 rounded-lg flex items-center justify-center font-black text-xs"
                                                    x-text="secIndex + 1"></span>
                                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Sección</span>
                                            </div>
                                            <button type="button" @click="eliminarSeccion(secIndex)" x-show="secciones.length > 1"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors"
                                                title="Eliminar sección">
                                                <i class="fas fa-trash-can text-xs"></i>
                                            </button>
                                        </div>

                                        {{-- Título de la sección --}}
                                        <div class="mb-4">
                                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                                                Título de la Sección <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" :name="'secciones_estructura['+secIndex+'][titulo]'" x-model="seccion.titulo" required
                                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                class="w-full h-10 px-3.5 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                                placeholder="Ej: Antecedentes Personales">
                                        </div>

                                        {{-- Descripción de la sección --}}
                                        <div class="mb-4">
                                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                                                Descripción (Opcional)
                                            </label>
                                            <input type="text" :name="'secciones_estructura['+secIndex+'][descripcion_general]'" x-model="seccion.descripcion_general"
                                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                class="w-full h-10 px-3.5 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                                placeholder="Ej: Historial médico y psicológico del paciente">
                                        </div>

                                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800/80">
                                            {{-- Campos (Segmentos) --}}
                                            <div>
                                                <div class="flex items-center justify-between mb-3">
                                                    <label class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                                                        Campos (Segmentos)
                                                    </label>
                                                    <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800 p-1 rounded-xl border border-gray-200 dark:border-gray-700">
                                                        <button type="button" @click="if(seccion.numCampos > 1) { seccion.numCampos--; actualizarSegmentos(seccion); }"
                                                            class="w-6 h-6 flex items-center justify-center rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-xs font-bold">-</button>
                                                        <span class="w-6 text-center text-xs font-black text-gray-700 dark:text-gray-200" x-text="seccion.numCampos"></span>
                                                        <button type="button" @click="if(seccion.numCampos < 10) { seccion.numCampos++; actualizarSegmentos(seccion); }"
                                                            class="w-6 h-6 flex items-center justify-center rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-xs font-bold">+</button>
                                                    </div>
                                                </div>

                                                <div class="space-y-2">
                                                    <template x-for="(seg, segIndex) in seccion.segmentos" :key="segIndex">
                                                        <div class="flex items-center gap-2.5">
                                                            <div class="w-6 h-6 rounded-lg bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-900/30 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 flex items-center justify-center font-bold text-[10px] shrink-0"
                                                                x-text="segIndex + 1"></div>
                                                            <input type="text" x-model="seccion.segmentos[segIndex]" :name="'secciones_estructura['+secIndex+'][segmentos]['+segIndex+']'" required
                                                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                                class="w-full h-9 px-3 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-xs font-medium transition-all"
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

                    </div>

                    {{-- Acciones / Guardar --}}
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.psicologia.maestros.plantillas_globales.index') }}"
                            class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl {{ $btnClass }} text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                            <i class="fas fa-sync-alt text-xs"></i>
                            <span>Actualizar Esquema</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>