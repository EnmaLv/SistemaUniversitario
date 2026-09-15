<x-app-layout>
    @php
        $pacienteNombreInicial = old('paciente_nombre');
        if (!$pacienteNombreInicial && isset($consulta) && $consulta->paciente) {
            $pacienteNombreInicial = trim(
                ($consulta->paciente->cedula_persona ?? '') .
                    ' ' .
                    ($consulta->paciente->nombre_persona ?? '') .
                    ' ' .
                    ($consulta->paciente->apellido_persona ?? ''),
            );
        }

        $fechaValue = old('fecha');
        if (!$fechaValue) {
            if (isset($consulta->fecha)) {
                $fechaValue =
                    $consulta->fecha instanceof \DateTimeInterface
                        ? $consulta->fecha->format('Y-m-d')
                        : \Carbon\Carbon::parse($consulta->fecha)->format('Y-m-d');
            } else {
                $fechaValue = now()->format('Y-m-d');
            }
        }

        if (!isset($enfermedadesSeleccionadas)) {
            $enfermedadesSeleccionadas = collect();
            if (old('enfermedades')) {
                $oldIds = (array) old('enfermedades');
                $lista = $enfermedades ?? ($todasEnfermedades ?? collect());
                $enfermedadesSeleccionadas = collect($lista)->whereIn('id', $oldIds)->values();

                if ($enfermedadesSeleccionadas->isEmpty()) {
                    $enfermedadesSeleccionadas = collect($oldIds)->map(function ($id) {
                        return ['id' => $id, 'nombre' => 'Enfermedad #' . $id];
                    });
                }
            } elseif (isset($consulta) && $consulta->enfermedades) {
                $enfermedadesSeleccionadas = $consulta->enfermedades->map(function ($enf) {
                    return [
                        'id' => $enf->id,
                        'nombre' => $enf->nombre ?? ($enf->nombre_enfermedad ?? 'Sin nombre'),
                        'codigo' => $enf->codigo ?? ($enf->codigo_cie ?? ''),
                    ];
                });
            }
        }
    @endphp

    <div class="pt-6 pb-16 min-h-[calc(100vh-4rem)]">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10">

            @include('components.alert')

            {{-- Encabezado --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-sky-600 flex items-center justify-center text-white shadow-lg shadow-sky-600/20 shrink-0">
                        <i class="fas fa-stethoscope text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Nueva Consulta Médica
                        </h1>
                        <p class="mt-0.5 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                            Registra los datos clínicos para continuar con la recetación
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all self-start md:self-auto shadow-sm"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver al listado
                </a>
            </div>

            <x-consulta-stepper :step="1" />

            <form
                action="{{ $consulta->exists ? route('admin.salud.movimientos.consultas.update', $consulta) : route('admin.salud.movimientos.consultas.store') }}"
                method="POST">
                @csrf
                @if ($consulta->exists)
                    @method('PUT')
                @endif

                {{-- ============ FILA 1: Identificación (ancho completo) ============ --}}
                <div class="rounded-2xl border shadow-sm  mb-6"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">

                    <div class="flex items-center gap-2.5 px-6 py-4 border-b"
                        style="border-color: var(--border-color);">
                        <div
                            class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                            <i class="fas fa-hospital-user text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                            Identificación
                        </h3>
                    </div>

                    <div class="p-4 grid grid-cols-1 lg:grid-cols-[1.4fr_1fr_1fr] gap-4">
                        {{-- Paciente --}}
                        <div class="relative">
                            <label class="block tracking-wider titulos">
                                Paciente <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>

                                {{-- Se actualizó el placeholder --}}
                                <input type="text" id="paciente_buscar" autocomplete="off"
                                    placeholder="Buscar paciente por nombre o cédula..."
                                    value="{{ $pacienteNombreInicial }}"
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">

                                <input type="hidden" name="id_persona" id="id_persona"
                                    value="{{ old('id_persona', $consulta->id_persona ?? '') }}">
                                <input type="hidden" name="paciente_nombre" id="paciente_nombre"
                                    value="{{ $pacienteNombreInicial }}">
                            </div>

                            <div id="paciente_resultados"
                                class="hidden absolute z-50 left-0 right-0 mt-1.5 max-h-52 overflow-y-auto rounded-xl shadow-xl border py-1"
                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);">
                            </div>

                            @error('id_persona')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Médico --}}
                        <div>
                            <label class="block titulos tracking-wider">
                                Médico <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="medico_id" id="medico_id"
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full pl-3.5 pr-9 py-3 text-sm font-medium rounded-xl border appearance-none focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                                    <option value="" data-consultorio-id=""
                                        data-consultorio-nombre="Seleccione un médico primero..." selected disabled>
                                        Seleccione médico...
                                    </option>
                                    @foreach ($medicos as $medico)
                                        <option value="{{ $medico->id_persona }}"
                                            data-consultorio-id="{{ $medico->consultorio_id }}"
                                            data-consultorio-nombre="{{ $medico->consultorio_nombre }}"
                                            {{ old('medico_id', $consulta->medico_id ?? null) == $medico->id_persona ? 'selected' : '' }}>
                                            {{ $medico->nombre_completo }} - {{ $medico->nombre_rol }}
                                        </option>
                                    @endforeach
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                            </div>
                            @error('medico_id')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror

                            <input type="hidden" name="consultorio_id" id="consultorio_id"
                                value="{{ old('consultorio_id', $consulta->consultorio_id ?? '') }}">
                            <p class="mt-1.5 text-[11px] font-medium text-gray-400 flex items-center gap-1.5 truncate">
                                <i class="fas fa-door-open text-[10px] shrink-0"></i>
                                <span id="consultorio_nombre_display" class="truncate">Seleccione un médico
                                    primero...</span>
                            </p>
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <label class="block titulos tracking-wider">
                                Fecha de la consulta
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-calendar-day absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                <input readonly type="date" name="fecha" value="{{ $fechaValue }}"
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">
                            </div>
                            @error('fecha')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ============ FILA 2: Evaluación clínica ============ --}}
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

                    {{-- Evaluación clínica: ahora es la columna ancha, es el contenido más denso --}}
                    <div class="xl:col-span-7">
                        <div class="rounded-2xl border shadow-sm overflow-hidden h-full"
                            style="background-color: var(--bg-card); border-color: var(--border-color);">
                            <div class="flex items-center gap-2.5 px-6 py-4 border-b"
                                style="border-color: var(--border-color);">
                                <div
                                    class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <i class="fas fa-notes-medical text-xs"></i>
                                </div>
                                <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                                    Evaluación Clínica
                                </h3>
                            </div>

                            <div class="p-6 space-y-5">
                                {{-- Enfermedades asociadas --}}
                                <div x-data="enfermedadesAsyncSelector()" class="relative space-y-3">

                                    {{-- Buscador --}}
                                    <div class="relative group">
                                        <label class="block titulos tracking-widest">
                                            Enfermedades asociadas <span class="text-rose-500">*</span>
                                        </label>

                                        {{-- Enfermedades Seleccionadas --}}
                                        <div x-show="seleccionadas.length > 0" x-cloak
                                            class="flex flex-wrap gap-2 p-2 rounded-xl border border-dashed transition-all"
                                            style="background-color: rgba(0,0,0,0.01); border-color: var(--border-color);">

                                            <template x-for="item in seleccionadas" :key="item.id">
                                                {{-- Badge --}}
                                                <span
                                                    class="inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1 rounded-lg text-xs font-semibold border border-sky-300 dark:border-sky-700 bg-sky-200/30 dark:bg-sky-800/30 shadow-sm text-gray-700 dark:text-gray-200">
                                                    <span x-text="item.nombre"></span>
                                                    <template x-if="item.codigo">
                                                        <span class="text-[10px] font-mono"
                                                            x-text="'[' + item.codigo + ']'"></span>
                                                    </template>

                                                    <button type="button" @click="remover(item.id)"
                                                        class="text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded p-0.5 transition-colors">
                                                        <i class="fas fa-times text-[10px]"></i>
                                                    </button>
                                                    <input type="hidden" name="enfermedades[]"
                                                        :value="item.id">
                                                </span>
                                            </template>
                                        </div>

                                        <div class="relative mt-1.5">
                                            <i
                                                class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs transition-colors group-focus-within:text-sky-500 pointer-events-none"></i>
                                            <input type="text" x-model="search" @input.debounce.300ms="buscar()"
                                                @click.outside="open = false"
                                                placeholder="Buscar enfermedad por nombre o código (mín. 2 letras)..."
                                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                                class="w-full text-xs pl-9 pr-3.5 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder-gray-400">
                                        </div>

                                        {{-- Advertencia de longitud mínima --}}
                                        <template x-if="search.length > 0 && !cumpleRequisitos">
                                            <p
                                                class="text-[10px] text-amber-500/80 mt-1.5 pl-2 font-medium flex items-center gap-1">
                                                <i class="fas fa-exclamation-triangle"></i> Ingresa al menos 2 letras.
                                            </p>
                                        </template>

                                        {{-- Lista de resultados --}}
                                        <div x-show="open && resultados.length > 0" x-cloak
                                            class="absolute z-50 left-0 right-0 mt-1.5 max-h-48 overflow-y-auto rounded-xl shadow-xl border py-1"
                                            style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);">
                                            <template x-for="item in resultados" :key="item.id">
                                                <div @click="seleccionar(item)"
                                                    class="px-3 py-2 text-xs cursor-pointer flex justify-between items-center transition-colors hover:bg-sky-50 dark:hover:bg-sky-950/40">
                                                    <span class="font-medium truncate mr-2"
                                                        x-text="item.nombre"></span>
                                                    <span class="text-[10px] text-gray-400 font-mono shrink-0"
                                                        x-text="item.codigo || ''"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    @error('enfermedades')
                                        <p class="mt-1 text-xs font-semibold text-rose-500 flex items-center gap-1 pl-1">
                                            <i class="fas fa-info-circle text-[11px]"></i> {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Diagnóstico --}}
                                <div>
                                    <label class="block titulos tracking-wider">
                                        Diagnóstico clínico <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="diagnostico" rows="7" placeholder="Descripción detallada del diagnóstico médico..."
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full px-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all resize-none">{{ old('diagnostico', $consulta->diagnostico ?? '') }}</textarea>
                                    @error('diagnostico')
                                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Motivo y observaciones: columna secundaria, sticky --}}
                    <div class="xl:col-span-5">
                        <div class="rounded-2xl border shadow-sm overflow-hidden xl:sticky xl:top-8"
                            style="background-color: var(--bg-card); border-color: var(--border-color);">
                            <div class="flex items-center gap-2.5 px-6 py-4 border-b"
                                style="border-color: var(--border-color);">
                                <div
                                    class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                                    <i class="fas fa-file-medical-alt text-xs"></i>
                                </div>
                                <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                                    Motivo y Observaciones
                                </h3>
                            </div>

                            <div class="p-6 space-y-5">
                                <div>
                                    <label class="block titulos tracking-wider">
                                        Motivo de la consulta <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="motivo" rows="4" placeholder="Ej: Dolor de cabeza y fiebre desde hace 2 días"
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full px-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all resize-none">{{ old('motivo', $consulta->motivo ?? '') }}</textarea>
                                    @error('motivo')
                                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block titulos tracking-wider">
                                        Observaciones <span
                                            class="normal-case font-medium text-gray-400">(opcional)</span>
                                    </label>
                                    <textarea name="observaciones" rows="6" placeholder="Ej: Alergias a medicamentos, antecedentes sintomáticos..."
                                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                        class="w-full px-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all resize-none">{{ old('observaciones', $consulta->observaciones ?? '') }}</textarea>
                                    @error('observaciones')
                                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="mt-6 p-4 sm:p-5 rounded-2xl border shadow-sm flex items-center justify-between gap-3"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-sm shadow-md shadow-sky-600/20 active:scale-95 transition-all">
                        {{ $consulta->exists ? 'Actualizar y Continuar a Recetación' : 'Guardar y Continuar a Recetación' }}
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .titulos {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
    </style>

    <script>
       document.addEventListener('DOMContentLoaded', function() {
            const inputBuscar = document.getElementById('paciente_buscar');
            const inputId = document.getElementById('id_persona');
            const inputNombre = document.getElementById('paciente_nombre');
            const dropdown = document.getElementById('paciente_resultados');

            if (!inputBuscar) return;

            let timeout = null;

            inputBuscar.addEventListener('input', function() {
                const query = this.value.trim();

                if (query.length < 2) {
                    dropdown.classList.add('hidden');
                    dropdown.innerHTML = '';
                    inputId.value = '';
                    if (inputNombre) inputNombre.value = '';
                    return;
                }

                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    fetch(`{{ route('admin.salud.movimientos.consultas.buscar-personas') }}?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            dropdown.innerHTML = '';
                            if (data.length === 0) {
                                dropdown.innerHTML = '<div class="px-4 py-2.5 text-xs text-gray-400">No se encontraron pacientes</div>';
                            } else {
                                data.forEach(item => {
                                    const div = document.createElement('div');
                                    div.className = 'px-4 py-2.5 text-xs font-semibold text-inherit hover:bg-sky-50 dark:hover:bg-sky-950/40 cursor-pointer transition-colors';

                                    div.textContent = item.nombre;

                                    div.onclick = function() {
                                        inputBuscar.value = item.nombre;
                                        // Aseguramos que tome el id_persona correcto enviado desde el backend
                                        inputId.value = item.id_persona ?? item.id; 
                                        if (inputNombre) inputNombre.value = item.nombre;
                                        dropdown.classList.add('hidden');
                                    };
                                    dropdown.appendChild(div);
                                });
                            }
                            dropdown.classList.remove('hidden');
                        })
                        .catch(err => console.error('Error al buscar pacientes:', err));
                }, 300);
            });

            // Cerrar el dropdown al hacer clic afuera
            document.addEventListener('click', function(e) {
                if (!inputBuscar.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        function enfermedadesAsyncSelector() {
            return {
                search: '',
                open: false,
                resultados: [],
                seleccionadas: @json($enfermedadesSeleccionadas ?? []),

                get cumpleRequisitos() {
                    return this.search.trim().length >= 2;
                },

                async buscar() {
                    if (!this.cumpleRequisitos) {
                        this.resultados = [];
                        this.open = false;
                        return;
                    }

                    try {
                        const response = await fetch(
                            `{{ route('admin.salud.movimientos.consultas.buscar-enfermedades') }}?q=${encodeURIComponent(this.search)}`
                        );
                        const data = await response.json();

                        this.resultados = data.filter(e => !this.seleccionadas.some(s => String(s.id) === String(e
                            .id)));
                        this.open = this.resultados.length > 0;
                    } catch (error) {
                        console.error("Error al buscar enfermedades:", error);
                    }
                },

                seleccionar(item) {
                    if (!this.seleccionadas.some(s => String(s.id) === String(item.id))) {
                        this.seleccionadas.push(item);
                    }
                    this.search = '';
                    this.resultados = [];
                    this.open = false;
                },

                remover(id) {
                    this.seleccionadas = this.seleccionadas.filter(item => String(item.id) !== String(id));
                },

                init() {
                    if (!Array.isArray(this.seleccionadas)) {
                        this.seleccionadas = Object.values(this.seleccionadas);
                    }
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selectMedico = document.getElementById('medico_id');
            const inputConsultorioId = document.getElementById('consultorio_id');
            const inputConsultorioNombre = document.getElementById('consultorio_nombre_display');

            function actualizarConsultorio() {
                const option = selectMedico.options[selectMedico.selectedIndex];
                if (!option) return;

                const consultorioId = option.getAttribute('data-consultorio-id') || '';
                const consultorioNombre = option.getAttribute('data-consultorio-nombre') ||
                    'Seleccione un médico primero...';

                inputConsultorioId.value = consultorioId;
                if (inputConsultorioNombre) inputConsultorioNombre.textContent = consultorioNombre;
            }

            selectMedico.addEventListener('change', actualizarConsultorio);
            actualizarConsultorio();
        });
    </script>
</x-app-layout>
