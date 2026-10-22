@php
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Actividades y jornadas</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-running mr-1" style="color:var(--color-primary)"></i>
                        Eventos de un día (una jornada, una práctica) o entrenamientos que se repiten cada semana. Los torneos están en su propia pantalla.
                    </p>
                </div>
                @canRoute('admin.deportes.calendarios.create')
                <a href="{{ route('admin.deportes.calendarios.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Crear actividad
                </a>
                @endcanRoute
            </div>

            @include('admin.deportes.partials.errores')

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-3 border-b" style="border-color:var(--border-color);">
                    <form action="{{ route('admin.deportes.calendarios.index') }}" method="GET">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Listado de actividades</h3>
                            </div>
                            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                                <select name="tipo" class="rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                                    <option value="" @selected($tipo === '')>Todos los tipos</option>
                                    <option value="esporadica" @selected($tipo === 'esporadica')>Una sola vez</option>
                                    <option value="recurrente" @selected($tipo === 'recurrente')>Se repite cada semana</option>
                                </select>
                                <select name="estado" class="rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                                    <option value="1" @selected($estado === '1')>Vigentes</option>
                                    <option value="2" @selected($estado === '2')>Canceladas</option>
                                    <option value="todas" @selected($estado === 'todas')>Todas</option>
                                </select>
                                <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-80" style="background-color:var(--input-bg);border-color:var(--input-border);">
                                    <i class="fas fa-search text-gray-400"></i>
                                    <input type="text" name="q" value="{{ $buscar }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Nombre o sección...">
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                                    <i class="fas fa-search text-xs"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-4 text-center">#</th>
                                <th class="px-6 py-4">Actividad</th>
                                <th class="px-6 py-4">Cuándo</th>
                                <th class="px-6 py-4">Bloques (disciplina · espacio · hora)</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="fade-in">
                            @forelse ($actividades as $actividad)
                                <x-table-row :id="$actividad->id_cld" class="border-b align-top" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 text-center font-bold" style="color:var(--text-muted);">
                                        {{ ($actividades->currentPage() - 1) * $actividades->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold" style="color:var(--text-main);">{{ $actividad->nombre_cld }}</div>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            <span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $actividad->esRecurrente() ? 'bg-sky-100 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300' }}">{{ $actividad->tipoLegible() }}</span>
                                            @if ($actividad->seccionLegible())
                                                <span class="rounded-md border px-1.5 py-0.5 text-[10px] font-bold uppercase" style="border-color:var(--border-color);color:var(--text-muted);">Sección {{ $actividad->seccionLegible() }}</span>
                                            @endif
                                            @if (! $actividad->con_inscripcion_cld)
                                                <span class="rounded-md border px-1.5 py-0.5 text-[10px] font-bold uppercase" style="border-color:var(--border-color);color:var(--text-muted);">Sin inscripción</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">{{ $actividad->descripcionCuando() }}</td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">
                                        <ul class="space-y-1">
                                            @forelse ($actividad->horarios->sortBy('hora_inicio_hde') as $bloque)
                                                <li><span class="font-semibold">{{ $bloque->actividad?->nombre_acd }}</span> · {{ $bloque->espacio?->nombre_epd }} · <span style="color:var(--text-muted);">{{ $bloque->horas() }}</span></li>
                                            @empty
                                                <li style="color:var(--text-muted);">Sin bloques activos</li>
                                            @endforelse
                                        </ul>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($actividad->estaActiva())
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-2.5 py-1 text-[11px] font-extrabold uppercase text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Vigente</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-gray-200 px-2.5 py-1 text-[11px] font-extrabold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">Cancelada</span>
                                        @endif
                                    </td>
                                    <x-table-actions :id="$actividad->id_cld" baseUrl="admin/deportes/calendarios" :status="$actividad->estaActiva()" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-running fa-3x mb-3" style="opacity: 0.1"></i>
                                        <p>No hay actividades con estos filtros.</p>
                                        <p class="mt-1 normal-case font-medium">Para registrar una jornada o un entrenamiento usa el botón "Crear actividad".</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t" style="border-color:var(--border-color);">
                    {{ $actividades->appends(request()->query())->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
