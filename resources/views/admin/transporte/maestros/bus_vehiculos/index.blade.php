<x-app-layout>

    @include('components.alert')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                Vehículos
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                Bienvenido <span
                    class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span>.
                Gestione los viajes activos y programados.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.transporte.maestros.bus_vehiculos.create') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-lg active:scale-95 transition-all">
                <i class="fas fa-bus text-xs"></i>
                <span>Nuevo Vehiculo</span>
            </a>
        </div>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-3">

        <form action="{{ route('admin.transporte.maestros.bus_vehiculos.index') }}" method="GET" id="filterForm"
            class="flex flex-col sm:flex-row items-center gap-3 w-full">

            <div class="relative w-full lg:w-48 shrink-0">
                <select name="estado" onchange="this.form.submit()"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full py-2.5 px-3 rounded-xl border text-xs font-bold focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                    <option value="todos" {{ request('estado') == 'todos' || !request('estado') ? 'selected' : '' }}>
                        Todos los estados</option>
                    <option value="disponible" {{ request('estado') == 'disponible' ? 'selected' : '' }}>Disponible
                    </option>
                    <option value="mantenimiento" {{ request('estado') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                </select>
            </div>

            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </div>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                    placeholder="Buscar placa o color..."
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full pl-10 pr-10 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                <button type="submit"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-600 transition-colors">
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>
        <div class="flex items-center gap-2 px-3 py-2 rounded-xl border "
            style="border-color: var(--border-color);">
            <span
                class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Activos</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="estadoToggle" class="sr-only peer"
                    {{ request('activo', 1) == 1 ? 'checked' : '' }}>
                <div
                    class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-red-700 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4">
                </div>
            </label>
        </div>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden">
        <div class="overflow-x-auto" id="printArea">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                        <th class="px-6 py-4 text-center" style="width:60px">#</th>
                        <th class="px-6 py-4 text-center">Placa</th>
                        <th class="px-6 py-4 text-center">Marca / Modelo</th>
                        <th class="px-6 py-4 text-center">Año / Color</th>
                        <th class="px-6 py-4 text-center">Peso</th>
                        <th class="px-6 py-4 text-center">Combustible</th>
                        <th class="px-6 py-4 text-center">Capacidad</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th style="width:150px" class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                    @forelse($vehiculos as $vehiculo)
                        <x-table-row :id="$vehiculo->id">
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                    {{ ($vehiculos->currentPage() - 1) * $vehiculos->perPage() + $loop->iteration }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                style="color: var(--text-main);">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fas fa-bus text-red-600 dark:text-red-500"></i>
                                    {{ $vehiculo->placa }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="font-bold" style="color: var(--text-main);">
                                    {{ $vehiculo->modelo->busMarca->nombre ?? '-' }} /
                                    {{ $vehiculo->modelo->nombre ?? '-' }}
                                </div>

                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="font-bold" style="color: var(--text-main);">
                                    {{ $vehiculo->anio }} - <small class="text-muted">{{ $vehiculo->color }}</small>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                {{ $vehiculo->peso }}
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span
                                    title="Urbano: {{ $vehiculo->consumo_urbano }} L/km | Carretera: {{ $vehiculo->consumo_carretera }} L/km | Ralentí: {{ $vehiculo->consumo_relenti }} L/h">
                                    {{ $vehiculo->tipoCombustible->nombre ?? '-' }} <i
                                        class="fas fa-info-circle text-muted" style="font-size:0.8rem;"></i>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap font-semibold font-bold"
                                style="color: var(--text-main);">
                                {{ $vehiculo->cantidad_pasajeros }} pas.
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                {!! $vehiculo->estado_badge !!}
                            </td>

                            <x-table-actions :id="$vehiculo->id" baseUrl="admin/transporte/maestros/bus_vehiculos"
                                :status="$vehiculo->activo" :show="false" />

                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">No hay vehículos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3 d-flex justify-content-center">
                {{ $vehiculos->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
    <script>
        function confirmAccion(event, button, accion, entidad) {
            event.preventDefault();
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Desea ${accion} el ${entidad}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) button.closest('form').submit();
            });
        }

        document.getElementById('estadoToggle').addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('activo', this.checked ? 1 : 0);
            window.location.href = "{{ route('admin.transporte.maestros.bus_vehiculos.index') }}?" + params
                .toString();
        });
    </script>
</x-app-layout>
