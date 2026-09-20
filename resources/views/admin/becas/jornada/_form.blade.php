@php
    $modelo = $modelo ?? null;

    $criteriosActuales = $criteriosActuales ?? [];
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

        <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-6">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">

                    <div class="md:col-span-8">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Nombre de la jornada
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-bullhorn text-sm"></i>
                            </span>
                            <input type="text" name="nombre_jornada"
                                value="{{ old('nombre_jornada', $modelo?->nombre_jornada) }}"
                                placeholder="Ej: Convocatoria Comedor"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            El código del lapso se agrega automáticamente
                        </p>
                        @error('nombre_jornada')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Estado
                        </label>
                        <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl border"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Jornada activa
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="activa" value="0">
                                <input type="checkbox" name="activa" value="1" class="sr-only peer"
                                    {{ old('activa', $modelo?->activa ?? true) ? 'checked' : '' }}>
                                <div class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-600 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                        </div>
                        @error('activa')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Descripción
                    </label>
                    <textarea name="descripcion_jornada" rows="2"
                        placeholder="Descripción breve de la convocatoria"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">{{ old('descripcion_jornada', $modelo?->descripcion_jornada) }}</textarea>
                    @error('descripcion_jornada')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

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
                            <select name="beneficio_id" id="beneficio_id"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                <option value="" disabled {{ old('beneficio_id', $modelo?->beneficio_id) ? '' : 'selected' }}>
                                    Seleccione un beneficio...
                                </option>
                                @foreach ($beneficios as $ben)
                                    <option value="{{ $ben->id }}"
                                        @selected(old('beneficio_id', $modelo?->beneficio_id) == $ben->id)>
                                        {{ $ben->nombre_beneficio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('beneficio_id')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-6">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Lapso académico
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-calendar-alt text-sm"></i>
                            </span>
                            <select name="lapsos_id"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                <option value="" disabled {{ old('lapsos_id', $modelo?->lapsos_id) ? '' : 'selected' }}>
                                    Seleccione un lapso...
                                </option>
                                @foreach ($lapsos as $lap)
                                    <option value="{{ $lap->id }}"
                                        @selected(old('lapsos_id', $modelo?->lapsos_id) == $lap->id)>
                                        {{ $lap->codigo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('lapsos_id')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">

                    <div class="md:col-span-3">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Inicio solicitud
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-calendar-day text-sm"></i>
                            </span>
                            <input type="date" name="fecha_inicio_solicitud"
                                value="{{ old('fecha_inicio_solicitud', $modelo?->fecha_inicio_solicitud?->format('Y-m-d')) }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('fecha_inicio_solicitud')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Fin solicitud
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-calendar-check text-sm"></i>
                            </span>
                            <input type="date" name="fecha_fin_solicitud"
                                value="{{ old('fecha_fin_solicitud', $modelo?->fecha_fin_solicitud?->format('Y-m-d')) }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('fecha_fin_solicitud')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Cupos máximos
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-users text-sm"></i>
                            </span>
                            <input type="number" name="cupos_maximos" min="0"
                                value="{{ old('cupos_maximos', $modelo?->cupos_maximos ?? 0) }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('cupos_maximos')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Cupos asignados
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden opacity-60"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-user-check text-sm"></i>
                            </span>
                            <input type="text" disabled
                                value="{{ $modelo?->cupos_asignados ?? 0 }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN CRITERIOS --}}
            <div id="seccionCriterios" style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-6 hidden">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-lg font-extrabold tracking-tight" style="color: var(--text-main);">
                            Criterios de evaluación
                        </h2>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mt-0.5">
                            Heredados del beneficio · Editables para esta jornada
                        </p>
                    </div>

                    <button type="button" id="btnRecargarPlantilla"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-sky-200 dark:border-sky-900 text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/30 hover:bg-sky-100 font-bold text-xs active:scale-95 transition-all">
                        <i class="fas fa-rotate text-[10px]"></i> Recargar de plantilla
                    </button>
                </div>

                <div id="criteriosContainer"
                    class="rounded-2xl border p-3 overflow-x-auto"
                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                    <p id="sinCriterios" class="text-center text-xs font-bold uppercase tracking-wider text-gray-400 py-6">
                        Este beneficio no tiene criterios en la plantilla
                    </p>
                </div>
            </div>

            <div id="avisoBeneficio" class="rounded-2xl border p-6 text-center mb-6"
                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                <i class="fas fa-arrow-up text-2xl text-gray-300 dark:text-gray-700 mb-2 block"></i>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">
                    Selecciona un beneficio para cargar sus criterios
                </p>
            </div>

            <div class="mt-8 pt-6 border-t flex items-center justify-end gap-3"
                style="border-color: var(--border-color);">
                <a href="{{ route('admin.becas.jornada.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    Cancelar
                </a>

                <button type="submit"
                    class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                    <i class="fas fa-save text-xs"></i> {{ $modelo ? 'Actualizar' : 'Guardar' }} jornada
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.CRITERIOS_POR_BENEFICIO = @json($criteriosPorBeneficio);
    window.CRITERIOS_ACTUALES     = @json($criteriosActuales);
    window.OPERADORES_LABEL = {
        '=': 'Igual a', '!=': 'Distinto de',
        '>': 'Mayor que', '>=': 'Mayor o igual',
        '<': 'Menor que', '<=': 'Menor o igual',
        'in': 'En lista', 'not_in': 'No en lista', 'between': 'Entre'
    };
</script>

<script>
    let criteriosCount = 0;

    function buildCriterioRow(c) {
        const idx = criteriosCount++;
        const row = document.createElement('div');
        row.className = 'criterio-row grid grid-cols-12 gap-3 mb-3 items-center';
        row.dataset.idPregunta = c.id_pregunta;

        const opcionesOperador = Object.entries(window.OPERADORES_LABEL)
            .map(([val, label]) => `<option value="${val}" ${c.operador === val ? 'selected' : ''}>${val} — ${label}</option>`)
            .join('');

        row.innerHTML = `
            <input type="hidden" name="criterios[${idx}][id_pregunta]" value="${c.id_pregunta}">

            <div class="col-span-12 lg:col-span-4">
                <div class="flex flex-col">
                    <span class="text-sm font-bold" style="color: var(--text-main);">${c.pregunta ?? '—'}</span>
                    <span class="inline-flex items-center self-start mt-1 px-2 py-0.5 text-[10px] font-black rounded-md text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-800">
                        ${c.codigo ?? ''}
                    </span>
                </div>
            </div>

            <div class="col-span-6 lg:col-span-2">
                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500"
                    style="border-color: var(--border-color);">
                    <select name="criterios[${idx}][operador]"
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-2 py-2 text-xs font-medium border-none focus:ring-0 focus:outline-none font-mono">
                        <option value="" ${!c.operador ? 'selected' : ''}>Sin regla</option>
                        ${opcionesOperador}
                    </select>
                </div>
            </div>

            <div class="col-span-6 lg:col-span-2">
                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500"
                    style="border-color: var(--border-color);">
                    <input type="text" name="criterios[${idx}][valor_esperado]"
                        value="${c.valor_esperado ?? ''}"
                        placeholder="Ej: 12"
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-2 py-2 text-xs font-medium border-none focus:ring-0 focus:outline-none font-mono">
                </div>
            </div>

            <div class="col-span-6 lg:col-span-2">
                <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl border"
                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                    <span class="text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Elim.
                    </span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="criterios[${idx}][es_eliminatoria]" value="0">
                        <input type="checkbox" name="criterios[${idx}][es_eliminatoria]" value="1"
                            class="sr-only peer" ${c.es_eliminatoria ? 'checked' : ''}>
                        <div class="w-9 h-5 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-red-700 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                    </label>
                </div>
            </div>

            <div class="col-span-4 lg:col-span-1">
                <input type="number" name="criterios[${idx}][peso]" step="0.01" min="0"
                    value="${c.peso ?? 0}"
                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                    class="w-full px-2 py-2 rounded-xl border text-xs font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div class="col-span-2 lg:col-span-1 flex justify-end">
                <button type="button" onclick="removeCriterio(this)"
                    class="w-8 h-8 flex items-center justify-center rounded-xl border border-rose-200 dark:border-rose-900 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 active:scale-95 transition-all">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        `;
        return row;
    }

    function renderCriterios(lista) {
        const container = document.getElementById('criteriosContainer');
        container.querySelectorAll('.criterio-row').forEach(el => el.remove());

        const sin = document.getElementById('sinCriterios');

        if (!lista || lista.length === 0) {
            if (sin) sin.style.display = 'block';
            return;
        }

        if (sin) sin.style.display = 'none';

        lista.forEach(c => container.appendChild(buildCriterioRow(c)));
    }

    function removeCriterio(btn) {
        btn.closest('.criterio-row').remove();
        const container = document.getElementById('criteriosContainer');
        const sin = document.getElementById('sinCriterios');
        if (sin && container.querySelectorAll('.criterio-row').length === 0) {
            sin.style.display = 'block';
        }
    }

    function cargarCriteriosDelBeneficio() {
        const beneficioId = document.getElementById('beneficio_id').value;
        const secCriterios = document.getElementById('seccionCriterios');
        const aviso = document.getElementById('avisoBeneficio');

        if (!beneficioId) {
            secCriterios.classList.add('hidden');
            aviso.classList.remove('hidden');
            renderCriterios([]);
            return;
        }

        secCriterios.classList.remove('hidden');
        aviso.classList.add('hidden');

        const lista = window.CRITERIOS_POR_BENEFICIO[beneficioId] || [];
        renderCriterios(lista);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selectBen = document.getElementById('beneficio_id');

        selectBen.addEventListener('change', cargarCriteriosDelBeneficio);

        document.getElementById('btnRecargarPlantilla').addEventListener('click', function () {
            const beneficioId = document.getElementById('beneficio_id').value;
            if (!beneficioId) return;
            const lista = window.CRITERIOS_POR_BENEFICIO[beneficioId] || [];
            renderCriterios(lista);
        });

        // Estado inicial
        const beneficioActual = selectBen.value;

        if (window.CRITERIOS_ACTUALES && window.CRITERIOS_ACTUALES.length > 0) {
            document.getElementById('seccionCriterios').classList.remove('hidden');
            document.getElementById('avisoBeneficio').classList.add('hidden');
            renderCriterios(window.CRITERIOS_ACTUALES);
        } else if (beneficioActual) {
            cargarCriteriosDelBeneficio();
        }
    });
</script>