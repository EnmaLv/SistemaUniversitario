<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] bg-gray-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-gray-800">Historial de horarios</h1>
                    <p class="mt-1 text-sm text-gray-500">Registros cancelados y no activos del módulo de deportes.</p>
                </div>

                <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al calendario
                </a>
            </div>

            @if($horarios->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                    No hay horarios cancelados.
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700">
                            <thead class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-600">
                                <tr>
                                    <th class="px-4 py-3">Actividad</th>
                                    <th class="px-4 py-3">Espacio</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Día / Fecha</th>
                                    <th class="px-4 py-3">Horario</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Motivo</th>
                                    <th class="px-4 py-3">Cancelado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($horarios as $horario)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-800">{{ $horario->actividad?->nombre ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $horario->espacio?->nombre ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            {{ $horario->fecha_especifica ? 'Fecha específica' : 'Recurrente' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $horario->fecha_especifica ? 
                                                \Carbon\Carbon::parse($horario->fecha_especifica)->translatedFormat('d/m/Y') : 
                                                ucfirst($horario->dia_semana ?? '-') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">
                                                {{ $horario->estado ?? 'cancelado' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $horario->motivo_cancelacion ?: 'No especificado' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">
                                            {{ $horario->updated_at ? $horario->updated_at->format('d/m/Y H:i') : '-' }}
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
</x-app-layout>
