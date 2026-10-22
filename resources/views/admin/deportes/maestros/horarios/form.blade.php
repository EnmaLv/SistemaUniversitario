@props([
    'horario' => null,
    'calendarios' => [],
    'actividades' => [],
    'espacios' => [],
    'horariosActivos' => [],
    'bloquesSeleccionadosIniciales' => [],
    'dias' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
    'semanaInicio' => null,
])

@php
    use Carbon\Carbon;

    $semanaInicio = old('semana_inicio', $semanaInicio ?? (isset($horario) && $horario->semana_inicio_hde ? $horario->semana_inicio_hde->format('Y-m-d') : Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d')));

    $BLOQUES = [
        'Matutino' => [
            ['inicio' => '07:30', 'fin' => '08:15'],
            ['inicio' => '08:15', 'fin' => '09:00'],
            ['inicio' => '09:20', 'fin' => '10:05'],
            ['inicio' => '10:05', 'fin' => '10:50'],
            ['inicio' => '10:50', 'fin' => '11:35'],
            ['inicio' => '11:35', 'fin' => '12:20'],
        ],
        'Vespertino' => [
            ['inicio' => '13:30', 'fin' => '14:15'],
            ['inicio' => '14:15', 'fin' => '15:00'],
            ['inicio' => '15:00', 'fin' => '15:45'],
            ['inicio' => '15:45', 'fin' => '16:30'],
            ['inicio' => '16:30', 'fin' => '17:15'],
            ['inicio' => '17:15', 'fin' => '18:00'],
        ],
        'Nocturno' => [
            ['inicio' => '18:00', 'fin' => '18:40'],
            ['inicio' => '18:40', 'fin' => '19:20'],
            ['inicio' => '19:20', 'fin' => '20:00'],
            ['inicio' => '20:00', 'fin' => '20:40'],
            ['inicio' => '20:40', 'fin' => '21:20'],
            ['inicio' => '21:20', 'fin' => '22:00'],
        ],
    ];

    $tipoActual = old('tipo_horario', isset($horario) && $horario->fecha_especifica_hde ? 'fecha_especifica' : 'recurrente');
    $seleccionInicial = old('bloques', $bloquesSeleccionadosIniciales ?? []);
    $seleccionInicialRecurrente = old('bloques_recurrente', $bloquesSeleccionadosInicialesRecurrente ?? []);
    $seleccionInicialFechaEspecifica = old('bloques_fecha_especifica', $bloquesSeleccionadosInicialesFechaEspecifica ?? []);
    $diasSemana = array_values($dias);
    $claseCampo = 'w-full rounded-xl border px-3 py-2 text-sm';
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
@endphp

<form id="formHorarioDeporte" method="POST" action="{{ isset($horario) ? route('admin.deportes.maestros.horarios.update', $horario->id_hde) : route('admin.deportes.maestros.horarios.store') }}" class="mx-auto w-full max-w-[1500px] space-y-4">
    @csrf
    @if(isset($horario))
        @method('PUT')
    @endif

    @include('admin.deportes.partials.errores')

    <div id="bloquesContainer"></div>

    <div class="grid w-full grid-cols-1 gap-4 xl:grid-cols-[280px_minmax(680px,1fr)_240px] xl:items-stretch">
        <div class="space-y-2 rounded-2xl border p-3 shadow-sm xl:min-h-[680px]" style="background-color:var(--bg-card);border-color:var(--border-color);">
            <div>
                <h3 class="text-lg font-bold" style="color:var(--text-main);">Datos generales</h3>
            </div>

            @if(isset($horario))
                <div>
                    <label class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Calendario</label>
                    <input type="text" value="{{ $horario->calendario?->nombre_cld ?? 'Sin calendario' }}" readonly class="{{ $claseCampo }} opacity-70" style="{{ $estiloCampo }}" />
                    <input type="hidden" name="cld_id" id="cld_id_hidden" value="{{ $horario->cld_id }}"
                        data-inicio="{{ $horario->calendario?->fecha_inicio_cld?->format('Y-m-d') }}"
                        data-fin="{{ $horario->calendario?->fecha_fin_cld?->format('Y-m-d') }}"
                        data-sede="{{ $horario->calendario?->sed_id }}" />
                </div>
            @else
                <div>
                    <label class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Calendario</label>
                    <select name="cld_id" id="cld_id" required class="{{ $claseCampo }}" style="{{ $estiloCampo }}">
                        <option value="">Selecciona un calendario</option>
                        @foreach($calendarios as $c)
                            <option value="{{ $c->id_cld }}" data-inicio="{{ $c->fecha_inicio_cld->format('Y-m-d') }}" data-fin="{{ $c->fecha_fin_cld->format('Y-m-d') }}" data-sede="{{ $c->sed_id }}" @selected((string) old('cld_id') === (string) $c->id_cld)>
                                {{ $c->nombre_cld }} ({{ $c->fecha_inicio_cld->format('d/m/Y') }} - {{ $c->fecha_fin_cld->format('d/m/Y') }}){{ $calendarios->pluck('sed_id')->unique()->count() > 1 ? ' · ' . ($c->sede?->nombre_sed ?? '') : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if ($calendarios->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">No hay calendarios activos. Regístralo primero en Calendarios deportivos.</p>
                    @endif
                </div>
            @endif

            <div>
                <label class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Actividad</label>
                <select name="acd_id" id="acd_id" required class="{{ $claseCampo }}" style="{{ $estiloCampo }}">
                    <option value="">Selecciona una actividad</option>
                    @foreach($actividades as $a)
                        <option value="{{ $a->id_acd }}" @selected((string) old('acd_id', $horario->acd_id ?? '') === (string) $a->id_acd)>{{ $a->nombre_acd }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Espacio / Cancha</label>
                <select name="epd_id" id="epd_id" required class="{{ $claseCampo }}" style="{{ $estiloCampo }}">
                    <option value="">Selecciona un espacio</option>
                    @foreach($espacios as $e)
                        <option value="{{ $e->id_epd }}" data-sede="{{ $e->sed_id }}" @selected((string) old('epd_id', $horario->epd_id ?? '') === (string) $e->id_epd)>
                            {{ $e->nombre_epd }}{{ $espacios->pluck('sed_id')->unique()->count() > 1 ? ' · ' . ($e->sede?->nombre_sed ?? '') : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Tipo de horario</label>
                <div class="mt-2 flex gap-6 text-sm" style="color:var(--text-main);">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="tipo_horario" value="recurrente" {{ $tipoActual == 'recurrente' ? 'checked' : '' }} {{ isset($horario) ? 'disabled' : '' }}>
                        <span>Semanal</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="tipo_horario" value="fecha_especifica" {{ $tipoActual == 'fecha_especifica' ? 'checked' : '' }} {{ isset($horario) ? 'disabled' : '' }}>
                        <span>Fecha específica</span>
                    </label>
                </div>
            </div>

            <div id="campo-semana-inicio" class="{{ $tipoActual == 'fecha_especifica' ? 'hidden' : '' }}">
                <label for="semana_inicio" class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Vigente desde la semana del</label>
                <input id="semana_inicio" type="date" name="semana_inicio" value="{{ $semanaInicio }}" class="{{ $claseCampo }}" style="{{ $estiloCampo }}" />
                <p class="mt-1 text-[11px]" style="color:var(--text-muted);">Se repite cada semana hasta el cierre del calendario.</p>
            </div>

            <div id="campo-fecha-especifica" class="{{ $tipoActual == 'recurrente' ? 'hidden' : '' }}">
                <label class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Fecha específica</label>
                <input id="fecha_especifica" type="date" name="fecha_especifica" value="{{ old('fecha_especifica', isset($horario) && $horario->fecha_especifica_hde ? $horario->fecha_especifica_hde->format('Y-m-d') : '') }}" min="{{ Carbon::today()->format('Y-m-d') }}" class="{{ $claseCampo }}" style="{{ $estiloCampo }}" {{ isset($horario) ? 'disabled' : '' }} />
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Cupo máximo</label>
                <input type="number" name="cupo_maximo_hde" min="1" value="{{ old('cupo_maximo_hde', $horario->cupo_maximo_hde ?? '') }}" class="{{ $claseCampo }}" style="{{ $estiloCampo }}" placeholder="Sin límite" />
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" style="color:var(--text-main);">Observaciones</label>
                <textarea name="observaciones_hde" rows="4" class="{{ $claseCampo }}" style="{{ $estiloCampo }}">{{ old('observaciones_hde', $horario->observaciones_hde ?? '') }}</textarea>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm xl:min-h-[680px]">
            <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Seleccionar bloques</h3>
                    <p class="text-sm text-gray-500">Cada bloque puede seleccionarse o deseleccionarse.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="jornadaPrev" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Anterior</button>
                    <span id="jornadaIndicador" class="min-w-[60px] text-center text-sm font-bold text-gray-700">1 / 3</span>
                    <button type="button" id="jornadaNext" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Siguiente</button>
                </div>
            </div>

            <div class="mb-3 flex justify-end">
                <button type="button" id="limpiarSeleccion" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpiar selección</button>
            </div>

            <div id="bloquesMessage" class="mb-3 hidden rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700">
                Completa calendario, actividad y espacio para habilitar la selección de bloques.
            </div>

            <div class="space-y-4">
                @foreach($BLOQUES as $jornada => $bloques)
                    <div class="jornada-block rounded-xl border border-gray-200 bg-gray-50/30 p-3 {{ $loop->first ? '' : 'hidden' }}" data-jornada="{{ $jornada }}">
                        <div class="mb-2 text-xs font-black uppercase tracking-wider text-gray-600">Jornada {{ $jornada }}</div>
                        <div class="overflow-x-auto">
                            <div class="grid min-w-[560px]" style="grid-template-columns: 120px repeat(5, minmax(0, 1fr));">
                                <div class="border-b border-r border-gray-200 bg-gray-100 p-2 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500">Bloque</div>
                                @foreach($diasSemana as $dia)
                                    <div class="day-header border-b border-r border-gray-200 bg-gray-100 p-2 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600" data-dia="{{ strtolower($dia) }}">{{ strtoupper($dia) }}</div>
                                @endforeach

                                @foreach($bloques as $bloque)
                                    @php
                                        $inicio = Carbon::parse($bloque['inicio'])->format('H:i');
                                        $fin = Carbon::parse($bloque['fin'])->format('H:i');
                                    @endphp
                                    <div class="flex items-center justify-center border-b border-r border-gray-200 bg-white p-2 text-center text-[10px] font-semibold text-gray-700">
                                        {{ Carbon::parse($bloque['inicio'])->format('g:i A') }}<br> - <br>{{ Carbon::parse($bloque['fin'])->format('g:i A') }}
                                    </div>

                                    @foreach($diasSemana as $dia)
                                        @php
                                            $diaKey = strtolower($dia);
                                            $llave = $diaKey . '|' . $inicio . '|' . $fin;
                                        @endphp
                                        <button type="button" class="bloque-celda border-b border-r border-gray-200 p-2 text-center text-[10px] font-semibold transition-all focus:outline-none" data-dia="{{ $diaKey }}" data-inicio="{{ $inicio }}" data-fin="{{ $fin }}" data-key="{{ $llave }}">
                                            <span class="block">Disponible</span>
                                        </button>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <aside class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm xl:min-h-[680px] xl:sticky xl:top-4">
            <div class="mb-2 flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700">Bloques seleccionados</h3>
            </div>
            <div id="resumenSeleccion" class="h-[420px] max-h-[420px] overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 shadow-inner"></div>
        </aside>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Cancelar</a>
        @canRoute(isset($horario) ? 'admin.deportes.maestros.horarios.update' : 'admin.deportes.maestros.horarios.store')
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900"><i class="fas fa-save text-xs"></i> Guardar</button>
        @endcanRoute
    </div>
</form>

<div id="modalSinBloques" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-800">Sin bloques de horario</h3>
        </div>
        <div class="space-y-3 px-5 py-4 text-sm text-gray-700">
            <p>No has seleccionado ningún bloque de horario. Debes seleccionar al menos un bloque para guardar los cambios.</p>
        </div>
        <div class="flex justify-end border-t border-gray-200 px-5 py-4">
            <button type="button" id="cerrarModalSinBloques" class="rounded-xl bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700">Entendido</button>
        </div>
    </div>
</div>

<script>
    (function () {
        function bootHorarioBloquesForm() {
        const form = document.getElementById('formHorarioDeporte') || document.querySelector('form[action*="horarios"]');
        if (form && form.dataset.bloquesBooted === '1') {
            return;
        }
        if (form) {
            form.dataset.bloquesBooted = '1';
        }
        const tipoInputs = form
            ? form.querySelectorAll('input[name="tipo_horario"]')
            : document.querySelectorAll('input[name="tipo_horario"]');
        const fechaInput = document.getElementById('fecha_especifica');
        const campoFechaEspecifica = document.getElementById('campo-fecha-especifica');
        const limpiarBtn = document.getElementById('limpiarSeleccion');
        const resumen = document.getElementById('resumenSeleccion');
        const hiddenContainer = document.getElementById('bloquesContainer');
        const jornadaBlocks = document.querySelectorAll('.jornada-block');
        const jornadaPrev = document.getElementById('jornadaPrev');
        const jornadaNext = document.getElementById('jornadaNext');
        const jornadaIndicador = document.getElementById('jornadaIndicador');

        const selectedKeys = new Set();
        const recurrenteKeys = new Set();
        const fechaEspecificaKeys = new Set();
        const activeRows = @json($horariosActivos ?? []);
        const horarioActualId = {{ isset($horario) ? $horario->id_hde : 'null' }};
        const isEditMode = {{ isset($horario) ? 'true' : 'false' }};
        const modalSinBloques = document.getElementById('modalSinBloques');
        const cerrarModalSinBloques = document.getElementById('cerrarModalSinBloques');

        let currentJornadaIndex = 0;

        function getSelectionSetForType(tipo) {
            return tipo === 'fecha_especifica' ? fechaEspecificaKeys : recurrenteKeys;
        }

        function hydrateSelectionState() {
            recurrenteKeys.clear();
            fechaEspecificaKeys.clear();
            selectedKeys.clear();

            const inicialRecurrente = @json($seleccionInicialRecurrente ?? []);
            const inicialFechaEspecifica = @json($seleccionInicialFechaEspecifica ?? []);

            Array.from(inicialRecurrente).forEach((value) => {
                if (typeof value === 'string' && value.trim() !== '') {
                    recurrenteKeys.add(value.trim());
                }
            });

            Array.from(inicialFechaEspecifica).forEach((value) => {
                if (typeof value === 'string' && value.trim() !== '') {
                    fechaEspecificaKeys.add(value.trim());
                }
            });

            rebuildActiveSelection();
        }

        function isSpecificDateValid(dateValue) {
            if (!dateValue) {
                return false;
            }

            const selected = parseSafeDate(dateValue);
            if (!selected) {
                return false;
            }

            const today = new Date(new Date().toISOString().slice(0, 10) + 'T00:00:00');

            return selected >= today && selected.getDay() !== 0 && selected.getDay() !== 6;
        }

        function rebuildActiveSelection() {
            const tipoActual = getSelectedTipoHorario() || 'recurrente';
            const setActivo = getSelectionSetForType(tipoActual);

            selectedKeys.clear();
            Array.from(setActivo).forEach((value) => selectedKeys.add(value));

            return setActivo;
        }

        function clearActiveSelectionOnly() {
            const tipoActual = getSelectedTipoHorario() || 'recurrente';
            const setActivo = getSelectionSetForType(tipoActual);
            setActivo.clear();
            rebuildActiveSelection();
        }

        function resetSelectionForSpaceChange() {
            recurrenteKeys.clear();
            fechaEspecificaKeys.clear();
            selectedKeys.clear();
        }

        function enforceModeSelectionIsolation() {
            const tipoActual = getSelectedTipoHorario() || 'recurrente';

            if (tipoActual === 'fecha_especifica') {
                if (!fechaInput?.value || !isSpecificDateValid(fechaInput.value)) {
                    fechaEspecificaKeys.clear();
                    rebuildActiveSelection();
                }
                return;
            }

            if (tipoActual === 'recurrente' && fechaInput?.value && !isSpecificDateValid(fechaInput.value)) {
                fechaEspecificaKeys.clear();
                rebuildActiveSelection();
            }
        }

        function normalizeText(value) {
            return String(value ?? '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        }

        function parseTime(value) {
            const [hour, minute] = String(value).split(':').map(Number);
            return hour * 60 + minute;
        }

        function normalizeId(value) {
            return String(value ?? '').trim();
        }

        function isCurrentHorarioRow(row) {
            if (!isEditMode || horarioActualId === null || horarioActualId === undefined || horarioActualId === '') {
                return false;
            }

            return normalizeId(row?.id) === normalizeId(horarioActualId);
        }

        function normalizeDateValue(value) {
            const raw = String(value ?? '').trim();
            if (!raw) {
                return '';
            }

            return raw.includes('T') ? raw.split('T')[0] : raw;
        }

        function parseSafeDate(value) {
            if (value === null || value === undefined) {
                return null;
            }

            const raw = String(value).trim();
            if (!raw) {
                return null;
            }

            let date = null;

            const isoMatch = raw.match(/^(\d{4})-(\d{2})-(\d{2})(?:\s+\d{2}:\d{2}:\d{2})?$/);
            if (isoMatch) {
                const [, year, month, day] = isoMatch;
                date = new Date(`${year}-${month}-${day}T00:00:00`);
            } else {
                const dmyMatch = raw.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                if (dmyMatch) {
                    const [, day, month, year] = dmyMatch;
                    date = new Date(`${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}T00:00:00`);
                } else {
                    date = new Date(raw);
                }
            }

            if (Number.isNaN(date?.getTime?.())) {
                return null;
            }

            return date;
        }

        function getDayFromDate(value) {
            const date = parseSafeDate(value);
            if (!date) return null;
            const days = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            return days[date.getDay()];
        }

        function getSelectedDateDay() {
            const selectedType = document.querySelector('input[name="tipo_horario"]:checked')?.value || 'recurrente';
            const selectedDate = fechaInput?.value;

            if (selectedType === 'fecha_especifica' && selectedDate) {
                return getDayFromDate(selectedDate);
            }

            return null;
        }

        function getWeekStartDate(value) {
            const date = parseSafeDate(value);
            if (!date) return null;
            const day = date.getDay();
            const diffToMonday = day === 0 ? -6 : 1 - day;
            const monday = new Date(date);
            monday.setDate(date.getDate() + diffToMonday);
            return monday.toISOString().slice(0, 10);
        }

        function nextValidSpecificDate(dateValue) {
            const date = parseSafeDate(dateValue);
            if (!date) {
                return '';
            }

            while (date.getDay() === 0 || date.getDay() === 6) {
                date.setDate(date.getDate() + 1);
            }
            return date.toISOString().slice(0, 10);
        }

        function sanitizeSelectionByDate() {
            const selectedDateDay = getSelectedDateDay();
            if (!selectedDateDay) {
                fechaEspecificaKeys.clear();
                return;
            }

            const target = getSelectionSetForType('fecha_especifica');
            Array.from(target).forEach((key) => {
                const [dia] = key.split('|');
                if (normalizeText(dia) !== normalizeText(selectedDateDay)) {
                    target.delete(key);
                }
            });
        }

        function updateJornadaView() {
            if (!jornadaBlocks.length) {
                return;
            }

            currentJornadaIndex = Math.max(0, Math.min(currentJornadaIndex, jornadaBlocks.length - 1));
            jornadaBlocks.forEach((block, index) => {
                block.classList.toggle('hidden', index !== currentJornadaIndex);
            });

            if (jornadaIndicador) {
                jornadaIndicador.textContent = `${currentJornadaIndex + 1} / ${jornadaBlocks.length}`;
            }
        }


        function getNamedFieldValue(name) {
            const scope = form || document;
            const field = scope.querySelector(`[name="${name}"]`);

            if (!field) {
                return '';
            }

            if (field.type === 'radio') {
                return String(
                    scope.querySelector(`input[name="${name}"]:checked`)?.value || ''
                ).trim();
            }

            return String(field.value ?? '').trim();
        }

        function getSelectedTipoHorario() {
            const tipo = getNamedFieldValue('tipo_horario');
            return tipo || 'recurrente';
        }

        function getSelectionGateState() {
            const calendario = getNamedFieldValue('cld_id');
            const actividad = getNamedFieldValue('acd_id');
            const espacio = getNamedFieldValue('epd_id');
            const tipoSeleccionado = getSelectedTipoHorario();
            const fechaSeleccionada = String(fechaInput?.value || '').trim();
            const missing = [];

            if (!calendario) {
                missing.push('calendario');
            }
            if (!actividad) {
                missing.push('actividad');
            }
            if (!espacio) {
                missing.push('espacio');
            }

            if (tipoSeleccionado === 'fecha_especifica' && (!fechaSeleccionada || !isSpecificDateValid(fechaSeleccionada))) {
                missing.push('una fecha específica válida (lunes a viernes, desde hoy)');
            }

            return {
                calendario,
                actividad,
                espacio,
                tipoSeleccionado,
                fechaSeleccionada,
                missing,
                ready: missing.length === 0,
            };
        }

        function isSelectionReady() {
            return getSelectionGateState().ready;
        }

        function updateTipoHorarioUI() {
            const tipoSeleccionado = getSelectedTipoHorario() || 'recurrente';
            if (campoFechaEspecifica) {
                campoFechaEspecifica.classList.toggle('hidden', tipoSeleccionado !== 'fecha_especifica');
            }
            const campoSemanaInicio = document.getElementById('campo-semana-inicio');
            if (campoSemanaInicio) {
                campoSemanaInicio.classList.toggle('hidden', tipoSeleccionado === 'fecha_especifica');
            }
        }

        function setSelectionMessageState() {
            const message = document.getElementById('bloquesMessage');
            const gate = getSelectionGateState();

            if (!message) {
                return;
            }

            if (!gate.ready) {
                message.textContent = `Completa ${gate.missing.join(', ').replace(/, ([^,]*)$/, ' y $1')} para habilitar la selección de bloques.`;
                message.classList.remove('hidden');
                return;
            }

            message.textContent = 'Bloques disponibles. Puedes seleccionar o deseleccionar los que no estén ocupados.';
            message.classList.add('hidden');
        }

        function getCalendarioSeleccionado() {
            const scope = form || document;
            const campo = scope.querySelector('[name="cld_id"]');

            if (!campo) {
                return null;
            }

            const origen = campo.tagName === 'SELECT' ? campo.options[campo.selectedIndex] : campo;

            if (!origen || !origen.value || !origen.dataset.inicio || !origen.dataset.fin) {
                return null;
            }

            return { inicio: origen.dataset.inicio, fin: origen.dataset.fin, sede: origen.dataset.sede || '' };
        }

        function filtrarEspaciosPorSede() {
            const scope = form || document;
            const selectEspacio = scope.querySelector('select[name="epd_id"]');
            const calendario = getCalendarioSeleccionado();

            if (!selectEspacio) {
                return;
            }

            Array.from(selectEspacio.options).forEach((opcion) => {
                if (!opcion.value) {
                    return;
                }

                const visible = !calendario || !calendario.sede || opcion.dataset.sede === calendario.sede;
                opcion.hidden = !visible;
                opcion.disabled = !visible;

                if (!visible && opcion.selected) {
                    selectEspacio.value = '';
                }
            });
        }

        function getRangoCandidato() {
            const tipo = getSelectedTipoHorario();

            if (tipo === 'fecha_especifica') {
                const fecha = normalizeDateValue(fechaInput?.value);
                return fecha ? { desde: fecha, hasta: fecha } : null;
            }

            const calendario = getCalendarioSeleccionado();
            if (!calendario) {
                return null;
            }

            const semanaCampo = document.getElementById('semana_inicio');
            const semana = getWeekStartDate(semanaCampo?.value || new Date().toISOString().slice(0, 10));
            const desde = semana && semana > calendario.inicio ? semana : calendario.inicio;

            return { desde, hasta: calendario.fin };
        }

        function hayDiaEnRango(dia, desde, hasta) {
            const inicio = parseSafeDate(desde);
            const fin = parseSafeDate(hasta);
            const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

            if (!inicio || !fin) {
                return false;
            }

            for (let i = 0; i < 7 && inicio <= fin; i++) {
                if (dias[inicio.getDay()] === normalizeText(dia)) {
                    return true;
                }
                inicio.setDate(inicio.getDate() + 1);
            }

            return false;
        }

        function isBlockedByExisting(dia, inicio, fin) {
            const espacio = getNamedFieldValue('epd_id');
            const rango = getRangoCandidato();
            const tipo = getSelectedTipoHorario();
            const diaClave = tipo === 'fecha_especifica' ? getDayFromDate(fechaInput?.value) : normalizeText(dia);
            const blockStart = parseTime(inicio);
            const blockEnd = parseTime(fin);

            if (!espacio || !rango || !diaClave) {
                return false;
            }

            for (const row of activeRows) {
                if (isCurrentHorarioRow(row) || String(row.epd_id) !== String(espacio)) {
                    continue;
                }

                if (normalizeText(row.dia) !== normalizeText(diaClave)) {
                    continue;
                }

                if (!(blockStart < parseTime(row.hora_fin) && blockEnd > parseTime(row.hora_inicio))) {
                    continue;
                }

                const desde = rango.desde > row.desde ? rango.desde : row.desde;
                const hasta = rango.hasta < row.hasta ? rango.hasta : row.hasta;

                if (desde <= hasta && hayDiaEnRango(diaClave, desde, hasta)) {
                    return true;
                }
            }

            return false;
        }

        function syncHiddenInputs() {
            if (!hiddenContainer) {
                return;
            }

            hiddenContainer.innerHTML = '';
            Array.from(selectedKeys).forEach((value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'bloques[]';
                input.value = value;
                hiddenContainer.appendChild(input);
            });
        }

        function parseDayOrder(dia) {
            const normalized = normalizeText(dia);
            const order = {
                lunes: 1,
                martes: 2,
                miercoles: 3,
                jueves: 4,
                viernes: 5,
                sabado: 6,
                domingo: 7,
            };

            return order[normalized] ?? 99;
        }

        function renderSummary() {
            if (!resumen) return;

            const keys = Array.from(selectedKeys);

            const header = `<div class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-600">Bloques seleccionados (${keys.length})</div>`;

            if (!keys.length) {
                resumen.innerHTML = `${header}<div class="text-sm text-gray-500">Sin bloques seleccionados.</div>`;
                return;
            }

            const groups = {};
            keys.forEach((key) => {
                const [dia, inicio, fin] = key.split('|');
                const normalizedDia = normalizeText(dia);
                if (!groups[normalizedDia]) {
                    groups[normalizedDia] = [];
                }
                groups[normalizedDia].push({ inicio, fin, dia: normalizedDia });
            });

            const html = Object.entries(groups)
                .sort(([a], [b]) => parseDayOrder(a) - parseDayOrder(b))
                .map(([normalizedDia, ranges]) => {
                    const labelDia = normalizedDia.charAt(0).toUpperCase() + normalizedDia.slice(1);
                    const sortedRanges = [...ranges].sort((a, b) => parseTime(a.inicio) - parseTime(b.inicio));

                    return `
                        <div class="mb-2 rounded-lg border border-gray-200 bg-white p-2">
                            <div class="mb-1 text-xs font-bold uppercase tracking-wider text-gray-600">${labelDia}</div>
                            ${sortedRanges.map((range) => `<div class="text-sm text-gray-700">${range.inicio} - ${range.fin}</div>`).join('')}
                        </div>
                    `;
                }).join('');

            resumen.innerHTML = `${header}${html}`;
        }

        function refreshBlockStates() {
            const selectedType = getSelectedTipoHorario() || 'recurrente';
            const selectedDate = fechaInput?.value;
            const formReady = isSelectionReady();

            if (formReady && selectedType === 'fecha_especifica') {
                if (!selectedDate || !isSpecificDateValid(selectedDate)) {
                    fechaEspecificaKeys.clear();
                    rebuildActiveSelection();
                } else {
                    sanitizeSelectionByDate();
                }
            }

            if (formReady) {
                enforceModeSelectionIsolation();
            }

            const activeSet = formReady
                ? rebuildActiveSelection()
                : new Set(selectedKeys);

            document.querySelectorAll('.bloque-celda').forEach((button) => {
                const dia = button.dataset.dia;
                const inicio = button.dataset.inicio;
                const fin = button.dataset.fin;
                const key = button.dataset.key;
                const isSelected = formReady && activeSet.has(key);
                const isDateMismatch = formReady && selectedType === 'fecha_especifica' && selectedDate && normalizeText(dia) !== normalizeText(getDayFromDate(selectedDate));
                const blocked = formReady && !isDateMismatch && isBlockedByExisting(dia, inicio, fin);

                button.classList.remove('bg-emerald-500', 'text-white', 'border-emerald-500', 'bg-red-100', 'border-red-300', 'text-red-700', 'bg-gray-100', 'border-gray-200', 'text-gray-500', 'text-gray-700', 'cursor-not-allowed', 'opacity-60', 'opacity-50');
                button.removeAttribute('aria-disabled');
                button.disabled = false;
                button.innerHTML = '<span class="block">Disponible</span>';

                if (!formReady) {
                    // No usar disabled: un botón disabled no dispara click y no se puede resincronizar el estado.
                    button.setAttribute('aria-disabled', 'true');
                    button.classList.add('bg-white', 'border-gray-200', 'text-gray-500', 'cursor-not-allowed', 'opacity-50');
                    return;
                }

                if (selectedType === 'fecha_especifica' && (!selectedDate || !isSpecificDateValid(selectedDate))) {
                    button.setAttribute('aria-disabled', 'true');
                    button.classList.add('bg-gray-100', 'border-gray-200', 'text-gray-500', 'cursor-not-allowed', 'opacity-60');
                    button.innerHTML = '<span class="block text-[9px]">No aplica</span>';
                    return;
                }

                if (isDateMismatch) {
                    button.disabled = true;
                    button.classList.add('bg-gray-100', 'border-gray-200', 'text-gray-500', 'cursor-not-allowed', 'opacity-60');
                    button.innerHTML = '<span class="block text-[9px]">No aplica</span>';
                    return;
                }

                if (isSelected) {
                    button.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white');
                    button.innerHTML = '<span class="block font-bold">Seleccionado</span>';
                    return;
                }

                if (blocked) {
                    button.disabled = true;
                    button.classList.add('bg-red-100', 'border-red-300', 'text-red-700', 'cursor-not-allowed', 'opacity-60');
                    button.innerHTML = '<span class="block font-bold">No disponible</span>';
                    return;
                }

                button.classList.add('bg-white', 'border-gray-200', 'text-gray-700');
                button.innerHTML = '<span class="block">Disponible</span>';
            });

            setSelectionMessageState();
            renderSummary();
            syncHiddenInputs();
        }

        function toggleBloqueCelda(button) {
            refreshBlockStates();

            if (!isSelectionReady() || button.disabled || button.getAttribute('aria-disabled') === 'true') {
                return;
            }

            const currentTipo = getSelectedTipoHorario() || 'recurrente';
            const target = getSelectionSetForType(currentTipo);
            const key = button.dataset.key;

            if (target.has(key)) {
                target.delete(key);
            } else {
                target.add(key);
            }

            rebuildActiveSelection();
            refreshBlockStates();
        }

        if (form) {
            form.addEventListener('click', (event) => {
                const button = event.target.closest('.bloque-celda');
                if (!button || !form.contains(button)) {
                    return;
                }

                toggleBloqueCelda(button);
            });

            form.addEventListener('change', (event) => {
                const name = event.target && event.target.name;
                if (!name) {
                    return;
                }

                if (name === 'epd_id') {
                    resetSelectionForSpaceChange();
                }

                if (name === 'cld_id') {
                    filtrarEspaciosPorSede();
                }

                if (['cld_id', 'acd_id', 'epd_id', 'tipo_horario', 'fecha_especifica', 'semana_inicio'].includes(name)) {
                    refreshBlockStates();
                }
            });

            form.addEventListener('input', (event) => {
                const name = event.target && event.target.name;
                if (name === 'fecha_especifica') {
                    refreshBlockStates();
                }
            });
        } else {
            document.querySelectorAll('.bloque-celda').forEach((button) => {
                button.addEventListener('click', () => toggleBloqueCelda(button));
            });
        }

        tipoInputs.forEach((input) => {
            input.addEventListener('change', () => {
                const tipoSeleccionado = getSelectedTipoHorario() || 'recurrente';

                if (tipoSeleccionado === 'fecha_especifica' && !isSpecificDateValid(fechaInput?.value)) {
                    fechaEspecificaKeys.clear();
                }

                enforceModeSelectionIsolation();
                updateTipoHorarioUI();
                rebuildActiveSelection();
                refreshBlockStates();
            });
        });

        if (fechaInput) {
            const todayIso = new Date().toISOString().slice(0, 10);
            fechaInput.min = todayIso;
            fechaInput.addEventListener('change', function () {
                const selectedDate = fechaInput.value;
                if (!selectedDate) {
                    fechaEspecificaKeys.clear();
                    rebuildActiveSelection();
                    refreshBlockStates();
                    return;
                }

                const selected = new Date(selectedDate + 'T00:00:00');
                const today = new Date(todayIso + 'T00:00:00');
                if (selected < today || selected.getDay() === 0 || selected.getDay() === 6) {
                    fechaInput.value = nextValidSpecificDate(todayIso);
                }

                sanitizeSelectionByDate();
                rebuildActiveSelection();
                refreshBlockStates();
            });
        }

        if (jornadaPrev) {
            jornadaPrev.addEventListener('click', () => {
                currentJornadaIndex = currentJornadaIndex > 0 ? currentJornadaIndex - 1 : 0;
                updateJornadaView();
            });
        }

        if (jornadaNext) {
            jornadaNext.addEventListener('click', () => {
                currentJornadaIndex = currentJornadaIndex < jornadaBlocks.length - 1 ? currentJornadaIndex + 1 : jornadaBlocks.length - 1;
                updateJornadaView();
            });
        }

        if (limpiarBtn) {
            limpiarBtn.addEventListener('click', () => {
                const tipoActual = getSelectedTipoHorario() || 'recurrente';
                const setActivo = getSelectionSetForType(tipoActual);
                setActivo.clear();
                rebuildActiveSelection();
                refreshBlockStates();
            });
        }

        if (form && isEditMode) {
            form.addEventListener('submit', (event) => {
                if (selectedKeys.size === 0) {
                    event.preventDefault();
                    if (modalSinBloques) {
                        modalSinBloques.classList.remove('hidden');
                        modalSinBloques.classList.add('flex');
                    }
                }
            });
        }

        if (cerrarModalSinBloques) {
            cerrarModalSinBloques.addEventListener('click', () => {
                if (modalSinBloques) {
                    modalSinBloques.classList.add('hidden');
                    modalSinBloques.classList.remove('flex');
                }
            });
        }

        if (modalSinBloques) {
            modalSinBloques.addEventListener('click', (event) => {
                if (event.target === modalSinBloques) {
                    modalSinBloques.classList.add('hidden');
                    modalSinBloques.classList.remove('flex');
                }
            });
        }

        filtrarEspaciosPorSede();
        hydrateSelectionState();
        updateTipoHorarioUI();
        updateJornadaView();
        refreshBlockStates();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootHorarioBloquesForm);
        } else {
            bootHorarioBloquesForm();
        }
    })();
</script>
