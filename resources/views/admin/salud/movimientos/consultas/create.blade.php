<x-app-layout>
    @php
        $esEdicion = $consulta->exists;

        // ── Paciente ────────────────────────────────────────────
        $pacienteNombreInicial = old('paciente_nombre');
        if (!$pacienteNombreInicial && $consulta->paciente) {
            $pacienteNombreInicial = trim(
                ($consulta->paciente->cedula_persona ?? '') .
                    ' ' .
                    ($consulta->paciente->nombre_persona ?? '') .
                    ' ' .
                    ($consulta->paciente->apellido_persona ?? ''),
            );
        }

        // ── Fecha ───────────────────────────────────────────────
        $fechaValue = old('fecha');
        if (!$fechaValue) {
            $fechaValue = $consulta->fecha
                ? \Carbon\Carbon::parse($consulta->fecha)->format('Y-m-d')
                : now()->format('Y-m-d');
        }
        $fechaCarbon = \Carbon\Carbon::parse($fechaValue);
        $fechaTexto = $fechaCarbon->format('d/m/Y') . ($fechaCarbon->isToday() ? ' (hoy)' : '');

        // ── Enfermedades seleccionadas ──────────────────────────
        if (!isset($enfermedadesSeleccionadas)) {
            $enfermedadesSeleccionadas = collect();
            if (old('enfermedades')) {
                $oldIds = (array) old('enfermedades');
                $lista = $enfermedades ?? ($todasEnfermedades ?? collect());
                $enfermedadesSeleccionadas = collect($lista)->whereIn('id', $oldIds)->values();

                if ($enfermedadesSeleccionadas->isEmpty()) {
                    $enfermedadesSeleccionadas = collect($oldIds)->map(
                        fn($id) => [
                            'id' => $id,
                            'nombre' => 'Enfermedad #' . $id,
                        ],
                    );
                }
            } elseif ($consulta->enfermedades) {
                $enfermedadesSeleccionadas = $consulta->enfermedades->map(
                    fn($enf) => [
                        'id' => $enf->id,
                        'nombre' => $enf->nombre ?? ($enf->nombre_enfermedad ?? 'Sin nombre'),
                        'codigo' => $enf->codigo ?? ($enf->codigo_cie ?? ''),
                    ],
                );
            }
        }

        // ── Configuración para Alpine ───────────────────────────
        $configForm = [
            'pacienteId' => (string) old('id_persona', $consulta->id_persona ?? ''),
            'pacienteNombre' => (string) $pacienteNombreInicial,
            'medicoId' => (string) old('medico_id', $consulta->medico_id ?? ''),
            'motivo' => (string) old('motivo', $consulta->motivo ?? ''),
            'diagnostico' => (string) old('diagnostico', $consulta->diagnostico ?? ''),
            'enfermedades' => collect($enfermedadesSeleccionadas)->values(),
            'medicos' => collect($medicos)
                ->map(
                    fn($m) => [
                        'id' => (string) $m->id_persona,
                        'nombre' => $m->nombre_completo,
                        'consultorio_id' => (string) ($m->consultorio_id ?? ''),
                        'consultorio_nombre' => $m->consultorio_nombre ?? '',
                    ],
                )
                ->values(),
            'rutas' => [
                'personas' => route('admin.salud.movimientos.consultas.buscar-personas'),
                'enfermedades' => route('admin.salud.movimientos.consultas.buscar-enfermedades'),
            ],
        ];
    @endphp

    @include('admin.salud.movimientos.consultas.partials.ui')

    <div class="pt-5 pb-24 min-h-[calc(100vh-4rem)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <form x-data="consultaForm(@js($configForm))"
                action="{{ $esEdicion ? route('admin.salud.movimientos.consultas.update', $consulta) : route('admin.salud.movimientos.consultas.store') }}"
                method="POST" class="rd-prevent-double-submit">
                @csrf
                @if ($esEdicion)
                    @method('PUT')
                @endif

                {{-- ── BARRA SUPERIOR: Volver + Progreso del Formulario ── --}}
                <div class="flex items-center justify-between gap-3 mb-3">
                    <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-arrow-left text-[10px]" aria-hidden="true"></i>
                        <span>Consultas</span>
                    </a>

                    {{-- Progreso desplegable --}}
                    <div x-data="{ abierto: false }" class="relative inline-block">
                        <button type="button" @click="abierto = !abierto"
                            class="cx-card !py-1.5 !px-3.5 flex items-center gap-2.5 shadow-sm hover:shadow transition-shadow !rounded-full text-sm"
                            :aria-expanded="abierto" aria-label="Ver progreso del formulario">
                            <span
                                class="relative inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-bold tabular-nums"
                                :class="completos === checklist.length ?
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' :
                                    'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300'"
                                x-text="`${completos}/${checklist.length}`"></span>
                            <span class="text-sm font-semibold cx-text"
                                x-text="completos === checklist.length ? '¡Listo para continuar!' : 'Progreso'"></span>
                            <i class="fas text-[10px] cx-faint" :class="abierto ? 'fa-chevron-up' : 'fa-chevron-down'"
                                aria-hidden="true"></i>
                        </button>

                        {{-- Panel desplegable del progreso --}}
                        <div x-show="abierto" @click.outside="abierto = false" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="absolute right-0 top-full mt-2 cx-card p-4 w-72 z-50 shadow-xl">
                            <div class="flex items-baseline justify-between mb-2">
                                <h3 class="text-sm font-semibold cx-text">Para continuar</h3>
                                <span class="text-xs font-semibold tabular-nums"
                                    :class="completos === checklist.length ? 'text-emerald-600 dark:text-emerald-400' :
                                        'cx-muted'"
                                    x-text="`${completos} de ${checklist.length}`"></span>
                            </div>

                            <div class="h-1 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden mb-3">
                                <div class="h-full rounded-full transition-[width] duration-300"
                                    :class="completos === checklist.length ? 'bg-emerald-500' : 'bg-sky-600'"
                                    :style="`width: ${(completos / checklist.length) * 100}%`"></div>
                            </div>

                            <ul class="space-y-2">
                                <template x-for="item in checklist" :key="item.label">
                                    <li class="flex items-center gap-2 text-sm">
                                        <span
                                            class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 border"
                                            :class="item.ok ? 'bg-emerald-500 border-emerald-500 text-white' :
                                                'border-gray-300 dark:border-gray-600'">
                                            <i x-show="item.ok" class="fas fa-check text-[8px]" aria-hidden="true"></i>
                                        </span>
                                        <span :class="item.ok ? 'cx-muted line-through decoration-1' : 'cx-text'"
                                            x-text="item.label"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- Encabezado principal --}}
                <x-consulta-encabezado :titulo="$esEdicion ? 'Editar consulta' : 'Nueva consulta'"
                    descripcion="Registra la atención para continuar con la receta." :step="1"
                    :consulta="$consulta" />

                {{-- Campos ocultos --}}
                <input type="hidden" name="id_persona" id="id_persona" :value="pacienteId">
                <input type="hidden" name="paciente_nombre" id="paciente_nombre" :value="pacienteNombre">
                <input type="hidden" name="consultorio_id" id="consultorio_id" :value="consultorioId">
                <input type="hidden" name="fecha" value="{{ $fechaValue }}">

                <div class="cx-card">

                    {{-- ── Paciente y médico ── --}}
                    <section class="p-4 sm:p-5" aria-labelledby="sec-atencion">
                        <h2 id="sec-atencion" class="cx-title">¿A quién se atiende?</h2>
                        <p class="text-xs cx-muted mt-0.5 mb-3">Busca al paciente y elige el médico tratante.</p>

                        <div class="grid grid-cols-1 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] gap-3">

                            {{-- Paciente --}}
                            <div>
                                <label for="paciente_buscar" class="cx-label">
                                    Paciente<span class="cx-req" aria-hidden="true">*</span>
                                </label>

                                <div x-show="pacienteId" x-cloak
                                    class="flex items-center gap-2.5 p-2 rounded-lg border border-sky-200 dark:border-sky-900 bg-sky-50/70 dark:bg-sky-950/30">
                                    <span
                                        class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center text-xs font-bold shrink-0"
                                        x-text="iniciales(pacienteNombre)" aria-hidden="true"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold cx-text truncate leading-tight"
                                            x-text="pacienteNombre"></p>
                                        <p class="text-[11px] cx-muted leading-tight">Paciente seleccionado</p>
                                    </div>
                                    <button type="button" @click="cambiarPaciente()"
                                        class="cx-btn cx-btn-ghost !py-1 !px-2.5 !text-[11px]">
                                        Cambiar
                                    </button>
                                </div>

                                <div x-show="!pacienteId" class="relative" @click.outside="pacienteAbierto = false">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[11px] cx-faint pointer-events-none"
                                        aria-hidden="true"></i>
                                    <input type="text" id="paciente_buscar" x-ref="pacienteInput" autocomplete="off"
                                        role="combobox" aria-autocomplete="list" aria-controls="paciente_resultados"
                                        :aria-expanded="pacienteAbierto" x-model="pacienteQuery"
                                        @input="buscarPaciente()"
                                        @focus="pacienteResultados.length && (pacienteAbierto = true)"
                                        @keydown="tecla($event, 'paciente')" placeholder="Nombre, apellido o cédula"
                                        class="cx-input !pl-8 @error('id_persona') is-invalid @enderror">

                                    <span x-show="pacienteCargando" x-cloak
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] cx-faint">
                                        <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                                        <span class="sr-only">Buscando</span>
                                    </span>

                                    <div id="paciente_resultados" x-show="pacienteAbierto" x-cloak
                                        class="cx-dropdown" role="listbox">
                                        <template x-for="(item, i) in pacienteResultados"
                                            :key="item.id_persona ?? item.id">
                                            <button type="button" class="cx-option" role="option"
                                                :data-activo="i === pacienteActivo"
                                                :aria-selected="i === pacienteActivo" @mouseenter="pacienteActivo = i"
                                                @click="elegirPaciente(item)">
                                                <span class="font-medium truncate"
                                                    x-text="(item.nombre || '').trim()"></span>
                                            </button>
                                        </template>
                                        <p x-show="!pacienteCargando && !pacienteError && pacienteResultados.length === 0"
                                            class="px-3 py-2 text-[11px] cx-muted">
                                            No hay pacientes que coincidan con "<span x-text="pacienteQuery"></span>".
                                        </p>
                                        <p x-show="pacienteError" class="px-3 py-2 text-[11px] text-rose-600">
                                            No se pudo buscar. Revisa la conexión e intenta de nuevo.
                                        </p>
                                    </div>
                                </div>
                                <p x-show="!pacienteId && pacienteQuery.trim().length === 1" x-cloak class="cx-hint">
                                    Escribe al menos 2 caracteres.
                                </p>

                                @error('id_persona')
                                    <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                            aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Médico --}}
                            <div>
                                <label for="medico_id" class="cx-label">
                                    Médico tratante<span class="cx-req" aria-hidden="true">*</span>
                                </label>
                                <select name="medico_id" id="medico_id" x-model="medicoId"
                                    @change="sincronizarConsultorio()"
                                    class="cx-input @error('medico_id') is-invalid @enderror">
                                    <option value="" disabled>Selecciona un médico</option>
                                    @foreach ($medicos as $medico)
                                        <option value="{{ $medico->id_persona }}"
                                            {{ old('medico_id', $consulta->medico_id ?? null) == $medico->id_persona ? 'selected' : '' }}>
                                            {{ $medico->nombre_completo }} ({{ $medico->nombre_rol }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('medico_id')
                                    <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                            aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                                @error('consultorio_id')
                                    <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                            aria-hidden="true"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- ── Motivo + Evaluación ── --}}
                    <div class="cx-divider grid grid-cols-1 lg:grid-cols-2">

                        {{-- Motivo --}}
                        <section class="p-4 sm:p-5 lg:border-r lg:border-gray-200 dark:lg:border-gray-800"
                            aria-labelledby="sec-motivo">
                            <h2 id="sec-motivo" class="cx-title">¿Por qué consulta?</h2>
                            <p class="text-xs cx-muted mt-0.5 mb-3">Motivo del paciente y antecedentes.</p>

                            <div class="space-y-3">
                                <div>
                                    <div class="flex items-baseline justify-between gap-3">
                                        <label for="motivo" class="cx-label">
                                            Motivo<span class="cx-req" aria-hidden="true">*</span>
                                        </label>
                                        <span class="text-[10px] tabular-nums cx-faint"
                                            x-text="`${motivo.length}/1000`"></span>
                                    </div>
                                    <textarea id="motivo" name="motivo" rows="2" maxlength="1000" x-model="motivo"
                                        placeholder="Ej.: dolor de cabeza y fiebre desde hace 2 días"
                                        class="cx-input @error('motivo') is-invalid @enderror"></textarea>
                                    @error('motivo')
                                        <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                                aria-hidden="true"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="observaciones" class="cx-label">
                                        Observaciones<span class="cx-opt">(opcional)</span>
                                    </label>
                                    <textarea id="observaciones" name="observaciones" rows="2" maxlength="2000"
                                        placeholder="Ej.: alérgico a la penicilina, hipertenso"
                                        class="cx-input @error('observaciones') is-invalid @enderror">{{ old('observaciones', $consulta->observaciones ?? '') }}</textarea>
                                    <p class="cx-hint">Alergias o datos que el farmaceuta deba conocer.</p>
                                    @error('observaciones')
                                        <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                                aria-hidden="true"></i>{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        {{-- Evaluación --}}
                        <section class="p-4 sm:p-5" aria-labelledby="sec-evaluacion">
                            <h2 id="sec-evaluacion" class="cx-title">¿Qué tiene?</h2>
                            <p class="text-xs cx-muted mt-0.5 mb-3">Diagnóstico y enfermedades para estadísticas.</p>

                            <div class="space-y-3">
                                <div>
                                    <div class="flex items-baseline justify-between gap-3">
                                        <label for="diagnostico" class="cx-label">
                                            Diagnóstico clínico<span class="cx-req" aria-hidden="true">*</span>
                                        </label>
                                        <span class="text-[10px] tabular-nums cx-faint"
                                            x-text="`${diagnostico.length}/2000`"></span>
                                    </div>
                                    <textarea id="diagnostico" name="diagnostico" rows="3" maxlength="2000" x-model="diagnostico"
                                        placeholder="Descripción del diagnóstico médico" class="cx-input @error('diagnostico') is-invalid @enderror"></textarea>
                                    @error('diagnostico')
                                        <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                                aria-hidden="true"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Enfermedades --}}
                                <div>
                                    <label for="enfermedad_buscar" class="cx-label">
                                        Enfermedades asociadas<span class="cx-req" aria-hidden="true">*</span>
                                    </label>

                                    <div class="relative" @click.outside="enfAbierto = false">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[11px] cx-faint pointer-events-none"
                                            aria-hidden="true"></i>
                                        <input type="text" id="enfermedad_buscar" autocomplete="off"
                                            role="combobox" aria-autocomplete="list"
                                            aria-controls="enfermedad_resultados" :aria-expanded="enfAbierto"
                                            x-model="enfQuery" @input="buscarEnfermedades()"
                                            @focus="enfResultados.length && (enfAbierto = true)"
                                            @keydown="tecla($event, 'enf')" placeholder="Buscar por nombre o código"
                                            class="cx-input !pl-8 @error('enfermedades') is-invalid @enderror">

                                        <span x-show="enfCargando" x-cloak
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] cx-faint">
                                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                                        </span>

                                        <div id="enfermedad_resultados" x-show="enfAbierto" x-cloak
                                            class="cx-dropdown" role="listbox">
                                            <template x-for="(item, i) in enfResultados" :key="item.id">
                                                <button type="button" class="cx-option" role="option"
                                                    :data-activo="i === enfActivo" :aria-selected="i === enfActivo"
                                                    @mouseenter="enfActivo = i" @click="elegirEnfermedad(item)">
                                                    <span class="font-medium truncate" x-text="item.nombre"></span>
                                                    <span class="text-[11px] cx-faint shrink-0"
                                                        x-text="item.codigo || ''"></span>
                                                </button>
                                            </template>
                                            <p x-show="!enfCargando && !enfError && enfResultados.length === 0"
                                                class="px-3 py-2 text-[11px] cx-muted">
                                                Sin resultados nuevos para "<span x-text="enfQuery"></span>".
                                            </p>
                                            <p x-show="enfError" class="px-3 py-2 text-[11px] text-rose-600">
                                                No se pudo buscar. Revisa la conexión e intenta de nuevo.
                                            </p>
                                        </div>
                                    </div>
                                    <p x-show="enfQuery.trim().length === 1" x-cloak class="cx-hint">Escribe al menos
                                        2 caracteres.</p>

                                    {{-- Seleccionadas --}}
                                    <ul class="flex flex-wrap gap-1.5 mt-2" x-show="seleccionadas.length" x-cloak
                                        aria-label="Enfermedades seleccionadas">
                                        <template x-for="item in seleccionadas" :key="item.id">
                                            <li
                                                class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-0.5 rounded-md text-[11px] font-semibold border border-sky-200 dark:border-sky-900 bg-sky-50 dark:bg-sky-950/40 text-sky-800 dark:text-sky-200">
                                                <span x-text="item.nombre"></span>
                                                <span x-show="item.codigo" class="font-medium opacity-60"
                                                    x-text="item.codigo"></span>
                                                <button type="button" @click="quitarEnfermedad(item.id)"
                                                    class="w-5 h-5 rounded flex items-center justify-center hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-950/50 transition-colors"
                                                    :aria-label="`Quitar ${item.nombre}`">
                                                    <i class="fas fa-times text-[9px]" aria-hidden="true"></i>
                                                </button>
                                                <input type="hidden" name="enfermedades[]" :value="item.id">
                                            </li>
                                        </template>
                                    </ul>
                                    <p x-show="!seleccionadas.length" class="cx-hint">Agrega al menos una.</p>

                                    @error('enfermedades')
                                        <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                                aria-hidden="true"></i>{{ $message }}</p>
                                    @enderror
                                    @error('enfermedades.*')
                                        <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                                aria-hidden="true"></i>{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                {{-- ═══════════════ ACCIONES ═══════════════ --}}
                <div
                    class="cx-actionbar px-4 py-3 sm:px-5 mt-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="{{ route('admin.salud.movimientos.consultas.index') }}" class="cx-btn cx-btn-ghost">
                        Cancelar
                    </a>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <p class="hidden md:block text-xs cx-muted" x-show="completos < checklist.length">
                            Faltan <span x-text="checklist.length - completos"></span> datos obligatorios.
                        </p>
                        <button type="submit" class="rd-submit-btn cx-btn cx-btn-primary">
                            {{ $esEdicion ? 'Guardar cambios y continuar' : 'Guardar y continuar a la receta' }}
                            <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function consultaForm(cfg) {
            const esMismo = (a, b) => String(a) === String(b);

            return {
                // Paciente
                pacienteId: cfg.pacienteId || '',
                pacienteNombre: cfg.pacienteNombre || '',
                pacienteQuery: '',
                pacienteResultados: [],
                pacienteAbierto: false,
                pacienteCargando: false,
                pacienteError: false,
                pacienteActivo: -1,

                // Médico / consultorio
                medicos: cfg.medicos || [],
                medicoId: cfg.medicoId || '',
                consultorioId: '',

                // Clínico
                motivo: cfg.motivo || '',
                diagnostico: cfg.diagnostico || '',

                // Enfermedades
                seleccionadas: Array.isArray(cfg.enfermedades) ? cfg.enfermedades : Object.values(cfg.enfermedades || {}),
                enfQuery: '',
                enfResultados: [],
                enfAbierto: false,
                enfCargando: false,
                enfError: false,
                enfActivo: -1,

                _timerPaciente: null,
                _timerEnf: null,

                init() {
                    this.sincronizarConsultorio();
                },

                // ── Derivados ──────────────────────────────────
                get medicoActual() {
                    return this.medicos.find(m => esMismo(m.id, this.medicoId)) || null;
                },

                get consultorioNombre() {
                    return this.medicoActual ? this.medicoActual.consultorio_nombre : '';
                },

                get checklist() {
                    return [{
                            label: 'Paciente',
                            ok: !!this.pacienteId
                        },
                        {
                            label: 'Médico tratante',
                            ok: !!this.medicoId
                        },
                        {
                            label: 'Motivo de la consulta',
                            ok: this.motivo.trim().length >= 3
                        },
                        {
                            label: 'Diagnóstico clínico',
                            ok: this.diagnostico.trim().length >= 3
                        },
                        {
                            label: 'Al menos una enfermedad',
                            ok: this.seleccionadas.length > 0
                        },
                    ];
                },

                get completos() {
                    return this.checklist.filter(i => i.ok).length;
                },

                iniciales(nombre) {
                    const partes = (nombre || '').trim().split(/\s+/).filter(p => !/\d/.test(p));
                    return ((partes[0] || '')[0] || '') + ((partes[1] || '')[0] || '') || '?';
                },

                sincronizarConsultorio() {
                    this.consultorioId = this.medicoActual ? this.medicoActual.consultorio_id : '';
                },

                // ── Paciente ───────────────────────────────────
                buscarPaciente() {
                    clearTimeout(this._timerPaciente);
                    const q = this.pacienteQuery.trim();

                    if (q.length < 2) {
                        this.pacienteResultados = [];
                        this.pacienteAbierto = false;
                        return;
                    }

                    this._timerPaciente = setTimeout(async () => {
                        this.pacienteCargando = true;
                        this.pacienteError = false;
                        try {
                            const res = await fetch(`${cfg.rutas.personas}?q=${encodeURIComponent(q)}`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            if (!res.ok) throw new Error(res.status);
                            this.pacienteResultados = await res.json();
                            this.pacienteActivo = this.pacienteResultados.length ? 0 : -1;
                        } catch (e) {
                            console.error('Error al buscar pacientes:', e);
                            this.pacienteResultados = [];
                            this.pacienteError = true;
                        } finally {
                            this.pacienteCargando = false;
                            this.pacienteAbierto = true;
                        }
                    }, 300);
                },

                elegirPaciente(item) {
                    this.pacienteId = String(item.id_persona ?? item.id);
                    this.pacienteNombre = (item.nombre || '').trim();
                    this.pacienteQuery = '';
                    this.pacienteResultados = [];
                    this.pacienteAbierto = false;
                },

                cambiarPaciente() {
                    this.pacienteId = '';
                    this.pacienteNombre = '';
                    this.$nextTick(() => this.$refs.pacienteInput?.focus());
                },

                // ── Enfermedades ───────────────────────────────
                buscarEnfermedades() {
                    clearTimeout(this._timerEnf);
                    const q = this.enfQuery.trim();

                    if (q.length < 2) {
                        this.enfResultados = [];
                        this.enfAbierto = false;
                        return;
                    }

                    this._timerEnf = setTimeout(async () => {
                        this.enfCargando = true;
                        this.enfError = false;
                        try {
                            const res = await fetch(`${cfg.rutas.enfermedades}?q=${encodeURIComponent(q)}`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            if (!res.ok) throw new Error(res.status);
                            const data = await res.json();
                            this.enfResultados = data.filter(e => !this.seleccionadas.some(s => esMismo(s.id, e
                                .id)));
                            this.enfActivo = this.enfResultados.length ? 0 : -1;
                        } catch (e) {
                            console.error('Error al buscar enfermedades:', e);
                            this.enfResultados = [];
                            this.enfError = true;
                        } finally {
                            this.enfCargando = false;
                            this.enfAbierto = true;
                        }
                    }, 300);
                },

                elegirEnfermedad(item) {
                    if (!this.seleccionadas.some(s => esMismo(s.id, item.id))) {
                        this.seleccionadas.push(item);
                    }
                    this.enfQuery = '';
                    this.enfResultados = [];
                    this.enfAbierto = false;
                },

                quitarEnfermedad(id) {
                    this.seleccionadas = this.seleccionadas.filter(s => !esMismo(s.id, id));
                },

                // ── Teclado ────────────────────────────────────
                tecla(e, tipo) {
                    const lista = tipo === 'paciente' ? this.pacienteResultados : this.enfResultados;
                    const abierto = tipo === 'paciente' ? this.pacienteAbierto : this.enfAbierto;
                    const activo = tipo === 'paciente' ? 'pacienteActivo' : 'enfActivo';

                    if (e.key === 'Enter') e.preventDefault();

                    if (!abierto || !lista.length) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this[activo] = (this[activo] + 1) % lista.length;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this[activo] = (this[activo] - 1 + lista.length) % lista.length;
                    } else if (e.key === 'Enter' && this[activo] >= 0) {
                        tipo === 'paciente' ?
                            this.elegirPaciente(lista[this[activo]]) :
                            this.elegirEnfermedad(lista[this[activo]]);
                    } else if (e.key === 'Escape') {
                        tipo === 'paciente' ? (this.pacienteAbierto = false) : (this.enfAbierto = false);
                    }
                },
            };
        }
    </script>
</x-app-layout>
