@php
    $horario = $torneo->horario;
    $vigente = in_array($torneo->estado_tde, \App\Models\Deporte\TorneoDeportivo::ESTADOS_VIGENTES, true);
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.torneos.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a torneos
            </a>

            <div class="mt-2 mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">{{ $torneo->nombre_tde }}</h1>
                    <p class="mt-1 text-sm" style="color:var(--text-muted);">Arma aquí los equipos. Los participantes del torneo son los integrantes de sus equipos.</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @include('admin.deportes.partials.estado_torneo', ['torneo' => $torneo])
                        @if ($torneo->modalidad_tde)
                            <span class="rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase" style="border-color:var(--border-color);color:var(--text-main);">{{ $torneo->modalidad_tde }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @canRoute('admin.deportes.torneos.cartel_pdf')
                    <a href="{{ route('admin.deportes.torneos.cartel_pdf', $torneo->id_tde) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-file-pdf text-xs"></i> Cartel PDF
                    </a>
                    @endcanRoute
                    @if ($vigente)
                        @canRoute('admin.deportes.torneos.edit')
                        <a href="{{ route('admin.deportes.torneos.edit', $torneo->id_tde) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                            <i class="fas fa-edit text-xs"></i> Editar
                        </a>
                        @endcanRoute
                    @endif
                </div>
            </div>

            @include('admin.deportes.partials.errores')

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                        <h3 class="text-sm font-black uppercase tracking-wider" style="color:var(--text-muted);">Datos del torneo</h3>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Disciplina</p><p class="font-semibold">{{ $horario?->actividad?->nombre_acd ?? '-' }}</p></div>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Espacio</p><p class="font-semibold">{{ $horario?->espacio?->nombre_epd ?? '-' }}</p></div>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Cuándo</p><p class="font-semibold">{{ $horario?->descripcionCuando() ?? '-' }}</p></div>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Sede</p><p class="font-semibold">{{ $horario?->calendario?->sede?->nombre_sed ?? '-' }}</p></div>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Participantes</p><p class="font-semibold">{{ $participantes }} en {{ $torneo->equipos->count() }} equipos</p></div>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Coordinador</p><p class="font-semibold">{{ trim(($torneo->coordinador?->persona?->nombres_per ?? '') . ' ' . ($torneo->coordinador?->persona?->apellidos_per ?? '')) ?: '-' }}</p></div>
                        @if ($torneo->descripcion_tde)
                            <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Descripción</p><p class="text-sm">{{ $torneo->descripcion_tde }}</p></div>
                        @endif
                    </div>

                    @if ($vigente)
                        @canRoute('admin.deportes.torneos.update_estado')
                        <form method="POST" action="{{ route('admin.deportes.torneos.update_estado', $torneo->id_tde) }}" class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);"
                            data-confirmar="¿Cambiar el estado del torneo?" data-confirmar-texto="Finalizado y cancelado son estados definitivos." data-confirmar-boton="Sí, cambiar">
                            @csrf
                            @method('PATCH')
                            <h3 class="text-sm font-black uppercase tracking-wider" style="color:var(--text-muted);">Cambiar estado</h3>
                            <select name="estado_tde" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                                @foreach (\App\Models\Deporte\TorneoDeportivo::ESTADOS as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected($torneo->estado_tde === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="w-full rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-red-900">Actualizar estado</button>
                        </form>
                        @endcanRoute
                    @endif

                    <div class="rounded-2xl border p-5 shadow-sm space-y-2 text-sm" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                        <h3 class="text-sm font-black uppercase tracking-wider" style="color:var(--text-muted);">Cómo inscribir un equipo</h3>
                        <ol class="list-decimal space-y-1 pl-5">
                            <li>Escribe el nombre del equipo y, si representa a un PNF, elígelo. Pulsa "Agregar equipo".</li>
                            <li>En el equipo, escribe la cédula de cada integrante y pulsa "Agregar". Puedes pegar varias cédulas separadas por coma o una por línea.</li>
                            <li>Una persona solo puede estar en un equipo del mismo torneo.</li>
                        </ol>
                        @canRoute('admin.deportes.asistencia.index')
                        <a href="{{ route('admin.deportes.asistencia.index', ['q' => $torneo->nombre_tde]) }}" class="mt-2 inline-flex items-center gap-2 text-xs font-bold" style="color:var(--color-primary);">
                            <i class="fas fa-clipboard-check"></i> Tomar asistencia de un día del torneo
                        </a>
                        @endcanRoute
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        <div class="border-b p-4" style="border-color:var(--border-color);">
                            <h3 class="text-lg font-extrabold" style="color:var(--text-main);"><i class="fas fa-shield-alt mr-1" style="color:var(--color-primary);"></i> Equipos</h3>
                        </div>

                        @if ($vigente)
                            @canRoute('admin.deportes.torneos.equipos.store')
                            <form method="POST" action="{{ route('admin.deportes.torneos.equipos.store', $torneo->id_tde) }}" class="flex flex-col gap-2 border-b p-4 sm:flex-row" style="border-color:var(--border-color);">
                                @csrf
                                <input type="text" name="nombre_equipo_etd" maxlength="150" required value="{{ old('nombre_equipo_etd') }}" placeholder="Nombre del equipo"
                                    class="flex-1 rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}">
                                <select name="prg_id" class="rounded-xl border px-3 py-2 text-sm sm:w-64" style="{{ $estiloCampo }}">
                                    <option value="">Sin PNF (equipo libre)</option>
                                    @foreach ($programas as $programa)
                                        <option value="{{ $programa->id_prg }}" @selected((string) old('prg_id') === (string) $programa->id_prg)>{{ $programa->nombre_prg }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2 text-sm font-extrabold text-white hover:bg-red-900">
                                    <i class="fas fa-plus text-xs"></i> Agregar equipo
                                </button>
                            </form>
                            @endcanRoute
                        @endif

                        <div class="divide-y" style="border-color:var(--border-color);">
                            @forelse ($torneo->equipos as $equipo)
                                <div class="p-4 space-y-3">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="font-bold" style="color:var(--text-main);">{{ $equipo->nombre_equipo_etd }}</p>
                                            <p class="text-xs" style="color:var(--text-muted);">{{ $equipo->programa?->nombre_prg ?? 'Sin PNF' }} · {{ \App\Models\Deporte\EquipoTorneo::ESTADOS[$equipo->estado_etd] ?? $equipo->estado_etd }} · {{ $equipo->integrantes->count() }} integrantes</p>
                                        </div>
                                        @if ($vigente)
                                            @canRoute('admin.deportes.torneos.equipos.destroy')
                                            <form method="POST" action="{{ route('admin.deportes.torneos.equipos.destroy', $equipo->id_etd) }}"
                                                data-confirmar="¿Eliminar el equipo?" data-confirmar-texto="Se quitarán también sus integrantes." data-confirmar-boton="Sí, eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-50">
                                                    <i class="fas fa-trash-alt text-[10px]"></i> Eliminar equipo
                                                </button>
                                            </form>
                                            @endcanRoute
                                        @endif
                                    </div>

                                    <ul class="flex flex-wrap gap-2">
                                        @foreach ($equipo->integrantes as $integrante)
                                            <li class="inline-flex items-center gap-2 rounded-lg border px-2.5 py-1 text-xs" style="border-color:var(--border-color);color:var(--text-main);">
                                                <span class="font-semibold">{{ trim(($integrante->persona?->nombres_per ?? '') . ' ' . ($integrante->persona?->apellidos_per ?? '')) }}</span>
                                                <span style="color:var(--text-muted);">{{ $integrante->persona?->cedula_per }}</span>
                                                @if ($vigente)
                                                    @canRoute('admin.deportes.torneos.integrantes.destroy')
                                                    <form method="POST" action="{{ route('admin.deportes.torneos.integrantes.destroy', $integrante->id_ied) }}" class="inline"
                                                        data-confirmar="¿Quitar al integrante?" data-confirmar-boton="Sí, quitar">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Quitar"><i class="fas fa-times"></i></button>
                                                    </form>
                                                    @endcanRoute
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if ($vigente)
                                        @canRoute('admin.deportes.torneos.integrantes.store')
                                        <form method="POST" action="{{ route('admin.deportes.torneos.integrantes.store', $equipo->id_etd) }}" class="flex flex-col gap-2 sm:flex-row">
                                            @csrf
                                            <input type="text" name="cedula" maxlength="2000" required placeholder="Cédula (o varias separadas por coma)"
                                                class="w-full rounded-xl border px-3 py-1.5 text-sm sm:w-80" style="{{ $estiloCampo }}">
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-xl border px-3 py-1.5 text-xs font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                                                <i class="fas fa-user-plus text-[10px]"></i> Agregar
                                            </button>
                                        </form>
                                        @endcanRoute
                                    @endif
                                </div>
                            @empty
                                <p class="p-6 text-center text-sm" style="color:var(--text-muted);">Todavía no hay equipos. Escribe el nombre del primero arriba y pulsa "Agregar equipo".</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.deportes.partials.confirmar')
</x-app-layout>
