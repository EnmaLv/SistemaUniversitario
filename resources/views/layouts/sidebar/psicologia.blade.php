@php
    $agendaKeys = ['agenda', 'agenda_historial', 'agenda_estadisticas', 'agenda_prioridades'];
    $horariosKeys = ['horarios', 'horarios_crear', 'grupo_horarios'];
    $citaKeys = ['citas', 'citas_historial', 'citas_crear'];
    $muralKeys = ['mural'];
    $historiasKeys = [
        'historias',
        'plantillas_globales',
        'plantillas',
        'campos_evolucion',
        'enfermedades',
        'avances_sesion',
        'estado_animos',
    ];
    $publicacionesKeys = ['publicaciones', 'publicaciones_crear'];
    $adminKeys = ['admin_users'];
@endphp

@if (auth()->user()->tieneRol('paciente'))
    @canMenu($citaKeys)
    <div x-data="{ open: {{ request()->routeIs('citas.*') ? 'true' : 'false' }} }" class="w-full space-y-1">
        <button @click="open = !open"
            class="w-full flex items-center justify-between h-10 rounded-lg px-3 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
            :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Citas">
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fa-solid fa-calendar-days text-base w-5 text-center flex-shrink-0 text-white"></i>
                <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                    Mis Citas
                </span>
            </div>
            <i class="fas fa-chevron-down text-xs text-white/70 transition-transform duration-200"
                :class="{ 'rotate-180': open, 'hidden': !sidebarOpen }"></i>
        </button>

        <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">
            <a href="{{ route('admin.psicologia.maestros.citas.index') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.psicologia.maestros.citas.index') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
                <i class="fa-solid fa-calendar-days text-xs w-4 text-center flex-shrink-0"></i>
                <span class="truncate">Mis Citas Activas</span>
            </a>
            <a href="{{ route('admin.psicologia.maestros.citas.create') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.psicologia.maestros.agenda.estadisticas') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
                <i class="fa-solid fa-calendar-plus text-xs w-4 text-center flex-shrink-0"></i>
                <span class="truncate">Solicitar Cita</span>
            </a>
        </div>
    </div>
    @endcanMenu
@endif

@if (auth()->user()->tieneRol('paciente'))
    @canMenu($muralKeys)
    @canMenu('mural')
    <a href="{{ url('admin/psicologia/maestros/publicaciones/mural') }}"
        class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Mural de Avisos">
        <i class="fas fa-newspaper text-base w-5 text-center flex-shrink-0 text-white"></i>
        <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
            Mural de Avisos
        </span>
    </a>
    @endcanMenu
    @endcanMenu
@endif
@if (!auth()->user()->tieneRol('paciente'))
@canMenu($agendaKeys)
<div x-data="{ open: {{ request()->routeIs('agenda.*') ? 'true' : 'false' }} }" class="w-full space-y-1">
    <button @click="open = !open"
        class="w-full flex items-center justify-between h-10 rounded-lg px-3 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Agenda">
        <div class="flex items-center gap-2.5 min-w-0">
            <i class="fa-solid fa-calendar-days text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Agenda
            </span>
        </div>
        <i class="fas fa-chevron-down text-xs text-white/70 transition-transform duration-200"
            :class="{ 'rotate-180': open, 'hidden': !sidebarOpen }"></i>
    </button>

    <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">
        <a href="{{ route('admin.psicologia.maestros.agenda.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.psicologia.maestros.agenda.index') && !request()->has('view') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-calendar-day text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Vista de Agenda</span>
        </a>
        <a href="{{ route('admin.psicologia.maestros.agenda.index', ['view' => 'list']) }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->query('view') === 'list' ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-clock-rotate-left text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Historial de Citas</span>
        </a>
        <a href="{{ route('admin.psicologia.maestros.agenda.estadisticas', ['format' => 'html']) }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.psicologia.maestros.agenda.estadisticas') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-chart-pie text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Estadísticas</span>
        </a>
        <a href="{{ route('admin.psicologia.maestros.prioridades.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.psicologia.maestros.agenda.prioridades.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-list-ol text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Prioridades de Atención</span>
        </a>
    </div>
