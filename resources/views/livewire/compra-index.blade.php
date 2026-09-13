<div class="main-content">

    <div class="rd-card rd-card-full">

        <div class="rd-card-body">
            <div class="rd-card-header rd-header-space">
                <div>
                    <h3 class="rd-title-sm">Requisiciones Registradas</h3>
                </div>

                <div class="rd-actions">
                    <div class="rd-search-inline">
                        <input type="text" class="rd-search-input" placeholder="Escriba la compra"
                            wire:model.live="buscar" />
                    </div>

                    <button class="rd-icon-btn"   aria-expanded="false"
                        aria-controls="filters" title="Filtros">
                        <i class="fas fa-filter"></i>
                    </button>

                    <div class="rd-export-group">
                        <button class="rd-btn rd-btn-danger" id="pdfBtn" title="Exportar PDF"><i
                                class="fas fa-file-pdf"></i>
                            PDF</button>
                    </div>
                </div>
            </div>

            <div class="collapse" id="filters">
                <div class="rd-filters">
                    <form action="{{ route('admin.movimientos.compras.index') }}" method="GET"
                        class="rd-filters-form">
                        <div class="rd-filter-row">
                            <label>Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" />
                        </div>
                        <div class="rd-filter-row">
                            <label>Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" />
                        </div>
                        <div class="rd-filter-row rd-filter-actions">
                            <button class="rd-btn rd-btn-primary" type="submit">Aplicar</button>
                            <button type="button" class="rd-btn rd-btn-default"
                                onclick="document.getElementById('fecha_desde').value=''; document.getElementById('fecha_hasta').value='';">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabla --}}
            <div id="printArea">
                <table class="rd-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Proveedor</th>
                            <th>Fecha de la Requisicion</th>
                            <th>Total</th>
                            <th style="width:120px">Requisicion</th>
                            <th style="width:150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compras as $compra)
                            <tr>
                                <td class="text-center">
                                    {{ ($compras->currentPage() - 1) * $compras->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ $compra->proveedor_nombre }}</td>
                                <td>{{ $compra->fecha }}</td>
                                <td>{{ number_format($compra->total, 2, ',', '.') }} .BS</td>
                                <td class="text-center">
                                    @if ($compra->estado == 'Pendiente')
                                        <span class="rd-badge rd-badge-danger">Pendiente</span>
                                    @elseif ($compra->estado == 'Enviado al proveedor')
                                        <span class="rd-badge rd-badge-warning">En espera</span>
                                    @else
                                        <span class="rd-badge rd-badge-success">Finalizada</span>
                                    @endif
                                </td>

                                @if ($compra->estado == 'Pendiente' || $compra->estado == 'Enviado al proveedor')
                                    <td class="text-center">
                                        <div class="rd-action-group">

                                            <a href="{{ url('admin/movimientos/compras/' . $compra->id . '/edit') }}"
                                                class="rd-action" title="Continuar"><i class="fas fa-arrow-right"></i></a>

                                            <a href="{{ url('admin/movimientos/compras/' . $compra->id) }}"
                                                class="rd-action" title="Ver Detalles"><i class="fas fa-eye"></i></a>

                                            <form action="{{ url('admin/movimientos/compras/' . $compra->id) }}"
                                                method="POST" class="form-delete" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rd-action rd-btn-danger"
                                                    onclick="confirmDelete(event, this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>

                                            <script>
                                                function confirmDelete(event, button) {
                                                    event.preventDefault();
                                                    Swal.fire({
                                                        title: '¿Estás seguro?',
                                                        text: "Desea inactivar la compra?",
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonColor: '#3085d6',
                                                        cancelButtonColor: '#d33',
                                                        confirmButtonText: 'Sí, inactivar',
                                                        cancelButtonText: 'Cancelar'
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            button.closest('form').submit();
                                                        }
                                                    });
                                                }
                                            </script>

                                        </div>
                                    </td>
                                @else
                                    <td class="text-center">
                                        <div class="rd-action-group">
                                            <a href="{{ url('admin/movimientos/compras/' . $compra->id) }}"
                                                class="rd-action" title="Ver Detalles"><i class="fas fa-eye"></i></a>
                                        </div>
                                    </td>
                                @endif

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No hay Requisiciones</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación del servidor --}}
            <div class="mt-3 d-flex justify-content-center">
                {{ $compras->onEachSide(1)->links('components.pagination-livewire') }}
            </div>
        </div>
    </div>

</div>
