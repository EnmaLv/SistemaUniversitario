@php
    $editando = $calendario !== null;
    $sedeActual = old('sed_id', $calendario?->sed_id ?? \App\Support\Sedes\SedeActiva::idParaCrear());
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.calendarios.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a calendarios
            </a>
            <h1 class="mt-2 mb-6 text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                {{ $editando ? 'Editar calendario deportivo' : 'Registrar calendario deportivo' }}
            </h1>

            @include('admin.deportes.partials.errores')

            <form method="POST" action="{{ $editando ? route('admin.deportes.calendarios.update', $calendario->id_cld) : route('admin.deportes.calendarios.store') }}"
                class="space-y-5 rounded-2xl border p-6 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                @csrf
                @if ($editando)
                    @method('PUT')
                @endif

                <div>
                    <label for="nombre_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Nombre <span class="text-red-600">*</span></label>
                    <input id="nombre_cld" type="text" name="nombre_cld" maxlength="255" required value="{{ old('nombre_cld', $calendario?->nombre_cld) }}"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);" placeholder="Ej. Período deportivo 2026-II">
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="fecha_inicio_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Inicio <span class="text-red-600">*</span></label>
                        <input id="fecha_inicio_cld" type="date" name="fecha_inicio_cld" required value="{{ old('fecha_inicio_cld', $calendario?->fecha_inicio_cld?->format('Y-m-d')) }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                    </div>
                    <div>
                        <label for="fecha_fin_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Cierre <span class="text-red-600">*</span></label>
                        <input id="fecha_fin_cld" type="date" name="fecha_fin_cld" required value="{{ old('fecha_fin_cld', $calendario?->fecha_fin_cld?->format('Y-m-d')) }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                    </div>
                </div>

                <div>
                    <label for="sed_id" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Sede anfitriona</label>
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
                    <label for="descripcion_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Bases generales</label>
                    <textarea id="descripcion_cld" name="descripcion_cld" rows="4" maxlength="2000"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">{{ old('descripcion_cld', $calendario?->descripcion_cld) }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.deportes.calendarios.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</a>
                    @canRoute($editando ? 'admin.deportes.calendarios.update' : 'admin.deportes.calendarios.store')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-save text-xs"></i> Guardar
                    </button>
                    @endcanRoute
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
