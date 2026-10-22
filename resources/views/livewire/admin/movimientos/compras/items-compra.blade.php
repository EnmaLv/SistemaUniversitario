<div>
    @php
        $estiloCampo = 'background-color: var(--input-bg, rgba(0,0,0,0.02)); border-color: var(--input-border, var(--border-color)); color: var(--text-main);';
        $claseCampo = 'w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800 transition-all disabled:opacity-60 disabled:cursor-not-allowed';
        $hayProducto = $unidad !== [];
    @endphp

    <div class="rounded-2xl border p-4 sm:p-5"
        style="background-color: rgba(0,0,0,0.015); border-color: var(--border-color);">
        <p class="text-xs font-medium mb-4" style="color: var(--text-muted);">
            Elige un producto: el formulario te pide la cantidad en la unidad en que se compra (kilos, envases o unidades) y trae su precio vigente.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-start">
            <div class="lg:col-span-4">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="productoId" class="block titulos">Producto</label>
                    @canRoute('admin.maestros.productos.create')
                        <a href="{{ route('admin.maestros.productos.create', ['from' => url()->current()]) }}"
                            class="text-[10px] font-bold text-red-800 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors">
                            + Nuevo producto
                        </a>
                    @endcanRoute
                </div>

                <div class="relative">
                    <i class="fas fa-box absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    <select @disabled(! $editable) wire:model.live="productoId" id="productoId"
                        class="{{ $claseCampo }} pr-9 appearance-none" style="{{ $estiloCampo }}">
                        <option value="">Seleccione un producto</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id_pdt }}">
                                {{ $producto->codigo_pdt }} - {{ $producto->nombre_pdt }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                </div>
                @if ($productos->isEmpty())
                    <p class="mt-1.5 text-xs font-semibold text-amber-600">
                        No hay productos activos para el área de esta requisición. Regístralos primero en su catálogo (Productos, Medicamentos o Implementos y materiales).
                    </p>
                @endif
                @error('productoId')
                    <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-[10px]"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="sm:col-span-1 lg:col-span-2">
                <label for="lote" class="block titulos">Lote</label>
                <div class="relative">
                    <i class="fas fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    <input type="text" wire:model="codigoLote" id="lote" placeholder="Se genera solo" readonly
                        class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border opacity-70 cursor-not-allowed focus:outline-none"
                        style="{{ $estiloCampo }}">
                </div>
            </div>

            <div class="sm:col-span-1 lg:col-span-2">
                <label for="cantidad" class="block titulos">{{ $unidad['etiqueta'] ?? 'Cantidad' }}</label>
                <div class="relative">
                    <i class="fas {{ ($unidad['fraccionable'] ?? false) ? 'fa-weight-hanging' : 'fa-hashtag' }} absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    <input @disabled(! $editable || ! $hayProducto) type="number" id="cantidad"
                        wire:model.live.debounce.400ms="cantidad"
                        placeholder="{{ $unidad['placeholder'] ?? 'Primero elige un producto' }}"
                        step="{{ $unidad['paso'] ?? '1' }}" min="{{ $unidad['minimo'] ?? '1' }}"
                        inputmode="{{ ($unidad['fraccionable'] ?? false) ? 'decimal' : 'numeric' }}"
                        class="{{ $claseCampo }}" style="{{ $estiloCampo }}">
                </div>
                @error('cantidad')
                    <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-[10px]"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="sm:col-span-1 lg:col-span-2">
                <label for="precioCompra" class="block titulos">
                    Precio (Bs) {{ $unidad['precio_por'] ?? '' }}
                </label>
                <div class="relative">
                    <i class="fas fa-money-bill-wave absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    <input @disabled(! $editable || ! $hayProducto) type="number" wire:model.live.debounce.400ms="precioCompra"
                        id="precioCompra" placeholder="Ej: 120.50" step="0.01" min="0" inputmode="decimal"
                        class="{{ $claseCampo }}" style="{{ $estiloCampo }}">
                </div>
                @error('precioCompra')
                    <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-[10px]"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="sm:col-span-2 lg:col-span-2">
                <span class="block titulos opacity-0 pointer-events-none">Acción</span>
                @if ($editable)
                    @canAction('compras', 'editar')
                        <button type="button" wire:click="agregarItems" wire:loading.attr="disabled"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-md shadow-red-800/20 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-plus text-xs"></i>
                            <span wire:loading.remove wire:target="agregarItems">Agregar</span>
                            <span wire:loading wire:target="agregarItems">Agregando...</span>
                        </button>
                    @endcanAction
                @else
                    <div class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl border text-sm font-bold opacity-60"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-lock text-xs"></i> Pedido cerrado
                    </div>
                @endif
            </div>
        </div>

        @if ($hayProducto)
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="p-3 rounded-xl border flex items-start gap-3" style="border-color: var(--border-color); background-color: var(--bg-card);">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-ruler text-xs"></i>
                    </div>
                    <div class="text-xs" style="color: var(--text-main);">
                        <p class="font-bold">{{ $unidad['tipo'] ?? 'Producto' }} · se compra por {{ $unidad['unidad'] }}</p>
                        <p class="mt-0.5" style="color: var(--text-muted);">{{ $unidad['ayuda'] }}</p>
                    </div>
                </div>

                <div class="p-3 rounded-xl border flex items-start gap-3" style="border-color: var(--border-color); background-color: var(--bg-card);">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-tag text-xs"></i>
                    </div>
                    <div class="text-xs" style="color: var(--text-main);">
                        @if (($precio['usd'] ?? null) !== null)
                            <p class="font-bold">
                                Precio vigente: {{ number_format($precio['usd'], 2, ',', '.') }} USD {{ $unidad['precio_por'] }}
                                @if ($precio['bs'] !== null)
                                    ≈ {{ number_format($precio['bs'], 2, ',', '.') }} Bs
                                @endif
                            </p>
                            <p class="mt-0.5" style="color: var(--text-muted);">
                                @if ($precio['bs'] !== null)
                                    Calculado con la tasa del {{ \Carbon\Carbon::parse($precio['fecha_tasa'])->format('d/m/Y') }} ({{ number_format($precio['tasa'], 2, ',', '.') }} Bs por dólar). Puedes cambiarlo si el proveedor cobra otro precio.
                                @else
                                    No hay tasa del dólar registrada: escribe el precio en bolívares.
                                @endif
                            </p>
                        @else
                            <p class="font-bold">Este producto no tiene precio registrado.</p>
                            <p class="mt-0.5" style="color: var(--text-muted);">
                                Escribe el precio {{ $unidad['precio_por'] }} en bolívares. Para que aparezca solo, regístralo en la ficha del producto.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-4 pt-4 border-t flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] font-medium"
            style="border-color: var(--border-color); color: var(--text-muted);">
            <span class="flex items-center gap-2">
                <i class="fas fa-info-circle text-[10px]"></i>
                @if ($resumen)
                    <span>
                        <strong style="color: var(--text-main);">Resumen:</strong> {{ $resumen['texto'] }}
                        @if ($resumen['subtotal'] !== null)
                            · Subtotal {{ number_format($resumen['subtotal'], 2, ',', '.') }} Bs
                        @endif
                    </span>
                @elseif ($hayProducto)
                    <span>Escribe la cantidad para ver cuánto entra al inventario.</span>
                @else
                    <span>Selecciona un producto para ver en qué unidad se compra y su precio.</span>
                @endif
            </span>
        </div>
    </div>

    <div x-data
        x-on:mostrar-alerta.window="
            const detalle = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
            if (window.Swal) {
                Swal.fire({
                    position: 'center',
                    icon: detalle.icono || 'info',
                    title: detalle.mensaje || '',
                    showConfirmButton: detalle.icono === 'error',
                    timer: detalle.icono === 'error' ? undefined : (detalle.timer || 3000),
                    timerProgressBar: detalle.icono !== 'error',
                    customClass: {
                        popup: 'rounded-2xl dark:bg-gray-800 dark:text-gray-100 dark:border dark:border-gray-700'
                    }
                });
            } else {
                alert(detalle.mensaje || '');
            }
        ">
    </div>

    <div class="mt-6">
        @if ($compra->detalleCompras->count() > 0)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h4 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">Productos agregados</h4>
                    <p class="text-[11px] font-medium mt-0.5" style="color: var(--text-muted);">
                        Cantidad comprada, lo que entra al inventario y el precio de cada producto.
                    </p>
                </div>
                <span class="inline-flex self-start sm:self-auto items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-bold">
                    <i class="fas fa-boxes text-[9px]"></i>
                    {{ $compra->detalleCompras->count() }}
                    {{ $compra->detalleCompras->count() == 1 ? 'producto' : 'productos' }}
                </span>
            </div>

            <div class="overflow-x-auto rounded-2xl border" style="border-color: var(--border-color);">
                <table class="w-full text-sm">
                    <thead style="background-color: rgba(0,0,0,0.025); border-bottom: 1px solid var(--border-color);">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">Producto</th>
                            <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">Lote</th>
                            <th class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">Cantidad comprada</th>
                            <th class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">Entra al inventario</th>
                            <th class="px-4 py-3 text-right text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">Precio</th>
                            <th class="px-4 py-3 text-right text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">Subtotal</th>
                            <th class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">Acción</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @foreach ($compra->detalleCompras as $detalle)
                            @php
                                $u = $unidadesDetalle[$detalle->id_dcm] ?? null;
                                $icono = match ($u?->modo) {
                                    \App\Support\Inventario\UnidadCompra::MODO_PESO => 'fa-weight-hanging',
                                    \App\Support\Inventario\UnidadCompra::MODO_ENVASE => 'fa-box',
                                    default => 'fa-cube',
                                };
                            @endphp
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]" style="border-color: var(--border-color);">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-800 dark:text-red-400 flex items-center justify-center shrink-0">
                                            <i class="fas {{ $icono }} text-[10px]"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold truncate" style="color: var(--text-main);">{{ $detalle->producto->nombre_pdt }}</p>
                                            <p class="text-[10px] mt-0.5" style="color: var(--text-muted);">
                                                {{ $detalle->producto->codigo_pdt }}{{ $u?->tipo ? ' · ' . $u->tipo : '' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-[10px] font-mono font-semibold text-gray-600 dark:text-gray-300">
                                        {{ $detalle->lote->codigo_lote_lot ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-semibold whitespace-nowrap" style="color: var(--text-main);">
                                    {{ $u ? $u->describirCantidad((float) $detalle->cantidad_dcm) : \App\Support\Inventario\UnidadCompra::numero((float) $detalle->cantidad_dcm) }}
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-semibold whitespace-nowrap" style="color: var(--text-main);">
                                    {{ \App\Support\Inventario\UnidadCompra::numero((float) $detalle->cantidad_convertida_dcm) }}
                                    <span class="text-[10px] font-normal ml-0.5" style="color: var(--text-muted);">{{ $u->unidadBase ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-xs font-semibold whitespace-nowrap" style="color: var(--text-main);">
                                    {{ number_format($detalle->precio_unitario_dcm, 2, ',', '.') }} Bs
                                    <span class="block text-[10px] font-normal" style="color: var(--text-muted);">{{ $u?->precioPor() }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-xs font-extrabold whitespace-nowrap" style="color: var(--text-main);">
                                    {{ number_format($detalle->subtotal_dcm, 2, ',', '.') }} Bs
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($editable)
                                        @canAction('compras', 'editar')
                                            <button type="button"
                                                x-on:click="
                                                    const quitar = () => $wire.eliminarItem({{ $detalle->id_dcm }});
                                                    if (window.Swal) {
                                                        Swal.fire({ title: '¿Quitar este producto?', text: 'Se quitará de la requisición.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, quitar', cancelButtonText: 'No', confirmButtonColor: '#9f1239' }).then(r => { if (r.isConfirmed) quitar(); });
                                                    } else if (confirm('¿Quitar este producto de la requisición?')) {
                                                        quitar();
                                                    }
                                                "
                                                wire:loading.attr="disabled" title="Quitar de la requisición"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border text-rose-500 hover:bg-rose-50 hover:border-rose-200 dark:hover:bg-rose-950/30 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                                                style="border-color: var(--border-color);">
                                                <i class="fas fa-trash text-[10px]"></i>
                                            </button>
                                        @endcanAction
                                    @else
                                        <i class="fas fa-lock text-[10px] text-gray-300 dark:text-gray-600" title="Pedido cerrado"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-2xl border border-dashed p-8 text-center" style="border-color: var(--border-color);">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mb-3">
                    <i class="fas fa-box-open"></i>
                </div>
                <p class="text-sm font-bold" style="color: var(--text-main);">Todavía no hay productos en esta requisición</p>
                <p class="text-xs mt-1" style="color: var(--text-muted);">
                    Elige un producto arriba, escribe la cantidad y el precio, y pulsa "Agregar". Necesitas al menos uno para enviarla al proveedor.
                </p>
            </div>
        @endif
    </div>

    <div class="mt-5 p-4 sm:p-5 rounded-2xl border flex flex-col md:flex-row md:items-center justify-between gap-4"
        style="background-color: rgba(0,0,0,0.015); border-color: var(--border-color);">
        <div>
            <p class="text-[10px] uppercase tracking-wider font-bold" style="color: var(--text-muted);">Total de la requisición</p>
            <p class="text-xl sm:text-2xl font-black mt-0.5" style="color: var(--text-main);">
                {{ number_format($compra->total_cmp, 2, ',', '.') }} Bs
            </p>
        </div>

        @if ($compra->detalleCompras->count() == 0)
            <div class="inline-flex items-center gap-2 text-xs font-semibold text-rose-500">
                <i class="fas fa-exclamation-circle"></i> Agrega productos para continuar.
            </div>
        @elseif (! $editable)
            <div class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <i class="fas fa-check-circle"></i> El pedido ya fue enviado al proveedor.
            </div>
        @else
            @canRoute('admin.movimientos.compras.enviarCorreo')
                <button type="button" wire:click="confirmarEnvio"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-md shadow-red-800/20 active:scale-95 transition-all">
                    <i class="fas fa-paper-plane text-xs"></i> Enviar al proveedor
                </button>
            @endcanRoute
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('confirmar-envio', event => {
            const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            const compraId = detail?.compraId;

            if (!compraId) {
                return;
            }

            const enviar = () => {
                window.location.href = `{{ url('admin/movimientos/compras') }}/${compraId}/enviar-correo`;
            };

            if (!window.Swal) {
                if (confirm('¿Enviar el pedido al proveedor por correo? Luego no podrás agregar más productos.')) {
                    enviar();
                }
                return;
            }

            Swal.fire({
                title: '¿Enviar el pedido al proveedor?',
                text: 'Se le enviará por correo. Luego no podrás agregar ni quitar productos.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#9f1239',
                cancelButtonColor: '#6b7280',
                customClass: {
                    popup: 'rounded-2xl dark:bg-gray-800 dark:text-gray-100 dark:border dark:border-gray-700'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    enviar();
                }
            });
        });
    });
</script>
