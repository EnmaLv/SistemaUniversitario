@php $themeColor = 'green'; @endphp
<x-app-layout>
    <div class="pt-8 pb-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="text-sm">← Volver</a>
            <h1 class="text-2xl font-bold my-4">Horario #{{ $horario->id }}</h1>

            <div class="bg-white shadow rounded p-4">
                <p><strong>Calendario:</strong> {{ $horario->calendario->nombre ?? '' }}</p>
                <p><strong>Actividad:</strong> {{ $horario->actividad->nombre ?? '' }}</p>
                <p><strong>Espacio:</strong> {{ $horario->espacio->nombre ?? '' }}</p>
                <p><strong>Día/Fecha:</strong> {{ $horario->fecha_especifica ? $horario->fecha_especifica->format('d/m/Y') : $horario->dia_semana }}</p>
                <p><strong>Hora:</strong> {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('g:i A') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('g:i A') }}</p>
                <p><strong>Observaciones:</strong> {{ $horario->observaciones }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
