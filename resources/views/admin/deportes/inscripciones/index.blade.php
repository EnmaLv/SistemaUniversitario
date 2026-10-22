@php
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $vigentes = $inscripciones->where('estado_ide', '!=', \App\Models\Deporte\InscripcionDeportiva::ESTADO_RETIRADO)->count();
    $cupo = $bloque?->cupo_maximo_hde;
    $lleno = $cupo !== null && $vigentes >= $cupo;
    $porcentaje = $cupo ? min(100, round($vigentes * 100 / $cupo)) : 0;
    $estados = [
        'inscrito'   => ['Inscrito', 'bg-emerald-100 text-emerald-700'],
        'asistio'    => ['Asistió', 'bg-sky-100 text-sky-700'],
        'no_asistio' => ['No asistió', 'bg-amber-100 text-amber-700'],
        'retirado'   => ['Retirado', 'bg-gray-200 text-gray-600'],
    ];
    $actividadBloque = $bloque?->calendario;
    $admite = $bloque && $actividadBloque && $bloque->estaActivo() && $actividadBloque->estaActiva() && $actividadBloque->con_inscripcion_cld && ! $actividadBloque->esTorneo();
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Inscripciones</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-user-plus mr-1" style="color:var(--color-primary)"></i>
                        Anota a una persona en una actividad que pide inscripción previa, usando su cédula.
                    </p>
                </div>
            </div>

            @include('admin.deportes.partials.errores')

            <div class="mb-6 rounded-2xl border p-4 text-sm shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                <p class="mb-2 font-extrabold"><i class="fas fa-info-circle mr-1" style="color:var(--color-primary);"></i> ¿Cómo funciona?</p>
                <ol class="list-decimal space-y-1 pl-5" style="color:var(--text-muted);">
                    <li>Elige abajo la actividad y el bloque (deporte y hora) en que se anotará la persona.</li>
                    <li>Escribe su cédula y pulsa "Inscribir". Puede ser estudiante, docente, administrativo, obrero o de la comunidad universitaria.</li>
                    <li>Si el bloque tiene cupo, el sistema no deja pasar del límite. Al retirar a alguien, su cupo queda libre.</li>
                    <li><strong>Torneos:</strong> no se inscriben personas sueltas. Se arman equipos dentro de la pantalla del torneo y se agregan sus integrantes por cédula.</li>
                    <li><strong>Actividades abiertas</strong> (sin inscripción): no aparecen aquí; solo se toma la asistencia el día del evento.</li>
                </ol>
            </div>

            <div class="mb-6 rounded-2xl border p-4 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <form method="GET" action="{{ route('admin.deportes.inscripciones.index') }}">
                    <label for="filtro_hde_id" class="mb-1 block text-xs font-black uppercase tracking-wider" style="color:var(--text-muted);">Actividad y bloque</label>
                    <select id="filtro_hde_id" name="hde_id" onchange="this.form.submit()" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                        <option value="">Elige una actividad...</option>
                        @foreach ($actividades as $opcionActividad)
                            <optgroup label="{{ $opcionActividad->nombre_cld }} · {{ $opcionActividad->descripcionCuando() }}">
                                @foreach ($opcionActividad->horarios as $opcion)
                                    <option value="{{ $opcion->id_hde }}" @selected($bloque && (int) $bloque->id_hde === (int) $opcion->id_hde)>
                                        {{ $opcion->actividad?->nombre_acd }} · {{ $opcion->espacio?->nombre_epd }} · {{ $opcion->horas() }} ({{ $opcion->inscritos_count }}{{ $opcion->cupo_maximo_hde ? ' de ' . $opcion->cupo_maximo_hde : '' }} inscritos)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="mt-2 rounded-xl bg-red-700 px-4 py-2 text-sm font-bold text-white">Ver</button></noscript>
                </form>
            </div>

            @if (! $bloque)
                <div class="rounded-2xl border border-dashed p-10 text-center" style="border-color:var(--border-color);color:var(--text-muted);background-color:var(--bg-card);">
                    <i class="fas fa-hand-pointer fa-2x mb-3" style="opacity:.3"></i>
                    @if ($actividades->isEmpty())
                        <p class="text-sm font-semibold">No hay actividades vigentes que pidan inscripción.</p>
                        <p class="text-sm">Crea una actividad en "Actividades y jornadas" y marca "Requiere inscripción previa".</p>
                    @else
                        <p class="text-sm font-semibold">Elige arriba una actividad para ver sus inscritos y anotar personas.</p>
                    @endif
                </div>
            @elseif ($actividadBloque?->esTorneo())
                <div class="rounded-2xl border p-6 text-sm shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                    <p class="font-bold">Este bloque pertenece a un torneo.</p>
                    <p style="color:var(--text-muted);">En los torneos las personas participan como integrantes de un equipo. Agrégalas desde la pantalla del torneo.</p>
                    @if ($bloque->torneos->first())
                        <a href="{{ route('admin.deportes.torneos.show', $bloque->torneos->first()->id_tde) }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2 text-sm font-extrabold text-white hover:bg-red-900">
                            <i class="fas fa-trophy text-xs"></i> Ir al torneo
                        </a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="space-y-6">
                        <div class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                            <p class="text-lg font-extrabold">{{ $actividadBloque?->nombre_cld }}</p>
                            <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Disciplina</p><p class="font-semibold">{{ $bloque->actividad?->nombre_acd }}</p></div>
                            <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Espacio</p><p class="font-semibold">{{ $bloque->espacio?->nombre_epd }}</p></div>
                            <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Cuándo</p><p class="font-semibold">{{ $bloque->descripcionCuando() }}</p></div>
                            <div>
                                <p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Cupos</p>
                                @if ($cupo === null)
                                    <p class="font-semibold">{{ $vigentes }} inscritos · sin límite</p>
                                @else
                                    <p class="font-semibold {{ $lleno ? 'text-red-700' : '' }}">{{ $vigentes }} de {{ $cupo }} ({{ max(0, $cupo - $vigentes) }} libres)</p>
                                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-2 rounded-full {{ $lleno ? 'bg-red-700' : 'bg-emerald-500' }}" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if ($admite)
                            @canRoute('admin.deportes.inscripciones.store')
                            <form method="POST" action="{{ route('admin.deportes.inscripciones.store') }}" class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);">
                                @csrf
                                <input type="hidden" name="hde_id" value="{{ $bloque->id_hde }}">
                                <h3 class="text-sm font-black uppercase tracking-wider" style="color:var(--text-muted);">Inscribir persona</h3>
                                @if ($lleno)
                                    <p class="rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">Ya no hay cupos. Solo podrás inscribir a alguien si otra persona se retira.</p>
                                @endif
                                <div>
                                    <label for="cedula" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Cédula</label>
                                    <div class="flex gap-2">
                                        <input id="cedula" type="text" name="cedula" maxlength="20" required value="{{ old('cedula') }}" placeholder="Ej. 12345678" autocomplete="off"
                                            class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                                        <button type="button" id="btnBuscarPersona" class="rounded-xl border px-3 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);" title="Buscar persona">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <p id="resultadoPersona" class="mt-2 text-xs font-semibold" style="color:var(--text-muted);"></p>
                                </div>
                                <div>
                                    <label for="observaciones_ide" class="mb-1 block text-sm font-bold" style="color:var(--text-main);">Observaciones</label>
                                    <textarea id="observaciones_ide" name="observaciones_ide" rows="2" maxlength="1000" class="w-full rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}" placeholder="Condición médica, talla, etc.">{{ old('observaciones_ide') }}</textarea>
                                </div>
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                                    <i class="fas fa-user-plus text-xs"></i> Inscribir
                                </button>
                            </form>
                            @endcanRoute
                        @else
                            <p class="rounded-2xl border p-4 text-sm font-semibold" style="border-color:var(--border-color);color:var(--text-muted);background-color:var(--bg-card);">
                                {{ $actividadBloque && ! $actividadBloque->con_inscripcion_cld ? 'Esta actividad es abierta: no lleva inscripción, solo se toma la asistencia.' : 'Esta actividad está cancelada o terminada: no admite inscripciones nuevas.' }}
                            </p>
                        @endif
                    </div>

                    <div class="lg:col-span-2 rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        <div class="flex items-center justify-between border-b p-4" style="border-color:var(--border-color);">
                            <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Inscritos</h3>
                            @canRoute('admin.deportes.calendarios.show')
                            <a href="{{ route('admin.deportes.calendarios.show', $bloque->cld_id) }}" class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                                <i class="fas fa-clipboard-check text-[10px]"></i> Ver eventos y asistencia
                            </a>
                            @endcanRoute
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b text-[12px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                        <th class="px-4 py-3">Cédula</th>
                                        <th class="px-4 py-3">Nombre</th>
                                        <th class="px-4 py-3">Perfil</th>
                                        <th class="px-4 py-3">Inscrito</th>
                                        <th class="px-4 py-3 text-center">Estado</th>
                                        <th class="px-4 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inscripciones as $inscripcion)
                                        @php [$etiquetaEstado, $claseEstado] = $estados[$inscripcion->estado_ide] ?? [$inscripcion->estado_ide, 'bg-gray-100 text-gray-600']; @endphp
                                        <tr class="border-b text-sm {{ $inscripcion->estaVigente() ? '' : 'opacity-60' }}" style="border-color:var(--border-color);color:var(--text-main);">
                                            <td class="px-4 py-3 font-mono text-xs">{{ $inscripcion->persona?->cedula_per }}</td>
                                            <td class="px-4 py-3 font-semibold">
                                                {{ trim(($inscripcion->persona?->nombres_per ?? '') . ' ' . ($inscripcion->persona?->apellidos_per ?? '')) }}
                                                @if ($inscripcion->observaciones_ide)
                                                    <span class="block text-xs font-normal" style="color:var(--text-muted);">{{ \Illuminate\Support\Str::limit($inscripcion->observaciones_ide, 60) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs" style="color:var(--text-muted);">{{ $inscripcion->persona?->perfil?->nombre_perfil ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted);">{{ $inscripcion->fecha_registro_ide?->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-center"><span class="rounded-lg px-2 py-1 text-[11px] font-bold uppercase {{ $claseEstado }}">{{ $etiquetaEstado }}</span></td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($inscripcion->estaVigente())
                                                    @canRoute('admin.deportes.inscripciones.destroy')
                                                    <form method="POST" action="{{ route('admin.deportes.inscripciones.destroy', $inscripcion->id_ide) }}" class="inline"
                                                        data-confirmar="¿Retirar la inscripción?" data-confirmar-texto="Se liberará el cupo. El historial de asistencia se conserva." data-confirmar-boton="Sí, retirar">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold text-red-700 hover:bg-red-50">
                                                            <i class="fas fa-user-minus text-[10px]"></i> Retirar
                                                        </button>
                                                    </form>
                                                    @endcanRoute
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-sm" style="color:var(--text-muted);">Aún no hay inscritos. Escribe una cédula en el recuadro "Inscribir persona" para anotar a la primera.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('admin.deportes.partials.confirmar')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const boton = document.getElementById('btnBuscarPersona');
            const campo = document.getElementById('cedula');
            const resultado = document.getElementById('resultadoPersona');

            if (!boton || !campo || !resultado) {
                return;
            }

            function buscar() {
                const cedula = campo.value.trim();
                if (!cedula) {
                    resultado.textContent = '';
                    return;
                }

                resultado.style.color = 'var(--text-muted)';
                resultado.textContent = 'Buscando...';

                fetch(@json(route('admin.deportes.inscripciones.buscar_persona')) + '?cedula=' + encodeURIComponent(cedula), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then((respuesta) => respuesta.json().then((datos) => ({ ok: respuesta.ok, datos })))
                    .then(({ ok, datos }) => {
                        if (!ok || !datos.encontrada) {
                            resultado.style.color = '#b91c1c';
                            resultado.textContent = datos.mensaje || 'No se encontró la persona.';
                            return;
                        }

                        const persona = datos.persona;
                        resultado.style.color = persona.activa ? '#047857' : '#b45309';
                        resultado.textContent = persona.nombre + ' · ' + (persona.perfil || 'Sin perfil') + (persona.sede ? ' · ' + persona.sede : '') + (persona.activa ? '' : ' (inactiva)');
                    })
                    .catch(() => {
                        resultado.style.color = '#b91c1c';
                        resultado.textContent = 'No se pudo consultar la cédula.';
                    });
            }

            boton.addEventListener('click', buscar);
            campo.addEventListener('blur', buscar);
        });
    </script>
</x-app-layout>
