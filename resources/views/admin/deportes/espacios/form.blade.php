@php
    $editando = $espacio !== null;
    $sedeActual = old('sed_id', $espacio?->sed_id ?? \App\Support\Sedes\SedeActiva::idParaCrear());
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.espacios.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a espacios
            </a>
            <h1 class="mt-2 mb-6 text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                {{ $editando ? 'Editar espacio deportivo' : 'Registrar espacio deportivo' }}
            </h1>

            @include('admin.deportes.partials.errores')

            <form method="POST" action="{{ $editando ? route('admin.deportes.espacios.update', $espacio->id_epd) : route('admin.deportes.espacios.store') }}"
                class="space-y-5 rounded-2xl border p-6 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                @csrf
                @if ($editando)
                    @method('PUT')
                @endif

                <div>
                    <label for="nombre_epd" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Nombre <span class="text-red-600">*</span></label>
                    <input id="nombre_epd" type="text" name="nombre_epd" maxlength="150" required value="{{ old('nombre_epd', $espacio?->nombre_epd) }}"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);" placeholder="Ej. Cancha de usos múltiples">
                </div>

                <div>
                    <label for="ubicacion_epd" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Ubicación</label>
                    <input id="ubicacion_epd" type="text" name="ubicacion_epd" maxlength="255" value="{{ old('ubicacion_epd', $espacio?->ubicacion_epd) }}"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);" placeholder="Ej. Detrás del edificio administrativo">
                </div>

                <div>
                    <label for="sed_id" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Sede</label>
                    <select id="sed_id" name="sed_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);" @disabled($sedes->count() <= 1)>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id_sed }}" @selected((int) $sedeActual === (int) $sede->id_sed)>{{ $sede->nombre_sed }}</option>
                        @endforeach
                    </select>
                    @if ($sedes->count() <= 1)
                        <input type="hidden" name="sed_id" value="{{ $sedes->first()?->id_sed ?? $sedeActual }}">
                    @endif
                </div>

                <div>
                    <label for="descripcion_epd" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Descripción</label>
                    <textarea id="descripcion_epd" name="descripcion_epd" rows="4" maxlength="2000"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);" placeholder="Superficie, condiciones, capacidad...">{{ old('descripcion_epd', $espacio?->descripcion_epd) }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.deportes.espacios.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</a>
                    @canRoute($editando ? 'admin.deportes.espacios.update' : 'admin.deportes.espacios.store')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-save text-xs"></i> Guardar
                    </button>
                    @endcanRoute
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
