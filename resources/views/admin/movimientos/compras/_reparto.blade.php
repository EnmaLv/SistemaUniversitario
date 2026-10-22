@php
    $formato = fn ($valor) => \App\Support\Inventario\UnidadCompra::numero((float) $valor);
    $sedesReparto = collect($reparto['sedes'] ?? []);
    $lineasReparto = $reparto['lineas'] ?? [];
@endphp

@if ($sedesReparto->isEmpty() || $lineasReparto === [])
    <div class="rounded-2xl border border-dashed p-6 text-center text-xs" style="border-color: var(--border-color); color: var(--text-muted);">
        {{ $vacio ?? 'No hay productos para repartir.' }}
    </div>
@else
    <div class="overflow-x-auto rounded-2xl border" style="border-color: var(--border-color);">
        <table class="w-full text-sm">
            <thead style="background-color: rgba(0,0,0,0.025); border-bottom: 1px solid var(--border-color);">
                <tr>
                    <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">Producto</th>
                    <th class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400 whitespace-nowrap">Total</th>
                    @foreach ($sedesReparto as $sede)
                        <th class="px-4 py-3 text-center text-[10px] uppercase tracking-wider font-black whitespace-nowrap {{ $sede->principal ? 'text-red-800 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $sede->nombre }}
                            @if ($sede->principal)
                                <span class="block normal-case tracking-normal font-bold text-[9px]">Principal · recibe el sobrante</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border-color);">
                @foreach ($lineasReparto as $linea)
                    <tr style="border-color: var(--border-color);">
                        <td class="px-4 py-3 text-xs" style="color: var(--text-main);">
                            <p class="font-bold">{{ $linea['producto'] }}</p>
                            <p class="text-[10px]" style="color: var(--text-muted);">
                                Lote {{ $linea['lote'] ?? '—' }} ·
                                {{ $linea['nota'] ?? ($linea['fraccionable'] ? 'se reparte con decimales' : 'se reparte sin decimales, en ' . $linea['unidad_plural']) }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-center text-xs font-extrabold whitespace-nowrap" style="color: var(--text-main);">
                            {{ $formato($linea['cantidad']) }} {{ $linea['fraccionable'] ? $linea['unidad'] : $linea['unidad_plural'] }}
                            @if ($linea['unidad_base'] !== $linea['unidad_plural'])
                                <span class="block text-[10px] font-normal" style="color: var(--text-muted);">
                                    {{ $formato($linea['convertida']) }} {{ $linea['unidad_base'] }}
                                </span>
                            @endif
                        </td>
                        @foreach ($sedesReparto as $sede)
                            @php $parte = $linea['partes'][$sede->id] ?? ['cantidad' => 0, 'convertida' => 0]; @endphp
                            <td class="px-4 py-3 text-center text-xs font-semibold whitespace-nowrap {{ $sede->principal ? 'bg-red-50/50 dark:bg-red-950/10' : '' }}"
                                style="color: var(--text-main);">
                                @if ((float) $parte['cantidad'] > 0)
                                    {{ $formato($parte['cantidad']) }} {{ $linea['fraccionable'] ? $linea['unidad'] : ((float) $parte['cantidad'] == 1 ? $linea['unidad'] : $linea['unidad_plural']) }}
                                    @if ($linea['unidad_base'] !== $linea['unidad_plural'])
                                        <span class="block text-[10px] font-normal" style="color: var(--text-muted);">
                                            {{ $formato($parte['convertida']) }} {{ $linea['unidad_base'] }}
                                        </span>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
