@php
    $saludKeys = ['envases_primarios', 'categorias_medicamentos', 'medicamentos', 'agenda_psicologica', 'enfermedades', 'consultorios', 'horarios', 'consultas'];
@endphp

@canMenu($saludKeys)
    <div class="pt-3 pb-1" :class="sidebarOpen ? 'block' : 'hidden'">
        <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-white/50">
            Salud y Farmacia
        </span>
    </div>

    @if (auth()->user()->tieneRol('administrador-de-salud') || auth()->user()->tieneRol('doctor(a)') )
        @canMenu('envases_primarios')
            <a href="{{ url('admin/salud/maestros/envases_primarios') }}"
            class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
            :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
            title="Envases Primarios">
                <i class="fas fa-box text-base w-5 text-center flex-shrink-0 text-white"></i>
                <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                    Envases Primarios
                </span>
            </a>
        @endcanMenu
    @endif

    @canMenu('categorias_medicamentos')
        <a href="{{ url('admin/salud/maestros/categorias') }}"
           class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
           :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
           title="Categorías">
            <i class="fas fa-tags text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Categorías
            </span>
        </a>
    @endcanMenu
 
    @canMenu('medicamentos')
        <a href="{{ url('admin/salud/maestros/medicamentos') }}"
           class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
           :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
           title="Medicamentos">
            <i class="fas fa-pills text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Medicamentos
            </span>
        </a>
    @endcanMenu

    @canMenu('enfermedades')
        <a href="{{ route('admin.enfermedades.index', ['tipo' => 'fisica']) }}"
        class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
        :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
        title="Enfermedades">
            <i class="fa-solid fa-disease text-xs w-4 text-center flex-shrink-0"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Enfermedades
            </span>
        </a>
    @endcanMenu

    @canMenu('consultorios')
        <a href="{{ route('admin.salud.maestros.consultorios.index') }}"
           class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
           :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
           title="Consultorios">
            <i class="fas fa-stethoscope text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Consultorios
            </span>
        </a>
    @endcanMenu
    @canMenu('horarios')
        <a href="{{ route('admin.salud.movimientos.horarios.index') }}"
           class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
           :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
           title="Horarios">
            <i class="fas fa-clock text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Horarios
            </span>
        </a>
    @endcanMenu
    @canMenu('consultas')
        <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
           class="w-full flex items-center h-10 rounded-lg px-3 gap-2.5 text-white/90 hover:bg-[#623739] hover:text-white transition-all min-w-0"
           :class="sidebarOpen ? 'px-3' : 'justify-center px-0'"
           title="Consultas">
            <i class="fas fa-stethoscope text-base w-5 text-center flex-shrink-0 text-white"></i>
            <span class="text-sm font-medium truncate" :class="sidebarOpen ? 'block' : 'hidden'">
                Consultas
            </span>
        </a>
    @endcanMenu
@endcanMenu