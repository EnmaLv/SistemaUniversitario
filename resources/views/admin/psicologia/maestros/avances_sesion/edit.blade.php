@php
    $moduloActivo = strtolower(session('modulo_activo', 'general'));
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'psicología', 'mental']);

    $themeColor = $esPsicologia ? 'indigo' : 'red';
    $btnClass = $esPsicologia ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-red-600 hover:bg-red-700';
    $focusRingClass = $esPsicologia
        ? 'focus:ring-indigo-500/20 focus:border-indigo-500'
        : 'focus:ring-red-500/20 focus:border-red-500';
@endphp

<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <a href="{{ route('admin.psicologia.maestros.avances_sesion.index') }}"
                class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-{{ $themeColor }}-600 mb-6 transition-colors group">
                <i class="fas fa-arrow-left text-xs transition-transform group-hover:-translate-x-1"></i>
                <span>Volver a Avances de Sesión</span>
            </a>

            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                    Editar Avance de Sesión
                </h1>
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    Modifica los parámetros de este avance para el módulo de <strong
                        class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">Salud Mental</strong>.
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

            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                class="rounded-2xl border shadow-sm overflow-hidden p-6 sm:p-8">

                <form action="{{ route('admin.psicologia.maestros.avances_sesion.update', $avance->id_avs) }}"
                    method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">

                        <div>
                            <label for="nombre"
                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Nombre del Avance <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" id="nombre"
                                value="{{ old('nombre', $avance->nombre_avs) }}"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                required>
                            @error('nombre')
                                <p class="mt-2 text-xs text-red-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="valor"
                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Valor Numérico (1 - 10) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="valor" id="valor" min="1" max="10"
                                value="{{ old('valor', $avance->valor_avs) }}"
                                oninput="if(this.value > 10) this.value = 10; if(this.value < 0) this.value = 0; if(this.value.length > 2) this.value = this.value.slice(0, 2);"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all"
                                required>
                            @error('valor')
                                <p class="mt-2 text-xs text-red-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                            <p
                                class="mt-2.5 text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center gap-1.5">
                                <i
                                    class="fas fa-info-circle text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400"></i>
                                <span>Este valor numérico se utiliza para graficar la evolución del paciente.</span>
                            </p>
                        </div>

                        <div>
                            <label for="descripcion"
                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Descripción (Opcional)
                            </label>
                            <textarea name="descripcion" id="descripcion" rows="4"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full p-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all resize-none"
                                placeholder="Detalles o criterios para asignar este avance...">{{ old('descripcion', $avance->descripcion_avs) }}</textarea>
                            @error('descripcion')
                                <p class="mt-2 text-xs text-red-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                    <div
                        class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.psicologia.maestros.avances_sesion.index') }}"
                            class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl {{ $btnClass }} text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                            <i class="fas fa-pen-to-square text-xs"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
