@php
    $editando = $torneo !== null;
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $modalidadActual = old('modalidad_tde', $torneo?->modalidad_tde);
    $sedeActual = old('sed_id', $actividad?->sed_id ?? \App\Support\Sedes\SedeActiva::idParaCrear());
    $diasMarcados = (array) old('dias', $actividad?->dias() ?? []);
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ $editando ? route('admin.deportes.torneos.show', $torneo->id_tde) : route('admin.deportes.torneos.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver
            </a>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                {{ $editando ? 'Editar torneo' : 'Crear torneo' }}
            </h1>
            <p class="mt-1 mb-6 text-sm" style="color:var(--text-muted);">
                Indica el deporte, dónde y cuándo se juega. Después de guardar, agrega los equipos y sus integrantes por cédula. En los torneos nadie se inscribe solo: participa dentro de un equipo.
            </p>

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
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}" placeholder="Ej. Copa Aniversario de Baloncesto">
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="acd_id" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Disciplina <span class="text-red-600">*</span></label>
                        <select id="acd_id" name="acd_id" required class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                            <option value="">Elige...</option>
                            @foreach ($disciplinas as $disciplina)
                                <option value="{{ $disciplina->id_acd }}" @selected((string) old('acd_id', $bloque?->acd_id) === (string) $disciplina->id_acd)>{{ $disciplina->nombre_acd }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="epd_id" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Espacio <span class="text-red-600">*</span></label>
                        <select id="epd_id" name="epd_id" required class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                            <option value="">Elige...</option>
                            @foreach ($espacios as $espacio)
                                <option value="{{ $espacio->id_epd }}" @selected((string) old('epd_id', $bloque?->epd_id) === (string) $espacio->id_epd)>{{ $espacio->nombre_epd }}{{ $sedes->count() > 1 ? ' (' . ($espacio->sede?->nombre_sed ?? '') . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="fecha_inicio_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Primer día <span class="text-red-600">*</span></label>
                        <input id="fecha_inicio_cld" type="date" name="fecha_inicio_cld" required value="{{ old('fecha_inicio_cld', $actividad?->fecha_inicio_cld?->format('Y-m-d')) }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                    </div>
                    <div>
                        <label for="fecha_fin_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Último día <span class="text-red-600">*</span></label>
                        <input id="fecha_fin_cld" type="date" name="fecha_fin_cld" required value="{{ old('fecha_fin_cld', $actividad?->fecha_fin_cld?->format('Y-m-d')) }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                        <p class="mt-1 text-xs" style="color:var(--text-muted);">Si el torneo es de un solo día, pon la misma fecha.</p>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-sm font-bold" style="color:var(--text-main);">Días de juego (opcional)</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Models\Deporte\DeporteCalendario::DIAS_LEGIBLES as $clave => $nombreDia)
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-sm" style="border-color:var(--border-color);color:var(--text-main);">
                                <input type="checkbox" name="dias[]" value="{{ $clave }}" @checked(in_array($clave, $diasMarcados, true))>
                                {{ $nombreDia }}
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs" style="color:var(--text-muted);">Si no marcas ninguno, se juega todos los días entre el primer y el último día.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="hora_inicio" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Hora de inicio <span class="text-red-600">*</span></label>
                        <input id="hora_inicio" type="time" name="hora_inicio" required value="{{ old('hora_inicio', $bloque?->horaInicio()) }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                    </div>
                    <div>
                        <label for="hora_fin" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Hora de fin <span class="text-red-600">*</span></label>
                        <input id="hora_fin" type="time" name="hora_fin" required value="{{ old('hora_fin', $bloque?->horaFin()) }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                        <label for="sed_id" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Sede</label>
                        <select id="sed_id" name="sed_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}" @disabled($sedes->count() <= 1)>
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id_sed }}" @selected((int) $sedeActual === (int) $sede->id_sed)>{{ $sede->nombre_sed }}</option>
                            @endforeach
                        </select>
                        @if ($sedes->count() <= 1)
                            <input type="hidden" name="sed_id" value="{{ $sedes->first()?->id_sed ?? $sedeActual }}">
                        @endif
                    </div>
                </div>

                <div>
                    <label for="descripcion_tde" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Formato y reglas (opcional)</label>
                    <textarea id="descripcion_tde" name="descripcion_tde" rows="3" maxlength="2000"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}" placeholder="Eliminación directa, todos contra todos, requisitos de los equipos...">{{ old('descripcion_tde', $torneo?->descripcion_tde) }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ $editando ? route('admin.deportes.torneos.show', $torneo->id_tde) : route('admin.deportes.torneos.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</a>
                    @canRoute($editando ? 'admin.deportes.torneos.update' : 'admin.deportes.torneos.store')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-save text-xs"></i> Guardar torneo
                    </button>
                    @endcanRoute
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
