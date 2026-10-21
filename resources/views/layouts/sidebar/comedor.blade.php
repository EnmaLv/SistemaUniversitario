@php
    $kRecetas      = ['comedor_recetas'];
    $kIngredientes = ['comedor_ingredientes'];
    $kRegComida    = ['comedor_registro_comida', 'registro_comida'];
    $kRegDiario    = ['comedor_registro_diario', 'registro_diario'];
    $kCategorias   = ['comedor_categorias', 'categorias'];
    $kProductos    = ['comedor_productos', 'productos'];
    $kLotes        = ['comedor_lotes', 'lotes'];
    $kStock        = ['comedor_stock', 'inventario_sedes_lotes'];
    $kCompras      = ['comedor_compras', 'compras'];
    $kMovimientos  = ['comedor_movimientos', 'movimientos'];

    $comedorKeys    = array_merge($kRecetas, $kIngredientes, $kRegComida, $kRegDiario);
    $inventarioKeys = array_merge($kCategorias, $kProductos, $kLotes, $kStock, $kCompras, $kMovimientos);
@endphp

@canMenu($comedorKeys)
    <div class="pt-3 pb-1" :class="sidebarOpen ? 'block' : 'hidden'">
        <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-white/50">
            Gestión de Comedor
        </span>
    </div>

    @canMenu(array_merge($kRecetas, $kIngredientes))
    <div class="flex flex-col">
        <button @click="activeSection = (activeSection === 'recetas' ? '' : 'recetas')"
            class="w-full flex items-center justify-between h-10 rounded-lg px-3 gap-2 text-white/90 hover:bg-[#623739] hover:text-white transition-all"
            :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
            title="Recetas y Platos">
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fas fa-utensils text-base w-5 text-center flex-shrink-0 text-white"></i>
                <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                    Recetas y Platos
                </span>
            </div>
            <svg class="h-4 w-4 flex-shrink-0 transition-transform duration-200 text-white"
                 :class="[activeSection === 'recetas' ? 'rotate-180' : '', sidebarOpen ? 'block' : 'hidden']"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="activeSection === 'recetas' && sidebarOpen" x-collapse class="pl-9 pr-2 py-1 space-y-1">
            @canMenu($kRecetas)
                <a href="{{ url('admin/maestros/recetas') }}"
                   class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
                   title="Recetas">
                    <i class="fas fa-book-open w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Recetas</span>
                </a>
            @endcanMenu
            @canMenu($kIngredientes)
                <a href="{{ url('admin/maestros/receta_ingredientes') }}"
                   class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
                   title="Ingredientes">
                    <i class="fas fa-carrot w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Ingredientes</span>
                </a>
            @endcanMenu
        </div>
    </div>
    @endcanMenu

    @canMenu(array_merge($kRegComida, $kRegDiario))
    <div class="flex flex-col">
        <button @click="activeSection = (activeSection === 'registro_comida' ? '' : 'registro_comida')"
            class="w-full flex items-center justify-between h-10 rounded-lg px-3 gap-2 text-white/90 hover:bg-[#623739] hover:text-white transition-all"
            :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
            title="Registro de Comidas">
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fas fa-clipboard-check text-base w-5 text-center flex-shrink-0 text-white"></i>
                <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                    Registro de Comidas
                </span>
            </div>
            <svg class="h-4 w-4 flex-shrink-0 transition-transform duration-200 text-white"
                 :class="[activeSection === 'registro_comida' ? 'rotate-180' : '', sidebarOpen ? 'block' : 'hidden']"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="activeSection === 'registro_comida' && sidebarOpen" x-collapse class="pl-9 pr-2 py-1 space-y-1">
            @canMenu($kRegComida)
                <a href="{{ url('admin/movimientos/registro_comida') }}"
                   class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
                   title="Registrar Comida">
                    <i class="fas fa-utensils w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Registrar Comida</span>
                </a>
            @endcanMenu
            @canMenu($kRegDiario)
                <a href="{{ url('admin/movimientos/registro_diario') }}"
                   class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
                   title="Registro Diario">
                    <i class="fas fa-concierge-bell w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Registro Diario</span>
                </a>
            @endcanMenu
        </div>
    </div>
    @endcanMenu
