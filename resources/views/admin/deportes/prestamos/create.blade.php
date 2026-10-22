<x-app-layout>

    @include('components.alert')

    @php
        $claseInput = 'w-full rounded-xl border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800';
        $estiloInput = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
        $claseEtiqueta = 'mb-1.5 block text-xs font-extrabold uppercase tracking-wider';
        $sancion = $situacion['sancion_activa'] ?? null;
        $pendiente = $situacion['prestamo_pendiente'] ?? null;
        $elegible = $persona && ! $sancion && ! $pendiente;
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Nuevo préstamo</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-map-marker-alt mr-1" style="color:var(--color-primary)"></i>
                        Sede: {{ $sede ?? 'Sin sede' }} · el implemento sale del inventario de esta sede
                    </p>
                </div>
                <a href="{{ route('admin.deportes.prestamos.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            @include('admin.deportes._errores')

            <div class="rounded-2xl border p-5 shadow-sm mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <h3 class="text-lg font-extrabold mb-3" style="color:var(--text-main);">1. Persona que recibe</h3>
                <form action="{{ route('admin.deportes.prestamos.create') }}" method="GET" class="flex flex-col gap-2 sm:flex-row">
                    <div class="flex flex-1 items-center gap-2 rounded-xl border px-3 py-2.5" style="background-color:var(--input-bg);border-color:var(--input-border);">
                        <i class="fas fa-id-card text-gray-400"></i>
                        <input type="text" name="cedula" value="{{ $cedula }}" required class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Cédula de la persona (estudiante, docente, obrero...)">
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                        <i class="fas fa-search text-xs"></i> Buscar
                    </button>
                </form>

                @if ($cedula !== '' && ! $persona)
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
                        <i class="fas fa-user-times mr-1"></i> No existe ninguna persona registrada con esa cédula. El préstamo solo se otorga a miembros de la universidad.
                    </div>
                @endif

                @if ($persona)
                    <div class="mt-4 rounded-xl border p-4" style="border-color:var(--border-color);">
                        <p class="font-extrabold" style="color:var(--text-main);">{{ $persona->nombres_per }} {{ $persona->apellidos_per }}</p>
                        <p class="text-xs" style="color:var(--text-muted);">C.I. {{ $persona->cedula_per }}</p>
                    </div>

                    @if ($sancion)
                        <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
                            <p class="font-extrabold"><i class="fas fa-ban mr-1"></i> Tiene una sanción activa y no puede recibir préstamos.</p>
                            <p class="mt-1">{{ $sancion->motivo_snd }} (desde {{ $sancion->fecha_sancion_snd?->format('d/m/Y') }})</p>
                            @canRoute('admin.deportes.sanciones.show')
                                <a href="{{ route('admin.deportes.sanciones.show', $sancion->id_snd) }}" class="mt-2 inline-block font-bold underline">Ver sanción</a>
                            @endcanRoute
                        </div>
                    @elseif ($pendiente)
                        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                            <p class="font-extrabold"><i class="fas fa-exclamation-circle mr-1"></i> Ya tiene un préstamo pendiente: solo se permite uno a la vez.</p>
                            <p class="mt-1">#{{ $pendiente->id_prd }} · {{ $pendiente->producto?->nombre_pdt }} · {{ \App\Models\Deporte\PrestamoDeportivo::etiquetaEstado($pendiente->estado_prd) }}</p>
                            @canRoute('admin.deportes.prestamos.show')
                                <a href="{{ route('admin.deportes.prestamos.show', $pendiente->id_prd) }}" class="mt-2 inline-block font-bold underline">Ver préstamo</a>
                            @endcanRoute
                        </div>
                    @endif
                @endif
            </div>

            @if ($elegible)
                <form action="{{ route('admin.deportes.prestamos.store') }}" method="POST" class="rounded-2xl border p-5 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    @csrf
                    <input type="hidden" name="cedula" value="{{ $persona->cedula_per }}">
                    <h3 class="text-lg font-extrabold mb-4" style="color:var(--text-main);">2. Datos del préstamo</h3>

                    @if ($implementos->isEmpty())
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                            <i class="fas fa-box-open mr-1"></i> No hay implementos deportivos con existencia en esta sede. Registre una compra o una entrada de inventario primero.
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="lot_id" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Implemento y lote</label>
                                <select id="lot_id" name="lot_id" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                                    <option value="">Seleccione un implemento</option>
                                    @foreach ($implementos as $implemento)
                                        <optgroup label="{{ $implemento->codigo_pdt }} - {{ $implemento->nombre_pdt }}">
                                            @foreach ($implemento->lotes as $lote)
                                                <option value="{{ $lote->lot_id }}" @selected((int) old('lot_id') === (int) $lote->lot_id)>
                                                    {{ $implemento->nombre_pdt }} · lote {{ $lote->codigo_lote_lot }} · disponibles: {{ rtrim(rtrim(number_format((float) $lote->cantidad_isl, 2, ',', '.'), '0'), ',') }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="cantidad" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Cantidad</label>
                                <input type="number" id="cantidad" name="cantidad" min="1" max="100" value="{{ old('cantidad', 1) }}" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                            </div>

                            <div>
                                <label for="motivo" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Motivo (para qué)</label>
                                <input type="text" id="motivo" name="motivo" maxlength="255" value="{{ old('motivo') }}" required class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Ej.: práctica libre de voleibol">
                            </div>

                            <div>
                                <label for="epd_id" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Espacio deportivo</label>
                                <select id="epd_id" name="epd_id" class="{{ $claseInput }}" style="{{ $estiloInput }}">
                                    <option value="">Otro lugar (especifique)</option>
                                    @foreach ($espacios as $espacio)
                                        <option value="{{ $espacio->id_epd }}" @selected((int) old('epd_id') === (int) $espacio->id_epd)>{{ $espacio->nombre_epd }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="lugar" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Lugar (si no es un espacio registrado)</label>
                                <input type="text" id="lugar" name="lugar" maxlength="255" value="{{ old('lugar') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Ej.: patio central">
                            </div>

                            <div>
                                <label for="fecha_prestamo" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Fecha y hora de entrega</label>
                                <input type="datetime-local" id="fecha_prestamo" name="fecha_prestamo" value="{{ old('fecha_prestamo', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}">
                            </div>

                            <div>
                                <label for="fecha_devolucion_esperada" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Devolución esperada</label>
                                <input type="datetime-local" id="fecha_devolucion_esperada" name="fecha_devolucion_esperada" value="{{ old('fecha_devolucion_esperada', now()->addHours(2)->format('Y-m-d\TH:i')) }}" min="{{ now()->format('Y-m-d\TH:i') }}" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                            </div>

                            <div class="md:col-span-2">
                                <label for="observaciones" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Condición del material al prestar</label>
                                <textarea id="observaciones" name="observaciones" rows="3" maxlength="1000" class="{{ $claseInput }}" style="{{ $estiloInput }}">{{ old('observaciones') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            @canRoute('admin.deportes.prestamos.store')
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-6 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                                <i class="fas fa-hand-holding text-xs"></i> Registrar préstamo
                            </button>
                            @endcanRoute
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
