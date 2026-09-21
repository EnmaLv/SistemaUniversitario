@php
    $moduloActivo = session('modulo_activo', 'general');
@endphp

@if (session()->has('tasa_pendiente') || session()->has('tasa_obligatoria'))
    @include('components.alert-home-tasa')
@endif

@switch($moduloActivo)
    @case('administracion')
        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                const tasaPendiente = @json(session()->has('tasa_pendiente'));

                if (tasaPendiente) {
                    return;
                }

                const hoy = new Date().toISOString().slice(0, 10);
                const alertas = [];
                @if ($total_lotes_vencidos > 0)
                    if (localStorage.getItem('alerta_lotes_vencidos') === hoy) {
                        alertas.push(async () => {
                            const result = await Swal.fire({
                                title: '⚠️ Lotes vencidos',
                                html: `
                                    <p style="font-size:15px">
                                        Existen <b>{{ $total_lotes_vencidos }}</b> lote(s) vencido(s).<br>
                                        Requieren atención inmediata.
                                    </p>
                                `,
                                icon: 'error',
                                showCancelButton: true,
                                confirmButtonText: 'Ver lotes',
                                cancelButtonText: 'Cerrar',
                                confirmButtonColor: '#dc2626'
                            });

                            localStorage.setItem('alerta_lotes_vencidos', hoy);

                            if (result.isConfirmed) {
                                window.location.href =
                                    "{{ url('/admin/movimientos/lotes?filtro=vencido') }}";
                            }
                        });
                    }
                @endif

                @if ($total_lotes_por_vencer > 0)
                    if (localStorage.getItem('alerta_por_vencer') === hoy) {
                        alertas.push(async () => {
                            const result = await Swal.fire({
                                title: 'Productos por vencer',
                                html: `
                                    <p style="font-size:15px">
                                        Hay <b>{{ $total_lotes_por_vencer }}</b> producto(s)
                                        que vencerán en los próximos <b>7 días</b>.
                                    </p>
                                `,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Revisar',
                                cancelButtonText: 'Cerrar',
                                confirmButtonColor: '#f59e0b'
                            });

                            localStorage.setItem('alerta_por_vencer', hoy);

                            if (result.isConfirmed) {
                                window.location.href =
                                    "{{ url('/admin/movimientos/lotes?filtro=por_vencer') }}";
                            }
                        });
                    }
                @endif
                @if ($total_productos_stock_minimo > 0)
                    if (localStorage.getItem('alerta_stock_minimo') === hoy) {
                        alertas.push(async () => {
                            const result = await Swal.fire({
                                title: '📉 Stock mínimo alcanzado',
                                html: `
                                    <p style="font-size:15px">
                                        Existen <b>{{ $total_productos_stock_minimo }}</b> producto(s)
                                        por debajo del stock mínimo.
                                    </p>
                                    <ul style="text-align:left; font-size:14px; max-height:200px; overflow-y:auto;">
                                        @foreach ($productos_stock_minimo as $producto)
                                            <li>
                                                <strong>{{ $producto->nombre }}</strong>
                                                <small class="text-danger">
                                                    ({{ number_format($producto->stock_actual, 2) }} /
                                                    {{ number_format($producto->stock_minimo, 2) }})
                                                </small>
                                            </li>
                                        @endforeach
                                    </ul>
                                `,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Revisar inventario',
                                cancelButtonText: 'Cerrar',
                                confirmButtonColor: '#f59e0b'
                            });

                            localStorage.setItem('alerta_stock_minimo', hoy);

                            if (result.isConfirmed) {
                                window.location.href =
                                    "{{ url('/admin/maestros/productos?filtro=stock_minimo') }}";
                            }
                        });
                    }
                @endif
                for (const alerta of alertas) {
                    await alerta();
                }

            });
        </script>
    @break

@endswitch
