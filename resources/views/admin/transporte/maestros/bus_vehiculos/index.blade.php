<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">Vehículos</h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.transporte.maestros.bus_vehiculos.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Nuevo vehículo
                </a>
            </div>

            <div class="mb-3 flex flex-col gap-4 rounded-2xl border p-2.5 shadow-sm lg:flex-row lg:items-center"
                style="background-color:var(--bg-card);border-color:var(--border-color);">
                <form action="{{ route('admin.transporte.maestros.bus_vehiculos.index') }}" method="GET" class="relative w-full">
                    <input type="hidden" name="activo" value="{{ request('activo', 1) }}">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar placa o color..."
                        class="w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500"
                        style="background-color:var(--input-bg);border-color:var(--border-color);color:var(--text-main);">
                </form>
                <div class="flex shrink-0 items-center gap-2 rounded-xl border px-3 py-2" style="border-color:var(--border-color);">
                    <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Activos</span>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" id="estadoToggle" class="sr-only peer" {{ request('activo', 1) == 1 ? 'checked' : '' }}>
                        <span class="h-6 w-10 rounded-full bg-gray-300 transition peer-checked:bg-red-700 dark:bg-gray-700"></span>
                        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-4"></span>
                    </label>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-4 text-center">#</th>
                                <th class="px-6 py-4 text-center">Placa</th>
                                <th class="px-6 py-4 text-center">Marca / Modelo</th>
                                <th class="px-6 py-4 text-center">Año / Color</th>
                                <th class="px-6 py-4 text-center">Peso</th>
                                <th class="px-6 py-4 text-center">Combustible</th>
                                <th class="px-6 py-4 text-center">Capacidad</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-xs font-medium">
                            @forelse($vehiculos as $vehiculo)
                                <x-table-row :id="$vehiculo->id">
                                    <td class="px-6 py-4 text-center" style="color:var(--text-muted);">
                                        {{ ($vehiculos->currentPage() - 1) * $vehiculos->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold" style="color:var(--text-main);">{{ $vehiculo->placa }}</td>
                                    <td class="px-6 py-4 text-center" style="color:var(--text-main);">
                                        {{ $vehiculo->modelo->busMarca->nombre ?? '-' }} / {{ $vehiculo->modelo->nombre ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center" style="color:var(--text-muted);">
                                        {{ $vehiculo->anio }} · {{ $vehiculo->color }}
                                    </td>
                                    <td class="px-6 py-4 text-center" style="color:var(--text-muted);">{{ $vehiculo->peso }}</td>
                                    <td class="px-6 py-4 text-center" style="color:var(--text-muted);">
                                        <span title="Urbano: {{ $vehiculo->consumo_urbano }} L/km | Carretera: {{ $vehiculo->consumo_carretera }} L/km | Ralentí: {{ $vehiculo->consumo_relenti }} L/h">
                                            {{ $vehiculo->tipoCombustible->nombre ?? '-' }}
                                            <i class="fas fa-info-circle ml-1 text-gray-400"></i>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center" style="color:var(--text-muted);">{{ $vehiculo->cantidad_pasajeros }} pas.</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        @if ($vehiculo->activo)
                                            <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                <i class="fas fa-check-circle"></i> Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400">
                                                <i class="fas fa-times-circle"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <x-table-actions :id="$vehiculo->id" baseUrl="admin/transporte/maestros/bus_vehiculos"
                                        :status="$vehiculo->activo" :show="false" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        No hay vehículos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($vehiculos->hasPages())
                    <div class="flex justify-center border-t p-4" style="border-color:var(--border-color);">
                        {{ $vehiculos->onEachSide(1)->appends(request()->query())->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.getElementById('estadoToggle')?.addEventListener('change', function () {
                const params = new URLSearchParams(window.location.search);
                params.set('activo', this.checked ? '1' : '0');
                window.location.href = "{{ route('admin.transporte.maestros.bus_vehiculos.index') }}?" + params.toString();
            });
        </script>
    @endpush
</x-app-layout>
