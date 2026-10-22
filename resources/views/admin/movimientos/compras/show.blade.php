<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            @php
                $finalizada = $compra->estado_compra_cmp === \App\Models\Compra::ESTADO_FINALIZADA;
                $sedeCompra = $compra->sed_id ? \Illuminate\Support\Facades\DB::table('sede')->where('id_sed', $compra->sed_id)->value('nombre_sed') : null;
                $datosCompra = [
                    ['fa-user-tie', 'Proveedor', $compra->proveedor_empresa ?: $compra->proveedor_nombre],
                    ['fa-calendar-alt', 'Fecha', \Carbon\Carbon::parse($compra->fecha_compra_cmp)->format('d/m/Y H:i')],
                    ['fa-flag', 'Estado', $compra->estado_compra_cmp],
                    ['fa-store-alt', 'Sede que compró', $sedeCompra ?? '—'],
                    ['fa-sticky-note', 'Observaciones', $compra->observacion_cmp ?: 'Sin observaciones'],
                ];
            @endphp

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Requisición #{{ $compra->id_cmp }}
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium" style="color: var(--text-muted);">
                        Consulta qué se compró y cómo se repartió entre las sedes.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if (! $finalizada)
                        @canRoute('admin.movimientos.compras.edit')
                            <a href="{{ route('admin.movimientos.compras.edit', $compra->id_cmp) }}"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-md active:scale-95 transition-all">
                                <i class="fas fa-arrow-right text-xs"></i> Continuar la requisición
                            </a>
                        @endcanRoute
                    @endif
                    <a href="{{ route('admin.movimientos.compras.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-arrow-left text-[10px]"></i> Volver
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border shadow-sm p-5 sm:p-6 mb-6" style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    @foreach ($datosCompra as [$icono, $etiqueta, $valor])
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-black" style="color: var(--text-muted);">
                                <i class="fas {{ $icono }} mr-1"></i> {{ $etiqueta }}
                            </p>
                            <p class="mt-1 text-sm font-bold break-words" style="color: var(--text-main);">{{ $valor }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden mb-6" style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="px-5 sm:px-6 py-4 border-b" style="border-color: var(--border-color);">
                    <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">Productos comprados</h3>
                </div>
                <div class="p-5 sm:p-6">
                    @if ($detalles->count() > 0)
                        <div class="overflow-x-auto rounded-2xl border" style="border-color: var(--border-color);">
                            <table class="w-full text-sm">
                                <thead style="background-color: rgba(0,0,0,0.025); border-bottom: 1px solid var(--border-color);">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">Producto</th>
                                        <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">Lote</th>
                                        <th class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">Precio</th>
                                        <th class="px-4 py-3 text-right text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color);">
                                    @foreach ($detalles as $detalle)
                                        @php $u = \App\Support\Inventario\UnidadCompra::de((int) $detalle->pdt_id); @endphp
                                        <tr style="border-color: var(--border-color);">
                                            <td class="px-4 py-3 text-xs font-bold" style="color: var(--text-main);">
                                                {{ $detalle->producto_nombre }}
                                                <span class="block text-[10px] font-normal" style="color: var(--text-muted);">{{ $detalle->producto_codigo }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-xs font-mono" style="color: var(--text-main);">{{ $detalle->codigo_lote_lot }}</td>
                                            <td class="px-4 py-3 text-center text-xs font-semibold whitespace-nowrap" style="color: var(--text-main);">
                                                {{ $u->describirCantidad((float) $detalle->cantidad_dcm) }}
                                                <span class="block text-[10px] font-normal" style="color: var(--text-muted);">
                                                    {{ \App\Support\Inventario\UnidadCompra::numero((float) $detalle->cantidad_convertida_dcm) }} {{ $u->unidadBase }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-xs whitespace-nowrap" style="color: var(--text-main);">
                                                {{ number_format($detalle->precio_unitario_dcm, 2, ',', '.') }} Bs
                                                <span class="block text-[10px]" style="color: var(--text-muted);">{{ $u->precioPor() }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-xs font-extrabold whitespace-nowrap" style="color: var(--text-main);">
                                                {{ number_format($detalle->subtotal_dcm, 2, ',', '.') }} Bs
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-4 text-right text-lg font-black" style="color: var(--text-main);">
                            Total: {{ number_format($compra->total_cmp, 2, ',', '.') }} Bs
                        </p>
                    @else
                        <div class="rounded-2xl border border-dashed p-8 text-center text-xs" style="border-color: var(--border-color); color: var(--text-muted);">
                            Esta requisición no tiene productos. Ábrela con "Continuar la requisición" para agregarlos.
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="px-5 sm:px-6 py-4 border-b" style="border-color: var(--border-color);">
                    <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">Reparto entre sedes</h3>
                    <p class="text-[11px] font-medium" style="color: var(--text-muted);">
                        Lo que recibió cada sede al recibir la compra. La sede principal se queda con el sobrante.
                    </p>
                </div>
                <div class="p-5 sm:p-6">
                    @if ($finalizada)
                        @include('admin.movimientos.compras._reparto', ['reparto' => $reparto, 'vacio' => 'No se encontraron entradas de inventario para esta compra.'])
                    @else
                        <div class="rounded-2xl border border-dashed p-6 text-center text-xs" style="border-color: var(--border-color); color: var(--text-muted);">
                            La compra todavía no se ha recibido. Cuando llegue el pedido, ábrela con "Continuar la requisición": allí verás la vista previa del reparto y el botón "Recibir y repartir".
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
