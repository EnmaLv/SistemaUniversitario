@php
    $transporteKeys = ['bus_marcas', 'bus_modelos', 'bus_tipo_combustibles', 'bus_vehiculos', 'bus_rutas', 'bus_paradas', 'bus_viajes'];
@endphp

@canMenu($transporteKeys)
    <div class="pt-2 pb-1" :class="sidebarOpen ? 'block' : 'hidden'">
        <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
            Gestión de Transporte
        </span>
    </div>

    {{-- ==========================================
            GRUPO 1: CONFIGURACIÓN DE FLOTA
        ========================================== --}}
    @canMenu(['bus_marcas', 'bus_modelos', 'bus_tipo_combustibles', 'bus_vehiculos'])
    <div x-data="{ open: {{ request()->is('admin/transporte/maestros/bus_marcas*')
                            || request()->is('admin/transporte/maestros/bus_modelos*')
                            || request()->is('admin/transporte/maestros/bus_tipo_combustibles*')
                            || request()->is('admin/transporte/maestros/bus_vehiculos*') ? 'true' : 'false' }} }"
        class="w-full space-y-1">

        <button @click="open = !open"
            class="w-full flex items-center justify-between h-10 rounded-xl px-3 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600 transition-all min-w-0"
            :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Configuración de Flota">

            <div class="flex items-center gap-3 min-w-0">
                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span class="text-sm font-medium whitespace-nowrap" :class="sidebarOpen ? 'block' : 'hidden'">
                    Configuración de Flota
                </span>
            </div>

            <svg class="h-4 w-4 flex-shrink-0 transition-transform duration-200"
                :class="[open ? 'rotate-180' : '', sidebarOpen ? 'block' : 'hidden']"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">

            @canMenu('bus_marcas')
            <a href="{{ url('admin/transporte/maestros/bus_marcas') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->is('admin/transporte/maestros/bus_marcas*') ? 'bg-blue-50 text-blue-600 font-semibold dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600' }}">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
                <span class="truncate">Marcas</span>
            </a>
            @endcanMenu

            @canMenu('bus_modelos')
            <a href="{{ url('admin/transporte/maestros/bus_modelos') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->is('admin/transporte/maestros/bus_modelos*') ? 'bg-blue-50 text-blue-600 font-semibold dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600' }}">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                    <polyline points="2 17 12 22 22 17"/>
                    <polyline points="2 12 12 17 22 12"/>
                </svg>
                <span class="truncate">Modelos</span>
            </a>
            @endcanMenu

            @canMenu('bus_tipo_combustibles')
            <a href="{{ url('admin/transporte/maestros/bus_tipo_combustibles') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->is('admin/transporte/maestros/bus_tipo_combustibles*') ? 'bg-blue-50 text-blue-600 font-semibold dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600' }}">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                </svg>
                <span class="truncate">Combustibles</span>
            </a>
            @endcanMenu

            @canMenu('bus_vehiculos')
            <a href="{{ url('admin/transporte/maestros/bus_vehiculos') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->is('admin/transporte/maestros/bus_vehiculos*') ? 'bg-blue-50 text-blue-600 font-semibold dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600' }}">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="6" width="18" height="13" rx="2"/>
                    <circle cx="7" cy="19" r="2"/>
                    <circle cx="17" cy="19" r="2"/>
                </svg>
                <span class="truncate">Vehículos</span>
            </a>
            @endcanMenu
        </div>
    </div>
    @endcanMenu

    {{-- ==========================================
            GRUPO 2: RUTAS Y OPERACIÓN
        ========================================== --}}
    @canMenu(['bus_rutas', 'bus_paradas', 'bus_viajes'])
    <div x-data="{ open: {{ request()->is('admin/transporte/maestros/bus_rutas*')
                            || request()->is('admin/transporte/maestros/bus_paradas*')
                            || request()->is('admin/transporte/maestros/bus_viajes*') ? 'true' : 'false' }} }"
        class="w-full space-y-1">

        <button @click="open = !open"
            class="w-full flex items-center justify-between h-10 rounded-xl px-3 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600 transition-all min-w-0"
            :class="sidebarOpen ? 'px-3' : 'justify-center px-0'" title="Rutas y Operación">

            <div class="flex items-center gap-3 min-w-0">
                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="6" cy="19" r="3"/>
                    <path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/>
                    <circle cx="18" cy="5" r="3"/>
                </svg>
                <span class="text-sm font-medium whitespace-nowrap" :class="sidebarOpen ? 'block' : 'hidden'">
                    Rutas y Operación
                </span>
            </div>

            <svg class="h-4 w-4 flex-shrink-0 transition-transform duration-200"
                :class="[open ? 'rotate-180' : '', sidebarOpen ? 'block' : 'hidden']"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open && sidebarOpen" x-collapse class="pl-7 space-y-1">

            @canMenu('bus_rutas')
            <a href="{{ url('admin/transporte/maestros/bus_rutas') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->is('admin/transporte/maestros/bus_rutas*') ? 'bg-blue-50 text-blue-600 font-semibold dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600' }}">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                    <line x1="8" y1="2" x2="8" y2="18"/>
                    <line x1="16" y1="6" x2="16" y2="22"/>
                </svg>
                <span class="truncate">Rutas</span>
            </a>
            @endcanMenu

            @canMenu('bus_paradas')
            <a href="{{ url('admin/transporte/maestros/bus_paradas') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->is('admin/transporte/maestros/bus_paradas*') ? 'bg-blue-50 text-blue-600 font-semibold dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600' }}">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <span class="truncate">Paradas</span>
            </a>
            @endcanMenu

            @canMenu('bus_viajes')
            <a href="{{ url('admin/transporte/maestros/bus_viajes') }}"
                class="flex items-center gap-2.5 h-8 rounded-lg px-3 text-xs font-medium transition-all {{ request()->is('admin/transporte/maestros/bus_viajes*') ? 'bg-blue-50 text-blue-600 font-semibold dark:bg-blue-900/50 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-600' }}">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span class="truncate">Viajes</span>
            </a>
            @endcanMenu
        </div>
    </div>
    @endcanMenu
@endcanMenu