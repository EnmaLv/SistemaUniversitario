<x-app-layout>

    @include('components.alert')

    @php
        $formatear = fn ($valor) => rtrim(rtrim(number_format((float) $valor, 2, ',', '.'), '0'), ',');
        $datos = [
            'Fecha'       => $entrega->fecha_ema?->format('d/m/Y H:i'),
            'Recibe'      => $entrega->destinatario(),
            'Dependencia' => $entrega->dependencia_ema ?? '—',
            'Motivo'      => $entrega->motivo_ema,
            'Sede'        => $entrega->sede?->nombre_sed,
            'Entregó'     => $entrega->usuario?->nombre_completo ?? '—',
        ];
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Entrega de material #{{ $entrega->id_ema }}</h1>
                <a href="{{ route('admin.deportes.entregas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            <div class="rounded-2xl border p-5 shadow-sm mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($datos as $etiqueta => $valor)
                        <div>
                            <dt class="text-[11px] font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">{{ $etiqueta }}</dt>
                            <dd class="mt-0.5 text-sm font-semibold" style="color:var(--text-main);">{{ $valor }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-4 border-b" style="border-color:var(--border-color);">
                    <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Productos entregados</h3>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                            <th class="px-6 py-3">Producto</th>
                            <th class="px-6 py-3 text-right">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entrega->detalles as $detalle)
                            <tr class="border-b" style="border-color:var(--border-color);">
                                <td class="px-6 py-3 text-sm" style="color:var(--text-main);">{{ $detalle->producto?->codigo_pdt }} - {{ $detalle->producto?->nombre_pdt }}</td>
                                <td class="px-6 py-3 text-right text-sm font-bold" style="color:var(--text-main);">{{ $formatear($detalle->cantidad_dem) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-4 border-b" style="border-color:var(--border-color);">
                    <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Salidas de inventario por lote</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-3">Producto</th>
                                <th class="px-6 py-3">Lote</th>
                                <th class="px-6 py-3 text-right">Cantidad</th>
                                <th class="px-6 py-3 text-right">Existencia final del lote</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movimientos as $movimiento)
                                <tr class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-3 text-sm" style="color:var(--text-main);">{{ $movimiento->nombre_pdt }}</td>
                                    <td class="px-6 py-3 text-sm" style="color:var(--text-muted);">{{ $movimiento->codigo_lote_lot }}</td>
                                    <td class="px-6 py-3 text-right text-sm" style="color:var(--text-main);">{{ $formatear($movimiento->cantidad_moi) }}</td>
                                    <td class="px-6 py-3 text-right text-sm" style="color:var(--text-muted);">{{ $formatear($movimiento->cantidad_final_moi) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
