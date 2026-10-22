<x-app-layout>
    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Historial de horarios</h1>
                    <p class="mt-1 text-sm" style="color:var(--text-muted);">Horarios cancelados del módulo de deportes.</p>
                </div>

                <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver al calendario
                </a>
            </div>

            @include('admin.deportes.partials.errores')

            @if($horarios->isEmpty())
                <div class="rounded-2xl border border-dashed p-8 text-center" style="border-color:var(--border-color);color:var(--text-muted);background-color:var(--bg-card);">
                    No hay horarios cancelados.
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm" style="color:var(--text-main);">
                            <thead class="text-xs font-black uppercase tracking-wider">
                                <tr class="border-b" style="border-color:var(--border-color);">
                                    <th class="px-4 py-3">Actividad</th>
                                    <th class="px-4 py-3">Espacio</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Día / Fecha</th>
                                    <th class="px-4 py-3">Horario</th>
                                    <th class="px-4 py-3">Motivo</th>
                                    <th class="px-4 py-3">Cancelado</th>
                                    <th class="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($horarios as $horario)
                                    <tr class="border-b" style="border-color:var(--border-color);">
                                        <td class="px-4 py-3 font-semibold">{{ $horario->actividad?->nombre_acd ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $horario->espacio?->nombre_epd ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $horario->esFechaEspecifica() ? 'Fecha específica' : 'Semanal' }}</td>
                                        <td class="px-4 py-3">{{ $horario->esFechaEspecifica() ? $horario->fecha_especifica_hde->format('d/m/Y') : $horario->diaLegible() }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $horario->horaInicio() }} - {{ $horario->horaFin() }}</td>
                                        <td class="px-4 py-3" style="color:var(--text-muted);">{{ $horario->motivo_cancelacion_hde ?: 'No especificado' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap" style="color:var(--text-muted);">{{ $horario->updated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @canRoute('admin.deportes.maestros.horarios.activate')
                                            <form method="POST" action="{{ route('admin.deportes.maestros.horarios.activate', $horario->id_hde) }}" class="inline"
                                                data-confirmar="¿Reactivar el horario?" data-confirmar-texto="Se verificará que el espacio siga libre." data-confirmar-boton="Sí, reactivar">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100" title="Reactivar">
                                                    <i class="fas fa-undo text-[10px]"></i> Reactivar
                                                </button>
                                            </form>
                                            @endcanRoute
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @include('admin.deportes.partials.confirmar')
</x-app-layout>
