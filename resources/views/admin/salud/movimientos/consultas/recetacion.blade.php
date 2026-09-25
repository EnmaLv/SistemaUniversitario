<x-app-layout>
    <div class="pt-6 pb-16 min-h-[calc(100vh-4rem)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- Encabezado --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-sky-600 flex items-center justify-center text-white shadow-lg shadow-sky-600/20 shrink-0">
                        <i class="fas fa-prescription text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Emitir Receta Médica
                        </h1>
                        <p class="mt-0.5 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                            Paso 2 de 3 · consulta #{{ $consulta->id }}
                        </p>
                    </div>

                </div>

                <a href="{{ route('admin.salud.movimientos.consultas.create', $consulta) }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-100 dark:hover:bg-white/10 transition-all shadow-sm"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver a Consulta (Paso 1)
                </a>
            </div>

            {{-- Stepper --}}
            <x-consulta-stepper :step="2" />

            <div x-data="{ mostrarAviso: false }" x-init="mostrarAviso = window.__recetaBorradorRestaurado === true" x-show="mostrarAviso" x-cloak
                class="mb-6 flex items-center gap-2.5 px-4 py-3 rounded-xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/30 text-xs font-bold text-amber-700 dark:text-amber-400">
                <i class="fas fa-history"></i>
                Se restauraron los datos que habías llenado antes de volver al Paso 1.
                <button type="button" @click="mostrarAviso = false"
                    class="ml-auto hover:text-amber-900 dark:hover:text-amber-200">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>

            {{-- Formulario Principal de Recetación --}}
            <form action="{{ route('admin.salud.movimientos.consultas.receta.store', $consulta) }}" method="POST"
                class="rd-prevent-double-submit" x-data="recetadorForm(@js($estadoInicial), @js($tieneDatosReales), {{ $consulta->id }})" @submit="limpiarBorrador()">
                @csrf

                {{-- Card: Cabecera de la Receta --}}
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm p-4 mb-4">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div
                            class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i class="fas fa-calendar-check text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                            Datos de la Receta
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block titulos tracking-wider">
                                Fecha de la Receta
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-calendar-day absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                <input readonly type="date" name="fecha" x-model="fecha"
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">
                            </div>
                            @error('fecha')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block titulos tracking-wider">
                                Vigencia hasta <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-hourglass-half absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                <input type="date" name="vigencia" x-model="vigencia"
                                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">
                            </div>
                            @error('vigencia')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card: Detalle de Prescripción --}}
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm p-6 mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                                <i class="fas fa-pills text-xs"></i>
                            </div>
                            <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                                Detalle de Prescripción
                            </h3>
                        </div>
                        <button type="button" @click="agregarItem()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white transition-all font-bold text-xs shadow-sm shadow-sky-600/20">
                            <i class="fas fa-plus text-[10px]"></i> Agregar Producto
                        </button>
                    </div>

                    @if ($errors->has('detalles') || $errors->has('detalles.*'))
                        <div
                            class="mb-4 p-4 rounded-xl border border-rose-200 dark:border-rose-900 bg-rose-50 dark:bg-rose-950/30 text-xs font-semibold text-rose-600 dark:text-rose-400 space-y-1">
                            <p class="font-bold">Revise los siguientes detalles en los medicamentos:</p>
                            <ul class="list-disc pl-5 space-y-0.5">
                                @if ($errors->has('detalles'))
                                    <li>{{ $errors->first('detalles') }}</li>
                                @endif
                                @foreach ($errors->get('detalles.*') as $messages)
                                    @foreach ($messages as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <template x-for="(item, index) in detalles" :key="index">
                            <div class="relative pt-6 first:pt-0" :class="{ 'border-t': index > 0 }"
                                :style="index > 0 ? 'border-color: var(--border-color);' : ''">

                                {{-- Header del medicamento --}}
                                <div class="flex items-center justify-between gap-3 mb-5">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span
                                            class="text-x font-bold tabular-nums leading-none"
                                            x-text="String(index + 1).padStart(2, '0')"></span>
                                        <span class="text-xs font-semibold truncate"
                                            x-text="item.producto_nombre || 'Nuevo medicamento'"></span>
                                    </div>
                                    <button type="button" @click="eliminarItem(index)"
                                        :disabled="detalles.length === 1"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-gray-400 transition-colors shrink-0">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>

                                {{-- Campos --}}
                                <div class="space-y-3.5">

                                    {{-- Producto --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-[150px_1fr] gap-2 sm:gap-4 items-center">
                                        <label class="titulos tracking-wider">
                                            Medicamento <span class="text-rose-500">*</span>
                                        </label>
                                        <select :name="`detalles[${index}][producto_id]`" x-model="item.producto_id"
                                            @change="item.producto_nombre = $event.target.options[$event.target.selectedIndex].text"
                                            required
                                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                            class="w-full px-3.5 py-2.5 text-sm font-semibold rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                            <option value="" disabled>Seleccione un medicamento...</option>
                                            @foreach ($productos as $prod)
                                                <option value="{{ $prod->id }}">{{ $prod->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Dosis: cantidad + unidad + frecuencia --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-[150px_1fr] gap-2 sm:gap-4 items-start">
                                        <label
                                            class="titulos tracking-wider pt-2.5">
                                            Dosis
                                        </label>
                                        <div class="grid grid-cols-12 gap-2.5">
                                            <div class="col-span-4 sm:col-span-3">
                                                <input type="number" step="0.01" min="0.01"
                                                    :name="`detalles[${index}][cantidad]`" x-model="item.cantidad"
                                                    placeholder="Cant." required
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                    class="w-full px-3 py-2.5 text-sm font-bold text-center rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                            </div>
                                            <div class="col-span-8 sm:col-span-4">
                                                <select :name="`detalles[${index}][unidad_id]`"
                                                    x-model="item.unidad_id" required
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                    class="w-full px-3 py-2.5 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                                    <option value="" disabled>Elija una Opcion</option>
                                                    @foreach ($unidades as $u)
                                                        <option value="{{ $u->id }}">{{ $u->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-span-12 sm:col-span-5">
                                                <input type="text" :name="`detalles[${index}][frecuencia]`"
                                                    x-model="item.frecuencia" placeholder="Ej: cada 8 horas" required
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                    class="w-full px-3 py-2.5 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Duración --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-[150px_1fr] gap-2 sm:gap-4 items-center">
                                        <label class="titulos tracking-wider ">
                                            Duración
                                        </label>
                                        <div class="flex items-center gap-3">
                                            <input type="date" :name="`detalles[${index}][fecha_inicio]`"
                                                x-model="item.fecha_inicio" required
                                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                class="flex-1 px-3 py-2.5 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                            <span class="text-gray-400 dark:text-gray-500 text-xs font-bold">→</span>
                                            <input type="date" :name="`detalles[${index}][fecha_fin]`"
                                                x-model="item.fecha_fin" required
                                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                class="flex-1 px-3 py-2.5 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                        </div>
                                    </div>

                                    {{-- Nota --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-[150px_1fr] gap-2 sm:gap-4 items-center">
                                        <label class="titulos tracking-wider">
                                            Nota <span
                                                class="normal-case font-medium text-gray-400 dark:text-gray-500">(opcional)</span>
                                        </label>
                                        <input type="text" :name="`detalles[${index}][observaciones]`"
                                            x-model="item.observaciones" placeholder="Ej: Tomar con alimentos"
                                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                            class="w-full px-3.5 py-2.5 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Card: Indicaciones Generales --}}
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm p-6 mb-6">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                            Indicaciones Generales
                        </h3>
                    </div>
                    <textarea name="descripcion" rows="3" x-model="descripcion"
                        placeholder="Ej: Tomar medicamentos con abundante agua. Guardar reposo por 3 días..." required
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full px-3.5 py-3 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all resize-none"></textarea>
                    @error('descripcion')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botones de Acción --}}
                <div class="p-4 sm:p-5 rounded-2xl border shadow-sm flex items-center justify-between gap-3"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">

                    <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-sm shadow-md shadow-sky-600/20 active:scale-95 transition-all">
                        Guardar Receta y Avanzar <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <style>
        .titulos {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .subtitulos {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }
    </style>

    <script>
        function recetadorForm(datosServidor, tieneDatosReales, consultaId) {
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

            return {
                fecha: estadoInicial.fecha,
                vigencia: estadoInicial.vigencia,
                descripcion: estadoInicial.descripcion,
                detalles: estadoInicial.detalles,

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
                        unidad_id: '',
                        cantidad: '',
                        frecuencia: '',
                        fecha_inicio: '{{ now()->toDateString() }}',
                        fecha_fin: '{{ now()->addDays(7)->toDateString() }}',
                        observaciones: ''
                    });
                },
                eliminarItem(index) {
                    if (this.detalles.length > 1) {
                        this.detalles.splice(index, 1);
                    }
                }
            }
        }
    </script>
</x-app-layout>
