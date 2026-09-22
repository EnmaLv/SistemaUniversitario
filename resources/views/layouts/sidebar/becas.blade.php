@php
    $becasConfigKeys = ['beneficios', 'preguntas', 'criterios'];
    $becasOpsKeys = ['jornada', 'solicitud'];
@endphp

@canMenu($becasConfigKeys)
<div x-data="{ open: {{ request()->routeIs('admin.becas.beneficios.*') || request()->routeIs('admin.becas.preguntas.*') || request()->routeIs('admin.becas.criterios.*') ? 'true' : 'false' }} }"
    class="w-full space-y-1">
    <button @click="open = !open"
        class="w-full flex items-center justify-between h-10 rounded-lg px-3 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Configuración de Beneficios">
        <div class="flex items-center gap-2.5 min-w-0">
            <i class="fa-solid fa-award text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Configuración
            </span>
        </div>
        <i class="fas fa-chevron-down text-xs text-white/70 transition-transform duration-200"
            :class="{ 'rotate-180': open, 'hidden': !sidebarOpen }"></i>
    </button>

    <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">
        @canMenu('beneficios')
        <a href="{{ url('admin/becas/beneficios') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.becas.beneficios.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-medal text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Beneficios</span>
        </a>
        @endcanMenu

        @canMenu('preguntas')
        <a href="{{ url('admin/becas/preguntas') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.becas.preguntas.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-circle-question text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Preguntas</span>
        </a>
        @endcanMenu

        @canMenu('criterios')
        <a href="{{ url('admin/becas/criterios') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.becas.criterios.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-shield-halved text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Criterios</span>
        </a>
        @endcanMenu
    </div>
</div>
@endcanMenu

@canMenu($becasOpsKeys)
<div x-data="{ open: {{ request()->routeIs('admin.becas.jornada.*') || request()->routeIs('admin.becas.solicitudes.*') ? 'true' : 'false' }} }"
    class="w-full space-y-1">
    <button @click="open = !open"
        class="w-full flex items-center justify-between h-10 rounded-lg px-3 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Operación de Becas">
        <div class="flex items-center gap-2.5 min-w-0">
            <i class="fa-solid fa-hand-holding-dollar text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Becas
            </span>
        </div>
        <i class="fas fa-chevron-down text-xs text-white/70 transition-transform duration-200"
            :class="{ 'rotate-180': open, 'hidden': !sidebarOpen }"></i>
    </button>

    <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">
        @canMenu('jornada')
        <a href="{{ url('admin/becas/jornada') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.becas.jornada.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-bullhorn text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Jornadas</span>
        </a>
        @endcanMenu

        @canMenu('solicitud')
        <a href="{{ url('admin/becas/solicitudes') }}"
            class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->routeIs('admin.becas.solicitudes.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
            <i class="fa-solid fa-file-signature text-xs w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Solicitudes</span>
        </a>
        @endcanMenu
    </div>
</div>
@endcanMenu

@if (auth()->user()->tieneRol('becario'))
@canMenu('solicitar')
<a href="{{ route('admin.becas.solicitar') }}"
    class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-sm font-medium transition-all {{ request()->routeIs('admin.becas.solicitudes.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80 hover:bg-[#623739]/60 hover:text-white' }}">
    <i class="fa-solid fa-file-signature w-4 text-center flex-shrink-0"></i>
    <span class="truncate">Solicitar Beca</span>
</a>
@endcanMenu
@endif