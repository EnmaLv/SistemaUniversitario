<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Actividades deportivas</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-running mr-1" style="color:var(--color-primary)"></i>
                        Disciplinas deportivas y recreativas · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                @canRoute('admin.deportes.actividades.create')
                <a href="{{ route('admin.deportes.actividades.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Registrar actividad
                </a>
                @endcanRoute
            </div>

            @include('admin.deportes.partials.errores')

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-3 border-b" style="border-color:var(--border-color);">
                    <form action="{{ route('admin.deportes.actividades.index') }}" method="GET">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Listado de actividades</h3>
                            </div>
                            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                                <select name="estado" class="rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="" @selected($estado === '')>Todas</option>
                                    <option value="1" @selected($estado === '1')>Activas</option>
                                    <option value="2" @selected($estado === '2')>Inactivas</option>
                                </select>
                                <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-96" style="background-color:var(--input-bg);border-color:var(--input-border);">
                                    <i class="fas fa-search text-gray-400"></i>
                                    <input type="text" name="q" value="{{ $buscar }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Buscar por nombre...">
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
                                <th class="px-6 py-4">Tipo</th>
                                <th class="px-6 py-4">Descripción</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="fade-in">
                            @forelse ($actividades as $actividad)
                                <x-table-row :id="$actividad->id_acd" class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 text-center font-bold" style="color:var(--text-muted);">
                                        {{ ($actividades->currentPage() - 1) * $actividades->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 font-bold" style="color:var(--text-main);">{{ $actividad->nombre_acd }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1 rounded-lg border px-3 py-1 text-xs font-extrabold uppercase tracking-wide" style="border-color:var(--border-color);color:var(--color-primary);">
                                            {{ $actividad->tipoLegible() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-muted);">{{ \Illuminate\Support\Str::limit($actividad->descripcion_acd, 80) ?: '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @include('admin.deportes.partials.estado', ['status' => $actividad->status_id])
                                    </td>
                                    <x-table-actions :id="$actividad->id_acd" baseUrl="admin/deportes/actividades" :show="false" :status="(int) $actividad->status_id === 1" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-running fa-3x mb-3" style="opacity: 0.1"></i>
                                        <p>No hay actividades deportivas registradas.</p>
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
