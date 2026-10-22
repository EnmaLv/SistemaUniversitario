<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Sanciones deportivas</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-ban mr-1" style="color:var(--color-primary)"></i>
                        Por préstamos vencidos, implementos dañados o perdidos · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-3 border-b" style="border-color:var(--border-color);">
                    <form action="{{ route('admin.deportes.sanciones.index') }}" method="GET">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Listado de sanciones</h3>
                            </div>
                            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                                <select name="estado" class="rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="activa" @selected($estado === 'activa')>Activas</option>
                                    <option value="levantada" @selected($estado === 'levantada')>Levantadas</option>
                                    <option value="todas" @selected(! in_array($estado, ['activa', 'levantada'], true))>Todas</option>
                                </select>
                                <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-80" style="background-color:var(--input-bg);border-color:var(--input-border);">
                                    <i class="fas fa-search text-gray-400"></i>
                                    <input type="text" name="q" value="{{ $buscar }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Buscar por cédula o nombre...">
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
                                <th class="px-6 py-4">Persona</th>
                                <th class="px-6 py-4">Motivo</th>
                                <th class="px-6 py-4">Implemento</th>
                                <th class="px-6 py-4">Fecha</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="fade-in">
                            @forelse ($sanciones as $sancion)
                                <x-table-row :id="$sancion->id_snd" class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 text-center font-bold" style="color:var(--text-muted);">
                                        {{ ($sanciones->currentPage() - 1) * $sanciones->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold" style="color:var(--text-main);">{{ $sancion->persona?->nombres_per }} {{ $sancion->persona?->apellidos_per }}</p>
                                        <p class="text-xs" style="color:var(--text-muted);">C.I. {{ $sancion->persona?->cedula_per }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">{{ \Illuminate\Support\Str::limit($sancion->motivo_snd, 70) }}</td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">{{ $sancion->prestamo?->producto?->nombre_pdt }}</td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">{{ $sancion->fecha_sancion_snd?->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide {{ $sancion->estaActiva() ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                                            {{ ucfirst($sancion->estado_snd) }}
                                        </span>
                                    </td>
                                    <x-table-actions :id="$sancion->id_snd" baseUrl="admin/deportes/sanciones" :edit="false" :toggle="false" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-ban fa-3x mb-3" style="opacity: 0.1"></i>
                                        <p>No se encontraron sanciones.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t flex justify-center" style="border-color:var(--border-color);">
                    {{ $sanciones->appends(request()->query())->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
