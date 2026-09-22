<div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-bold" style="color:var(--text-main);">Producto</label>
            <select @disabled($compra->estado == 'Enviado al proveedor') wire:model.live="productoId" class="w-full rounded-xl border px-4 py-3 text-sm" style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);">
                <option value="">Seleccione un producto</option>
                @foreach ($productos as $producto)<option value="{{ $producto->id }}">{{ $producto->codigo }} - {{ $producto->nombre }}</option>@endforeach
            </select>
            @error('productoId')<p class="mt-2 text-sm font-semibold text-red-500">{{ $message }}</p>@enderror
        </div>
        <div><label class="mb-2 block text-sm font-bold" style="color:var(--text-main);">Lote</label><input wire:model="codigoLote" readonly placeholder="Código de lote" class="w-full rounded-xl border px-4 py-3 text-sm" style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);"></div>
        <div><label class="mb-2 block text-sm font-bold" style="color:var(--text-main);">Cantidad (U)</label><input type="number" wire:model="cantidad" min="0" placeholder="Cantidad" @disabled($compra->estado == 'Enviado al proveedor') class="w-full rounded-xl border px-4 py-3 text-sm" style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);"></div>
        <div><label class="mb-2 block text-sm font-bold" style="color:var(--text-main);">Precio (Bs.)</label><input type="number" wire:model="precioCompra" min="0" placeholder="Precio" @disabled($compra->estado == 'Enviado al proveedor') class="w-full rounded-xl border px-4 py-3 text-sm" style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);"></div>
    </div>
    @if ($compra->estado != 'Enviado al proveedor')
        <div class="mt-4 flex justify-end"><button type="button" wire:click="agregarItems" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900"><i class="fas fa-plus text-xs"></i> Agregar producto</button></div>
    @endif
    <div class="mt-6 overflow-x-auto">
        @if ($compra->detalleCompras->count() > 0)
            <table class="w-full min-w-[760px] text-left text-sm"><thead><tr class="border-b text-[11px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);"><th class="px-4 py-3">Producto</th><th class="px-4 py-3">Lote</th><th class="px-4 py-3">Cantidad</th><th class="px-4 py-3">Precio</th><th class="px-4 py-3">Subtotal</th><th class="px-4 py-3 text-center">Acciones</th></tr></thead>
                <tbody>@foreach ($compra->detalleCompras as $detalle)<tr class="border-b" style="border-color:var(--border-color);"><td class="px-4 py-3 font-bold" style="color:var(--text-main);">{{ $detalle->producto->nombre }}</td><td class="px-4 py-3" style="color:var(--text-main);">{{ $detalle->lote->codigo_lote }}</td><td class="px-4 py-3" style="color:var(--text-main);">{{ round($detalle->cantidad) }}</td><td class="px-4 py-3" style="color:var(--text-main);">{{ number_format($detalle->precio_unitario,2,',','.') }} Bs.</td><td class="px-4 py-3 font-bold" style="color:var(--text-main);">{{ number_format($detalle->subtotal,2,',','.') }} Bs.</td><td class="px-4 py-3 text-center"><button @disabled($compra->estado == 'Enviado al proveedor') wire:click="eliminarItem({{ $detalle->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-700 text-red-400 hover:bg-red-950/50"><i class="fas fa-trash text-xs"></i></button></td></tr>@endforeach</tbody></table>
        @else
            <div class="rounded-xl border border-dashed p-8 text-center text-sm font-bold text-gray-400" style="border-color:var(--border-color);">No hay productos agregados a la requisición.</div>
        @endif
    </div>
    <div class="mt-5 flex justify-end border-t pt-4 text-base font-extrabold" style="border-color:var(--border-color);color:var(--text-main);">Total: {{ number_format($compra->total,2,',','.') }} Bs.</div>

    <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
        @if ($compra->detalleCompras->count() === 0)
            <span class="text-xs font-semibold text-amber-400">
                Agrega productos para enviar la requisición.
            </span>
        @elseif ($compra->estado === 'Enviado al proveedor')
            <span class="text-xs font-semibold text-emerald-400">
                La requisición ya fue enviada a Compras.
            </span>
        @else
            <button type="button" wire:click="confirmarEnvio"
                class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                <i class="fas fa-paper-plane text-xs"></i>
                Enviar a Compras
            </button>
        @endif
    </div>

    @script
        <script>
            $wire.on('confirmar-envio', (event) => {
                Swal.fire({
                    title: '¿Quieres enviar el correo a Compras?',
                    text: 'Luego de enviarlo no podrás agregar más productos ni enviar otro correo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#475569',
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `/admin/movimientos/compras/${event.compraId}/enviar-correo`;
                    }
                });
            });
        </script>
    @endscript
</div>
