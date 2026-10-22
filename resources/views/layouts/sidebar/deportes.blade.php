@php
    $horariosKeys = ['deportes_horarios'];
@endphp

@canMenu($horariosKeys)
<a href="{{ route('admin.deportes.maestros.horarios.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.maestros.horarios.index') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Horarios deportivos">
    <i class="fas fa-futbol text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Horarios deportivos
    </span>
</a>
@endcanMenu
