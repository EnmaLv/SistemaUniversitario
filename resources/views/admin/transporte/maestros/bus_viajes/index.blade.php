<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @include('components.alert')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                Despacho y Control de Viajes
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span> ·
                {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.transporte.maestros.bus_viajes.create') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-lg active:scale-95 transition-all">
                <i class="fas fa-bus text-xs"></i>
                <span>Programar nuevo viaje</span>
            </a>
        </div>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">

        <form action="{{ route('admin.transporte.maestros.bus_viajes.index') }}" method="GET" id="filterForm"
            class="flex flex-col sm:flex-row items-center gap-3 w-full">

            <div class="relative w-full lg:w-48 shrink-0">
                <select name="estado" onchange="this.form.submit()"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full py-2.5 px-3 rounded-xl border text-xs font-bold focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                    <option value="todos" {{ request('estado') == 'todos' || !request('estado') ? 'selected' : '' }}>
                        Todos los estados</option>
                    <option value="programado" {{ request('estado') == 'programado' ? 'selected' : '' }}>Programados
                    </option>
                    <option value="en_curso" {{ request('estado') == 'en_curso' ? 'selected' : '' }}>En Curso</option>
                    <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Finalizados
                    </option>
                    <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelados
                    </option>
                </select>
            </div>

            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </div>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                    placeholder="Buscar bus, ruta, chofer..."
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full pl-10 pr-10 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                <button type="submit"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-600 transition-colors">
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden">

        <div class="overflow-x-auto" id="printArea">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                        <th class="px-6 py-4 text-center" style="width: 60px;">#</th>
                        <th class="px-6 py-4 text-center">Vehículo</th>
                        <th class="px-6 py-4 text-center">Ruta Asignada</th>
                        <th class="px-6 py-4 text-center">Conductor</th>
                        <th class="px-6 py-4 text-center">Turno</th>
                        <th class="px-6 py-4 text-center">Inicio / Registro</th>
                        <th class="px-6 py-4 text-center" style="width: 130px;">Estado</th>
                        <th class="px-6 py-4 text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                    @forelse($viajes as $viaje)
                        <x-table-row :id="$viaje->id">
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                    {{ ($viajes->currentPage() - 1) * $viajes->perPage() + $loop->iteration }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                style="color: var(--text-main);">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fas fa-bus text-red-600 dark:text-red-500"></i>
                                    {{ $viaje->vehiculo->placa ?? 'N/A' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="font-bold" style="color: var(--text-main);">
                                    {{ $viaje->ruta->nombre ?? 'N/A' }}
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    {{ $viaje->ruta->distancia_km ?? '0' }} km
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($viaje->conductor && $viaje->conductor->persona)
                                    <span class="font-semibold" style="color: var(--text-main);">
                                        {{ $viaje->conductor->persona->nombre_persona }}
                                        {{ $viaje->conductor->persona->apellido_persona }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Por asignar</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($viaje->turno === 'mañana')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-[10px] font-black rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                        Mañana
                                    </span>
                                @elseif($viaje->turno === 'tarde')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-[10px] font-black rounded-lg bg-sky-100 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300">
                                        Tarde
                                    </span>
                                @elseif($viaje->turno === 'noche')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-[10px] font-black rounded-lg bg-slate-800 text-slate-100 dark:bg-slate-900 dark:text-slate-200">
                                        Noche
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td
                                class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400 font-semibold">
                                {{ $viaje->fecha_inicio ? $viaje->fecha_inicio->format('d/m/Y h:i A') : $viaje->created_at->format('d/m/Y h:i A') }}
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($viaje->estado === 'programado')
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                        Programado
                                    </span>
                                @elseif($viaje->estado === 'en_curso')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900 pulse-badge">
                                        <i class="fas fa-satellite-dish"></i> En Curso
                                    </span>
                                @elseif($viaje->estado === 'finalizado')
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                        Finalizado
                                    </span>
                                @elseif($viaje->estado === 'cancelado')
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                        Cancelado
                                    </span>
                                @endif
                            </td>

                            <x-table-actions :id="$viaje->id" baseUrl="admin/transporte/maestros/bus_viajes"
                                :status="$viaje->estado !== 'inactivo'" :toggle="false" :edit="$viaje->estado === 'programado'" :show="false">

                                @if (in_array($viaje->estado, ['programado', 'en_curso']))
                                    <button type="button" onclick="abrirModalCancelar({{ $viaje->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-950/50 transition-colors"
                                        title="Cancelar Viaje">
                                        <i class="fas fa-ban text-xs"></i>
                                    </button>
                                @endif

                                @if (in_array($viaje->estado, ['programado', 'en_curso']))
                                    <button type="button" onclick="confirmarEliminar({{ $viaje->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-950/50 transition-colors"
                                        title="Eliminar Viaje">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                @endif

                                <a href="{{ url('admin/transporte/maestros/bus_viajes/' . $viaje->id) }}"
                                    onclick="event.stopPropagation()"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-sky-500 hover:bg-sky-100 dark:hover:bg-sky-950/50 transition-colors"
                                    title="Ver en Mapa">
                                    <i class="fas fa-map-marked-alt text-xs"></i>
                                </a>

                                <form id="form-eliminar-{{ $viaje->id }}"
                                    action="{{ route('admin.transporte.maestros.bus_viajes.destroy', $viaje->id) }}"
                                    method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>

                            </x-table-actions>

                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="8"
                                class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                <i class="fas fa-bus text-3xl mb-3 block text-gray-300 dark:text-gray-700"></i>
                                No hay viajes registrados con el filtro seleccionado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($viajes->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                {{ $viajes->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
            </div>
        @endif
    </div>

    @include('admin.transporte.maestros.bus_viajes.modal-cancelar')

    <style>
        .pulse-badge {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% {
                box-shadow: 0 0 0 0px rgba(34, 197, 94, 0.4);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }

            100% {
                box-shadow: 0 0 0 0px rgba(34, 197, 94, 0);
            }
        }
    </style>

    <script>
        function abrirModalCancelar(id) {
            const url = `{{ url('admin/transporte/maestros/bus_viajes') }}/${id}/cancelar`;
            document.getElementById('formCancelarViaje').action = url;
            document.getElementById('motivo_cancelacion').value = '';
            document.getElementById('modalCancelarViaje').classList.remove('hidden');
        }

        function cerrarModalCancelar() {
            document.getElementById('modalCancelarViaje').classList.add('hidden');
        }

        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿Desea eliminar este viaje?',
                text: "El viaje pasará a estar inactivo y no se podrá recuperar.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'rounded-2xl dark:bg-gray-800 dark:text-white',
                    confirmButton: 'rounded-xl',
                    cancelButton: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`form-eliminar-${id}`).submit();
                }
            });
        }
    </script>
        </div>
    </div>
</x-app-layout>
