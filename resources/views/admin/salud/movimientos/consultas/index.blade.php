    <x-app-layout>
        <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                @include('components.alert')

                {{-- Encabezado --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Consultas
                        </h1>
                        <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                            Consulta → Recetación → Dispensación
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.salud.movimientos.consultas.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass ?? 'bg-sky-600 hover:bg-sky-700' }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Nueva Consulta</span>
                        </a>
                    </div>
                </div>

                {{-- Card de Buscador + Botón Filtros --}}
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">

                    <form action="{{ route('admin.salud.movimientos.consultas.index') }}" method="GET"
                        class="relative w-full">
                        <input type="hidden" name="fecha_desde" value="{{ request('fecha_desde') }}">
                        <input type="hidden" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                        <input type="hidden" name="medico_id" value="{{ request('medico_id') }}">
                        <input type="hidden" name="consultorio_id" value="{{ request('consultorio_id') }}">
                        <input type="hidden" name="estado" value="{{ request('estado') }}">

                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </div>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                            placeholder="Buscar por nombre del paciente..."
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 {{ $focusRingClass ?? 'focus:ring-sky-500' }} transition-all">
                    </form>

                    <div class="flex items-center gap-3 shrink-0">
                        {{-- Botón filtros --}}
                        <button type="button" id="filtersToggle"
                            class="w-10 h-10 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-800/50 text-gray-600 dark:text-gray-300 hover:bg-sky-50 dark:hover:bg-sky-950/50 hover:text-sky-600 hover:border-sky-300 dark:hover:border-sky-800 shadow-sm active:scale-95 transition-all"
                            title="Filtros">
                            <i class="fas fa-filter text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Filtros colapsables --}}
                <div id="filters" style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="{{ request('fecha') || request('medico_id') || request('consultorio_id') || request('estado') ? '' : 'hidden' }} p-4 rounded-2xl border shadow-sm mb-3">
                    <form action="{{ route('admin.salud.movimientos.consultas.index') }}" method="GET"
                        class="flex flex-col gap-4">
                        <input type="hidden" name="buscar" value="{{ request('buscar') }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                            {{-- Fecha Desde --}}
                            <div>
                                <label
                                    class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                                    Fecha Desde
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-calendar-alt text-xs"></i>
                                    </span>
                                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                </div>
                            </div>

                            {{-- Fecha Hasta --}}
                            <div>
                                <label
                                    class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                                    Fecha Hasta
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-calendar-alt text-xs"></i>
                                    </span>
                                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                </div>
                            </div>

                            {{-- Médico --}}
                            <div>
                                <label
                                    class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                                    Médico
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-user-doctor text-xs"></i>
                                    </span>
                                    <select name="medico_id"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                        <option value="">Todos los médicos</option>
                                        @foreach ($medicos as $medico)
                                            <option value="{{ $medico->id_persona }}"
                                                @if (request('medico_id') == $medico->id_persona) selected @endif>
                                                {{ $medico->nombre_completo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Consultorio --}}
                            <div>
                                <label
                                    class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                                    Consultorio
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-door-open text-xs"></i>
                                    </span>
                                    <select name="consultorio_id"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                        <option value="">Todos los consultorios</option>
                                        @foreach ($consultorios as $consultorio)
                                            <option value="{{ $consultorio->id }}"
                                                @if (request('consultorio_id') == $consultorio->id) selected @endif>
                                                {{ $consultorio->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label
                                    class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                                    Estado
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-filter text-xs"></i>
                                    </span>
                                    <select name="estado"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                        <option value="">Todos los estados</option>
                                        <option value="pendiente" @if (request('estado') == 'pendiente') selected @endif>
                                            Pendiente</option>
                                        <option value="completado" @if (request('estado') == 'completado') selected @endif>
                                            Completado</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs shadow-md shadow-sky-600/20 active:scale-95 transition-all">
                                <i class="fas fa-check text-[10px]"></i> Aplicar
                            </button>
                            <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 font-extrabold text-xs hover:bg-gray-200 dark:hover:bg-gray-700 active:scale-95 transition-all">
                                <i class="fas fa-times text-[10px]"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Tabla --}}
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm overflow-hidden">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[11px] font-black uppercase tracking-wider text-gray-400">
                                    <th class="px-6 py-4 text-center">Fecha</th>
                                    <th class="px-6 py-4 text-center">Paciente</th>
                                    <th class="px-6 py-4 text-center">Médico</th>
                                    <th class="px-6 py-4 text-center">Consultorio</th>
                                    <th class="px-6 py-4 text-center">Estado</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                                @forelse ($consultas as $consulta)
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                        <td
                                            class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            {{ optional($consulta->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                            style="color: var(--text-main);">
                                            {{ trim(optional($consulta->paciente)->nombre_persona . ' ' . optional($consulta->paciente)->apellido_persona) ?: '—' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            {{ trim(optional($consulta->medico)->nombre_persona . ' ' . optional($consulta->medico)->apellido_persona) ?: '—' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            {{ optional($consulta->consultorio)->nombre ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            @php $paso = $consulta->paso_actual; @endphp
                                            @if ($paso === 'receta')
                                                <span
                                                    class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                                    <i class="fas fa-prescription"></i> Falta recetar
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                    <i class="fas fa-check-circle"></i> Completa
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            @if ($paso === 'receta' || $paso === 'dispensacion')
                                                <a href="{{ route('admin.salud.movimientos.consultas.dispensacion', $consulta) }}"
                                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors">
                                                    <i class="fas fa-arrow-right"></i> Pendiente
                                                </a>
                                            @else
                                                <a href="{{ route('admin.salud.movimientos.consultas.show', $consulta) }}"
                                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/30 transition-colors">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>

                                                <a href="{{ route('admin.salud.movimientos.consultas.recipe_pdf', $consulta) }}"
                                                    target="_blank" class="btn btn-primary">
                                                    Imprimir Récipe
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                            <i
                                                class="fas fa-notes-medical text-3xl mb-3 block text-gray-300 dark:text-gray-700"></i>
                                            No hay consultas registradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($consultas->hasPages())
                        <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                            {{ $consultas->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btnToggle = document.getElementById('filtersToggle');
                const filtersDiv = document.getElementById('filters');

                if (btnToggle && filtersDiv) {
                    btnToggle.addEventListener('click', function() {
                        filtersDiv.classList.toggle('hidden');
                    });
                }
            });
        </script>
    </x-app-layout>
