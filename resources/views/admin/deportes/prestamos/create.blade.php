<x-app-layout>

    @include('components.alert')

    @php
        $claseInput = 'w-full rounded-xl border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800';
        $estiloInput = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
        $claseEtiqueta = 'mb-1.5 block text-xs font-extrabold uppercase tracking-wider';
        $sancion = $situacion['sancion_activa'] ?? null;
        $pendientes = $situacion['pendientes'] ?? collect();
        $tipos = [
            'deportivo' => ['titulo' => 'Implemento deportivo', 'texto' => 'Balones, mallas, petos, raquetas…', 'icono' => 'fa-futbol'],
            'oficina'   => ['titulo' => 'Material de oficina', 'texto' => 'Marcadores, lápices, resmas, cuadernos…', 'icono' => 'fa-pen'],
        ];
        $tiposLibres = array_values(array_filter(array_keys($tipos), fn ($tipo) => ! $pendientes->has($tipo)));
        $elegible = $persona && ! $sancion && $tiposLibres !== [];
        $tipoInicial = in_array(old('tipo'), $tiposLibres, true) ? old('tipo') : ($tiposLibres[0] ?? null);
        $lineasIniciales = collect(old('items', [['pdt_id' => '', 'cantidad' => 1]]))
            ->map(fn ($linea) => ['pdt_id' => (string) ($linea['pdt_id'] ?? ''), 'cantidad' => (int) ($linea['cantidad'] ?? 1) ?: 1])
            ->values()
            ->all();
        $catalogo = $productos->map(fn ($producto) => [
            'id'         => (string) $producto->id_pdt,
            'nombre'     => $producto->nombre_pdt,
            'codigo'     => $producto->codigo_pdt,
            'tipo'       => $producto->tipo,
            'disponible' => (int) $producto->disponible,
        ])->values()->all();
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Nuevo préstamo</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-map-marker-alt mr-1" style="color:var(--color-primary)"></i>
                        Presta implementos deportivos o material de oficina a una persona registrada; lo prestado sale del inventario de la sede {{ $sede ?? 'activa' }}.
                    </p>
                </div>
                <a href="{{ route('admin.deportes.prestamos.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            @include('admin.deportes._errores')

            <div class="rounded-2xl border p-5 shadow-sm mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <h3 class="text-lg font-extrabold mb-1" style="color:var(--text-main);">1. ¿Quién se lo lleva?</h3>
                <p class="mb-3 text-xs" style="color:var(--text-muted);">Escribe la cédula y pulsa Buscar. Solo se presta a personas registradas en el sistema.</p>
                <form action="{{ route('admin.deportes.prestamos.create') }}" method="GET" class="flex flex-col gap-2 sm:flex-row">
                    <div class="flex flex-1 items-center gap-2 rounded-xl border px-3 py-2.5" style="background-color:var(--input-bg);border-color:var(--input-border);">
                        <i class="fas fa-id-card text-gray-400"></i>
                        <input type="text" name="cedula" value="{{ $cedula }}" required autofocus class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Cédula de la persona (estudiante, docente, obrero...)">
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
                    @endif

                    @foreach ($pendientes as $tipoPendiente => $pendiente)
                        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                            <p class="font-extrabold"><i class="fas fa-exclamation-circle mr-1"></i> Ya tiene un préstamo de {{ mb_strtolower(\App\Models\Deporte\PrestamoDeportivo::etiquetaTipo($tipoPendiente)) }} sin devolver: solo se permite uno de cada tipo a la vez.</p>
                            <p class="mt-1">#{{ $pendiente->id_prd }} · {{ $pendiente->resumenProductos() }} · {{ \App\Models\Deporte\PrestamoDeportivo::etiquetaEstado($pendiente->estado_prd) }}</p>
                            @canRoute('admin.deportes.prestamos.show')
                                <a href="{{ route('admin.deportes.prestamos.show', $pendiente->id_prd) }}" class="mt-2 inline-block font-bold underline">Ver préstamo</a>
                            @endcanRoute
                        </div>
                    @endforeach
                @endif
            </div>

            @if ($elegible)
                <form action="{{ route('admin.deportes.prestamos.store') }}" method="POST" class="rounded-2xl border p-5 shadow-sm"
                    style="background-color:var(--bg-card);border-color:var(--border-color);"
                    x-data="{
                        tipo: @js($tipoInicial),
                        catalogo: @js($catalogo),
                        items: @js($lineasIniciales),
                        delTipo() { return this.catalogo.filter(p => p.tipo === this.tipo); },
                        disponible(id) { const p = this.catalogo.find(p => p.id === String(id)); return p ? p.disponible : 0; },
                        cambiarTipo(nuevo) { if (this.tipo === nuevo) return; this.tipo = nuevo; this.items = [{ pdt_id: '', cantidad: 1 }]; },
                        agregar() { this.items.push({ pdt_id: '', cantidad: 1 }); },
                        quitar(i) { if (this.items.length > 1) this.items.splice(i, 1); }
                    }">
                    @csrf
                    <input type="hidden" name="cedula" value="{{ $persona->cedula_per }}">
                    <input type="hidden" name="tipo" :value="tipo">

                    <h3 class="text-lg font-extrabold mb-1" style="color:var(--text-main);">2. ¿Qué se lleva?</h3>
                    <p class="mb-4 text-xs" style="color:var(--text-muted);">Elige el tipo y agrega cada producto con su cantidad en unidades. Puedes agregar varios productos del mismo tipo.</p>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 mb-5">
                        @foreach ($tipos as $clave => $info)
                            @php $ocupado = $pendientes->has($clave); @endphp
                            <button type="button" @click="cambiarTipo('{{ $clave }}')" @disabled($ocupado)
                                class="flex items-start gap-3 rounded-xl border-2 p-4 text-left transition-all {{ $ocupado ? 'cursor-not-allowed opacity-50' : '' }}"
                                :class="tipo === '{{ $clave }}' ? 'bg-red-50 dark:bg-red-950/30' : ''"
                                :style="tipo === '{{ $clave }}' ? 'border-color:#991b1b;' : 'border-color:var(--border-color);'">
                                <i class="fas {{ $info['icono'] }} mt-1 text-lg text-red-700"></i>
                                <span>
                                    <span class="block text-sm font-extrabold" style="color:var(--text-main);">{{ $info['titulo'] }}</span>
                                    <span class="block text-xs" style="color:var(--text-muted);">{{ $ocupado ? 'Ya tiene uno sin devolver.' : $info['texto'] }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <div x-show="delTipo().length === 0" class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                        <i class="fas fa-box-open mr-1"></i> No hay productos de este tipo con existencia en esta sede. Regístralos en "Implementos y materiales" y cárgalos al inventario con una compra.
                    </div>

                    <div x-show="delTipo().length > 0">
                        <template x-for="(item, indice) in items" :key="indice">
                            <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-12 sm:items-end">
                                <div class="sm:col-span-8">
                                    <label class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Producto</label>
                                    <select :name="`items[${indice}][pdt_id]`" x-model="item.pdt_id" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                                        <option value="">Seleccione un producto</option>
                                        <template x-for="producto in delTipo()" :key="producto.id">
                                            <option :value="producto.id" :selected="producto.id === String(item.pdt_id)" x-text="`${producto.nombre} (hay ${producto.disponible})`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Cantidad (unidades)</label>
                                    <input type="number" min="1" step="1" :max="disponible(item.pdt_id) || null" :name="`items[${indice}][cantidad]`" x-model="item.cantidad" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                                </div>
                                <div class="sm:col-span-1">
                                    <button type="button" @click="quitar(indice)" class="w-full rounded-xl border px-3 py-2.5 text-gray-400 hover:text-rose-600" style="border-color:var(--border-color);" title="Quitar este producto">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="agregar()" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                            <i class="fas fa-plus text-xs"></i> Agregar otro producto
                        </button>

                        <h3 class="mt-6 text-lg font-extrabold mb-1" style="color:var(--text-main);">3. ¿Para qué, dónde y hasta cuándo?</h3>
                        <p class="mb-4 text-xs" style="color:var(--text-muted);">Si no lo devuelve antes de la fecha "hasta", el préstamo vence y la persona queda sancionada.</p>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="motivo" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Para qué</label>
                                <input type="text" id="motivo" name="motivo" maxlength="255" value="{{ old('motivo') }}" required class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Ej.: clase de Matemática, práctica libre de voleibol">
                            </div>

                            <div>
                                <label for="epd_id" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Dónde: espacio deportivo</label>
                                <select id="epd_id" name="epd_id" class="{{ $claseInput }}" style="{{ $estiloInput }}">
                                    <option value="">Otro lugar (escríbelo al lado)</option>
                                    @foreach ($espacios as $espacio)
                                        <option value="{{ $espacio->id_epd }}" @selected((int) old('epd_id') === (int) $espacio->id_epd)>{{ $espacio->nombre_epd }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="lugar" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Dónde: otro lugar</label>
                                <input type="text" id="lugar" name="lugar" maxlength="255" value="{{ old('lugar') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Ej.: aula 12, patio central">
                            </div>

                            <div>
                                <label for="fecha_prestamo" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Desde</label>
                                <input type="datetime-local" id="fecha_prestamo" name="fecha_prestamo" value="{{ old('fecha_prestamo', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}">
                            </div>

                            <div>
                                <label for="fecha_devolucion_esperada" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Hasta (fecha de devolución)</label>
                                <input type="datetime-local" id="fecha_devolucion_esperada" name="fecha_devolucion_esperada" value="{{ old('fecha_devolucion_esperada', now()->addHours(2)->format('Y-m-d\TH:i')) }}" min="{{ now()->format('Y-m-d\TH:i') }}" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                            </div>

                            <div class="md:col-span-2">
                                <label for="observaciones" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Cómo se entrega (opcional)</label>
                                <textarea id="observaciones" name="observaciones" rows="2" maxlength="1000" class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Ej.: balón con un raspón, marcadores nuevos">{{ old('observaciones') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs" style="color:var(--text-muted);"><i class="fas fa-user-check mr-1"></i> Entrega: {{ $entregadoPor ?: 'usuario actual' }}</p>
                            @canRoute('admin.deportes.prestamos.store')
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-6 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                                <i class="fas fa-hand-holding text-xs"></i> Registrar préstamo
                            </button>
                            @endcanRoute
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
