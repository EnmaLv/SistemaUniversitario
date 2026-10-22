<x-app-layout>
    @php
        $moduloActivo = strtolower(session('modulo_activo', 'general'));
        $esPsicologia = in_array($moduloActivo, ['psicologia', 'psicología', 'mental']);
        $themeColor = $esPsicologia ? 'indigo' : 'blue';
        $btnClass = $esPsicologia ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-blue-600 hover:bg-blue-700';
        $focusRingClass = $esPsicologia
            ? 'focus:ring-indigo-500/20 focus:border-indigo-500'
            : 'focus:ring-blue-500/20 focus:border-blue-500';

        $segmentosIniciales = old('segmentos')
            ? array_values(old('segmentos'))
            : (isset($plantilla->segmentos_sph) && count($plantilla->segmentos_sph) > 0
                ? array_values($plantilla->segmentos_sph)
                : ['']);
    @endphp
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.psicologia.maestros.plantillas.index') }}"
                class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-{{ $themeColor }}-600 mb-6 transition-colors group">
                <i class="fas fa-arrow-left text-xs transition-transform group-hover:-translate-x-1"></i>
                <span>Volver a Anexos</span>
            </a>
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                    Editar Anexo
                </h1>
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    Modificación del instrumento clínico: <strong
                        class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">{{ $plantilla->titulo_sph }}</strong>
                </p>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                class="rounded-2xl border shadow-sm overflow-hidden p-6 sm:p-8" x-data="{
                    numCampos: {{ count($segmentosIniciales) }},
                    segmentos: {{ json_encode($segmentosIniciales) }},
                    mostrarMensaje: false,
                    actualizarSegmentos() {
                        let n = parseInt(this.numCampos);
                        if (isNaN(n) || n < 1) n = 1;
                        if (n > 4) n = 4;
                        this.numCampos = n;
                
                        while (this.segmentos.length < this.numCampos) {
                            this.segmentos.push('');
                        }
                        if (this.segmentos.length > this.numCampos) {
                            this.segmentos = this.segmentos.slice(0, this.numCampos);
                        }
                
                        if (n >= 4) {
                            this.mostrarMensaje = true;
                            setTimeout(() => { this.mostrarMensaje = false; }, 3000);
                        }
                    }
                }">

                <form action="{{ route('admin.psicologia.maestros.plantillas.update', $plantilla->id_sph) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div>
                            <label for="titulo"
                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Título de la Sección <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="titulo" id="titulo"
                                value="{{ old('titulo', $plantilla->titulo_sph) }}" required
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-bold transition-all"
                                placeholder="Ej: Prueba de Inteligencia...">
                            @error('titulo')
                                <p class="mt-2 text-xs text-red-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="descripcion_general"
                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Descripción General (Opcional)
                            </label>
                            <input type="text" name="descripcion_general" id="descripcion_general"
                                value="{{ old('descripcion_general', $plantilla->descripcion_general_sph) }}"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                placeholder="Ej: Evaluación cognitiva detallada...">
                            @error('descripcion_general')
                                <p class="mt-2 text-xs text-red-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <hr class="border-gray-100 dark:border-gray-800 my-6">

                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                                <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400">
                                    Campos del Instrumento <span class="text-red-500">*</span>
                                </label>

                                <div class="flex items-center gap-3">
                                    <div x-show="mostrarMensaje" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform translate-x-4"
                                        x-transition:enter-end="opacity-100 transform translate-x-0"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100 transform translate-x-0"
                                        x-transition:leave-end="opacity-0 transform translate-x-4"
                                        style="display: none;"
                                        class="bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 px-3 py-1 rounded-xl flex items-center gap-2">
                                        <i class="fas fa-triangle-exclamation text-xs"></i>
                                        <span class="text-[11px] font-bold">Máximo 4 campos por sección</span>
                                    </div>

                                    <div
                                        class="inline-flex items-center gap-1 p-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/20">
                                        <button type="button"
                                            @click="if(numCampos > 1) { numCampos--; actualizarSegmentos(); }"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:text-{{ $themeColor }}-600 shadow-sm transition-all active:scale-95">
                                            <i class="fas fa-minus text-[10px]"></i>
                                        </button>
                                        <input type="number" min="1" max="4" x-model="numCampos"
                                            @change="actualizarSegmentos()"
                                            class="w-10 h-7 text-center bg-transparent border-none text-xs font-black focus:ring-0 p-0"
                                            style="color: var(--text-main);">
                                        <button type="button"
                                            @click="if(numCampos < 4) { numCampos++; actualizarSegmentos(); }"
                                            :class="{ 'opacity-50 cursor-not-allowed': numCampos >= 4 }"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:text-{{ $themeColor }}-600 shadow-sm transition-all active:scale-95">
                                            <i class="fas fa-plus text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(seg, index) in segmentos" :key="index">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 flex items-center justify-center font-bold text-xs shrink-0 border border-{{ $themeColor }}-100 dark:border-{{ $themeColor }}-900/30"
                                            x-text="index + 1"></div>
                                        <input type="text" x-model="segmentos[index]"
                                            :name="'segmentos[' + index + ']'" required
                                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                            class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                            placeholder="Título del campo / segmento...">
                                    </div>
                                </template>
                            </div>
                            @error('segmentos')
                                <p class="mt-2 text-xs text-red-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                    <div
                        class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.psicologia.maestros.plantillas.index') }}"
                            class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl {{ $btnClass }} text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                            <i class="fas fa-floppy-disk text-xs"></i>
                            <span>Actualizar Anexo</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
