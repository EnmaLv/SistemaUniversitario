@canMenu('deportes_espacios')
<a href="{{ route('admin.deportes.espacios.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.espacios.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Espacios deportivos">
    <i class="fas fa-map-marked-alt text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Espacios deportivos
    </span>
</a>
@endcanMenu
@canMenu('deportes_actividades')
<a href="{{ route('admin.deportes.actividades.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.actividades.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Actividades">
    <i class="fas fa-running text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Actividades
    </span>
</a>
@endcanMenu
@canMenu('deportes_calendarios')
<a href="{{ route('admin.deportes.calendarios.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.calendarios.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Calendarios">
    <i class="fas fa-calendar-alt text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Calendarios
    </span>
</a>
@endcanMenu
@canMenu('deportes_horarios')
<a href="{{ route('admin.deportes.maestros.horarios.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.maestros.horarios.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Horarios deportivos">
    <i class="fas fa-futbol text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Horarios deportivos
    </span>
</a>
@endcanMenu
@canMenu('deportes_torneos')
<a href="{{ route('admin.deportes.torneos.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.torneos.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Torneos">
    <i class="fas fa-trophy text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Torneos
    </span>
</a>
@endcanMenu
@canMenu('deportes_inscripciones')
<a href="{{ route('admin.deportes.inscripciones.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.inscripciones.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Inscripciones">
    <i class="fas fa-user-plus text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Inscripciones
    </span>
</a>
@endcanMenu
@canMenu('deportes_asistencia')
<a href="{{ route('admin.deportes.asistencia.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.asistencia.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Asistencia">
    <i class="fas fa-clipboard-check text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Asistencia
    </span>
</a>
@endcanMenu
@canMenu('deportes_prestamos')
<a href="{{ route('admin.deportes.prestamos.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.prestamos.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Préstamos">
    <i class="fas fa-basketball-ball text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Préstamos
    </span>
</a>
@endcanMenu
@canMenu('deportes_sanciones')
<a href="{{ route('admin.deportes.sanciones.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.sanciones.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Sanciones">
    <i class="fas fa-ban text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Sanciones
    </span>
</a>
@endcanMenu
@canMenu('deportes_solvencia')
<a href="{{ route('admin.deportes.solvencia.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.solvencia.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Solvencia">
    <i class="fas fa-file-signature text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Solvencia
    </span>
</a>
@endcanMenu
@canMenu('deportes_entregas')
<a href="{{ route('admin.deportes.entregas.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.deportes.entregas.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Entregas de material">
    <i class="fas fa-paperclip text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Entregas de material
    </span>
</a>
@endcanMenu
@canMenu('compras')
<a href="{{ route('admin.movimientos.compras.index') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0 {{ request()->routeIs('admin.movimientos.compras.*') ? 'bg-[#623739] text-white font-semibold' : 'text-white/80' }}"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Compras">
    <i class="fas fa-shopping-cart text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Compras
    </span>
</a>
@endcanMenu
