<x-app-layout>

    @include('components.alert')

    @php
        $claseInput = 'w-full rounded-xl border px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800';
        $estiloInput = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
        $claseEtiqueta = 'mb-1.5 block text-xs font-extrabold uppercase tracking-wider';
        $itemsIniciales = collect(old('items', [['pdt_id' => '', 'cantidad' => 1]]))->values()->all();
        $formatear = fn ($valor) => rtrim(rtrim(number_format((float) $valor, 2, ',', '.'), '0'), ',');
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Nueva entrega de material</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-map-marker-alt mr-1" style="color:var(--color-primary)"></i>
                        Sede: {{ $sede ?? 'Sin sede' }} · el material se descuenta del inventario de esta sede y no se devuelve
                    </p>
                </div>
                <a href="{{ route('admin.deportes.entregas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            @include('admin.deportes._errores')

            <form action="{{ route('admin.deportes.entregas.store') }}" method="POST" class="rounded-2xl border p-5 shadow-sm"
                style="background-color:var(--bg-card);border-color:var(--border-color);"
                x-data="{ items: @js($itemsIniciales), agregar() { this.items.push({ pdt_id: '', cantidad: 1 }); }, quitar(i) { if (this.items.length > 1) this.items.splice(i, 1); } }">
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="cedula" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Cédula de la persona</label>
                        <input type="text" id="cedula" name="cedula" maxlength="20" value="{{ old('cedula') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Opcional si indica la dependencia">
                    </div>
                    <div>
                        <label for="dependencia" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Dependencia</label>
                        <input type="text" id="dependencia" name="dependencia" maxlength="150" value="{{ old('dependencia') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Ej.: Coordinación de Deportes">
                    </div>
                    <div class="md:col-span-2">
                        <label for="motivo" class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Motivo (para qué)</label>
                        <input type="text" id="motivo" name="motivo" maxlength="255" required value="{{ old('motivo') }}" class="{{ $claseInput }}" style="{{ $estiloInput }}" placeholder="Ej.: planillas de inscripción del torneo">
                    </div>
                </div>

                <h3 class="mt-6 mb-3 text-lg font-extrabold" style="color:var(--text-main);">Productos</h3>

                @if ($materiales->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                        <i class="fas fa-box-open mr-1"></i> No hay productos de tipo Material de oficina registrados.
                    </div>
                @else
                    <template x-for="(item, indice) in items" :key="indice">
                        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-12 sm:items-end">
                            <div class="sm:col-span-8">
                                <label class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Material</label>
                                <select :name="`items[${indice}][pdt_id]`" x-model="item.pdt_id" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                                    <option value="">Seleccione un material</option>
                                    @foreach ($materiales as $material)
                                        <option value="{{ $material->id_pdt }}" @disabled($material->disponible <= 0)>
                                            {{ $material->codigo_pdt }} - {{ $material->nombre_pdt }} (disponible: {{ $formatear($material->disponible) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="{{ $claseEtiqueta }}" style="color:var(--text-muted);">Cantidad</label>
                                <input type="number" min="0.01" step="0.01" :name="`items[${indice}][cantidad]`" x-model="item.cantidad" required class="{{ $claseInput }}" style="{{ $estiloInput }}">
                            </div>
                            <div class="sm:col-span-1">
                                <button type="button" @click="quitar(indice)" class="w-full rounded-xl border px-3 py-2.5 text-gray-400 hover:text-rose-600" style="border-color:var(--border-color);" title="Quitar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="agregar()" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-plus text-xs"></i> Agregar producto
                    </button>

                    <div class="mt-6 flex justify-end">
                        @canRoute('admin.deportes.entregas.store')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-6 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                            <i class="fas fa-save text-xs"></i> Registrar entrega
                        </button>
                        @endcanRoute
                    </div>
                @endif
            </form>
        </div>
    </div>
</x-app-layout>
