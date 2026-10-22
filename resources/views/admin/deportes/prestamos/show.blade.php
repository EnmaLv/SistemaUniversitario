<x-app-layout>

    @include('components.alert')

    @php
        $claseInput = 'w-full rounded-xl border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800';
        $estiloInput = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
        $etiquetasDevolucion = ['bueno' => 'Bueno (vuelve al inventario)', 'dañado' => 'Dañado (genera sanción hasta reposición)', 'perdido' => 'Perdido (genera sanción hasta reposición)'];
        $datos = [
            'Persona'             => trim(($prestamo->persona?->nombres_per ?? '') . ' ' . ($prestamo->persona?->apellidos_per ?? '')) . ' · C.I. ' . ($prestamo->persona?->cedula_per ?? ''),
            'Implemento'          => ($prestamo->producto?->nombre_pdt ?? '') . ' · lote ' . ($prestamo->lote?->codigo_lote_lot ?? ''),
            'Cantidad'            => $prestamo->cantidad_prd,
            'Motivo'              => $prestamo->motivo_prd,
            'Espacio o lugar'     => $prestamo->lugarDescriptivo(),
            'Sede'                => $prestamo->sede?->nombre_sed,
            'Entrega'             => $prestamo->fecha_prestamo_prd?->format('d/m/Y H:i'),
            'Devolución esperada' => $prestamo->fecha_devolucion_esperada_prd?->format('d/m/Y H:i'),
            'Devolución real'     => $prestamo->fecha_devolucion_real_prd?->format('d/m/Y H:i') ?? 'Pendiente',
            'Estado al devolver'  => $prestamo->estado_devolucion_prd ? ucfirst($prestamo->estado_devolucion_prd) : '—',
            'Entregado por'       => $prestamo->registradoPor?->nombre_completo ?? '—',
            'Recibido por'        => $prestamo->recibidoPor?->nombre_completo ?? '—',
        ];
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Préstamo #{{ $prestamo->id_prd }}</h1>
                    <div class="mt-2">@include('admin.deportes.prestamos._estado', ['estado' => $prestamo->estado_prd])</div>
                </div>
                <a href="{{ route('admin.deportes.prestamos.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            @include('admin.deportes._errores')

            <div class="rounded-2xl border p-5 shadow-sm mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($datos as $etiqueta => $valor)
                        <div>
                            <dt class="text-[11px] font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">{{ $etiqueta }}</dt>
                            <dd class="mt-0.5 text-sm font-semibold" style="color:var(--text-main);">{{ $valor }}</dd>
                        </div>
                    @endforeach
                    @if ($prestamo->observaciones_prd)
                        <div class="sm:col-span-2">
                            <dt class="text-[11px] font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">Observaciones</dt>
                            <dd class="mt-0.5 whitespace-pre-line text-sm" style="color:var(--text-main);">{{ $prestamo->observaciones_prd }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if ($prestamo->puedeDevolverse())
                @canRoute('admin.deportes.prestamos.update_devolucion')
                <form action="{{ route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd) }}" method="POST" id="form-devolucion" class="rounded-2xl border p-5 shadow-sm mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    @csrf
                    @method('PUT')
                    <h3 class="text-lg font-extrabold mb-4" style="color:var(--text-main);">Registrar devolución</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="estado_devolucion" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">Estado del implemento</label>
                            <select id="estado_devolucion" name="estado_devolucion" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                                @foreach ($etiquetasDevolucion as $valor => $texto)
                                    <option value="{{ $valor }}" @selected(old('estado_devolucion') === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="observaciones" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">Observaciones</label>
                            <input type="text" id="observaciones" name="observaciones" maxlength="1000" value="{{ old('observaciones') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}">
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-6 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                            <i class="fas fa-undo text-xs"></i> Registrar devolución
                        </button>
                    </div>
                </form>
                @endcanRoute
            @endif

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-4 border-b" style="border-color:var(--border-color);">
                    <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Sanciones del préstamo</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-3">Motivo</th>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Levantamiento</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prestamo->sanciones as $sancion)
                                <tr class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-3 text-sm" style="color:var(--text-main);">{{ $sancion->motivo_snd }}</td>
                                    <td class="px-6 py-3 text-sm" style="color:var(--text-main);">{{ $sancion->fecha_sancion_snd?->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 text-sm font-bold {{ $sancion->estaActiva() ? 'text-rose-600' : 'text-emerald-600' }}">{{ ucfirst($sancion->estado_snd) }}</td>
                                    <td class="px-6 py-3 text-sm" style="color:var(--text-muted);">
                                        {{ $sancion->fecha_levantamiento_snd?->format('d/m/Y') ?? '—' }} · {{ \App\Models\Deporte\SancionDeportiva::etiquetaForma($sancion->forma_levantamiento_snd) }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        @canRoute('admin.deportes.sanciones.show')
                                            <a href="{{ route('admin.deportes.sanciones.show', $sancion->id_snd) }}" class="text-xs font-bold" style="color:var(--color-primary);">Ver</a>
                                        @endcanRoute
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Sin sanciones</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formulario = document.getElementById('form-devolucion');
            if (!formulario) return;
            formulario.addEventListener('submit', function (evento) {
                if (formulario.dataset.confirmado === '1') return;
                evento.preventDefault();
                const texto = 'Se registrará la devolución con el estado seleccionado. Esta acción no se puede deshacer.';
                const enviar = () => { formulario.dataset.confirmado = '1'; formulario.submit(); };
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ title: '¿Registrar devolución?', text: texto, icon: 'question', showCancelButton: true, confirmButtonColor: 'var(--color-primary)', confirmButtonText: 'Sí, registrar', cancelButtonText: 'Cancelar' })
                        .then((r) => { if (r.isConfirmed) enviar(); });
                } else if (confirm(texto)) {
                    enviar();
                }
            });
        });
    </script>
</x-app-layout>
