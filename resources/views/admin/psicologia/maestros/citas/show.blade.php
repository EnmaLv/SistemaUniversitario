<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detalle de la cita') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-3xl border-l-8 border-blue-700 p-8">
                <h3 class="text-lg font-semibold mb-4">Cita con: {{ optional($cita->psicologo)->name ?? 'No asignado' }}</h3>
                <p><strong>Paciente:</strong> {{ optional($cita->paciente)->name ?: 'No identificado' }}</p>
                <p><strong>Fecha:</strong> {{ optional($cita->fecha)->format('d/m/Y') ?: 'No definida' }}</p>
                <p><strong>Hora:</strong> {{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('g:i A') : 'Pendiente' }}</p>
                <p><strong>Estado:</strong> {{ $cita->estado === 'no_asistio' ? 'Ausente' : ucfirst($cita->estado) }}</p>
                <p><strong>Motivo:</strong> {{ $cita->motivo ?: 'Sin motivo' }}</p>
                <p><strong>Bloques sugeridos:</strong> {{ $cita->bloques_sugeridos ?: 'No definido' }}</p>
                <p><strong>Notas:</strong> {{ $cita->notas ?: 'Sin notas' }}</p>

                <div class="mt-6">
                    <a href="{{ route('admin.psicologia.maestros.agenda.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">← Volver a Agenda</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>