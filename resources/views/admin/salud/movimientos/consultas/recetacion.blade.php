<x-app-layout>
    @php
        $paciente = $consulta->paciente;
        $pacienteNombre = trim(($paciente->nombre_persona ?? '') . ' ' . ($paciente->apellido_persona ?? ''));
        $medicoNombre = trim(
            ($consulta->medico->nombre_persona ?? '') . ' ' . ($consulta->medico->apellido_persona ?? ''),
        );

        $productosJs = $productos->map(fn($p) => ['id' => (string) $p->id, 'nombre' => $p->nombre])->values();
        $unidadesJs = $unidades->map(fn($u) => ['id' => (string) $u->id, 'nombre' => $u->nombre])->values();

        $frecuenciasSugeridas = [
            'Cada 4 horas',
            'Cada 6 horas',
            'Cada 8 horas',
            'Cada 12 horas',
            'Una vez al día',
            'Dos veces al día',
            'Antes de dormir',
            'En caso de dolor o fiebre',
        ];

        $erroresDetalle = collect($errors->get('detalles'))
            ->merge(collect($errors->get('detalles.*'))->flatten())
            ->unique()
            ->values();
    @endphp

    @include('admin.salud.movimientos.consultas.partials.ui')

    <div class="pt-6 pb-16 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <form action="{{ route('admin.salud.movimientos.consultas.receta.store', $consulta) }}" method="POST"
                class="rd-prevent-double-submit" x-data="recetadorForm(@js($estadoInicial), @js($tieneDatosReales), {{ $consulta->id }}, @js($productosJs), @js($unidadesJs))" @submit="limpiarBorrador()">
                @csrf
                <input type="hidden" name="fecha" :value="fecha">

                <datalist id="frecuencias-sugeridas">
                    @foreach ($frecuenciasSugeridas as $f)
                        <option value="{{ $f }}"></option>
                    @endforeach
                </datalist>

                {{-- ── BARRA SUPERIOR: Volver + Progreso del Formulario ── --}}
                <div class="flex items-center justify-between gap-3 mb-3">
                    <a href="{{ route('admin.salud.movimientos.consultas.create', $consulta) }}"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-arrow-left text-[10px]" aria-hidden="true"></i>
                        <span>Volver a la consulta</span>
                    </a>

                    {{-- Progreso desplegable --}}
                    <div x-data="{ abierto: false }" class="relative inline-block">
                        <button type="button" @click="abierto = !abierto"
                            class="cx-card !py-1.5 !px-3.5 flex items-center gap-2.5 shadow-sm hover:shadow transition-shadow !rounded-full text-sm"
                            :aria-expanded="abierto" aria-label="Ver progreso del formulario">
                            <span
                                class="relative inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-bold tabular-nums"
                                :class="completos === checklist.length ?
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' :
                                    'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300'"
                                x-text="`${completos}/${checklist.length}`"></span>
                            <span class="text-sm font-semibold cx-text"
                                x-text="completos === checklist.length ? '¡Listo para continuar!' : 'Progreso'"></span>
                            <i class="fas text-[10px] cx-faint" :class="abierto ? 'fa-chevron-up' : 'fa-chevron-down'"
                                aria-hidden="true"></i>
                        </button>

                        {{-- Panel desplegable del progreso --}}
                        <div x-show="abierto" @click.outside="abierto = false" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="absolute right-0 top-full mt-2 cx-card p-4 w-72 z-50 shadow-xl">
                            <div class="flex items-baseline justify-between mb-2">
                                <h3 class="text-sm font-semibold cx-text">Para continuar</h3>
                                <span class="text-xs font-semibold tabular-nums"
                                    :class="completos === checklist.length ? 'text-emerald-600 dark:text-emerald-400' :
                                        'cx-muted'"
                                    x-text="`${completos} de ${checklist.length}`"></span>
                            </div>

                            <div class="h-1 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden mb-3">
                                <div class="h-full rounded-full transition-[width] duration-300"
                                    :class="completos === checklist.length ? 'bg-emerald-500' : 'bg-sky-600'"
                                    :style="`width: ${(completos / checklist.length) * 100}%`"></div>
                            </div>

                            <ul class="space-y-2">
                                <template x-for="item in checklist" :key="item.label">
                                    <li class="flex items-center gap-2 text-sm">
                                        <span
                                            class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 border"
                                            :class="item.ok ? 'bg-emerald-500 border-emerald-500 text-white' :
                                                'border-gray-300 dark:border-gray-600'">
                                            <i x-show="item.ok" class="fas fa-check text-[8px]" aria-hidden="true"></i>
                                        </span>
                                        <span :class="item.ok ? 'cx-muted line-through decoration-1' : 'cx-text'"
                                            x-text="item.label"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Encabezado principal --}}
                <x-consulta-encabezado titulo="Receta médica"
                    descripcion="Indica qué debe tomar el paciente y por cuánto tiempo." :step="2"
                    :consulta="$consulta" />

                {{-- Aviso de borrador restaurado --}}
                <div x-data="{ visible: false }" x-init="visible = window.__recetaBorradorRestaurado === true" x-show="visible" x-cloak role="status"
                    class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/30 text-sm text-amber-800 dark:text-amber-300">
                    <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
                    <span>Recuperamos lo que habías escrito antes de volver a la consulta.</span>
                    <button type="button" @click="visible = false" aria-label="Cerrar aviso"
                        class="ml-auto w-7 h-7 rounded-md flex items-center justify-center hover:bg-amber-100 dark:hover:bg-amber-900/40">
                        <i class="fas fa-times text-xs" aria-hidden="true"></i>
                    </button>
                </div>

                {{-- ═══════════════ MEDICAMENTOS ═══════════════ --}}
                <section class="space-y-4 min-w-0" aria-labelledby="sec-medicamentos">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <h2 id="sec-medicamentos" class="cx-title">Medicamentos</h2>
                            <p class="text-sm cx-muted mt-0.5"
                                x-text="detalles.length === 1 ? '1 medicamento en la receta' : `${detalles.length} medicamentos en la receta`">
                            </p>
                        </div>

                        <button type="button" @click="agregarItem()"
                            class="cx-btn cx-btn-ghost !py-2 !px-3.5 shrink-0">
                            <i class="fas fa-plus text-xs mr-1.5" aria-hidden="true"></i>
                            Agregar medicamento
                        </button>
                    </div>

                    @if ($erroresDetalle->isNotEmpty())
                        <div role="alert"
                            class="p-4 rounded-xl border border-rose-200 dark:border-rose-900 bg-rose-50 dark:bg-rose-950/30 text-sm text-rose-700 dark:text-rose-300">
                            <p class="font-semibold">Corrige estos datos antes de guardar:</p>
                            <ul class="list-disc pl-5 mt-1.5 space-y-0.5 text-[13px]">
                                @foreach ($erroresDetalle as $mensaje)
                                    <li>{{ $mensaje }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <template x-for="(item, index) in detalles" :key="index">
                        <article class="cx-card" :aria-label="`Medicamento ${index + 1}`">

                            {{-- Encabezado de la tarjeta --}}
                            <div class="flex items-start gap-3 px-5 pt-5">
                                <span
                                    class="w-7 h-7 rounded-lg bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5"
                                    x-text="index + 1" aria-hidden="true"></span>

                                {{-- Buscador de medicamento --}}
                                <div class="flex-1 min-w-0 relative" x-data="{
                                    texto: item.producto_nombre || '',
                                    abierto: false,
                                    activo: 0,
                                    get opciones() {
                                        const q = normalizar(this.texto);
                                        const lista = (q && this.texto !== item.producto_nombre) ?
                                            productos.filter(p => normalizar(p.nombre).includes(q)) :
                                            productos;
                                        return lista.slice(0, 60);
                                    },
                                    elegir(p) {
                                        item.producto_id = p.id;
                                        item.producto_nombre = p.nombre;
                                        this.texto = p.nombre;
                                        this.abierto = false;
                                    },
                                    salir() {
                                        this.abierto = false;
                                        this.texto = item.producto_nombre || '';
                                    },
                                    tecla(e) {
                                        const n = this.opciones.length;
                                        if (e.key === 'Enter') e.preventDefault();
                                        if (e.key === 'ArrowDown') { e.preventDefault();
                                            this.abierto = true;
                                            this.activo = n ? (this.activo + 1) % n : 0; } else if (e.key === 'ArrowUp') { e.preventDefault();
                                            this.activo = n ? (this.activo - 1 + n) % n : 0; } else if (e.key === 'Enter' && this.abierto && n) { this.elegir(this.opciones[this.activo]); } else if (e.key === 'Escape') { this.salir(); }
                                    }
                                }"
                                    x-effect="if (!abierto) texto = item.producto_nombre || ''"
                                    @click.outside="salir()">
                                    <label :for="`producto_${index}`" class="cx-label">
                                        Medicamento<span class="cx-req" aria-hidden="true">*</span>
                                    </label>
                                    <input type="hidden" :name="`detalles[${index}][producto_id]`"
                                        :value="item.producto_id">
                                    <div class="relative">
                                        <i class="fas fa-capsules absolute left-3.5 top-1/2 -translate-y-1/2 text-xs cx-faint pointer-events-none"
                                            aria-hidden="true"></i>
                                        <input type="text" :id="`producto_${index}`" autocomplete="off"
                                            required role="combobox" aria-autocomplete="list"
                                            :aria-expanded="abierto" x-model="texto"
                                            x-effect="$el.setCustomValidity(item.producto_id ? '' : 'Selecciona un medicamento de la lista.')"
                                            @focus="abierto = true; activo = 0; $el.select()"
                                            @input="abierto = true; activo = 0; if (texto !== item.producto_nombre) { item.producto_id = ''; item.producto_nombre = ''; }"
                                            @keydown="tecla($event)" placeholder="Busca por nombre"
                                            class="cx-input !pl-9 font-semibold">
                                    </div>

                                    <div x-show="abierto" x-cloak class="cx-dropdown" role="listbox">
                                        <template x-for="(p, i) in opciones" :key="p.id">
                                            <button type="button" class="cx-option" role="option"
                                                :data-activo="i === activo"
                                                :aria-selected="item.producto_id === p.id"
                                                @mouseenter="activo = i" @mousedown.prevent="elegir(p)">
                                                <span class="truncate" x-text="p.nombre"></span>
                                                <i x-show="item.producto_id === p.id"
                                                    class="fas fa-check text-[10px] text-sky-600"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </template>
                                        <p x-show="opciones.length === 0" class="px-3 py-2.5 text-xs cx-muted">
                                            Ningún medicamento coincide con "<span x-text="texto"></span>".
                                        </p>
                                    </div>

                                    <p x-show="esDuplicado(index)" x-cloak
                                        class="cx-hint !opacity-100 text-amber-600 dark:text-amber-400">
                                        <i class="fas fa-triangle-exclamation text-[10px] mr-1"
                                            aria-hidden="true"></i>
                                        Este medicamento ya está en la receta.
                                    </p>
                                </div>

                                <button type="button" @click="eliminarItem(index)"
                                    :disabled="detalles.length === 1"
                                    :aria-label="`Quitar medicamento ${index + 1}`"
                                    :title="detalles.length === 1 ? 'La receta necesita al menos un medicamento' :
                                        'Quitar medicamento'"
                                    class="mt-6 w-9 h-9 flex items-center justify-center rounded-lg cx-faint hover:!opacity-100 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 disabled:!opacity-25 disabled:hover:bg-transparent disabled:hover:text-inherit disabled:cursor-not-allowed transition-colors shrink-0">
                                    <i class="fas fa-trash-can text-sm" aria-hidden="true"></i>
                                </button>
                            </div>

                            {{-- Dosis y duración --}}
                            <div class="px-5 pb-6 pt-4 pl-5 sm:pl-[3.75rem] space-y-4">

                                {{-- Cantidad / Unidad / Frecuencia en una sola línea --}}
                                <div class="grid grid-cols-12 gap-2 sm:gap-3 items-start">
                                    {{-- Cantidad --}}
                                    <div class="col-span-3 sm:col-span-2">
                                        <label :for="`cantidad_${index}`" class="cx-label">
                                            Cantidad<span class="cx-req" aria-hidden="true">*</span>
                                        </label>
                                        <input type="number" step="0.01" min="0.01" inputmode="decimal"
                                            required :id="`cantidad_${index}`"
                                            :name="`detalles[${index}][cantidad]`" x-model="item.cantidad"
                                            placeholder="1"
                                            class="cx-input text-center font-semibold tabular-nums">
                                    </div>

                                    {{-- Unidad --}}
                                    <div class="col-span-3 sm:col-span-4">
                                        <label :for="`unidad_${index}`" class="cx-label">
                                            Unidad<span class="cx-req" aria-hidden="true">*</span>
                                        </label>
                                        <select :id="`unidad_${index}`" :name="`detalles[${index}][unidad_id]`"
                                            x-model="item.unidad_id" required class="cx-input">
                                            <option value="" disabled>Elige</option>
                                            @foreach ($unidades as $u)
                                                <option value="{{ $u->id }}">{{ $u->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Frecuencia --}}
                                    <div class="col-span-6 sm:col-span-6">
                                        <label :for="`frecuencia_${index}`" class="cx-label">
                                            Frecuencia<span class="cx-req" aria-hidden="true">*</span>
                                        </label>
                                        <input type="text" list="frecuencias-sugeridas" required
                                            maxlength="255" :id="`frecuencia_${index}`"
                                            :name="`detalles[${index}][frecuencia]`" x-model="item.frecuencia"
                                            placeholder="Ej.: cada 8 horas" class="cx-input">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <span class="cx-label">Tratamiento<span class="cx-req"
                                                aria-hidden="true">*</span></span>
                                        <div class="flex items-center gap-2">
                                            <input type="date" required
                                                :name="`detalles[${index}][fecha_inicio]`" :min="fecha"
                                                x-model="item.fecha_inicio" aria-label="Fecha de inicio"
                                                class="cx-input !px-3">
                                            <span class="text-xs cx-faint shrink-0">al</span>
                                            <input type="date" required :name="`detalles[${index}][fecha_fin]`"
                                                :min="item.fecha_inicio || fecha" x-model="item.fecha_fin"
                                                aria-label="Fecha de fin" class="cx-input !px-3"
                                                :class="{ 'is-invalid': dias(item) < 0 }">
                                        </div>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                            <span class="text-xs cx-muted mr-0.5">Duración:</span>
                                            <template x-for="n in [3, 5, 7, 10, 14]" :key="n">
                                                <button type="button" @click="fijarDuracion(item, n)"
                                                    class="px-2 py-0.5 rounded-md text-xs font-semibold border transition-colors"
                                                    :class="dias(item) === n ?
                                                        'bg-sky-600 border-sky-600 text-white' :
                                                        'border-gray-200 dark:border-gray-700 cx-muted hover:border-sky-400'"
                                                    x-text="`${n} d`" :aria-pressed="dias(item) === n"></button>
                                            </template>
                                        </div>
                                        <p x-show="dias(item) < 0" x-cloak class="cx-error">
                                            <i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                                            La fecha de fin es anterior a la de inicio.
                                        </p>
                                    </div>

                                    <div>
                                        <label :for="`nota_${index}`" class="cx-label">
                                            Nota para el paciente<span class="cx-opt">(opcional)</span>
                                        </label>
                                        <input type="text" maxlength="255" :id="`nota_${index}`"
                                            :name="`detalles[${index}][observaciones]`"
                                            x-model="item.observaciones" placeholder="Ej.: tomar con alimentos"
                                            class="cx-input">
                                    </div>
                                </div>

                                {{-- Lectura en lenguaje natural --}}
                                <p class="flex items-start gap-2 text-[13px] px-3.5 py-2.5 rounded-lg cx-soft mt-1 mb-1"
                                    x-show="resumen(item)" x-cloak>
                                    <i class="fas fa-quote-left text-[10px] cx-faint mt-1" aria-hidden="true"></i>
                                    <span class="cx-text leading-relaxed" x-text="resumen(item)"></span>
                                </p>
                            </div>
                        </article>
                    </template>
                </section>

                {{-- ═══════════════ DATOS DE LA RECETA (debajo de medicamentos) ═══════════════ --}}
                <section class="cx-card p-5 mt-6 space-y-4" aria-labelledby="sec-datos-receta">
                    <h2 id="sec-datos-receta" class="cx-title">Datos de la receta</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="cx-label">Emitida</span>
                            <p class="text-sm font-semibold cx-text px-3 py-2.5 rounded-xl cx-soft"
                                x-text="formatearFecha(fecha)"></p>
                            @error('fecha')
                                <p class="cx-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="vigencia" class="cx-label">
                                Válida hasta<span class="cx-opt">(opcional)</span>
                            </label>
                            <input type="date" id="vigencia" name="vigencia" x-model="vigencia" :min="fecha"
                                class="cx-input @error('vigencia') is-invalid @enderror">
                            @error('vigencia')
                                <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                        aria-hidden="true"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <label for="descripcion" class="cx-label">
                                Indicaciones generales<span class="cx-req" aria-hidden="true">*</span>
                            </label>
                            <span class="text-[11px] tabular-nums cx-faint"
                                x-text="`${(descripcion || '').length}/500`"></span>
                        </div>
                        <textarea id="descripcion" name="descripcion" rows="4" maxlength="500" x-model="descripcion" required
                            placeholder="Ej.: tomar los medicamentos con abundante agua y guardar reposo por 3 días"
                            class="cx-input @error('descripcion') is-invalid @enderror"></textarea>
                        @error('descripcion')
                            <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5"
                                    aria-hidden="true"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- ═══════════════ ACCIONES ═══════════════ --}}
                <div
                    class="cx-actionbar px-4 py-3 sm:px-5 mt-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="{{ route('admin.salud.movimientos.consultas.index') }}" class="cx-btn cx-btn-ghost">
                        Cancelar
                    </a>
                    <button type="submit" class="rd-submit-btn cx-btn cx-btn-primary">
                        Guardar receta y continuar a la entrega
                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function normalizar(texto) {
            return (texto || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
        }

        function recetadorForm(datosServidor, tieneDatosReales, consultaId, productos, unidades) {
            const claveGuardado = `receta_borrador_consulta_${consultaId}`;
            let estadoInicial = datosServidor;
            window.__recetaBorradorRestaurado = false;

            if (!tieneDatosReales) {
                try {
                    const borrador = sessionStorage.getItem(claveGuardado);
                    if (borrador) {
                        const parsed = JSON.parse(borrador);
                        if (parsed && Array.isArray(parsed.detalles) && parsed.detalles.length > 0) {
                            estadoInicial = parsed;
                            window.__recetaBorradorRestaurado = true;
                        }
                    }
                } catch (e) {
                    console.warn('No se pudo restaurar el borrador de la receta:', e);
                }
            } else {
                sessionStorage.removeItem(claveGuardado);
            }

            const DIA = 86400000;
            const aFecha = (s) => s ? new Date(`${s}T00:00:00`) : null;
            const aTexto = (d) => [
                d.getFullYear(),
                String(d.getMonth() + 1).padStart(2, '0'),
                String(d.getDate()).padStart(2, '0'),
            ].join('-');
            const nombreProducto = (id) => (productos.find(p => p.id === String(id)) || {}).nombre || '';
            const detallesIniciales = Array.isArray(estadoInicial.detalles) ?
                estadoInicial.detalles :
                Object.values(estadoInicial.detalles || {});

            return {
                productos,
                unidades,
                fecha: estadoInicial.fecha,
                vigencia: estadoInicial.vigencia,
                descripcion: estadoInicial.descripcion,
                detalles: detallesIniciales.map(d => ({
                    ...d,
                    producto_id: d.producto_id ? String(d.producto_id) : '',
                    producto_nombre: d.producto_nombre || nombreProducto(d.producto_id),
                    unidad_id: d.unidad_id ? String(d.unidad_id) : '',
                })),

                init() {
                    if (!this.detalles || this.detalles.length === 0) {
                        this.agregarItem();
                    }

                    this.$watch(() => JSON.stringify({
                        fecha: this.fecha,
                        vigencia: this.vigencia,
                        descripcion: this.descripcion,
                        detalles: this.detalles
                    }), () => this.guardarBorrador());
                },

                guardarBorrador() {
                    try {
                        sessionStorage.setItem(claveGuardado, JSON.stringify({
                            fecha: this.fecha,
                            vigencia: this.vigencia,
                            descripcion: this.descripcion,
                            detalles: this.detalles
                        }));
                    } catch (e) {
                        console.warn('No se pudo guardar el borrador de la receta:', e);
                    }
                },

                limpiarBorrador() {
                    sessionStorage.removeItem(claveGuardado);
                },

                agregarItem() {
                    this.detalles.push({
                        producto_id: '',
                        producto_nombre: '',
                        unidad_id: '',
                        cantidad: '',
                        frecuencia: '',
                        fecha_inicio: '{{ now()->toDateString() }}',
                        fecha_fin: '{{ now()->addDays(7)->toDateString() }}',
                        observaciones: ''
                    });

                    this.$nextTick(() => document.getElementById(`producto_${this.detalles.length - 1}`)?.focus());
                },

                eliminarItem(index) {
                    if (this.detalles.length > 1) {
                        this.detalles.splice(index, 1);
                    }
                },

                // ── Progreso del formulario ─────────────────────
                get checklist() {
                    const detallesCompletos = this.detalles.length > 0 && this.detalles.every(d =>
                        d.producto_id && d.cantidad && d.unidad_id && d.frecuencia &&
                        d.fecha_inicio && d.fecha_fin && this.dias(d) >= 0
                    );

                    return [{
                            label: 'Al menos un medicamento',
                            ok: this.detalles.length > 0
                        },
                        {
                            label: 'Datos completos de cada medicamento',
                            ok: detallesCompletos
                        },
                        {
                            label: 'Fecha de emisión',
                            ok: !!this.fecha
                        },
                        {
                            label: 'Indicaciones generales',
                            ok: (this.descripcion || '').trim().length >= 3
                        },
                    ];
                },

                get completos() {
                    return this.checklist.filter(i => i.ok).length;
                },

                // ── Ayudas visuales (no alteran lo que se envía) ──
                esDuplicado(index) {
                    const id = this.detalles[index].producto_id;
                    return !!id && this.detalles.some((d, i) => i !== index && d.producto_id === id);
                },

                dias(item) {
                    const ini = aFecha(item.fecha_inicio),
                        fin = aFecha(item.fecha_fin);
                    return ini && fin ? Math.round((fin - ini) / DIA) : null;
                },

                fijarDuracion(item, n) {
                    const base = aFecha(item.fecha_inicio) || aFecha(this.fecha);
                    if (!base) return;
                    item.fecha_fin = aTexto(new Date(base.getTime() + n * DIA));
                },

                nombreUnidad(id) {
                    return (this.unidades.find(u => u.id === String(id)) || {}).nombre || '';
                },

                resumen(item) {
                    if (!item.cantidad || !item.unidad_id || !item.frecuencia) return '';
                    const d = this.dias(item);
                    const duracion = d > 0 ? `, durante ${d} ${d === 1 ? 'día' : 'días'}` : '';
                    const nota = item.observaciones ? `. ${item.observaciones}` : '';
                    return `${item.cantidad} ${this.nombreUnidad(item.unidad_id).toLowerCase()} ${item.frecuencia.toLowerCase()}${duracion}${nota}`;
                },

                formatearFecha(s) {
                    const d = aFecha(s);
                    return d ? d.toLocaleDateString('es-VE', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    }) : '—';
                },
            };
        }
    </script>
</x-app-layout>