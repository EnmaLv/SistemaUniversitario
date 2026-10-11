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

    $semanaInicio = old('semana_inicio', $semanaInicio ?? (isset($horario) && $horario->semana_inicio ? $horario->semana_inicio : Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d')));

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

    $tipoActual = old('tipo_horario', isset($horario) && $horario->fecha_especifica ? 'fecha_especifica' : 'recurrente');
    $seleccionInicial = old('bloques', $bloquesSeleccionadosIniciales ?? []);
    $diasSemana = array_values($dias);
@endphp

<form method="POST" action="{{ isset($horario) ? route('admin.deportes.maestros.horarios.update', $horario->id) : route('admin.deportes.maestros.horarios.store') }}" class="mx-auto w-full max-w-[1500px] space-y-4">
    @csrf
    @if(isset($horario))
        @method('PUT')
    @endif

    <input type="hidden" name="semana_inicio" value="{{ old('semana_inicio', $semanaInicio) }}">

    <div id="bloquesContainer"></div>

    <div class="grid w-full grid-cols-1 gap-4 xl:grid-cols-[280px_minmax(680px,1fr)_240px] xl:items-stretch">
        <div class="space-y-2 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm xl:min-h-[680px]">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Datos generales</h3>
            </div>

            @if(isset($horario))
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Calendario</label>
                    <input type="text" value="{{ $horario->calendario ? $horario->calendario->nombre : 'Sin calendario' }}" readonly class="w-full rounded-xl border border-gray-300 bg-gray-100 px-3 py-2 text-gray-700" />
                    <input type="hidden" name="id_calendario" id="id_calendario" value="{{ old('id_calendario', $horario->id_calendario) }}" />
                </div>
            @else
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Calendario</label>
                    <select name="id_calendario" id="id_calendario" required class="w-full rounded-xl border border-gray-300 px-3 py-2">
                        <option value="" {{ old('id_calendario', $horario->id_calendario ?? '') === '' ? 'selected' : '' }}>Selecciona un calendario</option>
                        @foreach($calendarios as $c)
                            <option value="{{ $c->id }}" {{ old('id_calendario', $horario->id_calendario ?? '') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Actividad</label>
                <select name="id_actividad" id="id_actividad" required class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    <option value="" {{ old('id_actividad', $horario->id_actividad ?? '') === '' ? 'selected' : '' }}>Selecciona una actividad</option>
                    @foreach($actividades as $a)
                        <option value="{{ $a->id }}" {{ old('id_actividad', $horario->id_actividad ?? '') == $a->id ? 'selected' : '' }}>{{ $a->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Espacio / Cancha</label>
                <select name="id_espacio" id="id_espacio" required class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    <option value="" {{ old('id_espacio', $horario->id_espacio ?? '') === '' ? 'selected' : '' }}>Selecciona una cancha</option>
                    @foreach($espacios as $e)
                        <option value="{{ $e->id }}" {{ old('id_espacio', $horario->id_espacio ?? '') == $e->id ? 'selected' : '' }}>{{ $e->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de horario</label>
                <div class="mt-2 flex gap-6">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="tipo_horario" value="recurrente" {{ old('tipo_horario', $tipoActual) == 'recurrente' ? 'checked' : '' }} {{ isset($horario) ? 'disabled' : '' }}>
                        <span>Recurrente</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="tipo_horario" value="fecha_especifica" {{ old('tipo_horario', $tipoActual) == 'fecha_especifica' ? 'checked' : '' }} {{ isset($horario) ? 'disabled' : '' }}>
                        <span>Fecha específica</span>
                    </label>
                </div>
            </div>

            <div id="campo-fecha-especifica" class="{{ old('tipo_horario', $tipoActual) == 'recurrente' ? 'hidden' : '' }}">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha específica</label>
                <input id="fecha_especifica" type="date" name="fecha_especifica" value="{{ old('fecha_especifica', isset($horario->fecha_especifica) ? $horario->fecha_especifica->format('Y-m-d') : '') }}" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2" {{ isset($horario) ? 'disabled' : '' }} />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cupo máximo</label>
                <input type="number" name="cupo_maximo" min="1" value="{{ old('cupo_maximo', $horario->cupo_maximo ?? '') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Observaciones</label>
                <textarea name="observaciones" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2">{{ old('observaciones', $horario->observaciones ?? '') }}</textarea>
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
                Completa los datos generales para habilitar la selección de bloques.
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

    @error('bloques')
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $message }}</div>
    @enderror

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Cancelar</a>
        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">Guardar</button>
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
    document.addEventListener('DOMContentLoaded', function () {
        const tipoInputs = document.querySelectorAll('input[name="tipo_horario"]');
        const fechaInput = document.getElementById('fecha_especifica');
        const campoFechaEspecifica = document.getElementById('campo-fecha-especifica');
        const espacioSelect = document.getElementById('id_espacio');
        const limpiarBtn = document.getElementById('limpiarSeleccion');
        const resumen = document.getElementById('resumenSeleccion');
        const hiddenContainer = document.getElementById('bloquesContainer');
        const jornadaBlocks = document.querySelectorAll('.jornada-block');
        const jornadaPrev = document.getElementById('jornadaPrev');
        const jornadaNext = document.getElementById('jornadaNext');
        const jornadaIndicador = document.getElementById('jornadaIndicador');

        const selectedKeys = new Set(@json($seleccionInicial ?? []));
        const activeRows = @json($horariosActivos ?? []);
        const horarioActualId = {{ isset($horario) ? $horario->id : 'null' }};
        const isEditMode = {{ isset($horario) ? 'true' : 'false' }};
        const modalSinBloques = document.getElementById('modalSinBloques');
        const cerrarModalSinBloques = document.getElementById('cerrarModalSinBloques');

        let currentJornadaIndex = 0;

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

        function getDayFromDate(value) {
            if (!value) return null;
            const date = new Date(value + 'T00:00:00');
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
            if (!value) return null;
            const date = new Date(value + 'T00:00:00');
            const day = date.getDay();
            const diffToMonday = day === 0 ? -6 : 1 - day;
            const monday = new Date(date);
            monday.setDate(date.getDate() + diffToMonday);
            return monday.toISOString().slice(0, 10);
        }

        function nextValidSpecificDate(dateValue) {
            const date = new Date(dateValue + 'T00:00:00');
            while (date.getDay() === 0 || date.getDay() === 6) {
                date.setDate(date.getDate() + 1);
            }
            return date.toISOString().slice(0, 10);
        }

        function sanitizeSelectionByDate() {
            const selectedDateDay = getSelectedDateDay();
            if (!selectedDateDay) {
                return;
            }

            Array.from(selectedKeys).forEach((key) => {
                const [dia] = key.split('|');
                if (normalizeText(dia) !== normalizeText(selectedDateDay)) {
                    selectedKeys.delete(key);
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

        function getSelectedTipoHorario() {
            return document.querySelector('input[name="tipo_horario"]:checked')?.value || '';
        }

        function isSelectionReady() {
            const calendario = document.getElementById('id_calendario')?.value || '';
            const actividad = document.getElementById('id_actividad')?.value || '';
            const espacio = document.getElementById('id_espacio')?.value || '';
            const tipoSeleccionado = getSelectedTipoHorario();
            const fechaSeleccionada = fechaInput?.value || '';

            if (!calendario || !actividad || !espacio || !tipoSeleccionado) {
                return false;
            }

            if (tipoSeleccionado === 'fecha_especifica' && !fechaSeleccionada) {
                return false;
            }

            return true;
        }

        function updateTipoHorarioUI() {
            const tipoSeleccionado = getSelectedTipoHorario() || 'recurrente';
            if (campoFechaEspecifica) {
                campoFechaEspecifica.classList.toggle('hidden', tipoSeleccionado !== 'fecha_especifica');
            }
        }

        function setSelectionMessageState() {
            const message = document.getElementById('bloquesMessage');
            const ready = isSelectionReady();

            if (message) {
                message.classList.toggle('hidden', ready);
            }
        }

        function isBlockedByExisting(dia, inicio, fin) {
            const selectedType = document.querySelector('input[name="tipo_horario"]:checked')?.value || 'recurrente';
            const selectedDate = fechaInput?.value;
            const blockStart = parseTime(inicio);
            const blockEnd = parseTime(fin);
            const selectedWeek = document.querySelector('input[name="semana_inicio"]')?.value
                ? getWeekStartDate(document.querySelector('input[name="semana_inicio"]').value)
                : getWeekStartDate(new Date().toISOString().slice(0, 10));

            for (const row of activeRows) {
                if (isCurrentHorarioRow(row)) {
                    continue;
                }

                if (String(row.estado || '').toLowerCase() !== 'activo') {
                    continue;
                }

                if (row.semana_inicio) {
                    const rowWeek = getWeekStartDate(row.semana_inicio);
                    if (selectedWeek && rowWeek && rowWeek !== selectedWeek) {
                        continue;
                    }
                }

                const recordStart = parseTime(row.hora_inicio);
                const recordEnd = parseTime(row.hora_fin);

                let sameDayEffective = false;

                if (selectedType === 'fecha_especifica' && selectedDate) {
                    const dayOfSelectedDate = getDayFromDate(selectedDate);
                    const selectedWeekDate = getWeekStartDate(selectedDate);

                    if (row.fecha_especifica && row.fecha_especifica === selectedDate) {
                        sameDayEffective = true;
                    }

                    if (!row.fecha_especifica && row.dia_semana) {
                        if (normalizeText(row.dia_semana) === normalizeText(dayOfSelectedDate)) {
                            if (row.semana_inicio && selectedWeekDate && getWeekStartDate(row.semana_inicio) !== selectedWeekDate) {
                                continue;
                            }
                            sameDayEffective = true;
                        }
                    }
                } else {
                    if (!row.fecha_especifica && row.dia_semana) {
                        if (normalizeText(row.dia_semana) === normalizeText(dia)) {
                            sameDayEffective = true;
                        }
                    }

                    if (row.fecha_especifica) {
                        const existingDay = getDayFromDate(row.fecha_especifica);
                        if (normalizeText(existingDay) === normalizeText(dia)) {
                            const rowWeek = row.semana_inicio ? getWeekStartDate(row.semana_inicio) : getWeekStartDate(row.fecha_especifica);
                            if (selectedWeek && rowWeek && rowWeek !== selectedWeek) {
                                continue;
                            }
                            sameDayEffective = true;
                        }
                    }
                }

                if (!sameDayEffective) {
                    continue;
                }

                if (blockStart < recordEnd && blockEnd > recordStart) {
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

            if (!keys.length) {
                resumen.innerHTML = '<div class="text-sm text-gray-500">Sin bloques seleccionados.</div>';
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

            resumen.innerHTML = `
                <div class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-600">Bloques seleccionados (${keys.length})</div>
                ${html}
            `;
        }

        function refreshBlockStates() {
            const selectedType = getSelectedTipoHorario() || 'recurrente';
            const selectedDate = fechaInput?.value;

            setSelectionMessageState();

            if (!isSelectionReady()) {
                document.querySelectorAll('.bloque-celda').forEach((button) => {
                    button.disabled = true;
                    button.classList.remove('bg-emerald-500', 'text-white', 'border-emerald-500', 'bg-red-100', 'border-red-300', 'text-red-700', 'bg-gray-100', 'border-gray-200', 'text-gray-500', 'text-gray-700', 'cursor-not-allowed', 'opacity-60');
                    button.classList.add('bg-white', 'border-gray-200', 'text-gray-700', 'cursor-not-allowed', 'opacity-50');
                    button.innerHTML = '<span class="block">Disponible</span>';
                });

                renderSummary();
                syncHiddenInputs();
                return;
            }

            if (selectedType === 'fecha_especifica') {
                sanitizeSelectionByDate();
            }

            document.querySelectorAll('.bloque-celda').forEach((button) => {
                const dia = button.dataset.dia;
                const inicio = button.dataset.inicio;
                const fin = button.dataset.fin;
                const key = button.dataset.key;
                const isSelected = selectedKeys.has(key);
                const isEffectiveDay = selectedType !== 'fecha_especifica' || !selectedDate || normalizeText(dia) === normalizeText(getDayFromDate(selectedDate));

                button.classList.remove('bg-emerald-500', 'text-white', 'border-emerald-500', 'bg-red-100', 'border-red-300', 'text-red-700', 'bg-gray-100', 'border-gray-200', 'text-gray-500', 'text-gray-700', 'cursor-not-allowed', 'opacity-60', 'opacity-50');
                button.disabled = false;
                button.innerHTML = '<span class="block">Disponible</span>';

                if (selectedType === 'fecha_especifica' && selectedDate && !isEffectiveDay) {
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

                const blocked = isEffectiveDay && isBlockedByExisting(dia, inicio, fin);

                if (blocked) {
                    button.disabled = true;
                    button.classList.add('bg-red-100', 'border-red-300', 'text-red-700', 'cursor-not-allowed', 'opacity-60');
                    button.innerHTML = '<span class="block font-bold">No disponible</span>';
                    return;
                }

                button.classList.add('bg-white', 'border-gray-200', 'text-gray-700');
                button.innerHTML = '<span class="block">Disponible</span>';
            });

            renderSummary();
            syncHiddenInputs();
        }

        document.querySelectorAll('.bloque-celda').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.disabled) return;
                const key = button.dataset.key;
                if (selectedKeys.has(key)) {
                    selectedKeys.delete(key);
                } else {
                    selectedKeys.add(key);
                }
                refreshBlockStates();
            });
        });

        tipoInputs.forEach((input) => {
            input.addEventListener('change', () => {
                updateTipoHorarioUI();
                refreshBlockStates();
            });
        });

        document.getElementById('id_calendario')?.addEventListener('change', refreshBlockStates);
        document.getElementById('id_actividad')?.addEventListener('change', refreshBlockStates);

        if (fechaInput) {
            const todayIso = new Date().toISOString().slice(0, 10);
            fechaInput.min = todayIso;
            fechaInput.addEventListener('change', function () {
                const selectedDate = fechaInput.value;
                if (!selectedDate) {
                    return;
                }

                const selected = new Date(selectedDate + 'T00:00:00');
                const today = new Date(todayIso + 'T00:00:00');
                if (selected < today || selected.getDay() === 0 || selected.getDay() === 6) {
                    fechaInput.value = nextValidSpecificDate(todayIso);
                }
                refreshBlockStates();
            });
        }

        if (espacioSelect) {
            espacioSelect.addEventListener('change', refreshBlockStates);
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
                selectedKeys.clear();
                refreshBlockStates();
            });
        }

        const form = document.querySelector('form[action*="horarios"]');
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

        updateTipoHorarioUI();
        updateJornadaView();
        refreshBlockStates();
    });
</script>
