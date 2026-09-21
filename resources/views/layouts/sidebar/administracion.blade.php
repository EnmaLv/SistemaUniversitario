@php
    $adminKeys = [
        'compras',
        'sedes',
        'proveedores',
        'ubicaciones',
        'ubicaciones_estados',
        'ubicaciones_municipios',
        'ubicaciones_localidades',
        'pnf',
        'persona',
        'config_empleados',
        'config_roles',
        'config_permisos',
        'archivos',
    ];
@endphp

@canMenu($adminKeys)

{{-- ==========================================
        ENLACE DIRECTO: COMPRAS Y REQUISICIONES
    ========================================== --}}
@canMenu('compras')
<a href="{{ url('admin/movimientos/compras') }}"
    class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
    :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Compras y Requisiciones">
    <i class="fas fa-shopping-cart text-base w-5 text-center flex-shrink-0 text-white"></i>
    <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
        Compras y Requisiciones
    </span>
</a>
@endcanMenu

{{-- ==========================================
        SECCIÓN 1: CONFIGURACIÓN INSTITUCIONAL
    ========================================== --}}
@canMenu(['sedes', 'proveedores', 'ubicaciones_estados', 'ubicaciones_municipios', 'ubicaciones_localidades', 'pnf',
'persona'])
<div class="pt-3 pb-1" :class="sidebarOpen ? 'block' : 'hidden'">
    <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-white/50">
        Configuración Institucional
    </span>
</div>

{{-- Dropdown: Configuración General --}}
<div class="flex flex-col" x-data="{ openUbicaciones: false }">
    <button @click="activeSection = (activeSection === 'config_general' ? '' : 'config_general')"
        class="w-full flex items-center justify-between h-10 rounded-lg px-3 gap-2 text-white/90 hover:bg-[#623739] hover:text-white transition-all"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Configuración General">
        <div class="flex items-center gap-2.5 min-w-0">
            <i class="fas fa-cog text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Configuración General
            </span>
        </div>
        <svg class="h-4 w-4 flex-shrink-0 transition-transform duration-200 text-white"
            :class="[activeSection === 'config_general' ? 'rotate-180' : '', sidebarOpen ? 'block' : 'hidden']"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="activeSection === 'config_general' && sidebarOpen" x-collapse class="pl-8 pr-2 py-1 space-y-1">

        {{-- Sedes y Anexos --}}
        @canMenu('sedes')
        <a href="{{ url('admin/maestros/sedes') }}"
            class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
            title="Sedes y Anexos">
            <i class="fas fa-store w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Sedes y Anexos</span>
        </a>
        @endcanMenu

        {{-- Proveedores --}}
        @canMenu('proveedores')
        <a href="{{ url('admin/maestros/proveedores') }}"
            class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
            title="Proveedores">
            <i class="fas fa-truck w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Proveedores</span>
        </a>
        @endcanMenu

        {{-- Submenú Anidado: Ubicaciones Geográficas --}}
        @canMenu(['ubicaciones_estados', 'ubicaciones_municipios', 'ubicaciones_localidades'])
        <div class="flex flex-col">
            <button @click="openUbicaciones = !openUbicaciones"
                class="w-full flex items-center justify-between py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] transition-all"
                title="Ubicaciones Geográficas">
                <div class="flex items-center gap-2 min-w-0">
                    <i class="fas fa-map-marked-alt w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Ubicaciones Geográficas</span>
                </div>
                <svg class="h-3.5 w-3.5 flex-shrink-0 transition-transform duration-200 text-white"
                    :class="openUbicaciones ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="openUbicaciones" x-collapse class="pl-5 pr-1 py-1 space-y-1">
                @canMenu('ubicaciones_estados')
                <a href="{{ url('admin/estado') }}"
                    class="flex items-center gap-2 py-1 px-2 text-[11px] rounded-lg text-white/70 hover:text-white hover:bg-[#623739] truncate"
                    title="Estados">
                    <i class="fas fa-globe w-3.5 text-center flex-shrink-0"></i>
                    <span class="truncate">Estados</span>
                </a>
                @endcanMenu
                @canMenu('ubicaciones_municipios')
                <a href="{{ url('admin/municipio') }}"
                    class="flex items-center gap-2 py-1 px-2 text-[11px] rounded-lg text-white/70 hover:text-white hover:bg-[#623739] truncate"
                    title="Municipios">
                    <i class="fas fa-city w-3.5 text-center flex-shrink-0"></i>
                    <span class="truncate">Municipios</span>
                </a>
                @endcanMenu
                @canMenu('ubicaciones_localidades')
                <a href="{{ url('admin/localidad') }}"
                    class="flex items-center gap-2 py-1 px-2 text-[11px] rounded-lg text-white/70 hover:text-white hover:bg-[#623739] truncate"
                    title="Localidades">
                    <i class="fas fa-home w-3.5 text-center flex-shrink-0"></i>
                    <span class="truncate">Localidades</span>
                </a>
                @endcanMenu
            </div>
        </div>
        @endcanMenu

        {{-- Programas de Formación --}}
        @canMenu('pnf')
        <a href="{{ url('admin/maestros/pnf') }}"
            class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
            title="Programas de Formación">
            <i class="fas fa-graduation-cap w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Programas de Formación</span>
        </a>
        @endcanMenu

        {{-- Estudiantes --}}
        @canMenu('persona')
        <a href="{{ url('admin/persona') }}"
            class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
            title="Estudiantes">
            <i class="fas fa-user-graduate w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Estudiantes</span>
        </a>
        @endcanMenu

    </div>
