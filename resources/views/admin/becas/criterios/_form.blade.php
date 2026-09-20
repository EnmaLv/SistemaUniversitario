@php
    $modelo = $modelo ?? null;
@endphp

<div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.alert')

        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                {{ $titulo }}
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ $subtitulo }}
            </p>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-8">

            <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">

                    <div class="md:col-span-6">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Beneficio
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-award text-sm"></i>
                            </span>
                            <select name="id_be_beneficio" id="id_be_beneficio"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                <option value="" disabled {{ old('id_be_beneficio', $modelo?->id_be_beneficio) ? '' : 'selected' }}>
                                    Seleccione un beneficio...
                                </option>
                                @foreach ($beneficios as $ben)
                                    <option value="{{ $ben->id }}"
                                        @selected(old('id_be_beneficio', $modelo?->id_be_beneficio) == $ben->id)>
                                        {{ $ben->nombre_beneficio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('id_be_beneficio')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-6">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Pregunta a evaluar
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-question text-sm"></i>
                            </span>
                            <select name="id_pregunta" id="id_pregunta"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                <option value="" disabled {{ old('id_pregunta', $modelo?->id_pregunta) ? '' : 'selected' }}>
                                    Seleccione una pregunta...
                                </option>
                                @foreach ($preguntas as $p)
                                    <option value="{{ $p->id }}"
                                        data-beneficio="{{ $p->id_be_beneficio }}"
                                        @selected(old('id_pregunta', $modelo?->id_pregunta) == $p->id)>
                                        {{ $p->etiqueta }} · {{ $p->codigo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p id="avisoSinPreguntas"
                            class="mt-1 text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 hidden">
                            Este beneficio no tiene preguntas activas
                        </p>
                        @error('id_pregunta')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">

                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Tipo de criterio
                        </label>
                        <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl border"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                            <span id="labelEliminatoria" class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ old('es_eliminatoria', $modelo?->es_eliminatoria ?? false) ? 'Eliminatorio' : 'Informativo' }}
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="es_eliminatoria" value="0">
                                <input type="checkbox" name="es_eliminatoria" value="1" id="es_eliminatoria"
                                    class="sr-only peer"
                                    {{ old('es_eliminatoria', $modelo?->es_eliminatoria ?? false) ? 'checked' : '' }}>
                                <div class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-red-700 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                        </div>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Eliminatorio rechaza · Informativo solo puntúa
                        </p>
                        @error('es_eliminatoria')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Operador
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-code-branch text-sm"></i>
                            </span>
                            <select name="operador" id="operador"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none font-mono">
                                <option value="" {{ old('operador', $modelo?->operador) ? '' : 'selected' }}>
                                    Sin regla
                                </option>
                                @foreach ($operadores as $op => $label)
                                    <option value="{{ $op }}" @selected(old('operador', $modelo?->operador) === $op)>
                                        {{ $op }} — {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('operador')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Valor esperado
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-equals text-sm"></i>
                            </span>
                            <input type="text" name="valor_esperado" id="valor_esperado"
                                value="{{ old('valor_esperado', $modelo?->valor_esperado) }}"
                                placeholder="Ej: 12"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none font-mono">
                        </div>
                        <p id="ayudaValor" class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Depende del operador seleccionado
                        </p>
                        @error('valor_esperado')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Peso (opcional)
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-weight-hanging text-sm"></i>
                            </span>
                            <input type="number" name="peso" step="0.01" min="0"
                                value="{{ old('peso', $modelo?->peso ?? 0) }}"
                                placeholder="0"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Solo si usas scoring
                        </p>
                        @error('peso')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t flex items-center justify-end gap-3"
                    style="border-color: var(--border-color);">
                    <a href="{{ route('admin.becas.criterios.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                        <i class="fas fa-save text-xs"></i> {{ $modelo ? 'Actualizar' : 'Guardar' }} criterio
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    function filtrarPreguntas() {
        const beneficioId   = document.getElementById('id_be_beneficio').value;
        const selectPreg    = document.getElementById('id_pregunta');
        const aviso         = document.getElementById('avisoSinPreguntas');

        const seleccionActual = selectPreg.value;
        let visibleActual = false;
        let visibles = 0;

        Array.from(selectPreg.options).forEach(opt => {
            if (!opt.value) return;
            const match = opt.dataset.beneficio === beneficioId;
            opt.hidden   = !match;
            opt.disabled = !match;
            if (match) visibles++;
            if (match && opt.value === seleccionActual) visibleActual = true;
        });

        if (!visibleActual) {
            selectPreg.value = '';
        }

        aviso.classList.toggle('hidden', visibles > 0 || !beneficioId);
    }

    function actualizarAyudaOperador() {
        const op = document.getElementById('operador').value;
        const ayuda = document.getElementById('ayudaValor');
        const input = document.getElementById('valor_esperado');

        const map = {
            'between': 'Formato: min,max · Ej: 12,20',
            'in':      'Lista separada por comas · Ej: 1,2,3',
            'not_in':  'Lista separada por comas · Ej: 1,2,3',
        };

        if (map[op]) {
            ayuda.textContent = map[op];
            input.placeholder = op === 'between' ? '12,20' : '1,2,3';
        } else {
            ayuda.textContent = 'Depende del operador seleccionado';
            input.placeholder = 'Ej: 12';
        }
    }

    function actualizarLabelEliminatoria() {
        const checked = document.getElementById('es_eliminatoria').checked;
        const label = document.getElementById('labelEliminatoria');
        label.textContent = checked ? 'Eliminatorio' : 'Informativo';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('id_be_beneficio').addEventListener('change', filtrarPreguntas);
        document.getElementById('operador').addEventListener('change', actualizarAyudaOperador);
        document.getElementById('es_eliminatoria').addEventListener('change', actualizarLabelEliminatoria);

        filtrarPreguntas();
        actualizarAyudaOperador();
        actualizarLabelEliminatoria();
    });
</script>