</div>
@endcanMenu



@canMenu($horariosKeys)
<div x-data="{ open: {{ request()->routeIs('horarios.*') || request()->routeIs('grupos_horarios.*') ? 'true' : 'false' }} }" class="w-full space-y-1">
    <button @click="open = !open"
        class="w-full flex items-center justify-between h-10 rounded-lg px-3 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Horarios">
        <div class="flex items-center gap-2.5 min-w-0">
            <i class="fa-solid fa-clock text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Horarios
            </span>
        </div>
        <i class="fas fa-chevron-down text-xs text-white/70 transition-transform duration-200"
            :class="{ 'rotate-180': open, 'hidden': !sidebarOpen }"></i>
    </button>

    <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">
        <a href="{{ route('admin.psicologia.maestros.horarios.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('horarios.index') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-business-time text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Bloques de Horario</span>
        </a>
        <a href="{{ route('admin.psicologia.maestros.grupos_horarios.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('grupos_horarios.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-layer-group text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Grupos de Horarios</span>
        </a>
    </div>
</div>
@endcanMenu

@canMenu($historiasKeys)
<div x-data="{ open: {{ request()->routeIs('historias.*') || request()->routeIs('plantillas.*') || request()->routeIs('campos_evolucion.*') || request()->routeIs('enfermedades.*') ? 'true' : 'false' }} }" class="w-full space-y-1">
    <button @click="open = !open"
        class="w-full flex items-center justify-between h-10 rounded-lg px-3 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Historias Clínicas">
        <div class="flex items-center gap-2.5 min-w-0">
            <i class="fa-solid fa-folder-open text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Historias Clínicas
            </span>
        </div>
        <i class="fas fa-chevron-down text-xs text-white/70 transition-transform duration-200"
            :class="{ 'rotate-180': open, 'hidden': !sidebarOpen }"></i>
    </button>

    <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">
        <a href="{{ route('admin.psicologia.maestros.plantillas_globales.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('plantillas_globales.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-file-lines text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Esquema General</span>
        </a>
        <a href="{{ route('admin.psicologia.maestros.plantillas.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('plantillas.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-paperclip text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Anexos Clínicos</span>
        </a>
        <a href="{{ route('admin.psicologia.maestros.campos_evolucion.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('campos_evolucion.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-chart-line text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Campos de Evolución</span>
        </a>
        <a href="{{ route('admin.enfermedades.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('enfermedades.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-disease text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Enfermedades</span>
        </a>
        <a href="{{ route('admin.psicologia.maestros.avances_sesion.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('avances_sesion.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-bars-staggered text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Avances de Sesión</span>
        </a>
        <a href="{{ route('admin.psicologia.maestros.estado_animos.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('estado_animos.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-face-smile text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Estados de Ánimo</span>
        </a>
    </div>
</div>
@endcanMenu
@endif

@if (!auth()->user()->tieneRol('paciente'))
@canMenu($publicacionesKeys)
<div x-data="{ open: {{ request()->routeIs('publicaciones.*') ? 'true' : 'false' }} }" class="w-full space-y-1">
    <button @click="open = !open"
        class="w-full flex items-center justify-between h-10 rounded-lg px-3 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Publicaciones">
        <div class="flex items-center gap-2.5 min-w-0">
            <i class="fa-solid fa-newspaper text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Publicaciones
            </span>
        </div>
        <i class="fas fa-chevron-down text-xs text-white/70 transition-transform duration-200"
            :class="{ 'rotate-180': open, 'hidden': !sidebarOpen }"></i>
    </button>

    <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">
        <a href="{{ route('admin.psicologia.maestros.publicaciones.index') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('publicaciones.index') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-newspaper text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Mis Publicaciones</span>
        </a>
    </div>
</div>
@endcanMenu
@endcanMenu