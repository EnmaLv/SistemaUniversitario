@php
    $editando = $actividad !== null;
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $sedeActual = old('sed_id', $actividad?->sed_id ?? \App\Support\Sedes\SedeActiva::idParaCrear());
    $tipoInicial = old('tipo_cld', $actividad?->tipo_cld ?? 'esporadica');
    $diasMarcados = old('dias', $actividad?->dias() ?? []);
    $conInscripcion = (bool) old('con_inscripcion_cld', $actividad ? $actividad->con_inscripcion_cld : false);
    $bloquesJs = collect($bloquesIniciales)->values()->map(fn ($b, $i) => [
        'clave'       => 'b' . $i,
        'id'          => $b['id'] ?? null,
        'acd_id'      => (string) ($b['acd_id'] ?? ''),
        'epd_id'      => (string) ($b['epd_id'] ?? ''),
        'hora_inicio' => (string) ($b['hora_inicio'] ?? ''),
        'hora_fin'    => (string) ($b['hora_fin'] ?? ''),
        'cupo'        => (string) ($b['cupo'] ?? ''),
    ])->all();
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ $editando ? route('admin.deportes.calendarios.show', $actividad->id_cld) : route('admin.deportes.calendarios.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver
            </a>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                {{ $editando ? 'Editar actividad' : 'Crear actividad o jornada' }}
            </h1>
            <p class="mt-1 mb-6 text-sm" style="color:var(--text-muted);">
                Escribe el nombre, elige si es de un solo día o si se repite cada semana, y agrega uno o más bloques (qué deporte, en qué espacio y a qué hora).
                El sistema no te dejará ocupar un espacio que ya está tomado a esa hora.
            </p>

            @include('admin.deportes.partials.errores')

            <form method="POST" action="{{ $editando ? route('admin.deportes.calendarios.update', $actividad->id_cld) : route('admin.deportes.calendarios.store') }}"
                x-data="{
                    tipo: @js($tipoInicial),
                    bloques: @js($bloquesJs),
                    contador: {{ count($bloquesJs) }},
                    agregar() {
                        const ultimo = this.bloques[this.bloques.length - 1] || {};
                        this.bloques.push({ clave: 'n' + (this.contador++), id: null, acd_id: '', epd_id: '', hora_inicio: ultimo.hora_inicio || '', hora_fin: ultimo.hora_fin || '', cupo: '' });
                    },
                    quitar(indice) {
                        if (this.bloques.length > 1) { this.bloques.splice(indice, 1); }
                    }
                }"
                class="space-y-6">
                @csrf
                @if ($editando)
                    @method('PUT')
                @endif

                <section class="space-y-5 rounded-2xl border p-6 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <h2 class="text-sm font-black uppercase tracking-wider" style="color:var(--text-muted);">1. Datos de la actividad</h2>

                    <div>
                        <label for="nombre_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Nombre <span class="text-red-600">*</span></label>
                        <input id="nombre_cld" type="text" name="nombre_cld" maxlength="255" required value="{{ old('nombre_cld', $actividad?->nombre_cld) }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}" placeholder="Ej. Jornada deportiva de Informática 631, Entrenamiento de la selección de fútbol">
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-bold" style="color:var(--text-main);">¿Cuándo ocurre? <span class="text-red-600">*</span></p>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3" :class="tipo === 'esporadica' ? 'ring-2 ring-red-700' : ''" style="border-color:var(--border-color);">
                                <input type="radio" name="tipo_cld" value="esporadica" x-model="tipo" class="mt-1">
                                <span>
                                    <span class="block text-sm font-bold" style="color:var(--text-main);">Una sola vez</span>
                                    <span class="block text-xs" style="color:var(--text-muted);">Un evento en una fecha: una jornada, una práctica, una exhibición.</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3" :class="tipo === 'recurrente' ? 'ring-2 ring-red-700' : ''" style="border-color:var(--border-color);">
                                <input type="radio" name="tipo_cld" value="recurrente" x-model="tipo" class="mt-1">
                                <span>
                                    <span class="block text-sm font-bold" style="color:var(--text-main);">Se repite cada semana</span>
                                    <span class="block text-xs" style="color:var(--text-muted);">Por ejemplo, el equipo de fútbol entrena lunes y miércoles durante el semestre.</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div x-show="tipo === 'esporadica'">
                        <label for="fecha" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Fecha del evento <span class="text-red-600">*</span></label>
                        <input id="fecha" type="date" name="fecha" value="{{ old('fecha', $actividad?->esEsporadica() ? $actividad->fecha_inicio_cld->format('Y-m-d') : '') }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm sm:w-64" style="{{ $estiloCampo }}">
                    </div>

                    <div x-show="tipo === 'recurrente'" class="space-y-4">
                        <div>
                            <p class="mb-2 text-sm font-bold" style="color:var(--text-main);">Días de la semana <span class="text-red-600">*</span></p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (\App\Models\Deporte\DeporteCalendario::DIAS_LEGIBLES as $clave => $nombreDia)
                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-sm" style="border-color:var(--border-color);color:var(--text-main);">
                                        <input type="checkbox" name="dias[]" value="{{ $clave }}" @checked(in_array($clave, (array) $diasMarcados, true))>
                                        {{ $nombreDia }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="fecha_inicio_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Desde <span class="text-red-600">*</span></label>
                                <input id="fecha_inicio_cld" type="date" name="fecha_inicio_cld" value="{{ old('fecha_inicio_cld', $actividad?->esRecurrente() ? $actividad->fecha_inicio_cld->format('Y-m-d') : '') }}"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                            </div>
                            <div>
                                <label for="fecha_fin_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Hasta <span class="text-red-600">*</span></label>
                                <input id="fecha_fin_cld" type="date" name="fecha_fin_cld" value="{{ old('fecha_fin_cld', $actividad?->esRecurrente() ? $actividad->fecha_fin_cld->format('Y-m-d') : '') }}"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                            </div>
                        </div>
                        <p class="text-xs" style="color:var(--text-muted);">Cada día que toque será un evento con su propia asistencia.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="prg_id" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">PNF de la sección (opcional)</label>
                            <select id="prg_id" name="prg_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                                <option value="">No es de una sección</option>
                                @foreach ($programas as $programa)
                                    <option value="{{ $programa->id_prg }}" @selected((string) old('prg_id', $actividad?->prg_id) === (string) $programa->id_prg)>{{ $programa->nombre_prg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="seccion_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Sección o grupo (opcional)</label>
                            <input id="seccion_cld" type="text" name="seccion_cld" maxlength="50" value="{{ old('seccion_cld', $actividad?->seccion_cld) }}"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}" placeholder="Ej. 631">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3" style="border-color:var(--border-color);">
                            <input type="hidden" name="con_inscripcion_cld" value="0">
                            <input type="checkbox" name="con_inscripcion_cld" value="1" class="mt-1" @checked($conInscripcion)>
                            <span>
                                <span class="block text-sm font-bold" style="color:var(--text-main);">Requiere inscripción previa</span>
                                <span class="block text-xs" style="color:var(--text-muted);">Márcalo si la gente debe anotarse antes (con cupo). Si no, es abierta: solo se toma la asistencia.</span>
                            </span>
                        </label>
                    </div>

                    <div>
                        <label for="descripcion_cld" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Descripción (opcional)</label>
                        <textarea id="descripcion_cld" name="descripcion_cld" rows="3" maxlength="2000"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}" placeholder="Qué deben llevar, requisitos, observaciones...">{{ old('descripcion_cld', $actividad?->descripcion_cld) }}</textarea>
                    </div>
                </section>

                <section class="space-y-4 rounded-2xl border p-6 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider" style="color:var(--text-muted);">2. Bloques: qué deporte, dónde y a qué hora</h2>
                        <p class="mt-1 text-xs" style="color:var(--text-muted);">Si en la jornada se juegan varios deportes a la vez en canchas distintas, agrega un bloque por cada uno. El cupo es opcional: déjalo vacío si no hay límite.</p>
                    </div>

                    <template x-for="(bloque, indice) in bloques" :key="bloque.clave">
                        <div class="rounded-xl border p-4" style="border-color:var(--border-color);">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-sm font-extrabold" style="color:var(--text-main);" x-text="'Bloque ' + (indice + 1)"></p>
                                <button type="button" x-show="bloques.length > 1" @click="quitar(indice)" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-bold text-red-700 hover:bg-red-50">
                                    <i class="fas fa-times text-[10px]"></i> Quitar bloque
                                </button>
                            </div>
                            <input type="hidden" :name="'bloques[' + indice + '][id]'" :value="bloque.id ?? ''">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                                <div class="lg:col-span-2">
                                    <label class="mb-1 block text-xs font-bold" style="color:var(--text-main);">Disciplina</label>
                                    <select :name="'bloques[' + indice + '][acd_id]'" x-model="bloque.acd_id" required class="w-full rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}">
                                        <option value="">Elige...</option>
                                        @foreach ($disciplinas as $disciplina)
                                            <option value="{{ $disciplina->id_acd }}">{{ $disciplina->nombre_acd }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lg:col-span-3">
                                    <label class="mb-1 block text-xs font-bold" style="color:var(--text-main);">Espacio</label>
                                    <select :name="'bloques[' + indice + '][epd_id]'" x-model="bloque.epd_id" required class="w-full rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}">
                                        <option value="">Elige...</option>
                                        @foreach ($espacios as $espacio)
                                            <option value="{{ $espacio->id_epd }}">{{ $espacio->nombre_epd }}{{ $sedes->count() > 1 ? ' (' . ($espacio->sede?->nombre_sed ?? '') . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold" style="color:var(--text-main);">Hora de inicio</label>
                                    <input type="time" :name="'bloques[' + indice + '][hora_inicio]'" x-model="bloque.hora_inicio" required class="w-full rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold" style="color:var(--text-main);">Hora de fin</label>
                                    <input type="time" :name="'bloques[' + indice + '][hora_fin]'" x-model="bloque.hora_fin" required class="w-full rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold" style="color:var(--text-main);">Cupo (opcional)</label>
                                    <input type="number" min="1" step="1" :name="'bloques[' + indice + '][cupo]'" x-model="bloque.cupo" class="w-full rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}" placeholder="Sin límite">
                                </div>
                            </div>
                        </div>
                    </template>

                    @if ($disciplinas->isEmpty() || $espacios->isEmpty())
                        <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                            Antes de crear actividades registra al menos una disciplina y un espacio deportivo.
                        </p>
                    @endif

                    <button type="button" @click="agregar()" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-plus text-xs"></i> Agregar otro bloque
                    </button>
                </section>

                <div class="flex justify-end gap-3">
                    <a href="{{ $editando ? route('admin.deportes.calendarios.show', $actividad->id_cld) : route('admin.deportes.calendarios.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</a>
                    @canRoute($editando ? 'admin.deportes.calendarios.update' : 'admin.deportes.calendarios.store')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-save text-xs"></i> Guardar actividad
                    </button>
                    @endcanRoute
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
