<x-app-layout>


    <div class="mb-6 flex flex-col gap-4 rounded-2xl border p-5 shadow-sm md:flex-row md:items-center md:justify-between"
        style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">Cargas de Combustible</h1>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                Bienvenido <strong>{{ auth()->user()->persona->nombres_per }}</strong>.
            </p>
        </div>
        <div>
            @canRoute('admin.transporte.maestros.bus_carga_combustibles.create')
            <a href="{{ route('admin.transporte.maestros.bus_carga_combustibles.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                <i class="fas fa-plus"></i> Nueva Carga
            </a>
            @endcanRoute
        </div>
    </div>



    @include('components.alert')

    <div class="rd-card rd-card-full">
        <div class="rd-card-body">
            <div class="rd-card-header rd-header-space">
                <div>
                    <h3 class="rd-title-sm">Historial de Recargas</h3>
                </div>
                <div class="rd-actions">
                    <form action="{{ route('admin.transporte.maestros.bus_carga_combustibles.index') }}" method="GET"
                        class="d-flex gap-3 align-items-center">
                        <select name="vehiculo_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input" style="width:180px;"
                            onchange="this.form.submit()">
                            <option value="">Todos los vehículos</option>
                            @foreach($vehiculos as $v)
                                <option value="{{ $v->id_vtr }}" {{ request('vehiculo_id') == $v->id_vtr ? 'selected' : '' }}>
                                    {{ $v->placa_vtr }}
                                </option>
                            @endforeach
                        </select>
                        <div class="rd-search-inline">
                            <input type="text" name="buscar" value="{{ request('buscar') }}"
                                class="rd-search-input" placeholder="Buscar placa, tipo o nota..." />
                            <button class="rd-icon-btn" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border" style="border-color:var(--border-color);"><table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Vehículo</th>
                        <th class="text-center">Tipo Combustible</th>
                        <th class="text-center">Boca #</th>
                        <th class="text-center">Litros</th>
                        <th class="text-center">Precio / L</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">KM Odómetro</th>
                        <th style="width:140px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cargas as $c)
                        <x-table-row :id="$c->id_ccb">
                            <td class="text-center">
                                {{ ($cargas->currentPage() - 1) * $cargas->perPage() + $loop->iteration }}
                            </td>
                            <td class="text-center">{{ $c->fecha_ccb ? $c->fecha_ccb->format('d/m/Y') : '-' }}</td>
                            <td class="text-center"><strong>{{ $c->vehiculo->placa_vtr ?? '-' }}</strong></td>
                            <td class="text-center">
                                <span class="rd-badge rd-badge-info">{{ $c->tipoCombustible->nombre_tcb ?? '-' }}</span>
                            </td>
                            <td class="text-center">{{ $c->boca_numero }}</td>
                            <td class="text-center"><strong>{{ number_format($c->litros_ccb, 2) }} L</strong></td>
                            <td class="text-center">${{ number_format($c->precio_litro_ccb, 2) }}</td>
                            <td class="text-center" style="color:var(--color-primary);font-weight:700;">
                                ${{ number_format($c->total_ccb, 2) }}
                            </td>
                            <td class="text-center">{{ number_format($c->km_al_cargar_ccb, 1) }} km</td>
                            <td class="text-center">
                                <div class="rd-action-group">
                                    @canRoute('admin.transporte.maestros.bus_carga_combustibles.edit')
                                    <a href="{{ route('admin.transporte.maestros.bus_carga_combustibles.edit', $c) }}"
                                        class="rd-action" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcanRoute
                                    @canRoute('admin.transporte.maestros.bus_carga_combustibles.destroy')
                                    <form action="{{ route('admin.transporte.maestros.bus_carga_combustibles.destroy', $c) }}"
                                        method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rd-action rd-btn-danger"
                                            onclick="confirmEliminar(event, this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanRoute
                                </div>
                            </td>
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">No hay cargas de combustible registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table></div></div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $cargas->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>


@push('js')
<script>
function confirmEliminar(event, button) {
    event.preventDefault();
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Desea eliminar este registro de carga? Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) button.closest('form').submit();
    });
}
</script>
@endpush
</x-app-layout>
