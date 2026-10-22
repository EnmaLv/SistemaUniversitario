<x-app-layout>

    @include('components.alert')

    @php
        $claseInput = 'w-full rounded-xl border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800';
        $estiloInput = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
        $prestamo = $sancion->prestamo;
        $datos = [
            'Persona'           => trim(($sancion->persona?->nombres_per ?? '') . ' ' . ($sancion->persona?->apellidos_per ?? '')) . ' · C.I. ' . ($sancion->persona?->cedula_per ?? ''),
            'Motivo'            => $sancion->motivo_snd,
            'Fecha de sanción'  => $sancion->fecha_sancion_snd?->format('d/m/Y'),
            'Aplicada por'      => $sancion->aplicadaPor?->nombre_completo ?? 'Sistema (vencimiento automático)',
            'Préstamo'          => $prestamo ? '#' . $prestamo->id_prd . ' · ' . \App\Models\Deporte\PrestamoDeportivo::etiquetaTipo($prestamo->tipo_prd) . ' · ' . $prestamo->resumenProductos() . ' · ' . ($prestamo->sede?->nombre_sed ?? '') : '—',
            'Estado'            => ucfirst($sancion->estado_snd),
            'Levantamiento'     => $sancion->fecha_levantamiento_snd ? $sancion->fecha_levantamiento_snd->format('d/m/Y') . ' · ' . \App\Models\Deporte\SancionDeportiva::etiquetaForma($sancion->forma_levantamiento_snd) : '—',
            'Levantada por'     => $sancion->levantadaPor?->nombre_completo ?? '—',
            'Lote de reposición' => $sancion->loteReposicion?->codigo_lote_lot ?? '—',
        ];
        $sinDevolver = $prestamo && $prestamo->fecha_devolucion_real_prd === null;
        $porReponer = $prestamo
            ? $prestamo->detalles
                ->map(fn ($detalle) => ['nombre' => $detalle->producto?->nombre_pdt ?? 'Producto', 'lote' => $detalle->lote?->codigo_lote_lot, 'cantidad' => $sinDevolver ? (float) $detalle->cantidad_dpd : $detalle->porReponer()])
                ->filter(fn ($linea) => $linea['cantidad'] > 0)
                ->values()
            : collect();
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Sanción #{{ $sancion->id_snd }}</h1>
                    @if ($sancion->estaActiva())
                        <p class="mt-1 text-sm font-bold text-rose-600"><i class="fas fa-ban mr-1"></i> Activa: la persona no puede recibir préstamos ni firmar la solvencia.</p>
                    @endif
                </div>
                <a href="{{ route('admin.deportes.sanciones.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
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
                    @if ($sancion->descripcion_snd)
                        <div class="sm:col-span-2">
                            <dt class="text-[11px] font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">Descripción</dt>
                            <dd class="mt-0.5 whitespace-pre-line text-sm" style="color:var(--text-main);">{{ $sancion->descripcion_snd }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($prestamo)
                    @canRoute('admin.deportes.prestamos.show')
                        <a href="{{ route('admin.deportes.prestamos.show', $prestamo->id_prd) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-bold" style="color:var(--color-primary);">
                            <i class="fas fa-external-link-alt text-xs"></i> Ver préstamo
                        </a>
                    @endcanRoute
                @endif
            </div>

            @if ($sancion->estaActiva() && $prestamo && in_array($prestamo->estado_prd, ['vencido', 'sancionado'], true))
                @if ($prestamo->estado_prd === 'vencido')
                    <div class="mb-4 rounded-xl border p-4 text-sm" style="border-color:var(--border-color);color:var(--text-muted);">
                        <i class="fas fa-info-circle mr-1"></i> Si la persona trae lo prestado, registra la devolución desde el préstamo (allí también se anota lo que se gastó): la sanción se levanta sola. Usa la reposición solo si entrega productos nuevos iguales.
                    </div>
                @endif

                @canRoute('admin.deportes.sanciones.update_reposicion')
                <form action="{{ route('admin.deportes.sanciones.update_reposicion', $sancion->id_snd) }}" method="POST" id="form-reposicion" class="rounded-2xl border p-5 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    @csrf
                    @method('PUT')
                    <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Registrar reposición</h3>
                    <p class="mb-2 text-xs" style="color:var(--text-muted);">
                        La persona entrega productos nuevos iguales a lo dañado o perdido. Entran al inventario de la sede {{ $prestamo->sede?->nombre_sed }} y la sanción queda levantada.
                    </p>
                    <ul class="mb-4 list-disc pl-5 text-sm" style="color:var(--text-main);">
                        @foreach ($porReponer as $linea)
                            <li>{{ \App\Models\Deporte\DetallePrestamoDeportivo::numero($linea['cantidad']) }} × {{ $linea['nombre'] }}</li>
                        @endforeach
                    </ul>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="lote" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">Lote de entrada</label>
                            <select id="lote" name="lote" class="{{ $claseInput }}" style="{{ $estiloInput }}">
                                <option value="nuevo" @selected(old('lote', 'nuevo') === 'nuevo')>Lote nuevo de reposición</option>
                                <option value="original" @selected(old('lote') === 'original')>Lote original de cada producto ({{ $porReponer->pluck('lote')->filter()->unique()->implode(', ') }})</option>
                            </select>
                        </div>
                        <div>
                            <label for="codigo_lote" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">Código del lote nuevo (opcional)</label>
                            <input type="text" id="codigo_lote" name="codigo_lote" maxlength="50" value="{{ old('codigo_lote') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Se genera si se deja vacío">
                        </div>
                        <div>
                            <label for="observaciones" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">Observaciones</label>
                            <input type="text" id="observaciones" name="observaciones" maxlength="1000" value="{{ old('observaciones') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}">
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-6 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                            <i class="fas fa-check text-xs"></i> Registrar reposición y levantar sanción
                        </button>
                    </div>
                </form>
                @endcanRoute
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formulario = document.getElementById('form-reposicion');
            if (!formulario) return;
            formulario.addEventListener('submit', function (evento) {
                if (formulario.dataset.confirmado === '1') return;
                evento.preventDefault();
                const texto = 'Se registrará la entrada de lo repuesto al inventario y se levantará la sanción.';
                const enviar = () => { formulario.dataset.confirmado = '1'; formulario.submit(); };
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ title: '¿Registrar reposición?', text: texto, icon: 'question', showCancelButton: true, confirmButtonColor: 'var(--color-primary)', confirmButtonText: 'Sí, registrar', cancelButtonText: 'Cancelar' })
                        .then((r) => { if (r.isConfirmed) enviar(); });
                } else if (confirm(texto)) {
                    enviar();
                }
            });
        });
    </script>
</x-app-layout>
