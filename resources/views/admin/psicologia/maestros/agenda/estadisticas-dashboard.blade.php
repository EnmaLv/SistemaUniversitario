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
    @include('components.alert')

    @if (session('error'))
        <div
            class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-lg"></i>
            <span><strong
                    class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.psicologia.maestros.agenda.index', ['psicologo_id' => $psicologoId]) }}"
                class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 flex items-center justify-center transition-all shadow-sm">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                    Panel Estadístico
                </h1>
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    Análisis interactivo de citas y pacientes en <strong
                        class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">Salud Mental</strong>.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
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
                        <button @click="selected = key; open = false; window.dashboardApp.cambiarFiltro(key);"
                            class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full"
                            :class="selected === key ?
                                'bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/40 text-{{ $themeColor }}-600' :
                                ''">
                            <div class="w-2 h-2 rounded-full"
                                :class="selected === key ? 'bg-{{ $themeColor }}-600' : 'bg-gray-300 dark:bg-gray-600'">
                            </div>
                            <span class="text-xs font-bold" style="color: var(--text-main);"
                                x-text="labels[key]"></span>
                        </button>
                    </template>
                    <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>
                    <button
                        @click="selected = 'personalizado'; open = false; document.getElementById('customDateModal').classList.remove('hidden'); document.getElementById('customDateModal').classList.add('flex');"
                        class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                        <i class="fas fa-sliders-h text-xs text-gray-400"></i>
                        <span class="text-xs font-bold" style="color: var(--text-main);">Rango Personalizado</span>
                    </button>
                </div>
            </div>

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
                    <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'completo');"
                        class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                        <div
                            class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-file-pdf text-xs"></i></div>
                        <div class="flex flex-col"><span class="text-xs font-bold"
                                style="color: var(--text-main);">Descargar PDF</span><span
                                class="text-[10px] text-gray-400">Todos los datos</span></div>
                    </button>
                    <button @click="openExport = false; window.dashboardApp.exportar('word', 'completo');"
                        class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                        <div
                            class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-file-word text-xs"></i></div>
                        <div class="flex flex-col"><span class="text-xs font-bold"
                                style="color: var(--text-main);">Descargar Word</span><span
                                class="text-[10px] text-gray-400">Todos los datos</span></div>
                    </button>
                    <button @click="openExport = false; window.dashboardApp.exportar('excel', 'completo');"
                        class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                        <div
                            class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-file-excel text-xs"></i></div>
                        <div class="flex flex-col"><span class="text-xs font-bold"
                                style="color: var(--text-main);">Descargar Excel</span><span
                                class="text-[10px] text-gray-400">Tabla de datos</span></div>
                    </button>
                    <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>
                    <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'citas_estados');"
                        class="flex items-center gap-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl text-xs font-medium"
                        style="color: var(--text-main);">
                        <i class="fas fa-calendar-check text-sky-500 w-4 text-center"></i> Citas y Estados
                    </button>
                    <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'demografico');"
                        class="flex items-center gap-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl text-xs font-medium"
                        style="color: var(--text-main);">
                        <i class="fas fa-users text-indigo-500 w-4 text-center"></i> Demográfico
                    </button>
                    <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'operativo');"
                        class="flex items-center gap-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl text-xs font-medium"
                        style="color: var(--text-main);">
                        <i class="fas fa-chart-bar text-amber-500 w-4 text-center"></i> Métricas Operativas
                    </button>
                    <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'clinico');"
                        class="flex items-center gap-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl text-xs font-medium"
                        style="color: var(--text-main);">
                        <i class="fas fa-heartbeat text-emerald-500 w-4 text-center"></i> Clínico y Seguimiento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="periodoIndicador" style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="mb-6 rounded-2xl border p-4 shadow-sm flex items-center gap-4">
        <div
            class="w-10 h-10 bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 rounded-xl flex items-center justify-center shrink-0 font-bold">
            <i class="fas fa-calendar-day text-base"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 uppercase tracking-wider"
                id="periodoLabel">
                Mostrando datos del período (Mensual)
            </p>
            <p class="text-sm font-bold" style="color: var(--text-main);" id="periodoTexto">
                {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} —
                {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            </p>
        </div>
        <div class="ml-auto" id="loadingSpinner" style="display: none;">
            <div
                class="w-5 h-5 border-2 border-{{ $themeColor }}-200 border-t-{{ $themeColor }}-600 rounded-full animate-spin">
            </div>
        </div>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 rounded-2xl border shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
            <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Estado Cita</label>
            <select id="filterEstado" onchange="window.dashboardApp.recargar()"
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
            <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Avance
                Sesión</label>
            <select id="filterAvance" onchange="window.dashboardApp.recargar()"
                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
                <option value="">Todos</option>
                @foreach ($avances as $avance)
                    <option value="{{ $avance->id_avs }}">{{ $avance->nombre_avs }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Estado
                Ánimo</label>
            <select id="filterEstadoAnimo" onchange="window.dashboardApp.recargar()"
                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
                <option value="">Todos</option>
                @foreach ($estados_animo as $animo)
                    <option value="{{ $animo->id_esa }}">{{ $animo->valor_esa }} - {{ $animo->nombre_esa }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Prioridad</label>
            <select id="filterPrioridad" onchange="window.dashboardApp.recargar()"
                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
                <option value="">Todas</option>
                @foreach ($prioridades as $prioridad)
                    <option value="{{ $prioridad->nombre_pat }}">{{ $prioridad->nombre_pat }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Rol Inst.</label>
            <select id="filterPerfilAcademico" onchange="window.dashboardApp.recargar()"
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
            <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">PNF /
                Carrera</label>
            <select id="filterPnf" onchange="window.dashboardApp.recargar()"
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
                <option value="SEGURIDAD_ALIMENTARIA">Seguridad alimentaria y Cultura Nutricional</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8" id="kpiCards">
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-4 rounded-2xl border shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Total Citas</p>
            <p class="text-2xl font-black" style="color: var(--text-main);" id="kpiTotalCitas">—</p>
        </div>
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-4 rounded-2xl border shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Pacientes</p>
            <p class="text-2xl font-black" style="color: var(--text-main);" id="kpiPacientes">—</p>
        </div>
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-4 rounded-2xl border shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Asistencia</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400" id="kpiAsistencia">—</p>
        </div>
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-4 rounded-2xl border shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Hora Pico</p>
            <p class="text-lg font-black text-amber-600 dark:text-amber-400" id="kpiHoraPico">—</p>
        </div>
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-4 rounded-2xl border shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Citas/Semana</p>
            <p class="text-2xl font-black text-sky-600 dark:text-sky-400" id="kpiSemanal">—</p>
        </div>
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-4 rounded-2xl border shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">vs. Período Ant.</p>
            <p class="text-2xl font-black" id="kpiComparativa">—</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-6 rounded-2xl border shadow-sm">
            <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Tendencia
                Semanal de Pacientes</h4>
            <div class="relative" style="height: 280px;">
                <canvas id="chartFlujoSemanal"></canvas>
            </div>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-6 rounded-2xl border shadow-sm">
            <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Distribución
                por Horas de Atención</h4>
            <div class="relative" style="height: 280px;">
                <canvas id="chartHoras"></canvas>
            </div>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-6 rounded-2xl border shadow-sm">
            <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Distribución
                de Edades</h4>
            <div class="relative" style="height: 280px;">
                <canvas id="chartEdades"></canvas>
            </div>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-6 rounded-2xl border shadow-sm">
            <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Género y
                Avances Clínicos</h4>
            <div class="grid grid-cols-2 gap-4 h-full">
                <div class="flex items-center justify-center" style="height: 250px;">
                    <canvas id="chartGenero"></canvas>
                </div>
                <div class="flex items-center justify-center" style="height: 250px;">
                    <canvas id="chartAvances"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-6 rounded-2xl border shadow-sm">
            <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Pacientes
                Atendidos por Prioridad</h4>
            <div class="relative" style="height: 250px;">
                <canvas id="chartPrioridades"></canvas>
            </div>
        </div>
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="p-6 rounded-2xl border shadow-sm">
            <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Pacientes
                Atendidos por Estado de Ánimo</h4>
            <div class="relative" style="height: 250px;">
                <canvas id="chartEstadosAnimo"></canvas>
            </div>
        </div>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-6 rounded-2xl border shadow-sm mb-8">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Métricas
            Detalladas</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="tablaMetricas">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider">Métrica</th>
                        <th class="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-wider text-right">Valor
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800" id="tablaMetricasBody">
                    <tr>
                        <td colspan="2" class="py-8 text-center text-gray-400 font-bold text-xs">Cargando datos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    </div>
    </div>

    <div id="customDateModal"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100]">
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-2xl w-full max-w-md mx-4 overflow-hidden p-6">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-base font-extrabold" style="color: var(--text-main);">Rango Personalizado</h4>
                <button
                    onclick="document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');"
                    class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider"
                        for="customStartDate">Fecha Inicio</label>
                    <input type="date" id="customStartDate"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2.5">
                </div>
                <div>
                    <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider"
                        for="customEndDate">Fecha Fin</label>
                    <input type="date" id="customEndDate"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2.5">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button
                    onclick="document.getElementById('customStartDate').value=''; document.getElementById('customEndDate').value=''; window.dashboardApp.cambiarFiltro('mensual'); document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');"
                    class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">Limpiar</button>
                <button
                    onclick="window.dashboardApp.aplicarPersonalizado(); document.getElementById('customDateModal').classList.add('hidden'); document.getElementById('customDateModal').classList.remove('flex');"
                    class="flex-1 px-4 py-2.5 {{ $btnClass }} text-white rounded-xl font-bold text-xs transition-all shadow-md active:scale-95">Aplicar
                    Filtro</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <script>
        window.dashboardApp = (function() {
            const psicologoId = {{ $psicologoId }};
            let currentStartDate = '{{ $fechaInicio }}';
            let currentEndDate = '{{ $fechaFin }}';
            let currentSelected = 'mensual';
            let charts = {};

            const COLORS = {
                sky:     { bg: 'rgba(14,165,233,0.15)', border: '#0ea5e9' },
                emerald: { bg: 'rgba(16,185,129,0.15)', border: '#10b981' },
                amber:   { bg: 'rgba(245,158,11,0.15)', border: '#f59e0b' },
                rose:    { bg: 'rgba(244,63,94,0.15)',   border: '#f43f5e' },
                violet:  { bg: 'rgba(139,92,246,0.15)',  border: '#8b5cf6' },
                indigo:  { bg: 'rgba(99,102,241,0.15)',  border: '#6366f1' },
                cyan:    { bg: 'rgba(6,182,212,0.15)',   border: '#06b6d4' },
            };

            const PALETTE = ['#0ea5e9','#10b981','#f59e0b','#f43f5e','#8b5cf6','#6366f1','#06b6d4','#ec4899'];

            function getFilterParams() {
                const estado = document.getElementById('filterEstado')?.value || '';
                const avance = document.getElementById('filterAvance')?.value || '';
                const animo = document.getElementById('filterEstadoAnimo')?.value || '';
                const prioridad = document.getElementById('filterPrioridad')?.value || '';
                const perfil = document.getElementById('filterPerfilAcademico')?.value || '';
                const pnf = document.getElementById('filterPnf')?.value || '';
                let params = '';
                if (estado) params += `&estado=${estado}`;
                if (avance) params += `&avance_id=${avance}`;
                if (animo) params += `&esa_id=${animo}`;
                if (prioridad) params += `&prioridad=${prioridad}`;
                if (perfil) params += `&perfil_academico=${perfil}`;
                if (pnf) params += `&pnf=${pnf}`;
                return params;
            }

            function formatDateDisplay(dateStr) {
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }

            function calcularFechas(tipo) {
                const hoy = new Date();
                let inicio;
                switch(tipo) {
                    case 'semanal':   inicio = new Date(hoy); inicio.setDate(hoy.getDate() - 7); break;
                    case 'mensual':   inicio = new Date(hoy); inicio.setDate(hoy.getDate() - 30); break;
                    case 'semestral': inicio = new Date(hoy); inicio.setMonth(hoy.getMonth() - 6); break;
                    case 'anual':     inicio = new Date(hoy); inicio.setFullYear(hoy.getFullYear() - 1); break;
                    default:          inicio = new Date(hoy); inicio.setDate(hoy.getDate() - 30);
                }
                return {
                    start: inicio.toISOString().split('T')[0],
                    end: hoy.toISOString().split('T')[0]
                };
            }

            async function fetchData(startDate, endDate) {
                document.getElementById('loadingSpinner').style.display = 'block';
                try {
                    const url = `{{ route('admin.psicologia.maestros.agenda.estadisticas') }}?format=json&psicologo_id=${psicologoId}&start_date=${startDate}&end_date=${endDate}${getFilterParams()}`;
                    const resp = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
                    if (!resp.ok) throw new Error('Error al obtener datos');
                    return await resp.json();
                } finally {
                    document.getElementById('loadingSpinner').style.display = 'none';
                }
            }

            function updateKPIs(resumen) {
                document.getElementById('kpiTotalCitas').textContent = resumen.total_citas;
                document.getElementById('kpiPacientes').textContent = resumen.total_pacientes;
                document.getElementById('kpiAsistencia').textContent = resumen.tasa_asistencia + '%';
                document.getElementById('kpiHoraPico').textContent = resumen.hora_pico || 'N/A';
                document.getElementById('kpiSemanal').textContent = resumen.promedio_semanal;

                const comp = document.getElementById('kpiComparativa');
                const val = resumen.comparativa_pacientes;
                comp.textContent = (val > 0 ? '+' : '') + val + '%';
                comp.className = 'text-2xl font-black ' + (val > 0 ? 'text-emerald-600' : (val < 0 ? 'text-rose-600' : 'text-slate-400'));
            }

            function updatePeriodoTexto(startDate, endDate) {
                document.getElementById('periodoTexto').textContent = formatDateDisplay(startDate) + ' — ' + formatDateDisplay(endDate);
                const periodNames = { semanal: 'Semanal', mensual: 'Mensual', semestral: 'Semestral', anual: 'Anual', personalizado: 'Personalizado' };
                document.getElementById('periodoLabel').textContent = 'Mostrando datos del período (' + (periodNames[currentSelected] || 'Personalizado') + ')';
            }

            function updateMetricsTable(resumen) {
                const rows = [
                    ['Total de Citas', resumen.total_citas],
                    ['Total de Pacientes Únicos', resumen.total_pacientes],
                    ['Hombres', resumen.genero?.masculino || 0],
                    ['Mujeres', resumen.genero?.femenino || 0],
                    ['Promedio de Edad', (resumen.edades?.promedio || 0) + ' años'],
                    ['Mediana de Edad', (resumen.edades?.mediana || 0) + ' años'],
                    ['Moda de Edad', (resumen.edades?.moda || 0) + ' años'],
                    ['Hora Pico (Moda)', resumen.hora_pico || 'N/A'],
                    ['Volumen Promedio Semanal', (resumen.promedio_semanal || 0) + ' citas/semana'],
                    ['Tasa de Asistencia', (resumen.tasa_asistencia || 0) + '%'],
                    ['Tiempo de Espera Promedio', (resumen.tiempo_espera_promedio || 0) + ' días'],
                    ['Comparativa vs. Período Anterior', (resumen.comparativa_pacientes > 0 ? '+' : '') + (resumen.comparativa_pacientes || 0) + '%'],
                ];

                rows.push(['<strong>ROLES INSTITUCIONALES</strong>', '']);
                if (resumen.perfil_academico) {
                    Object.entries(resumen.perfil_academico).forEach(([rol, cant]) => {
                        rows.push([rol, cant]);
                    });
                }
                rows.push(['<strong>PACIENTES POR PNF / CARRERA</strong>', '']);
                if (resumen.pnf) {
                    const pnfLabels = {
                        ADMINISTRACION: 'ADMINISTRACION',
                        MECANICA: 'MECANICA',
                        MANTENIMIENTO: 'MANTENIMIENTO',
                        ELECTRICIDAD: 'ELECTRICIDAD',
                        VETERINARIA: 'VETERINARIA',
                        INFORMATICA: 'INFORMATICA',
                        PROC_Y_DIST_DE_ALIMENTOS: 'PROC. Y DIST. DE ALIMENTOS',
                        DISTRIBUCIÓN_LOGÍSTICA: 'DISTRIBUCIÓN LOGÍSTICA',
                        AGROALIMENTACION: 'AGROALIMENTACION',
                        SEGURIDAD_ALIMENTARIA: 'SEGURIDAD ALIMENTARIA',
                        'No especificado': 'No especificado',
                        'No aplica': 'No aplica'
                    };
                    Object.entries(resumen.pnf).forEach(([pnfKey, cant]) => {
                        rows.push([pnfLabels[pnfKey] || pnfKey, cant]);
                    });
                }

                const tbody = document.getElementById('tablaMetricasBody');
                tbody.innerHTML = rows.map(([label, val]) => `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="py-3 text-sm font-medium text-slate-600 dark:text-gray-300">${label}</td>
                        <td class="py-3 text-sm font-bold text-slate-800 dark:text-white text-right">${val}</td>
                    </tr>
                `).join('');
            }

            function destroyChart(name) {
                if (charts[name]) { charts[name].destroy(); charts[name] = null; }
            }

            function buildCharts(resumen) {
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
                const tickColor = isDark ? '#9ca3af' : '#94a3b8';

                destroyChart('flujo');
                const flujoLabels = Object.keys(resumen.flujo_semanal || {}).map(k => 'Sem ' + k.split('-')[0]);
                const flujoData = Object.values(resumen.flujo_semanal || {});
                charts.flujo = new Chart(document.getElementById('chartFlujoSemanal'), {
                    type: 'line',
                    data: {
                        labels: flujoLabels,
                        datasets: [{
                            label: 'Pacientes',
                            data: flujoData,
                            borderColor: COLORS.sky.border,
                            backgroundColor: COLORS.sky.bg,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: COLORS.sky.border,
                            pointBorderWidth: 2.5,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 11 } } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                        }
                    }
                });

                destroyChart('horas');
                const horasLabels = Object.keys(resumen.distribucion_horas || {});
                const horasData = Object.values(resumen.distribucion_horas || {});
                charts.horas = new Chart(document.getElementById('chartHoras'), {
                    type: 'bar',
                    data: {
                        labels: horasLabels,
                        datasets: [{
                            label: 'Citas',
                            data: horasData,
                            backgroundColor: COLORS.emerald.bg,
                            borderColor: COLORS.emerald.border,
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 40
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 10 }, maxRotation: 0, autoSkip: false } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } }
                        }
                    }
                });

                destroyChart('edades');
                const edadLabels = Object.keys(resumen.edades?.rangos || {});
                const edadData = Object.values(resumen.edades?.rangos || {});
                const barColors = [COLORS.indigo, COLORS.sky, COLORS.emerald, COLORS.amber, COLORS.rose];
                charts.edades = new Chart(document.getElementById('chartEdades'), {
                    type: 'bar',
                    data: {
                        labels: edadLabels.map(l => l + ' años'),
                        datasets: [{
                            label: 'Pacientes',
                            data: edadData,
                            backgroundColor: barColors.map(c => c.bg),
                            borderColor: barColors.map(c => c.border),
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1, font: { weight: 'bold' } } },
                            y: { grid: { display: false }, ticks: { color: tickColor, font: { weight: 'bold', size: 12 } } }
                        }
                    }
                });

                destroyChart('genero');
                charts.genero = new Chart(document.getElementById('chartGenero'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Hombres', 'Mujeres', 'Otro'],
                        datasets: [{
                            data: [resumen.genero?.masculino || 0, resumen.genero?.femenino || 0, resumen.genero?.otro || 0],
                            backgroundColor: [COLORS.sky.border, COLORS.rose.border, COLORS.amber.border],
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 11 }, color: tickColor } }
                        }
                    }
                });

                destroyChart('avances');
                const avancesLabels = Object.keys(resumen.avances || {});
                const avancesData = Object.values(resumen.avances || {});
                charts.avances = new Chart(document.getElementById('chartAvances'), {
                    type: 'doughnut',
                    data: {
                        labels: avancesLabels,
                        datasets: [{
                            data: avancesData,
                            backgroundColor: PALETTE.slice(0, avancesLabels.length),
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 10 }, color: tickColor } }
                        }
                    }
                });

                destroyChart('prioridades');
                const prioridadesLabels = Object.keys(resumen.prioridades || {});
                const prioridadesData = Object.values(resumen.prioridades || {});
                charts.prioridades = new Chart(document.getElementById('chartPrioridades'), {
                    type: 'doughnut',
                    data: {
                        labels: prioridadesLabels,
                        datasets: [{
                            data: prioridadesData,
                            backgroundColor: PALETTE.slice(0, prioridadesLabels.length).reverse(),
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 10 }, color: tickColor } }
                        }
                    }
                });

                destroyChart('estadosAnimo');
                const estadosAnimoLabels = Object.keys(resumen.estados_animo || {});
                const estadosAnimoData = Object.values(resumen.estados_animo || {});
                charts.estadosAnimo = new Chart(document.getElementById('chartEstadosAnimo'), {
                    type: 'doughnut',
                    data: {
                        labels: estadosAnimoLabels,
                        datasets: [{
                            data: estadosAnimoData,
                            backgroundColor: PALETTE.slice(0, estadosAnimoLabels.length),
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { weight: 'bold', size: 10 }, color: tickColor } }
                        }
                    }
                });
            }

            async function loadDashboard(startDate, endDate) {
                currentStartDate = startDate;
                currentEndDate = endDate;
                updatePeriodoTexto(startDate, endDate);
                try {
                    const data = await fetchData(startDate, endDate);
                    updateKPIs(data.resumen);
                    updateMetricsTable(data.resumen);
                    buildCharts(data.resumen);
                } catch(err) {
                    console.error('Error cargando dashboard:', err);
                }
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
                if (s && e) {
                    loadDashboard(s, e);
                }
            }

            function recargar() {
                loadDashboard(currentStartDate, currentEndDate);
            }

            function exportar(formato, reportType = 'completo') {
                const url = `{{ route('admin.psicologia.maestros.agenda.estadisticas') }}?format=${formato}&report_type=${reportType}&psicologo_id=${psicologoId}&start_date=${currentStartDate}&end_date=${currentEndDate}&periodo=${currentSelected}${getFilterParams()}`;
                if (formato === 'pdf') {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                loadDashboard(currentStartDate, currentEndDate);
            });

            return { cambiarFiltro, aplicarPersonalizado, exportar, recargar };
        })();
    </script>
</x-app-layout>
