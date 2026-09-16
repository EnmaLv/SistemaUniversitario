<div>
    <div class="rounded-2xl border p-4 sm:p-5"
        style="
            background-color: rgba(0,0,0,0.015);
            border-color: var(--border-color);
        ">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-start">
            <div class="lg:col-span-4">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block titulos">
                        Producto
                    </label>
                    <a href="{{ route('admin.maestros.productos.create', [
                        'from' => url()->current(),
                    ]) }}"
                        class="text-[10px] font-bold text-red-800 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors">
                        + Nuevo producto
                    </a>
                </div>

                <div class="relative">
                    <i
                        class="fas fa-box absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                    </i>
                    <select @disabled($compra->estado == 'Enviado al proveedor') name="nombre" wire:model.live="productoId" id="nombre"
                        class="w-full pl-10 pr-9 py-3 text-sm font-medium rounded-xl border appearance-none focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                        style="
                            background-color: rgba(0,0,0,0.02);
                            border-color: var(--border-color);
                            color: var(--text-main);
                        ">
                        <option value="">
                            Seleccione un producto
                        </option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}"
                                {{ old('productoId', request('productoId')) == $producto->id ? 'selected' : '' }}>

                                {{ $producto->codigo }} - {{ $producto->nombre }}

                            </option>
                        @endforeach
                    </select>
                    <i
                        class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none">
                    </i>
                </div>
                @error('productoId')
                    <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-[10px]"></i>
                        {{ 'Este campo es obligatorio.' }}
                    </p>
                @enderror
            </div>

            <div class="sm:col-span-1 lg:col-span-2">
                <label class="block titulos">
                    Lote
                </label>
                <div class="relative">
                    <i
                        class="fas fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                    </i>
                    <input type="text" wire:model="codigoLote" id="lote" name="lote"
                        placeholder="Código de lote" readonly
                        class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border opacity-70 cursor-not-allowed focus:outline-none"
                        style="
                            background-color: rgba(0,0,0,0.02);
                            border-color: var(--border-color);
                            color: var(--text-main);
                        ">

                </div>
                @error('codigoLote')
                    <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-[10px]"></i>
                        {{ 'Este campo es obligatorio.' }}
                    </p>
                @enderror
            </div>

            <div class="sm:col-span-1 lg:col-span-2">
                <label class="block titulos">
                    Cantidad (U)
                </label>
                <div class="relative">
                    <i
                        class="fas fa-plus absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                    </i>
                    <input @disabled($compra->estado == 'Enviado al proveedor') type="number" id="cantidad" wire:model="cantidad"
                        name="cantidad" placeholder="Cantidad" value="{{ old('cantidad', $compra->cantidad) }}"
                        min="0"
                        class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                        style="
                            background-color: rgba(0,0,0,0.02);
                            border-color: var(--border-color);
                            color: var(--text-main);
                        ">

                </div>
                @error('cantidad')
                    <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-[10px]"></i>
                        {{ 'Este campo es obligatorio.' }}
                    </p>
                @enderror
            </div>

            <div class="sm:col-span-1 lg:col-span-2">
                <label class="block titulos">
                    Precio compra (.BS)
                </label>
                <div class="relative">
                    <i
                        class="fas fa-dollar-sign absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                    </i>
                    <input @disabled($compra->estado == 'Enviado al proveedor') type="number" wire:model="precioCompra" id="precioCompra"
                        name="precio_compra" placeholder="Precio"
                        value="{{ old('precio_compra', $compra->precio_compra) }}" min="0"
                        class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                        style="
                            background-color: rgba(0,0,0,0.02);
                            border-color: var(--border-color);
                            color: var(--text-main);
                        ">
                </div>
                @error('precioCompra')
                    <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                        <i class="fas fa-info-circle text-[10px]"></i>
                        {{ 'Este campo es obligatorio.' }}
                    </p>
                @enderror
            </div>

            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block titulos opacity-0 pointer-events-none">
                    Acción
                </label>
                @if ($compra->estado != 'Enviado al proveedor')
                    <button type="button" wire:click="agregarItems" wire:loading.attr="disabled"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md shadow-red-800/20 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-plus text-xs"></i>
                        <span wire:loading.remove wire:target="agregarItems">
                            Agregar
                        </span>

                        <span wire:loading wire:target="agregarItems">
                            Agregando...
                        </span>
                    </button>
                @else
                    <div class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl border text-sm font-bold opacity-50"
                        style="
                            border-color: var(--border-color);
                            color: var(--text-main);
                        ">

                        <i class="fas fa-lock text-xs"></i>
                        Pedido cerrado
                    </div>
                @endif
            </div>
        </div>
        <div class="mt-4 pt-4 border-t flex items-center gap-2 text-[11px] font-medium text-gray-400"
            style="border-color: var(--border-color);">

            <i class="fas fa-info-circle text-[10px]"></i>
            <span>
                Selecciona un producto, verifica su lote, cantidad y precio antes de agregarlo.
            </span>
        </div>
    </div>

    <div x-data
        x-on:mostrar-alerta.window="
            Swal.fire({
                position: 'center',
                icon: $event.detail.icono || 'info',
                title: $event.detail.mensaje || '',
                showConfirmButton: false,
                timer: $event.detail.timer || 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-2xl dark:bg-gray-800 dark:text-gray-100 dark:border dark:border-gray-700'
                }
            });
        ">
    </div>

    <div class="mt-6">
        @if ($compra->detalleCompras->count() > 0)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h4 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">

                        Productos agregados

                    </h4>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">

                        Detalle de los productos incluidos en la requisición.

                    </p>
                </div>
                <span
                    class="inline-flex self-start sm:self-auto items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-bold">
                    <i class="fas fa-boxes text-[9px]"></i>
                    {{ $compra->detalleCompras->count() }}
                    {{ $compra->detalleCompras->count() == 1 ? 'producto' : 'productos' }}
                </span>
            </div>

            <div class="overflow-x-auto rounded-2xl border" style="border-color: var(--border-color);">
                <table class="w-full text-sm">
                    <thead
                        style="
                            background-color: rgba(0,0,0,0.025);
                            border-bottom: 1px solid var(--border-color);
                        ">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">
                                Producto
                            </th>
                            <th
                                class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Lote
                            </th>
                            <th
                                class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Cantidad (U)
                            </th>
                            <th
                                class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Cantidad (g)
                            </th>
                            <th
                                class="px-4 py-3 text-right text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Precio unitario
                            </th>
                            <th
                                class="px-4 py-3 text-right text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Subtotal
                            </th>
                            <th
                                class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">
                                Acción
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y" style="--tw-divide-opacity: 1;">
                        @foreach ($compra->detalleCompras as $detalle)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                                style="border-color: var(--border-color);">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-800 dark:text-red-400 flex items-center justify-center shrink-0">

                                            <i class="fas fa-box text-[10px]"></i>

                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold truncate" style="color: var(--text-main);">

                                                {{ $detalle->producto->nombre }}

                                            </p>
                                            @if ($detalle->producto->codigo ?? false)
                                                <p class="text-[10px] text-gray-400 mt-0.5">
                                                    {{ $detalle->producto->codigo }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-[10px] font-mono font-semibold text-gray-600 dark:text-gray-300">

                                        {{ $detalle->lote->codigo_lote }}

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-semibold"
                                    style="color: var(--text-main);">

                                    {{ round($detalle->cantidad) }}

                                </td>
                                <td class="px-4 py-3 text-center text-xs font-semibold"
                                    style="color: var(--text-main);">

                                    {{ $detalle->cantidad_convertida }}

                                </td>
                                <td class="px-4 py-3 text-right text-xs font-semibold whitespace-nowrap"
                                    style="color: var(--text-main);">

                                    {{ number_format($detalle->precio_unitario, 2, ',', '.') }}
                                    .BS

                                </td>
                                <td class="px-4 py-3 text-right text-xs font-extrabold whitespace-nowrap"
                                    style="color: var(--text-main);">

                                    {{ number_format($detalle->subtotal, 2, ',', '.') }}
                                    .BS

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button @disabled($compra->estado == 'Enviado al proveedor') type="button"
                                        wire:click="eliminarItem({{ $detalle->id }})" wire:loading.attr="disabled"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg border text-rose-500 hover:bg-rose-50 hover:border-rose-200 dark:hover:bg-rose-950/30 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                                        style="border-color: var(--border-color);">

                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-2xl border border-dashed p-8 text-center" style="border-color: var(--border-color);">
                <div
                    class="w-12 h-12 mx-auto rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mb-3">

                    <i class="fas fa-box-open"></i>

                </div>
                <p class="text-sm font-bold" style="color: var(--text-main);">

                    No hay productos agregados

                </p>
                <p class="text-xs text-gray-400 mt-1">

                    Agrega al menos un producto para poder enviar la requisición.

                </p>
            </div>
        @endif
    </div>


    <div class="mt-5 p-4 sm:p-5 rounded-2xl border flex flex-col md:flex-row md:items-center justify-between gap-4"
        style="
            background-color: rgba(0,0,0,0.015);
            border-color: var(--border-color);
        ">
        <div>
            <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">
                Total de la requisición
            </p>
            <p class="text-xl sm:text-2xl font-black mt-0.5" style="color: var(--text-main);">

                {{ number_format($compra->total, 2, ',', '.') }} .BS
            </p>
        </div>

        @if ($compra->detalleCompras->count() == 0)
            <div class="inline-flex items-center gap-2 text-xs font-semibold text-rose-500">

                <i class="fas fa-exclamation-circle"></i>

                Agrega productos para continuar.

            </div>
        @elseif ($compra->estado == 'Enviado al proveedor')
            <div class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">

                <i class="fas fa-check-circle"></i>

                El pedido ya fue enviado a compras.

            </div>
        @else
            <button type="button" wire:click="confirmarEnvio"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md shadow-red-800/20 active:scale-95 transition-all">

                <i class="fas fa-paper-plane text-xs"></i>

                Enviar a compras

            </button>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('confirmar-envio', event => {
            const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            const compraId = detail?.compraId;

            Swal.fire({
                title: "¿Quieres enviar el correo a compras?",
                text: "Luego de enviarlo no podrás agregar más productos.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, enviar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#9f1239",
                cancelButtonColor: "#6b7280",
                customClass: {
                    popup: 'rounded-2xl dark:bg-gray-800 dark:text-gray-100 dark:border dark:border-gray-700'
                }
            }).then((result) => {
                if (result.isConfirmed && compraId) {
                    window.location.href = `/admin/movimientos/compras/${compraId}/enviar-correo`;
                }
            });
        });
    });
</script>