</div>
@endcanMenu

{{-- ==========================================
        SECCIÓN 2: ADMINISTRACIÓN DEL SISTEMA
    ========================================== --}}
@canMenu(['config_empleados', 'config_roles', 'config_permisos', 'archivos'])
<div class="pt-3 pb-1" :class="sidebarOpen ? 'block' : 'hidden'">
    <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-white/50">
        Administración del Sistema
    </span>
</div>

{{-- Dropdown: Gestión de Usuarios --}}
<div class="flex flex-col">
    <button @click="activeSection = (activeSection === 'gestion_usuarios' ? '' : 'gestion_usuarios')"
        class="w-full flex items-center justify-between h-10 rounded-lg px-3 gap-2 text-white/90 hover:bg-[#623739] hover:text-white transition-all"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Gestión de Usuarios">
        <div class="flex items-center gap-2.5 min-w-0">
            <i class="fas fa-users-cog text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Gestión de Usuarios
            </span>
        </div>
        <svg class="h-4 w-4 flex-shrink-0 transition-transform duration-200 text-white"
            :class="[activeSection === 'gestion_usuarios' ? 'rotate-180' : '', sidebarOpen ? 'block' : 'hidden']"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="activeSection === 'gestion_usuarios' && sidebarOpen" x-collapse class="pl-8 pr-2 py-1 space-y-1">
        @canMenu('config_empleados')
        <a href="{{ url('admin/configuracion/empleados') }}"
            class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
            title="Empleados">
            <i class="fas fa-users w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Empleados</span>
        </a>
        @endcanMenu

        @canMenu('config_roles')
        <a href="{{ url('admin/configuracion/roles') }}"
            class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
            title="Roles">
            <i class="fas fa-user-tag w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Roles</span>
        </a>
        @endcanMenu

        @canMenu('config_permisos')
        <a href="{{ url('admin/configuracion/permisos') }}"
            class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
            title="Permisos">
            <i class="fas fa-key w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Permisos</span>
        </a>
        @endcanMenu

        @canMenu('archivos')
        <a href="{{ url('admin/configuracion/archivos') }}"
            class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
            title="Archivos del Sistema">
            <i class="fas fa-folder-open w-4 text-center flex-shrink-0"></i>
            <span class="truncate">Archivos del Sistema</span>
        </a>
        @endcanMenu
    </div>
</div>
@endcanMenu

@endcanMenu
