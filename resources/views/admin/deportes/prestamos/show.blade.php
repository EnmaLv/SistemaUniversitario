<x-app-layout>

    @include('components.alert')

    @php
        $claseInput = 'w-full rounded-xl border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800';
        $estiloInput = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
        $numero = fn ($valor) => \App\Models\Deporte\DetallePrestamoDeportivo::numero((float) $valor);
        $oficina = $prestamo->esDeOficina();
        $cerrado = $prestamo->fecha_devolucion_real_prd !== null;
        $datos = [
            'Persona'            => trim(($prestamo->persona?->nombres_per ?? '') . ' ' . ($prestamo->persona?->apellidos_per ?? '')) . ' · C.I. ' . ($prestamo->persona?->cedula_per ?? ''),
            'Tipo'               => \App\Models\Deporte\PrestamoDeportivo::etiquetaTipo($prestamo->tipo_prd),
            'Para qué'           => $prestamo->motivo_prd,
            'Dónde'              => $prestamo->lugarDescriptivo(),
            'Sede'               => $prestamo->sede?->nombre_sed,
            'Desde'              => $prestamo->fecha_prestamo_prd?->format('d/m/Y h:i A'),
            'Hasta'              => $prestamo->fecha_devolucion_esperada_prd?->format('d/m/Y h:i A'),
            'Devuelto el'        => $prestamo->fecha_devolucion_real_prd?->format('d/m/Y h:i A') ?? 'Pendiente',
            'Estado al devolver' => $prestamo->estado_devolucion_prd ? ucfirst($prestamo->estado_devolucion_prd) : '—',
            'Entregó'            => $prestamo->registradoPor?->nombre_completo ?? '—',
            'Recibió'            => $prestamo->recibidoPor?->nombre_completo ?? '—',
        ];
        $lineasDevolucion = $prestamo->detalles->map(fn ($detalle) => [
            'id'        => $detalle->id_dpd,
            'nombre'    => $detalle->producto?->nombre_pdt ?? 'Producto',
            'prestada'  => (int) round($detalle->cantidad_dpd),
            'devuelta'  => (int) old('lineas.' . $detalle->id_dpd . '.devuelta', (int) round($detalle->cantidad_dpd)),
            'danada'    => (int) old('lineas.' . $detalle->id_dpd . '.danada', 0),
            'consumida' => (int) old('lineas.' . $detalle->id_dpd . '.consumida', 0),
        ])->values()->all();
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

            <div class="rounded-2xl border shadow-sm overflow-hidden mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-4 border-b" style="border-color:var(--border-color);">
                    <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Qué se prestó</h3>
                    <p class="text-xs" style="color:var(--text-muted);">Cantidades en unidades.@if (! $cerrado) Las columnas de devolución se llenan al registrar la devolución.@endif</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[12px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-5 py-3">Producto</th>
                                <th class="px-3 py-3 text-center">Prestado</th>
                                <th class="px-3 py-3 text-center">Volvió bien</th>
                                <th class="px-3 py-3 text-center">Dañado</th>
                                @if ($oficina)
                                    <th class="px-3 py-3 text-center">Se gastó</th>
                                @endif
                                <th class="px-3 py-3 text-center">Perdido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prestamo->detalles as $detalle)
                                <tr class="border-b text-sm" style="border-color:var(--border-color);color:var(--text-main);">
                                    <td class="px-5 py-3">
                                        <p class="font-semibold">{{ $detalle->producto?->nombre_pdt }}</p>
                                        <p class="text-xs" style="color:var(--text-muted);">Lote {{ $detalle->lote?->codigo_lote_lot }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold">{{ $numero($detalle->cantidad_dpd) }}</td>
                                    <td class="px-3 py-3 text-center">{{ $cerrado ? $numero($detalle->cantidad_devuelta_dpd) : '—' }}</td>
                                    <td class="px-3 py-3 text-center {{ $detalle->cantidad_danada_dpd > 0 ? 'font-bold text-amber-600' : '' }}">{{ $cerrado ? $numero($detalle->cantidad_danada_dpd) : '—' }}</td>
                                    @if ($oficina)
                                        <td class="px-3 py-3 text-center">{{ $cerrado ? $numero($detalle->cantidad_consumida_dpd) : '—' }}</td>
                                    @endif
                                    <td class="px-3 py-3 text-center {{ $detalle->cantidad_perdida_dpd > 0 ? 'font-bold text-rose-600' : '' }}">{{ $cerrado ? $numero($detalle->cantidad_perdida_dpd) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($prestamo->puedeDevolverse())
                @canRoute('admin.deportes.prestamos.update_devolucion')
                <form action="{{ route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd) }}" method="POST" id="form-devolucion" class="rounded-2xl border p-5 shadow-sm mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);"
                    x-data="{
                        oficina: @js($oficina),
                        lineas: @js($lineasDevolucion),
                        n(valor) { const numero = parseInt(valor, 10); return isNaN(numero) || numero < 0 ? 0 : numero; },
                        perdida(l) { return l.prestada - this.n(l.devuelta) - this.n(l.danada) - (this.oficina ? this.n(l.consumida) : 0); },
                        total(campo) { return this.lineas.reduce((suma, l) => suma + (campo === 'perdida' ? Math.max(0, this.perdida(l)) : this.n(l[campo])), 0); },
                        excedida() { return this.lineas.some(l => this.perdida(l) < 0); },
                        aviso() {
                            const sancion = this.total('danada') + this.total('perdida');
                            return sancion > 0
                                ? 'Hay ' + sancion + ' unidad(es) dañada(s) o perdida(s): la persona quedará sancionada hasta que las reponga.'
                                : 'Lo que volvió bien regresa al inventario' + (this.oficina ? ' y lo gastado queda como consumo, sin sanción.' : '.');
                        }
                    }" :data-aviso="aviso()">
                    @csrf
                    @method('PUT')
                    <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Registrar devolución</h3>
                    <p class="mb-4 text-xs" style="color:var(--text-muted);">
                        Escribe cuántas unidades volvieron bien y cuántas dañadas{{ $oficina ? ', y cuántas se gastaron durante el uso (consumo justificado, sin sanción)' : '' }}. Lo que falte se cuenta como perdido y genera sanción.
                    </p>

                    <div class="space-y-3">
                        <template x-for="linea in lineas" :key="linea.id">
                            <div class="rounded-xl border p-3" style="border-color:var(--border-color);">
                                <p class="mb-2 text-sm font-extrabold" style="color:var(--text-main);"><span x-text="linea.nombre"></span> · prestado: <span x-text="linea.prestada"></span></p>
                                <div class="grid grid-cols-2 gap-3" :class="oficina ? 'sm:grid-cols-4' : 'sm:grid-cols-3'">
                                    <label class="text-xs font-bold" style="color:var(--text-muted);">Volvió bien
                                        <input type="number" min="0" step="1" :max="linea.prestada" :name="`lineas[${linea.id}][devuelta]`" x-model="linea.devuelta" class="mt-1 {{ $claseInput }}" style="{{ $estiloInput }}">
                                    </label>
                                    <label class="text-xs font-bold" style="color:var(--text-muted);">Volvió dañado
                                        <input type="number" min="0" step="1" :max="linea.prestada" :name="`lineas[${linea.id}][danada]`" x-model="linea.danada" class="mt-1 {{ $claseInput }}" style="{{ $estiloInput }}">
                                    </label>
                                    <template x-if="oficina">
                                        <label class="text-xs font-bold" style="color:var(--text-muted);">Se gastó
                                            <input type="number" min="0" step="1" :max="linea.prestada" :name="`lineas[${linea.id}][consumida]`" x-model="linea.consumida" class="mt-1 {{ $claseInput }}" style="{{ $estiloInput }}">
                                        </label>
                                    </template>
                                    <div class="text-xs font-bold" style="color:var(--text-muted);">Perdido / no devuelto
                                        <p class="mt-1 rounded-xl px-3 py-2 text-sm font-extrabold" :class="perdida(linea) > 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' : (perdida(linea) < 0 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300')"
                                            x-text="perdida(linea) < 0 ? 'Sobran ' + (-perdida(linea)) : perdida(linea)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 rounded-xl border p-3 text-sm" style="border-color:var(--border-color);color:var(--text-main);">
                        <p><i class="fas fa-info-circle mr-1" style="color:var(--color-primary)"></i> <span x-text="aviso()"></span></p>
                        <p x-show="excedida()" class="mt-1 font-bold text-amber-700">La suma de una línea supera lo prestado: revisa las cantidades.</p>
                    </div>

                    <div class="mt-4">
                        <label for="observaciones" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">Observaciones (opcional)</label>
                        <input type="text" id="observaciones" name="observaciones" maxlength="1000" value="{{ old('observaciones') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Ej.: el balón volvió desinflado">
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" :disabled="excedida()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-6 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95 disabled:opacity-50">
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
                const texto = (formulario.dataset.aviso || '') + ' Esta acción no se puede deshacer.';
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
