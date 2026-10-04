@php
    $fechaInicio  = $psicologiaAdminData['fechaInicio']  ?? \Carbon\Carbon::now()->subDays(30)->toDateString();
    $fechaFin     = $psicologiaAdminData['fechaFin']     ?? \Carbon\Carbon::now()->toDateString();
    $avances      = $psicologiaAdminData['avances']      ?? collect();
    $estadosAnimo = $psicologiaAdminData['estadosAnimo'] ?? collect();

    $btnClass       = 'bg-red-600 hover:bg-red-700';
    $focusRingClass = 'focus:ring-red-500/20 focus:border-red-500';
@endphp

<div class="py-2">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

        {{-- ════════════════ HEADER ════════════════ --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                    Panel Estadístico General
                </h1>
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    Vista global del módulo de Psicología · Todos los psicólogos agregados.
                </p>
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
                        style="display:none; background-color: var(--bg-card); border-color: var(--border-color);"
                        class="absolute right-0 mt-2 w-56 rounded-2xl shadow-xl border overflow-hidden z-50 p-2 space-y-1">
                        <template x-for="key in ['semanal','mensual','semestral','anual']" :key="key">
                            <button @click="selected = key; open = false; window.psicoAdminApp.cambiarFiltro(key);"
                                class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full"
                                :class="selected === key ? 'bg-red-50 dark:bg-red-950/40 text-red-600' : ''">
                                <div class="w-2 h-2 rounded-full" :class="selected === key ? 'bg-red-600' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                <span class="text-xs font-bold" style="color: var(--text-main);" x-text="labels[key]"></span>
                            </button>
                        </template>
                        <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>
                        <button @click="selected = 'personalizado'; open = false; document.getElementById('psicoAdminDateModal').classList.remove('hidden'); document.getElementById('psicoAdminDateModal').classList.add('flex');"
                            class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                            <i class="fas fa-sliders-h text-xs text-gray-400"></i>
                            <span class="text-xs font-bold" style="color: var(--text-main);">Rango Personalizado</span>
                        </button>
                    </div>
                </div>

                {{-- Exportar PDF --}}
                <button onclick="window.psicoAdminApp.exportar('pdf')"
                    style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border font-bold text-sm shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                    <i class="fas fa-file-pdf text-rose-500"></i>
                    <span>PDF</span>
                </button>
            </div>
        </div>

        {{-- ════════════════ INDICADOR DE PERÍODO ════════════════ --}}
        <div id="psicoAdminPeriodoIndicador" style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="mb-6 rounded-2xl border p-4 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 rounded-xl flex items-center justify-center shrink-0 font-bold">
                <i class="fas fa-calendar-day text-base"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-red-600 dark:text-red-400 uppercase tracking-wider" id="psicoAdminPeriodoLabel">
                    Mostrando datos del período (Mensual)
                </p>
                <p class="text-sm font-bold" style="color: var(--text-main);" id="psicoAdminPeriodoTexto">
                    {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} —
                    {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </p>
            </div>
            <div class="ml-auto" id="psicoAdminLoadingSpinner" style="display:none;">
                <div class="w-5 h-5 border-2 border-red-200 border-t-red-600 rounded-full animate-spin"></div>
            </div>
        </div>

        {{-- ════════════════ FILTROS ════════════════ --}}
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-4 rounded-2xl border shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Estado de Cita</label>
                <select id="psicoAdminFilterEstado" onchange="window.psicoAdminApp.recargar()"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="confirmada">Confirmada</option>
                    <option value="realizada">Realizada</option>
                    <option value="cancelada">Cancelada</option>
                    <option value="no_asistio">No Asistió</option>
                    <option value="rechazada">Rechazada</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Avance Sesión</label>
                <select id="psicoAdminFilterAvance" onchange="window.psicoAdminApp.recargar()"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
                    <option value="">Todos</option>
                    @foreach ($avances as $avance)
                        <option value="{{ $avance->id }}">{{ $avance->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Estado Ánimo</label>
                <select id="psicoAdminFilterAnimo" onchange="window.psicoAdminApp.recargar()"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
                    <option value="">Todos</option>
                    @foreach ($estadosAnimo as $animo)
                        <option value="{{ $animo->id }}">{{ $animo->valor }} - {{ $animo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Rol Inst.</label>
                <select id="psicoAdminFilterPerfil" onchange="window.psicoAdminApp.recargar()"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
                    <option value="">Todos</option>
                    <option value="Estudiante">Estudiante</option>
                    <option value="Profesor">Profesor</option>
                    <option value="Obrero">Obrero</option>
                    <option value="Administrativo">Administrativo</option>
                    <option value="Pre-escolar">Pre-escolar</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">PNF / Carrera</label>
                <select id="psicoAdminFilterPnf" onchange="window.psicoAdminApp.recargar()"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
                    <option value="">Todos</option>
                    <option value="ADMINISTRACION">Administración</option>
                    <option value="MECANICA">Mecánica</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                    <option value="ELECTRICIDAD">Electricidad</option>
                    <option value="VETERINARIA">Veterinaria</option>
                    <option value="INFORMATICA">Informática</option>
                    <option value="PROC_Y_DIST_DE_ALIMENTOS">PDA</option>
                    <option value="DISTRIBUCIÓN_LOGÍSTICA">Distribución y Logística</option>
                    <option value="AGROALIMENTACION">Agroalimentación</option>
                    <option value="SEGURIDAD_ALIMENTARIA">Seguridad Alimentaria</option>
                </select>
            </div>
        </div>

        {{-- ════════════════ KPIs ════════════════ --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Total Citas</p>
                <p class="text-2xl font-black" style="color: var(--text-main);" id="psicoKpiTotalCitas">—</p>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Pacientes Únicos</p>
                <p class="text-2xl font-black text-red-600 dark:text-red-400" id="psicoKpiPacientes">—</p>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Psicólogos Activos</p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400" id="psicoKpiPsicologos">—</p>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Tasa Asistencia</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400" id="psicoKpiAsistencia">—</p>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Hora Pico</p>
                <p class="text-lg font-black text-amber-600 dark:text-amber-400" id="psicoKpiHoraPico">—</p>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">vs. Período Ant.</p>
                <p class="text-2xl font-black" id="psicoKpiComparativa">—</p>
            </div>
        </div>

        {{-- ════════════════ GRÁFICOS FILA 1 ════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">
                    Citas por Psicólogo
                </h4>
                <div class="relative" style="height: 300px;"><canvas id="psicoChartPorPsicologo"></canvas></div>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">
                    Tendencia Semanal de Citas
                </h4>
                <div class="relative" style="height: 300px;"><canvas id="psicoChartFlujo"></canvas></div>
            </div>
        </div>

        {{-- ════════════════ GRÁFICOS FILA 2 ════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Citas por Estado</h4>
                <div class="relative" style="height: 250px;"><canvas id="psicoChartEstado"></canvas></div>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Distribución por Horas</h4>
                <div class="relative" style="height: 250px;"><canvas id="psicoChartHoras"></canvas></div>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Distribución de Edades</h4>
                <div class="relative" style="height: 250px;"><canvas id="psicoChartEdades"></canvas></div>
            </div>
        </div>

        {{-- ════════════════ GRÁFICOS FILA 3 ════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Género</h4>
                <div class="relative" style="height: 250px;"><canvas id="psicoChartGenero"></canvas></div>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Roles Institucionales</h4>
                <div class="relative" style="height: 250px;"><canvas id="psicoChartPerfil"></canvas></div>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Prioridades</h4>
                <div class="relative" style="height: 250px;"><canvas id="psicoChartPrioridades"></canvas></div>
            </div>
        </div>

        {{-- ════════════════ TABLA POR PSICÓLOGO ════════════════ --}}
        <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm mb-8">
            <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">
                Detalle por Psicólogo
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="psicoTablaPorPsicologo">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider">#</th>
                            <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider">Psicólogo</th>
                            <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider text-right">Citas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800" id="psicoTablaPorPsicologoBody">
                        <tr><td colspan="3" class="py-8 text-center text-gray-400 font-bold text-xs">Cargando datos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ════════════════ TABLA DE MÉTRICAS ════════════════ --}}
        <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-6 rounded-2xl border shadow-sm mb-8">
            <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">
                Métricas Detalladas
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="psicoTablaMetricas">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider">Métrica</th>
                            <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800" id="psicoTablaMetricasBody">
                        <tr><td colspan="2" class="py-8 text-center text-gray-400 font-bold text-xs">Cargando datos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ════════════════ MODAL DE RANGO PERSONALIZADO ════════════════ --}}
        <div id="psicoAdminDateModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100]">
            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-2xl w-full max-w-md mx-4 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-base font-extrabold" style="color: var(--text-main);">Rango Personalizado</h4>
                    <button onclick="document.getElementById('psicoAdminDateModal').classList.add('hidden'); document.getElementById('psicoAdminDateModal').classList.remove('flex');"
                        class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider" for="psicoAdminStartDate">Fecha Inicio</label>
                        <input type="date" id="psicoAdminStartDate"
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} px-3 py-2.5">
                    </div>
                    <div>
                        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider" for="psicoAdminEndDate">Fecha Fin</label>
                        <input type="date" id="psicoAdminEndDate"
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} px-3 py-2.5">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button onclick="document.getElementById('psicoAdminStartDate').value=''; document.getElementById('psicoAdminEndDate').value=''; window.psicoAdminApp.cambiarFiltro('mensual'); document.getElementById('psicoAdminDateModal').classList.add('hidden'); document.getElementById('psicoAdminDateModal').classList.remove('flex');"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">Limpiar</button>
                    <button onclick="window.psicoAdminApp.aplicarPersonalizado(); document.getElementById('psicoAdminDateModal').classList.add('hidden'); document.getElementById('psicoAdminDateModal').classList.remove('flex');"
                        class="flex-1 px-4 py-2.5 {{ $btnClass }} text-white rounded-xl font-bold text-xs shadow-md active:scale-95 transition-all">Aplicar Filtro</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    window.psicoAdminApp = (function () {
        const ENDPOINT = @json(route('admin.psicologia.estadisticas.generales'));
        let currentStartDate = @json($fechaInicio);
        let currentEndDate   = @json($fechaFin);
        let currentSelected  = 'mensual';
        let charts = {};

        const PALETTE = ['#dc2626','#ef4444','#f87171','#b91c1c','#991b1b','#fca5a5','#7f1d1d','#fecaca',
                         '#10b981','#0ea5e9','#6366f1','#f59e0b'];
        const COLORS = {
            red:     { bg: 'rgba(220,38,38,0.15)',  border: '#dc2626' },
            amber:   { bg: 'rgba(245,158,11,0.15)', border: '#f59e0b' },
            emerald: { bg: 'rgba(16,185,129,0.15)', border: '#10b981' },
            sky:     { bg: 'rgba(14,165,233,0.15)', border: '#0ea5e9' },
            indigo:  { bg: 'rgba(99,102,241,0.15)', border: '#6366f1' },
        };

        function getFilters() {
            const map = {
                psicoAdminFilterEstado:  'estado',
                psicoAdminFilterAvance:  'avance_id',
                psicoAdminFilterAnimo:   'estado_animo_id',
                psicoAdminFilterPerfil:  'perfil_academico',
                psicoAdminFilterPnf:     'pnf',
            };
            const params = new URLSearchParams();
            Object.entries(map).forEach(([id, key]) => {
                const v = document.getElementById(id)?.value;
                if (v) params.append(key, v);
            });
            return params.toString();
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
            const spinner = document.getElementById('psicoAdminLoadingSpinner');
            if (spinner) spinner.style.display = 'block';
            try {
                const qs = getFilters();
                const url = `${ENDPOINT}?format=json&start_date=${startDate}&end_date=${endDate}${qs ? '&' + qs : ''}`;
                const resp = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!resp.ok) throw new Error('Error al obtener datos');
                return await resp.json();
            } finally {
                if (spinner) spinner.style.display = 'none';
            }
        }

        function updateKPIs(r) {
            document.getElementById('psicoKpiTotalCitas').textContent   = r.total_citas ?? 0;
            document.getElementById('psicoKpiPacientes').textContent    = r.total_pacientes ?? 0;
            document.getElementById('psicoKpiPsicologos').textContent   = r.total_psicologos_activos ?? 0;
            document.getElementById('psicoKpiAsistencia').textContent   = (r.tasa_asistencia ?? 0) + '%';
            document.getElementById('psicoKpiHoraPico').textContent     = r.hora_pico || 'N/A';

            const comp = document.getElementById('psicoKpiComparativa');
            const val  = r.comparativa_pacientes ?? 0;
            comp.textContent = (val > 0 ? '+' : '') + val + '%';
            comp.className   = 'text-2xl font-black ' + (val > 0 ? 'text-emerald-600' : (val < 0 ? 'text-rose-600' : 'text-slate-400'));
        }

        function updatePeriodo(startDate, endDate) {
            document.getElementById('psicoAdminPeriodoTexto').textContent = formatDateDisplay(startDate) + ' — ' + formatDateDisplay(endDate);
            const names = { semanal: 'Semanal', mensual: 'Mensual', semestral: 'Semestral', anual: 'Anual', personalizado: 'Personalizado' };
            document.getElementById('psicoAdminPeriodoLabel').textContent = 'Mostrando datos del período (' + (names[currentSelected] || 'Personalizado') + ')';
        }

        function updateTablaMetricas(r) {
            const rows = [
                ['Total de Citas', r.total_citas ?? 0],
                ['Pacientes Únicos', r.total_pacientes ?? 0],
                ['Psicólogos Activos', r.total_psicologos_activos ?? 0],
                ['Hombres', r.genero?.masculino ?? 0],
                ['Mujeres', r.genero?.femenino ?? 0],
                ['Promedio de Edad', (r.edades?.promedio ?? 0) + ' años'],
                ['Mediana de Edad', (r.edades?.mediana ?? 0) + ' años'],
                ['Hora Pico', r.hora_pico || 'N/A'],
                ['Promedio Semanal', (r.promedio_semanal ?? 0) + ' citas/semana'],
                ['Tasa de Asistencia', (r.tasa_asistencia ?? 0) + '%'],
                ['Tiempo de Espera Promedio', (r.tiempo_espera_promedio ?? 0) + ' días'],
                ['Comparativa vs. Período Anterior', (r.comparativa_pacientes > 0 ? '+' : '') + (r.comparativa_pacientes ?? 0) + '%'],
            ];
            document.getElementById('psicoTablaMetricasBody').innerHTML = rows.map(([label, val]) => `
                <tr class="hover:bg-red-50/30 dark:hover:bg-red-950/10 transition-colors">
                    <td class="py-3 text-sm font-medium text-slate-600 dark:text-gray-300">${label}</td>
                    <td class="py-3 text-sm font-bold text-slate-800 dark:text-white text-right">${val}</td>
                </tr>`).join('');
        }

        function updateTablaPorPsicologo(list) {
            const tbody = document.getElementById('psicoTablaPorPsicologoBody');
            if (!Array.isArray(list) || list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="py-8 text-center text-gray-400 font-bold text-xs">Sin datos</td></tr>';
                return;
            }
            tbody.innerHTML = list.map((p, i) => `
                <tr class="hover:bg-red-50/30 dark:hover:bg-red-950/10 transition-colors">
                    <td class="py-3 text-xs font-black text-gray-400">${i + 1}</td>
                    <td class="py-3 text-sm font-medium text-slate-700 dark:text-gray-200">${p.nombre}</td>
                    <td class="py-3 text-sm font-black text-red-600 dark:text-red-400 text-right">${p.total}</td>
                </tr>`).join('');
        }

        function destroy(name) { if (charts[name]) { charts[name].destroy(); charts[name] = null; } }

        function buildCharts(r, porPsicologo) {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
            const tickColor = isDark ? '#9ca3af' : '#94a3b8';
            const baseOpts  = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };
            const donutOpts = { responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 10 }, color: tickColor } } } };

            // Citas por psicólogo
            destroy('porPsicologo');
            charts.porPsicologo = new Chart(document.getElementById('psicoChartPorPsicologo'), {
                type: 'bar',
                data: {
                    labels: porPsicologo.slice(0, 10).map(p => p.nombre),
                    datasets: [{
                        label: 'Citas',
                        data: porPsicologo.slice(0, 10).map(p => p.total),
                        backgroundColor: COLORS.red.bg,
                        borderColor: COLORS.red.border,
                        borderWidth: 2, borderRadius: 8, borderSkipped: false,
                    }]
                },
                options: { indexAxis: 'y', ...baseOpts, scales: {
                    x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } },
                    y: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 10 } } }
                }}
            });

            // Tendencia semanal
            destroy('flujo');
            charts.flujo = new Chart(document.getElementById('psicoChartFlujo'), {
                type: 'line',
                data: {
                    labels: Object.keys(r.flujo_semanal || {}).map(k => 'Sem ' + k.split('-')[0]),
                    datasets: [{
                        label: 'Citas', data: Object.values(r.flujo_semanal || {}),
                        borderColor: COLORS.sky.border, backgroundColor: COLORS.sky.bg,
                        fill: true, tension: 0.4, borderWidth: 3,
                        pointBackgroundColor: '#fff', pointBorderColor: COLORS.sky.border,
                        pointBorderWidth: 2.5, pointRadius: 5
                    }]
                },
                options: { ...baseOpts, scales: {
                    x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 11 } } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                }}
            });

            // Por estado
            destroy('estado');
            charts.estado = new Chart(document.getElementById('psicoChartEstado'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(r.por_estado || {}).map(k => k.replace('_', ' ')),
                    datasets: [{ data: Object.values(r.por_estado || {}), backgroundColor: PALETTE, borderWidth: 0, hoverOffset: 8 }]
                },
                options: donutOpts
            });

            // Horas
            destroy('horas');
            charts.horas = new Chart(document.getElementById('psicoChartHoras'), {
                type: 'bar',
                data: {
                    labels: Object.keys(r.distribucion_horas || {}),
                    datasets: [{ data: Object.values(r.distribucion_horas || {}),
                        backgroundColor: COLORS.emerald.bg, borderColor: COLORS.emerald.border,
                        borderWidth: 2, borderRadius: 8, borderSkipped: false, maxBarThickness: 40 }]
                },
                options: { ...baseOpts, scales: {
                    x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 10 } } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                }}
            });

            // Edades
            destroy('edades');
            const ageColors = [COLORS.indigo, COLORS.sky, COLORS.emerald, COLORS.amber, COLORS.red];
            charts.edades = new Chart(document.getElementById('psicoChartEdades'), {
                type: 'bar',
                data: {
                    labels: Object.keys(r.edades?.rangos || {}).map(l => l + ' años'),
                    datasets: [{ data: Object.values(r.edades?.rangos || {}),
                        backgroundColor: ageColors.map(c => c.bg), borderColor: ageColors.map(c => c.border),
                        borderWidth: 2, borderRadius: 8, borderSkipped: false }]
                },
                options: { indexAxis: 'y', ...baseOpts, scales: {
                    x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } },
                    y: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 11 } } }
                }}
            });

            // Género
            destroy('genero');
            charts.genero = new Chart(document.getElementById('psicoChartGenero'), {
                type: 'doughnut',
                data: {
                    labels: ['Hombres', 'Mujeres', 'Otro'],
                    datasets: [{ data: [r.genero?.masculino || 0, r.genero?.femenino || 0, r.genero?.otro || 0],
                        backgroundColor: ['#0ea5e9', '#f43f5e', '#f59e0b'], borderWidth: 0, hoverOffset: 8 }]
                },
                options: donutOpts
            });

            // Perfil académico
            destroy('perfil');
            charts.perfil = new Chart(document.getElementById('psicoChartPerfil'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(r.perfil_academico || {}),
                    datasets: [{ data: Object.values(r.perfil_academico || {}), backgroundColor: PALETTE, borderWidth: 0, hoverOffset: 8 }]
                },
                options: donutOpts
            });

            // Prioridades
            destroy('prioridades');
            charts.prioridades = new Chart(document.getElementById('psicoChartPrioridades'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(r.prioridades || {}),
                    datasets: [{ data: Object.values(r.prioridades || {}), backgroundColor: PALETTE, borderWidth: 0, hoverOffset: 8 }]
                },
                options: donutOpts
            });
        }

        async function loadDashboard(startDate, endDate) {
            currentStartDate = startDate;
            currentEndDate   = endDate;
            updatePeriodo(startDate, endDate);
            try {
                const data = await fetchData(startDate, endDate);
                updateKPIs(data.resumen);
                updateTablaMetricas(data.resumen);
                updateTablaPorPsicologo(data.por_psicologo);
                buildCharts(data.resumen, data.por_psicologo);
            } catch (err) { console.error('Error cargando panel general:', err); }
        }

        function cambiarFiltro(tipo) {
            currentSelected = tipo;
            const { start, end } = calcularFechas(tipo);
            loadDashboard(start, end);
        }

        function aplicarPersonalizado() {
            currentSelected = 'personalizado';
            const s = document.getElementById('psicoAdminStartDate').value;
            const e = document.getElementById('psicoAdminEndDate').value;
            if (s && e) loadDashboard(s, e);
        }

        function recargar() { loadDashboard(currentStartDate, currentEndDate); }

        function exportar(formato) {
            const qs = getFilters();
            const url = `${ENDPOINT}?format=${formato}&start_date=${currentStartDate}&end_date=${currentEndDate}&periodo=${currentSelected}${qs ? '&' + qs : ''}`;
            window.open(url, '_blank');
        }

        document.addEventListener('DOMContentLoaded', () => loadDashboard(currentStartDate, currentEndDate));

        return { cambiarFiltro, aplicarPersonalizado, recargar, exportar };
    })();
</script>