@endcanMenu

@canMenu($inventarioKeys)
    <div class="pt-3 pb-1" :class="sidebarOpen ? 'block' : 'hidden'">
        <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-white/50">
            Gestión de Inventario
        </span>
    </div>

    @canMenu(array_merge($kCategorias, $kProductos))
    <div class="flex flex-col">
        <button @click="activeSection = (activeSection === 'catalogo' ? '' : 'catalogo')"
            class="w-full flex items-center justify-between h-10 rounded-lg px-3 gap-2 text-white/90 hover:bg-[#623739] hover:text-white transition-all"
            :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
            title="Catálogo de Productos">
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fas fa-boxes text-base w-5 text-center flex-shrink-0 text-white"></i>
                <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                    Catálogo de Productos
                </span>
            </div>
            <svg class="h-4 w-4 flex-shrink-0 transition-transform duration-200 text-white"
                 :class="[activeSection === 'catalogo' ? 'rotate-180' : '', sidebarOpen ? 'block' : 'hidden']"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="activeSection === 'catalogo' && sidebarOpen" x-collapse class="pl-9 pr-2 py-1 space-y-1">
            @canMenu($kCategorias)
                <a href="{{ url('admin/maestros/categorias') }}"
                   class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
                   title="Categorías">
                    <i class="fas fa-tags w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Categorías</span>
                </a>
            @endcanMenu
            @canMenu($kProductos)
                <a href="{{ url('admin/maestros/productos') }}"
                   class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
                   title="Productos">
                    <i class="fas fa-box w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Productos</span>
                </a>
            @endcanMenu
        </div>
    </div>
    @endcanMenu

    @canMenu(array_merge($kLotes, $kStock))
    <div class="flex flex-col">
        <button @click="activeSection = (activeSection === 'control_stock' ? '' : 'control_stock')"
            class="w-full flex items-center justify-between h-10 rounded-lg px-3 gap-2 text-white/90 hover:bg-[#623739] hover:text-white transition-all"
            :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
            title="Control de Stock">
            <div class="flex items-center gap-2.5 min-w-0">
                <i class="fas fa-warehouse text-base w-5 text-center flex-shrink-0 text-white"></i>
                <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                    Control de Stock
                </span>
            </div>
            <svg class="h-4 w-4 flex-shrink-0 transition-transform duration-200 text-white"
                 :class="[activeSection === 'control_stock' ? 'rotate-180' : '', sidebarOpen ? 'block' : 'hidden']"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="activeSection === 'control_stock' && sidebarOpen" x-collapse class="pl-9 pr-2 py-1 space-y-1">
            @canMenu($kLotes)
                <a href="{{ url('admin/movimientos/lotes') }}"
                   class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
                   title="Lotes">
                    <i class="fas fa-boxes w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Lotes</span>
                </a>
            @endcanMenu
            @canMenu($kStock)
                <a href="{{ url('admin/movimientos/sedes_lotes') }}"
                   class="flex items-center gap-2 py-1.5 px-2 text-xs rounded-lg text-white/80 hover:text-white hover:bg-[#623739] truncate"
                   title="Existencias por Sede">
                    <i class="fas fa-store-alt w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">Existencias por Sede</span>
                </a>
            @endcanMenu
        </div>
    </div>
    @endcanMenu

    @canMenu($kCompras)
    <a href="{{ url('admin/movimientos/compras') }}"
       class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
       title="Compras">
        <i class="fas fa-shopping-cart text-base w-5 text-center flex-shrink-0 text-white"></i>
        <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
            Compras
        </span>
    </a>
    @endcanMenu

    @canMenu($kMovimientos)
    <a href="{{ url('admin/movimientos/historial_movimientos') }}"
       class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
       :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
       title="Historial de Movimientos">
        <i class="fas fa-clipboard-list text-base w-5 text-center flex-shrink-0 text-white"></i>
        <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
            Historial de Movimientos
        </span>
    </a>
    @endcanMenu
@endcanMenu