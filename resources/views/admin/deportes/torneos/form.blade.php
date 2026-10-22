@php
    $editando = $torneo !== null;
    $horarioActual = old('hde_id', $torneo?->hde_id);
    $modalidadActual = old('modalidad_tde', $torneo?->modalidad_tde);
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ $editando ? route('admin.deportes.torneos.show', $torneo->id_tde) : route('admin.deportes.torneos.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver
            </a>
            <h1 class="mt-2 mb-6 text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                {{ $editando ? 'Editar torneo' : 'Crear torneo' }}
            </h1>

            @include('admin.deportes.partials.errores')

            <form method="POST" action="{{ $editando ? route('admin.deportes.torneos.update', $torneo->id_tde) : route('admin.deportes.torneos.store') }}"
                class="space-y-5 rounded-2xl border p-6 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                @csrf
                @if ($editando)
                    @method('PUT')
                @endif

                <div>
                    <label for="nombre_tde" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Nombre <span class="text-red-600">*</span></label>
                    <input id="nombre_tde" type="text" name="nombre_tde" maxlength="255" required value="{{ old('nombre_tde', $torneo?->nombre_tde) }}"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}" placeholder="Ej. Copa Aniversario 2026">
                </div>

                <div>
                    <label for="hde_id" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Horario (espacio, día y hora) <span class="text-red-600">*</span></label>
                    <select id="hde_id" name="hde_id" required class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                        <option value="">Selecciona un horario</option>
                        @foreach ($horarios as $horario)
                            <option value="{{ $horario->id_hde }}" @selected((string) $horarioActual === (string) $horario->id_hde)>
                                {{ $horario->etiqueta() }}{{ $horario->cupo_maximo_hde ? ' · cupo ' . $horario->cupo_maximo_hde : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs" style="color:var(--text-muted);">
                        Los cupos del torneo son los del horario. Si no aparece el horario que necesitas, créalo primero en
                        <a href="{{ route('admin.deportes.maestros.horarios.create') }}" class="font-semibold underline" style="color:var(--color-primary);">Horarios</a>.
                    </p>
                </div>

                <div>
                    <label for="modalidad_tde" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Modalidad</label>
                    <select id="modalidad_tde" name="modalidad_tde" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                        <option value="">Sin especificar</option>
                        @foreach (\App\Models\Deporte\TorneoDeportivo::MODALIDADES as $modalidad)
                            <option value="{{ $modalidad }}" @selected($modalidadActual === $modalidad)>{{ $modalidad }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="descripcion_tde" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Descripción / formato</label>
                    <textarea id="descripcion_tde" name="descripcion_tde" rows="4" maxlength="2000"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}" placeholder="Eliminación directa, todos contra todos, premios...">{{ old('descripcion_tde', $torneo?->descripcion_tde) }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.deportes.torneos.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</a>
                    @canRoute($editando ? 'admin.deportes.torneos.update' : 'admin.deportes.torneos.store')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-save text-xs"></i> Guardar
                    </button>
                    @endcanRoute
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
