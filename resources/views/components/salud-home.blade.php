@php
    $fechaInicio = $saludData['fechaInicio'];
    $fechaFin = $saludData['fechaFin'];
    $medicoId = $saludData['medicoId'];
    $consultorios = $saludData['consultorios'];
    $medicos = $saludData['medicos'];

    $themeColor = 'sky';
    $btnClass = 'bg-sky-600 hover:bg-sky-700';
    $focusRingClass = 'focus:ring-sky-500/20 focus:border-sky-500';
@endphp
@include('components.alert')

{{-- ENCABEZADO --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                Panel Estadístico de Salud
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                Análisis interactivo de consultas, recetas y dispensaciones.
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
                    <button @click="selected = key; open = false; window.dashboardApp.cambiarFiltro(key);"
                        class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full"
                        :class="selected === key ?
                            'bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/40 text-{{ $themeColor }}-600' :
                            ''">
                        <div class="w-2 h-2 rounded-full"
                            :class="selected === key ? 'bg-{{ $themeColor }}-600' : 'bg-gray-300 dark:bg-gray-600'">
                        </div>
                        <span class="text-xs font-bold" style="color: var(--text-main);" x-text="labels[key]"></span>
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
                <button @click="openExport = false; window.dashboardApp.exportar('pdf', 'completo');"
                    class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                    <div
                        class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-file-pdf text-xs"></i>
                    </div>
                    <div class="flex flex-col"><span class="text-xs font-bold"
                            style="color: var(--text-main);">Descargar PDF</span><span
                            class="text-[10px] text-gray-400">Todos los datos</span></div>
                </button>
                <button @click="openExport = false; window.dashboardApp.exportar('word', 'completo');"
                    class="flex items-center gap-3 p-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors text-left w-full">
                    <div
                        class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-file-word text-xs"></i>
                    </div>
                    <div class="flex flex-col"><span class="text-xs font-bold"
                            style="color: var(--text-main);">Descargar Word</span><span
                            class="text-[10px] text-gray-400">Todos los datos</span></div>
                </button>
                
            </div>
        </div>
    </div>
</div>

{{-- INDICADOR DE PERÍODO --}}
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

{{-- FILTROS --}}
<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="p-4 rounded-2xl border shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
    <div>
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Consultorio</label>
        <select id="filterConsultorio" onchange="window.dashboardApp.recargar()"
            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
            <option value="">Todos</option>
            @foreach ($consultorios as $c)
                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Médico</label>
        <select id="filterMedico" onchange="window.dashboardApp.recargar()"
            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
            <option value="">Todos</option>
            @foreach ($medicos as $m)
                <option value="{{ $m->id_persona }}">
                    {{ $m->nombre_completo }} — {{ $m->nombre_rol }}
                </option>
            @endforeach
        </select>
    </div>
    <div x-data="enfermedadFilter()" class="relative">
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Enfermedades</label>

        {{-- Badges de seleccionadas --}}
        <div x-show="seleccionadas.length > 0" x-cloak class="flex flex-wrap gap-1.5 mb-1.5">
            <template x-for="item in seleccionadas" :key="item.id">
                <span
                    class="inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded-lg text-[10px] font-semibold border border-sky-300 dark:border-sky-700 bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300">
                    <span x-text="item.nombre" class="max-w-[120px] truncate"></span>
                    <button type="button" @click="remover(item.id)"
                        class="text-sky-500 hover:text-rose-500 rounded p-0.5 transition-colors">
                        <i class="fas fa-times text-[9px]"></i>
                    </button>
                </span>
            </template>
        </div>

        {{-- Input --}}
        <div class="relative">
            <input type="text" x-model="search" @input.debounce.300ms="buscar()"
                @focus="if (search.length >= 2) buscar()" @click.outside="open = false"
                @keydown.escape="open = false" @keydown.enter.prevent="buscar()" placeholder="Buscar enfermedad..."
                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all pl-3 pr-8 py-2">

            <button type="button" x-show="seleccionadas.length > 0" x-cloak @click="limpiar()"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 hover:text-rose-500 flex items-center justify-center transition-colors"
                title="Quitar todas">
                <i class="fas fa-times text-[9px]"></i>
            </button>
        </div>

        {{-- Dropdown --}}
        <div x-show="open && resultados.length > 0" x-cloak
            class="absolute z-50 left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl shadow-xl border py-1"
            style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);">
            <template x-for="item in resultados" :key="item.id">
                <div @click="seleccionar(item)"
                    class="px-3 py-2 text-xs cursor-pointer flex justify-between items-center hover:bg-emerald-sky-50 dark:hover:bg-sky-950/40 transition-colors">
                    <span class="font-medium truncate mr-2" x-text="item.nombre"></span>
                    <span class="text-[10px] text-gray-400 font-mono shrink-0" x-text="item.codigo || ''"></span>
                </div>
            </template>
        </div>

        <div x-show="open && resultados.length === 0 && search.length >= 2" x-cloak
            class="absolute z-50 left-0 right-0 mt-1 rounded-xl shadow-xl border px-3 py-2.5 text-xs text-gray-400"
            style="background-color: var(--bg-card); border-color: var(--border-color);">
            No se encontraron enfermedades
        </div>
    </div>
    <div>
        <label class="block mb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider">Estado
            Receta</label>
        <select id="filterEstadoReceta" onchange="window.dashboardApp.recargar()"
            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
            class="w-full rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all px-3 py-2">
            <option value="">Todos</option>
            <option value="con_receta">Con receta</option>
            <option value="sin_receta">Sin receta</option>
            <option value="completado">Completado</option>
            <option value="sin_dispensacion">Sin dispensación</option>
            <option value="con_pendientes">Con pendientes</option>
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
            <option value="SEGURIDAD_ALIMENTARIA">Seguridad Alimentaria</option>
        </select>
    </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8" id="kpiCards">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Total Consultas</p>
        <p class="text-2xl font-black" style="color: var(--text-main);" id="kpiTotalConsultas">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Pacientes</p>
        <p class="text-2xl font-black" style="color: var(--text-main);" id="kpiPacientes">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Recetas</p>
        <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400" id="kpiRecetas">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Med. Dispensados</p>
        <p class="text-2xl font-black text-amber-600 dark:text-amber-400" id="kpiDispensados">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Hora Pico</p>
        <p class="text-lg font-black text-sky-600 dark:text-sky-400" id="kpiHoraPico">—</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">vs. Período Ant.</p>
        <p class="text-2xl font-black" id="kpiComparativa">—</p>
    </div>
</div>

{{-- GRÁFICOS FILA 1 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Tendencia
            Semanal de Consultas</h4>
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
            Estado de Recetas</h4>
        <div class="grid grid-cols-2 gap-4 h-full">
            <div class="flex items-center justify-center" style="height: 250px;">
                <canvas id="chartGenero"></canvas>
            </div>
            <div class="flex items-center justify-center" style="height: 250px;">
                <canvas id="chartEstadoRecetas"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- GRÁFICOS FILA 2 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Enfermedades
            Más Frecuentes</h4>
        <div class="relative" style="height: 280px;">
            <canvas id="chartEnfermedades"></canvas>
        </div>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Top Productos
            Dispensados</h4>
        <div class="relative" style="height: 280px;">
            <canvas id="chartProductos"></canvas>
        </div>
    </div>
</div>

{{-- GRÁFICOS FILA 3 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Consultas por
            Médico</h4>
        <div class="relative" style="height: 280px;">
            <canvas id="chartMedicos"></canvas>
        </div>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-6 rounded-2xl border shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider mb-4 text-gray-500 dark:text-gray-400">Consultas por
            Consultorio</h4>
        <div class="relative" style="height: 280px;">
            <canvas id="chartConsultorios"></canvas>
        </div>
    </div>
</div>

{{-- TABLA DETALLADA --}}
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

{{-- MODAL RANGO PERSONALIZADO --}}
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
    document.addEventListener('alpine:init', () => {
        Alpine.data('enfermedadFilter', () => ({
            search: '',
            open: false,
            resultados: [],
            seleccionadas: [],

            async buscar() {
                const q = this.search.trim();
                if (q.length < 2) {
                    this.resultados = [];
                    this.open = false;
                    return;
                }
                try {
                    const resp = await fetch(
                        `{{ route('admin.salud.movimientos.consultas.buscar-enfermedades') }}?q=${encodeURIComponent(q)}`
                    );
                    const data = await resp.json();
                    // Excluye las ya seleccionadas
                    this.resultados = data.filter(e =>
                        !this.seleccionadas.some(s => String(s.id) === String(e.id))
                    );
                    this.open = true;
                } catch (e) {
                    console.error('Error al buscar enfermedades:', e);
                }
            },

            seleccionar(item) {
                if (!this.seleccionadas.some(s => String(s.id) === String(item.id))) {
                    this.seleccionadas.push(item);
                    window.dashboardApp.setEnfermedades(this.seleccionadas.map(s => s.id));
                }
                this.search = '';
                this.resultados = [];
                this.open = false;
            },

            remover(id) {
                this.seleccionadas = this.seleccionadas.filter(s => String(s.id) !== String(id));
                window.dashboardApp.setEnfermedades(this.seleccionadas.map(s => s.id));
            },

            limpiar() {
                this.seleccionadas = [];
                this.search = '';
                this.resultados = [];
                this.open = false;
                window.dashboardApp.setEnfermedades([]);
            }
        }));
    });

    window.dashboardApp = (function() {
        const medicoDefault = {{ $medicoId ?? 'null' }};
        let currentStartDate = '{{ $fechaInicio }}';
        let currentEndDate = '{{ $fechaFin }}';
        let currentSelected = 'mensual';
        let charts = {};
        let enfermedadIds = [];

        const COLORS = {
            sky: {
                bg: 'rgba(14,165,233,0.15)',
                border: '#0ea5e9'
            },
            emerald: {
                bg: 'rgba(16,185,129,0.15)',
                border: '#10b981'
            },
            amber: {
                bg: 'rgba(245,158,11,0.15)',
                border: '#f59e0b'
            },
            rose: {
                bg: 'rgba(244,63,94,0.15)',
                border: '#f43f5e'
            },
            violet: {
                bg: 'rgba(139,92,246,0.15)',
                border: '#8b5cf6'
            },
            indigo: {
                bg: 'rgba(99,102,241,0.15)',
                border: '#6366f1'
            },
            cyan: {
                bg: 'rgba(6,182,212,0.15)',
                border: '#06b6d4'
            },
        };

        const PALETTE = ['#0ea5e9', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#6366f1', '#06b6d4', '#ec4899',
            '#84cc16', '#f97316'
        ];

        function setEnfermedades(ids) {
            enfermedadIds = Array.isArray(ids) ? ids : [];
            recargar();
        }

        function getFilterParams() {
            const params = new URLSearchParams();
            const map = {
                filterConsultorio: 'consultorio_id',
                filterMedico: 'medico_id',
                filterEstadoReceta: 'estado_receta',
                filterPerfilAcademico: 'perfil_academico',
                filterPnf: 'pnf',
            };
            Object.entries(map).forEach(([elId, key]) => {
                const v = document.getElementById(elId)?.value;
                if (v) params.append(key, v);
            });

            enfermedadIds.forEach(id => params.append('enfermedad_ids[]', id));

            return params.toString() ? '&' + params.toString() : '';
        }

        function formatDateDisplay(dateStr) {
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('es-VE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function calcularFechas(tipo) {
            const hoy = new Date();
            let inicio;
            switch (tipo) {
                case 'semanal':
                    inicio = new Date(hoy);
                    inicio.setDate(hoy.getDate() - 7);
                    break;
                case 'mensual':
                    inicio = new Date(hoy);
                    inicio.setDate(hoy.getDate() - 30);
                    break;
                case 'semestral':
                    inicio = new Date(hoy);
                    inicio.setMonth(hoy.getMonth() - 6);
                    break;
                case 'anual':
                    inicio = new Date(hoy);
                    inicio.setFullYear(hoy.getFullYear() - 1);
                    break;
                default:
                    inicio = new Date(hoy);
                    inicio.setDate(hoy.getDate() - 30);
            }
            return {
                start: inicio.toISOString().split('T')[0],
                end: hoy.toISOString().split('T')[0]
            };
        }

        async function fetchData(startDate, endDate) {
            document.getElementById('loadingSpinner').style.display = 'block';
            try {
                const url =
                    `{{ route('admin.salud.movimientos.consultas.estadisticas') }}?format=json&start_date=${startDate}&end_date=${endDate}${getFilterParams()}`;
                const resp = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!resp.ok) throw new Error('Error al obtener datos');
                return await resp.json();
            } finally {
                document.getElementById('loadingSpinner').style.display = 'none';
            }
        }

        function updateKPIs(resumen) {
            document.getElementById('kpiTotalConsultas').textContent = resumen.total_consultas;
            document.getElementById('kpiPacientes').textContent = resumen.total_pacientes;
            document.getElementById('kpiRecetas').textContent = resumen.total_recetas;
            document.getElementById('kpiDispensados').textContent = resumen.total_dispensados;
            document.getElementById('kpiHoraPico').textContent = resumen.hora_pico || 'N/A';

            const comp = document.getElementById('kpiComparativa');
            const val = resumen.comparativa_consultas;
            comp.textContent = (val > 0 ? '+' : '') + val + '%';
            comp.className = 'text-2xl font-black ' + (val > 0 ? 'text-emerald-600' : (val < 0 ?
                'text-rose-600' : 'text-slate-400'));
        }

        function updatePeriodoTexto(startDate, endDate) {
            document.getElementById('periodoTexto').textContent = formatDateDisplay(startDate) + ' — ' +
                formatDateDisplay(endDate);
            const periodNames = {
                semanal: 'Semanal',
                mensual: 'Mensual',
                semestral: 'Semestral',
                anual: 'Anual',
                personalizado: 'Personalizado'
            };
            document.getElementById('periodoLabel').textContent = 'Mostrando datos del período (' + (
                periodNames[currentSelected] || 'Personalizado') + ')';
        }

        function updateMetricsTable(resumen) {
            const rows = [
                ['Total de Consultas', resumen.total_consultas],
                ['Total de Pacientes Únicos', resumen.total_pacientes],
                ['Total de Recetas Emitidas', resumen.total_recetas],
                ['Total de Medicamentos Dispensados', resumen.total_dispensados],
                ['Hombres', resumen.genero?.masculino || 0],
                ['Mujeres', resumen.genero?.femenino || 0],
                ['Promedio de Edad', (resumen.edades?.promedio || 0) + ' años'],
                ['Mediana de Edad', (resumen.edades?.mediana || 0) + ' años'],
                ['Moda de Edad', (resumen.edades?.moda || 0) + ' años'],
                ['Hora Pico (Moda)', resumen.hora_pico || 'N/A'],
                ['Volumen Promedio Semanal', (resumen.promedio_semanal || 0) + ' consultas/semana'],
                ['Tasa Consultas con Receta', (resumen.tasa_con_receta || 0) + '%'],
                ['Tasa Recetas Dispensadas', (resumen.tasa_dispensada || 0) + '%'],
                ['Comparativa vs. Período Anterior', (resumen.comparativa_consultas > 0 ? '+' : '') + (
                    resumen.comparativa_consultas || 0) + '%'],
            ];

            rows.push(['<strong>ROLES INSTITUCIONALES</strong>', '']);
            if (resumen.perfil_academico) {
                Object.entries(resumen.perfil_academico).forEach(([rol, cant]) => rows.push([rol, cant]));
            }

            rows.push(['<strong>PACIENTES POR PNF / CARRERA</strong>', '']);
            if (resumen.pnf) {
                Object.entries(resumen.pnf).forEach(([k, v]) => rows.push([k, v]));
            }

            rows.push(['<strong>ENFERMEDADES MÁS FRECUENTES</strong>', '']);
            if (resumen.enfermedades) {
                Object.entries(resumen.enfermedades).slice(0, 10).forEach(([k, v]) => rows.push([k, v]));
            }

            rows.push(['<strong>TOP PRODUCTOS DISPENSADOS</strong>', '']);
            if (resumen.productos_top) {
                Object.entries(resumen.productos_top).slice(0, 10).forEach(([k, v]) => rows.push([k, v +
                    ' und'
                ]));
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
            if (charts[name]) {
                charts[name].destroy();
                charts[name] = null;
            }
        }

        function buildCharts(resumen) {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
            const tickColor = isDark ? '#9ca3af' : '#94a3b8';

            // Flujo semanal
            destroyChart('flujo');
            const flujoLabels = Object.keys(resumen.flujo_semanal || {}).map(k => 'Sem ' + k.split('-')[0]);
            const flujoData = Object.values(resumen.flujo_semanal || {});
            charts.flujo = new Chart(document.getElementById('chartFlujoSemanal'), {
                type: 'line',
                data: {
                    labels: flujoLabels,
                    datasets: [{
                        label: 'Consultas',
                        data: flujoData,
                        borderColor: COLORS.emerald.border,
                        backgroundColor: COLORS.emerald.bg,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: COLORS.emerald.border,
                        pointBorderWidth: 2.5,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    weight: 'bold',
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: tickColor,
                                stepSize: 1,
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                }
            });

            // Horas
            destroyChart('horas');
            charts.horas = new Chart(document.getElementById('chartHoras'), {
                type: 'bar',
                data: {
                    labels: Object.keys(resumen.distribucion_horas || {}),
                    datasets: [{
                        label: 'Consultas',
                        data: Object.values(resumen.distribucion_horas || {}),
                        backgroundColor: COLORS.sky.bg,
                        borderColor: COLORS.sky.border,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    weight: 'bold',
                                    size: 10
                                },
                                maxRotation: 0,
                                autoSkip: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: tickColor,
                                stepSize: 1,
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    }
                }
            });

            // Edades
            destroyChart('edades');
            const barColors = [COLORS.indigo, COLORS.sky, COLORS.emerald, COLORS.amber, COLORS.rose];
            charts.edades = new Chart(document.getElementById('chartEdades'), {
                type: 'bar',
                data: {
                    labels: Object.keys(resumen.edades?.rangos || {}).map(l => l + ' años'),
                    datasets: [{
                        label: 'Pacientes',
                        data: Object.values(resumen.edades?.rangos || {}),
                        backgroundColor: barColors.map(c => c.bg),
                        borderColor: barColors.map(c => c.border),
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: tickColor,
                                stepSize: 1,
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    weight: 'bold',
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });

            // Género
            destroyChart('genero');
            charts.genero = new Chart(document.getElementById('chartGenero'), {
                type: 'doughnut',
                data: {
                    labels: ['Hombres', 'Mujeres', 'Otro'],
                    datasets: [{
                        data: [resumen.genero?.masculino || 0, resumen.genero?.femenino || 0,
                            resumen.genero?.otro || 0
                        ],
                        backgroundColor: [COLORS.sky.border, COLORS.rose.border, COLORS.amber
                            .border
                        ],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    weight: 'bold',
                                    size: 11
                                },
                                color: tickColor
                            }
                        }
                    }
                }
            });

            // Estado de recetas
            destroyChart('estadoRecetas');
            charts.estadoRecetas = new Chart(document.getElementById('chartEstadoRecetas'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(resumen.estado_recetas || {}),
                    datasets: [{
                        data: Object.values(resumen.estado_recetas || {}),
                        backgroundColor: PALETTE.slice(0, Object.keys(resumen.estado_recetas ||
                        {}).length),
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    weight: 'bold',
                                    size: 10
                                },
                                color: tickColor
                            }
                        }
                    }
                }
            });

            // Enfermedades
            destroyChart('enfermedades');
            charts.enfermedades = new Chart(document.getElementById('chartEnfermedades'), {
                type: 'bar',
                data: {
                    labels: Object.keys(resumen.enfermedades || {}).slice(0, 8),
                    datasets: [{
                        label: 'Casos',
                        data: Object.values(resumen.enfermedades || {}).slice(0, 8),
                        backgroundColor: COLORS.rose.bg,
                        borderColor: COLORS.rose.border,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: tickColor,
                                stepSize: 1,
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    weight: 'bold',
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Productos
            destroyChart('productos');
            charts.productos = new Chart(document.getElementById('chartProductos'), {
                type: 'bar',
                data: {
                    labels: Object.keys(resumen.productos_top || {}).slice(0, 8),
                    datasets: [{
                        label: 'Unidades',
                        data: Object.values(resumen.productos_top || {}).slice(0, 8),
                        backgroundColor: COLORS.amber.bg,
                        borderColor: COLORS.amber.border,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    weight: 'bold',
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Médicos
            destroyChart('medicos');
            charts.medicos = new Chart(document.getElementById('chartMedicos'), {
                type: 'bar',
                data: {
                    labels: Object.keys(resumen.medicos || {}).slice(0, 8),
                    datasets: [{
                        label: 'Consultas',
                        data: Object.values(resumen.medicos || {}).slice(0, 8),
                        backgroundColor: COLORS.indigo.bg,
                        borderColor: COLORS.indigo.border,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: tickColor,
                                stepSize: 1,
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: tickColor,
                                font: {
                                    weight: 'bold',
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Consultorios
            destroyChart('consultorios');
            charts.consultorios = new Chart(document.getElementById('chartConsultorios'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(resumen.consultorios || {}),
                    datasets: [{
                        data: Object.values(resumen.consultorios || {}),
                        backgroundColor: PALETTE.slice(0, Object.keys(resumen.consultorios ||
                            {}).length),
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    weight: 'bold',
                                    size: 10
                                },
                                color: tickColor
                            }
                        }
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
            } catch (err) {
                console.error('Error cargando dashboard:', err);
            }
        }

        function cambiarFiltro(tipo) {
            currentSelected = tipo;
            const {
                start,
                end
            } = calcularFechas(tipo);
            loadDashboard(start, end);
        }

        function aplicarPersonalizado() {
            currentSelected = 'personalizado';
            const s = document.getElementById('customStartDate').value;
            const e = document.getElementById('customEndDate').value;
            if (s && e) loadDashboard(s, e);
        }

        function recargar() {
            loadDashboard(currentStartDate, currentEndDate);
        }

        function exportar(formato, reportType = 'completo') {
            const url =
                `{{ route('admin.salud.movimientos.consultas.estadisticas') }}?format=${formato}&report_type=${reportType}&start_date=${currentStartDate}&end_date=${currentEndDate}&periodo=${currentSelected}${getFilterParams()}`;
            if (formato === 'pdf') window.open(url, '_blank');
            else window.location.href = url;
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadDashboard(currentStartDate, currentEndDate);
        });

        return {
            cambiarFiltro,
            aplicarPersonalizado,
            exportar,
            recargar,
            setEnfermedades
        };
    })();
</script>
