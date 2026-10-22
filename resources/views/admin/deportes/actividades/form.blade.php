@php
    $editando = $actividad !== null;
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.actividades.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a disciplinas
            </a>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                {{ $editando ? 'Editar disciplina' : 'Registrar disciplina' }}
            </h1>
            <p class="mt-1 mb-6 text-sm" style="color:var(--text-muted);">Escribe el nombre del deporte tal como lo verá la gente en los carteles y listados.</p>

            @include('admin.deportes.partials.errores')

            <form method="POST" action="{{ $editando ? route('admin.deportes.actividades.update', $actividad->id_acd) : route('admin.deportes.actividades.store') }}"
                class="space-y-5 rounded-2xl border p-6 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                @csrf
                @if ($editando)
                    @method('PUT')
                @endif

                <div>
                    <label for="nombre_acd" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Nombre <span class="text-red-600">*</span></label>
                    <input id="nombre_acd" type="text" name="nombre_acd" maxlength="150" required value="{{ old('nombre_acd', $actividad?->nombre_acd) }}"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);" placeholder="Ej. Voleibol, Fútbol sala">
                </div>

                <div>
                    <label for="descripcion_acd" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Descripción</label>
                    <textarea id="descripcion_acd" name="descripcion_acd" rows="4" maxlength="2000"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);" placeholder="Reglamento, objetivos, requisitos...">{{ old('descripcion_acd', $actividad?->descripcion_acd) }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.deportes.actividades.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</a>
                    @canRoute($editando ? 'admin.deportes.actividades.update' : 'admin.deportes.actividades.store')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-save text-xs"></i> Guardar
                    </button>
                    @endcanRoute
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
