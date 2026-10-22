<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Préstamos de implementos</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-basketball-ball mr-1" style="color:var(--color-primary)"></i>
                        Control de implementos deportivos prestados · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                @canRoute('admin.deportes.prestamos.create')
                <a href="{{ route('admin.deportes.prestamos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Nuevo préstamo
                </a>
                @endcanRoute
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-3 border-b" style="border-color:var(--border-color);">
                    <form action="{{ route('admin.deportes.prestamos.index') }}" method="GET">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Listado de préstamos</h3>
                            </div>
                            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                                <select name="estado" class="rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="">Todos los estados</option>
                                    @foreach (['activo', 'vencido', 'sancionado', 'devuelto', 'repuesto'] as $opcion)
                                        <option value="{{ $opcion }}" @selected($estado === $opcion)>{{ \App\Models\Deporte\PrestamoDeportivo::etiquetaEstado($opcion) }}</option>
                                    @endforeach
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
                                <th class="px-6 py-4">Implemento</th>
                                <th class="px-6 py-4">Entrega</th>
                                <th class="px-6 py-4">Devolución esperada</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="fade-in">
                            @forelse ($prestamos as $prestamo)
                                <x-table-row :id="$prestamo->id_prd" class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 text-center font-bold" style="color:var(--text-muted);">
                                        {{ ($prestamos->currentPage() - 1) * $prestamos->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold" style="color:var(--text-main);">{{ $prestamo->persona?->nombres_per }} {{ $prestamo->persona?->apellidos_per }}</p>
                                        <p class="text-xs" style="color:var(--text-muted);">C.I. {{ $prestamo->persona?->cedula_per }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold" style="color:var(--text-main);">{{ $prestamo->producto?->nombre_pdt }}</p>
                                        <p class="text-xs" style="color:var(--text-muted);">Cantidad: {{ $prestamo->cantidad_prd }} · {{ $prestamo->sede?->nombre_sed }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">{{ $prestamo->fecha_prestamo_prd?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">{{ $prestamo->fecha_devolucion_esperada_prd?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @include('admin.deportes.prestamos._estado', ['estado' => $prestamo->estado_prd])
                                        @if ($prestamo->tiene_sancion_activa)
                                            <p class="mt-1 text-[10px] font-bold uppercase text-rose-600"><i class="fas fa-ban"></i> Sanción activa</p>
                                        @endif
                                    </td>
                                    <x-table-actions :id="$prestamo->id_prd" baseUrl="admin/deportes/prestamos" :edit="false" :toggle="false" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-basketball-ball fa-3x mb-3" style="opacity: 0.1"></i>
                                        <p>No se encontraron préstamos registrados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t flex justify-center" style="border-color:var(--border-color);">
                    {{ $prestamos->appends(request()->query())->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
