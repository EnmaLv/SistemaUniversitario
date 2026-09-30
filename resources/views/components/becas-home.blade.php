@php
    $fechaInicio = $becasData['fechaInicio'];
    $fechaFin    = $becasData['fechaFin'];
    $beneficios  = $becasData['beneficios'];
    $jornadas    = $becasData['jornadas'];
    $lapsos      = $becasData['lapsos'];

    $btnClass       = 'bg-red-600 hover:bg-red-700';
    $focusRingClass = 'focus:ring-red-500/20 focus:border-red-500';
@endphp
@include('components.alert')

{{-- ENCABEZADO --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                Panel Estadístico de Becas
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                Análisis de solicitudes, jornadas, beneficios y cupones del sistema de becas.
            </p>
        </div>
    </div>

    <div class="flex items-center gap-3 flex-wrap">
        {{-- Selector de período --}}
        <div x-data="{ open: false, selected: 'mensual', labels: { semanal: 'Últimos 7 días', mensual: 'Últimos 30 días', semestral: 'Últimos 6 meses', anual: 'Último año', personalizado: 'Personalizado' } }" class="relative z-30">
            <button @click="open = !open" @click.away="open = false"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl {{ $btnClass }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                <i class="fas fa-calendar-alt text-xs"></i>
                <span x-text="labels[selected]">Últimos 30 días</span>
                <i class="fas fa-chevron-down text-[10px] opacity-70"></i>
            </button>
            <div x-show="open" x-transition
                style="display: none; background-color: var(--bg-card); border-color: var(--border-color);"
                class="absolute right-0 mt-2 w-56 rounded-2xl shadow-xl border overflow-hidden z-50 p-2 space-y-1">
                <template x-for="key in ['semanal','mensual','semestral','anual']" :key="key">
                    <button @click="selected = key; open = false; window.becasApp.cambiarFiltro(key);"
                        class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full"
                        :class="selected === key ? 'bg-red-50 dark:bg-red-950/40 text-red-600' : ''">
                        <div class="w-2 h-2 rounded-full" :class="selected === key ? 'bg-red-600' : 'bg-gray-300 dark:bg-gray-600'"></div>
                        <span class="text-xs font-bold" style="color: var(--text-main);" x-text="labels[key]"></span>
                    </button>
                </template>
                <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>
                <button @click="selected = 'personalizado'; open = false; document.getElementById('customDateModal').classList.remove('hidden'); document.getElementById('customDateModal').classList.add('flex');"
                    class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                    <i class="fas fa-sliders-h text-xs text-gray-400"></i>
                    <span class="text-xs font-bold" style="color: var(--text-main);">Rango Personalizado</span>
                </button>
            </div>
        </div>

        {{-- Exportar --}}
        <div x-data="{ openExport: false }" class="relative z-20">
            <button @click="openExport = !openExport" @click.away="openExport = false"
                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border font-bold text-sm shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                <i class="fas fa-file-export text-xs"></i>
                <span>Exportar</span>
                <i class="fas fa-chevron-down text-[10px] opacity-70"></i>
            </button>
            <div x-show="openExport" x-transition
                style="display: none; background-color: var(--bg-card); border-color: var(--border-color);"
                class="absolute right-0 mt-2 w-64 rounded-2xl shadow-xl border overflow-hidden z-50 p-2 space-y-1">
                <button @click="openExport = false; window.becasApp.exportar('pdf');"
                    class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                    <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-file-pdf text-xs"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold" style="color: var(--text-main);">Descargar PDF</span>
                        <span class="text-[10px] text-gray-400">Reporte completo</span>
                    </div>
                </button>
                <button @click="openExport = false; window.becasApp.exportar('word');"
                    class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                    <div class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-950/50 text-red-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-file-word text-xs"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold" style="color: var(--text-main);">Descargar Word</span>
                        <span class="text-[10px] text-gray-400">Reporte completo</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- INDICADOR DE PERÍODO --}}
<div id="periodoIndicador" style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="mb-6 rounded-2xl border p-4 shadow-sm flex items-center gap-4">
    <div class="w-10 h-10 bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 rounded-xl flex items-center justify-center shrink-0 font-bold">
        <i class="fas fa-calendar-day text-base"></i>
    </div>
    <div>
        <p class="text-[10px] font-black text-red-600 dark:text-red-400 uppercase tracking-wider" id="periodoLabel">
            Mostrando datos del período (Mensual)
        </p>
        <p class="text-sm font-bold" style="color: var(--text-main);" id="periodoTexto">
            {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} —
            {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
        </p>
    </div>
    <div class="ml-auto" id="loadingSpinner" style="display: none;">
        <div class="w-5 h-5 border-2 border-red-200 border-t-red-600 rounded-full animate-spin"></div>
    </div>
</div>

{{-- FILTROS --}}
<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="p-4 rounded-2xl border shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
    <div>
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Beneficio</label>
        <select id="filterBeneficio" onchange="window.becasApp.recargar()"
            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
            <option value="">Todos</option>
            @foreach ($beneficios as $b)
                <option value="{{ $b->id }}">{{ $b->nombre_beneficio }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Jornada</label>
        <select id="filterJornada" onchange="window.becasApp.recargar()"
            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
            <option value="">Todas</option>
            @foreach ($jornadas as $j)
                <option value="{{ $j->id }}">{{ $j->nombre_jornada }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Estado</label>
        <select id="filterEstado" onchange="window.becasApp.recargar()"
            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
            <option value="">Todos</option>
            <option value="0">Pendiente</option>
            <option value="1">Aprobado</option>
            <option value="2">Rechazado</option>
        </select>
    </div>
    <div>
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Tipo</label>
        <select id="filterTipo" onchange="window.becasApp.recargar()"
            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
            <option value="">Todos</option>
            <option value="nueva">Nueva</option>
            <option value="renovacion">Renovación</option>
        </select>
    </div>
    <div>
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Lapso</label>
        <select id="filterLapso" onchange="window.becasApp.recargar()"
            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
            <option value="">Todos</option>
            @foreach ($lapsos as $l)
                <option value="{{ $l->id }}">{{ $l->codigo }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8" id="kpiCards">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Solicitudes</p>
        <p class="text-2xl font-black" style="color: var(--text-main);" id="kpiTotal">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Pendientes</p>
        <p class="text-2xl font-black text-amber-600 dark:text-amber-400" id="kpiPendientes">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Aprobadas</p>
        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400" id="kpiAprobadas">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Tasa Aprobación</p>
        <p class="text-2xl font-black text-red-600 dark:text-red-400" id="kpiTasaAprob">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Jornadas Activas</p>
        <p class="text-2xl font-black" style="color: var(--text-main);" id="kpiJornadas">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Cupones Disponibles</p>
        <p class="text-2xl font-black text-sky-600 dark:text-sky-400" id="kpiCupones">—</p>
    </div>
</div>

{{-- GRÁFICOS FILA 1 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Tendencia Semanal de Solicitudes</h4>
        <div class="relative" style="height: 280px;"><canvas id="chartFlujo"></canvas></div>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Solicitudes por Día</h4>
        <div class="relative" style="height: 280px;"><canvas id="chartPorDia"></canvas></div>
    </div>
</div>

{{-- GRÁFICOS FILA 2 --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Solicitudes por Estado</h4>
        <div class="relative" style="height: 250px;"><canvas id="chartEstado"></canvas></div>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Solicitudes por Tipo</h4>
        <div class="relative" style="height: 250px;"><canvas id="chartTipo"></canvas></div>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Solicitudes por Lapso</h4>
        <div class="relative" style="height: 250px;"><canvas id="chartLapso"></canvas></div>
    </div>
</div>

{{-- GRÁFICOS FILA 3 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Top Beneficios con Más Solicitudes</h4>
        <div class="relative" style="height: 280px;"><canvas id="chartBeneficios"></canvas></div>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Top Jornadas con Más Solicitudes</h4>
        <div class="relative" style="height: 280px;"><canvas id="chartJornadas"></canvas></div>
    </div>
</div>

{{-- GRÁFICOS FILA 4 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Cupones por Beneficio</h4>
        <div class="relative" style="height: 280px;"><canvas id="chartCupones"></canvas></div>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Top Verificadores</h4>
        <div class="relative" style="height: 280px;"><canvas id="chartVerificadores"></canvas></div>
    </div>
</div>

{{-- TABLA DETALLADA --}}
<div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm mb-8">
    <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Métricas Detalladas</h4>
    <div class="overflow-x-auto">
        <table class="w-full text-left" id="tablaMetricas">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider">Métrica</th>
                    <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider text-right">Valor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800" id="tablaMetricasBody">
                <tr><td colspan="2" class="py-8 text-center text-gray-400 font-bold text-xs">Cargando datos...</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL RANGO PERSONALIZADO --}}
<div id="customDateModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100]">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-2xl w-full max-w-md mx-4 overflow-hidden p-6">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-base font-extrabold" style="color: var(--text-main);">Rango Personalizado</h4>
            <button onclick="document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');"
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider" for="customStartDate">Fecha Inicio</label>
                <input type="date" id="customStartDate"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2.5">
            </div>
            <div>
                <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider" for="customEndDate">Fecha Fin</label>
                <input type="date" id="customEndDate"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2.5">
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button onclick="document.getElementById('customStartDate').value=''; document.getElementById('customEndDate').value=''; window.becasApp.cambiarFiltro('mensual'); document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');"
                class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">Limpiar</button>
            <button onclick="window.becasApp.aplicarPersonalizado(); document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');"
                class="flex-1 px-4 py-2.5 {{ $btnClass }} text-white rounded-xl font-bold text-xs transition-all shadow-md active:scale-95">Aplicar Filtro</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>
    window.becasApp = (function () {
        let currentStartDate = '{{ $fechaInicio }}';
        let currentEndDate   = '{{ $fechaFin }}';
        let currentSelected  = 'mensual';
        let charts = {};

        const PALETTE = ['#dc2626', '#ef4444', '#f87171', '#b91c1c', '#991b1b', '#fca5a5', '#7f1d1d', '#fecaca'];

        function getFilterParams() {
            const params = new URLSearchParams();
            const map = {
                filterBeneficio: 'beneficio_id',
                filterJornada:   'jornada_id',
                filterEstado:    'estado',
                filterTipo:      'tipo_solicitud',
                filterLapso:     'lapso_id',
            };
            Object.entries(map).forEach(([elId, key]) => {
                const v = document.getElementById(elId)?.value;
                if (v !== '' && v !== null) params.append(key, v);
            });
            return params.toString() ? '&' + params.toString() : '';
        }

        function calcularFechas(tipo) {
            const hoy = new Date();
            let inicio = new Date(hoy);
            if (tipo === 'semanal')        inicio.setDate(hoy.getDate() - 7);
            else if (tipo === 'mensual')   inicio.setDate(hoy.getDate() - 30);
            else if (tipo === 'semestral') inicio.setMonth(hoy.getMonth() - 6);
            else if (tipo === 'anual')     inicio.setFullYear(hoy.getFullYear() - 1);
            else                           inicio.setDate(hoy.getDate() - 30);
            return { start: inicio.toISOString().split('T')[0], end: hoy.toISOString().split('T')[0] };
        }

        function formatDateDisplay(dateStr) {
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        async function fetchData(startDate, endDate) {
            document.getElementById('loadingSpinner').style.display = 'block';
            try {
                const url = `{{ route('admin.becas.estadisticas') }}?format=json&start_date=${startDate}&end_date=${endDate}${getFilterParams()}`;
                const resp = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!resp.ok) throw new Error('Error al obtener datos');
                return await resp.json();
            } finally {
                document.getElementById('loadingSpinner').style.display = 'none';
            }
        }

        function updateKPIs(r) {
            document.getElementById('kpiTotal').textContent       = r.total_solicitudes;
            document.getElementById('kpiPendientes').textContent  = r.pendientes;
            document.getElementById('kpiAprobadas').textContent   = r.aprobadas;
            document.getElementById('kpiTasaAprob').textContent   = r.tasa_aprobacion + '%';
            document.getElementById('kpiJornadas').textContent    = r.jornadas_activas;
            document.getElementById('kpiCupones').textContent     = r.cupones_disponibles;
        }

        function updatePeriodoTexto(startDate, endDate) {
            document.getElementById('periodoTexto').textContent = formatDateDisplay(startDate) + ' — ' + formatDateDisplay(endDate);
            const names = { semanal: 'Semanal', mensual: 'Mensual', semestral: 'Semestral', anual: 'Anual', personalizado: 'Personalizado' };
            document.getElementById('periodoLabel').textContent = 'Mostrando datos del período (' + (names[currentSelected] || 'Personalizado') + ')';
        }

        function updateMetricsTable(r) {
            const rows = [
                ['Total de Solicitudes', r.total_solicitudes],
                ['Pendientes', r.pendientes],
                ['Aprobadas', r.aprobadas],
                ['Rechazadas', r.rechazadas],
                ['Tasa de Aprobación', r.tasa_aprobacion + '%'],
                ['Tasa de Rechazo', r.tasa_rechazo + '%'],
                ['Jornadas Activas', r.jornadas_activas],
                ['Beneficios Activos', r.beneficios_activos],
                ['Cupones Disponibles', r.cupones_disponibles],
                ['Cupones Ocupados', r.cupones_ocupados],
                ['Becas Asignadas Activas', r.becas_asignadas_activas],
                ['Tiempo Promedio de Verificación', r.tiempo_promedio_verif + ' horas'],
            ];
            rows.push(['<strong>SOLICITUDES POR TIPO</strong>', '']);
            Object.entries(r.por_tipo || {}).forEach(([k, v]) => rows.push([k.charAt(0).toUpperCase() + k.slice(1), v]));
            rows.push(['<strong>CUPONES POR BENEFICIO</strong>', '']);
            Object.entries(r.cupones_por_beneficio || {}).forEach(([k, v]) => {
                rows.push([k, `${v.disponibles} disp. / ${v.ocupados} ocup.`]);
            });

            document.getElementById('tablaMetricasBody').innerHTML = rows.map(([label, val]) => `
                <tr class="hover:bg-red-50/30 dark:hover:bg-red-950/10 transition-colors">
                    <td class="py-3 text-sm font-medium text-slate-600 dark:text-gray-300">${label}</td>
                    <td class="py-3 text-sm font-bold text-slate-800 dark:text-white text-right">${val}</td>
                </tr>`).join('');
        }

        function destroy(name) { if (charts[name]) { charts[name].destroy(); charts[name] = null; } }

        function buildCharts(r) {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
            const tickColor = isDark ? '#9ca3af' : '#94a3b8';
            const baseOpts  = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };

            // 1. Flujo semanal
            destroy('flujo');
            charts.flujo = new Chart(document.getElementById('chartFlujo'), {
                type: 'line',
                data: {
                    labels: Object.keys(r.flujo_semanal || {}).map(k => 'Sem ' + k.split('-')[0]),
                    datasets: [{
                        label: 'Solicitudes',
                        data: Object.values(r.flujo_semanal || {}),
                        borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.15)',
                        fill: true, tension: 0.4, borderWidth: 3,
                        pointBackgroundColor: '#fff', pointBorderColor: '#dc2626', pointBorderWidth: 2.5,
                        pointRadius: 5, pointHoverRadius: 7
                    }]
                },
                options: { ...baseOpts, scales: {
                    x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 11 } } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                }}
            });

            // 2. Por día
            destroy('porDia');
            const diaLabels = Object.keys(r.por_dia || {}).map(d => {
                const dt = new Date(d + 'T00:00:00');
                return dt.toLocaleDateString('es-VE', { day: '2-digit', month: 'short' });
            });
            charts.porDia = new Chart(document.getElementById('chartPorDia'), {
                type: 'line',
                data: {
                    labels: diaLabels,
                    datasets: [{
                        label: 'Solicitudes', data: Object.values(r.por_dia || {}),
                        borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.15)',
                        fill: true, tension: 0.35, borderWidth: 2.5,
                        pointBackgroundColor: '#fff', pointBorderColor: '#dc2626', pointBorderWidth: 2, pointRadius: 4
                    }]
                },
                options: { ...baseOpts, scales: {
                    x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 }, maxRotation: 45 } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, font: { weight: 'bold' } } }
                }}
            });

            // 3. Estado
            destroy('estado');
            charts.estado = new Chart(document.getElementById('chartEstado'), {
                type: 'doughnut',
                data: {
                    labels: ['Pendientes', 'Aprobadas', 'Rechazadas'],
                    datasets: [{
                        data: [r.pendientes, r.aprobadas, r.rechazadas],
                        backgroundColor: ['#f59e0b', '#10b981', '#dc2626'],
                        borderWidth: 0, hoverOffset: 8
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 11 }, color: tickColor } } }
                }
            });

            // 4. Tipo
            destroy('tipo');
            charts.tipo = new Chart(document.getElementById('chartTipo'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(r.por_tipo || {}).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
                    datasets: [{ data: Object.values(r.por_tipo || {}), backgroundColor: PALETTE, borderWidth: 0, hoverOffset: 8 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 11 }, color: tickColor } } }
                }
            });

            // 5. Lapso
            destroy('lapso');
            charts.lapso = new Chart(document.getElementById('chartLapso'), {
                type: 'bar',
                data: {
                    labels: Object.keys(r.por_lapso || {}),
                    datasets: [{
                        label: 'Solicitudes', data: Object.values(r.por_lapso || {}),
                        backgroundColor: 'rgba(220,38,38,0.15)', borderColor: '#dc2626',
                        borderWidth: 2, borderRadius: 8, borderSkipped: false, maxBarThickness: 40
                    }]
                },
                options: { ...baseOpts, scales: {
                    x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 10 } } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                }}
            });

            // 6. Beneficios
            destroy('beneficios');
            charts.beneficios = new Chart(document.getElementById('chartBeneficios'), {
                type: 'bar',
                data: {
                    labels: Object.keys(r.por_beneficio || {}).slice(0, 8),
                    datasets: [{
                        label: 'Solicitudes', data: Object.values(r.por_beneficio || {}).slice(0, 8),
                        backgroundColor: 'rgba(220,38,38,0.15)', borderColor: '#dc2626',
                        borderWidth: 2, borderRadius: 8, borderSkipped: false
                    }]
                },
                options: { indexAxis: 'y', ...baseOpts, scales: {
                    x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } },
                    y: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 11 } } }
                }}
            });

            // 7. Jornadas
            destroy('jornadas');
            charts.jornadas = new Chart(document.getElementById('chartJornadas'), {
                type: 'bar',
                data: {
                    labels: Object.keys(r.por_jornada || {}).slice(0, 8),
                    datasets: [{
                        label: 'Solicitudes', data: Object.values(r.por_jornada || {}).slice(0, 8),
                        backgroundColor: 'rgba(245,158,11,0.15)', borderColor: '#f59e0b',
                        borderWidth: 2, borderRadius: 8, borderSkipped: false
                    }]
                },
                options: { indexAxis: 'y', ...baseOpts, scales: {
                    x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } },
                    y: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 11 } } }
                }}
            });

            // 8. Cupones por beneficio
            destroy('cupones');
            const cuponesData = r.cupones_por_beneficio || {};
            charts.cupones = new Chart(document.getElementById('chartCupones'), {
                type: 'bar',
                data: {
                    labels: Object.keys(cuponesData).slice(0, 8),
                    datasets: [
                        {
                            label: 'Disponibles',
                            data: Object.values(cuponesData).slice(0, 8).map(v => v.disponibles),
                            backgroundColor: 'rgba(16,185,129,0.5)', borderColor: '#10b981',
                            borderWidth: 2, borderRadius: 8, borderSkipped: false
                        },
                        {
                            label: 'Ocupados',
                            data: Object.values(cuponesData).slice(0, 8).map(v => v.ocupados),
                            backgroundColor: 'rgba(220,38,38,0.5)', borderColor: '#dc2626',
                            borderWidth: 2, borderRadius: 8, borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 11 }, color: tickColor } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 10 }, maxRotation: 30 } },
                        y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                    }
                }
            });

            // 9. Verificadores
            destroy('verificadores');
            charts.verificadores = new Chart(document.getElementById('chartVerificadores'), {
                type: 'bar',
                data: {
                    labels: Object.keys(r.top_verificadores || {}).slice(0, 8),
                    datasets: [{
                        label: 'Solicitudes procesadas', data: Object.values(r.top_verificadores || {}).slice(0, 8),
                        backgroundColor: 'rgba(127,29,29,0.15)', borderColor: '#7f1d1d',
                        borderWidth: 2, borderRadius: 8, borderSkipped: false
                    }]
                },
                options: { indexAxis: 'y', ...baseOpts, scales: {
                    x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } },
                    y: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 11 } } }
                }}
            });
        }

        async function loadDashboard(startDate, endDate) {
            currentStartDate = startDate;
            currentEndDate   = endDate;
            updatePeriodoTexto(startDate, endDate);
            try {
                const data = await fetchData(startDate, endDate);
                updateKPIs(data.resumen);
                updateMetricsTable(data.resumen);
                buildCharts(data.resumen);
            } catch (err) { console.error('Error cargando dashboard becas:', err); }
        }

        function cambiarFiltro(tipo) {
            currentSelected = tipo;
            const { start, end } = calcularFechas(tipo);
            loadDashboard(start, end);
        }

        function aplicarPersonalizado() {
            currentSelected = 'personalizado';
            const s = document.getElementById('customStartDate').value;
            const e = document.getElementById('customEndDate').value;
            if (s && e) loadDashboard(s, e);
        }

        function recargar() { loadDashboard(currentStartDate, currentEndDate); }

        function exportar(formato) {
            const url = `{{ route('admin.becas.estadisticas') }}?format=${formato}&start_date=${currentStartDate}&end_date=${currentEndDate}&periodo=${currentSelected}${getFilterParams()}`;
            if (formato === 'pdf') window.open(url, '_blank');
            else window.location.href = url;
        }

        document.addEventListener('DOMContentLoaded', () => loadDashboard(currentStartDate, currentEndDate));

        return { cambiarFiltro, aplicarPersonalizado, exportar, recargar };
    })();
</script>