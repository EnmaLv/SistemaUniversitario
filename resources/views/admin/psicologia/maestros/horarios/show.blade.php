<x-app-layout>
    {{-- ================================Vista de Detalle del Bloque de Horario================================== --}}
    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-3xl border-t-8 border-blue-600 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-blue-900 mb-6">Detalle del bloque</h3>
                    {{-- ================================Encabezado con Día y Horas================================== --}}
                    <div class="flex items-center justify-between mb-6">

                    {{--
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $horario->dia_hpr }}</h3>
                            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($horario->hora_inicio_hpr)->format('g:i A') }} - {{ \Carbon\Carbon::parse($horario->hora_fin_hpr)->format('g:i A') }}</p>
                        </div>

                    --}}
                        <div>
                            <a href="{{ route('admin.psicologia.maestros.horarios.index', isset($grupoRetorno) && $grupoRetorno ? ['grupo' => $grupoRetorno] : []) }}" class="text-gray-600 hover:text-gray-800 text-sm font-semibold bg-gray-100 px-4 py-2 rounded-lg">← Volver</a>
                        </div>
                    </div>

                    {{-- ================================Sección de Detalles================================== --}}
                    <div class="grid grid-cols-1 gap-4">
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-gray-600"><span class="font-semibold">Día:</span> {{ $horario->dia_hpr }}</p>
                            <p class="text-gray-600"><span class="font-semibold">Hora inicio:</span> {{ \Carbon\Carbon::parse($horario->hora_inicio_hpr)->format('g:i A') }}</p>
                            <p class="text-gray-600"><span class="font-semibold">Hora fin:</span> {{ \Carbon\Carbon::parse($horario->hora_fin_hpr)->format('g:i A') }}</p>
@php
    $statusLabel = 'Eliminado';
    $statusClass = 'text-gray-500';

    if ($horario->status_id == \App\Models\salud\Horario::STATUS_ACTIVE) {
        $statusLabel = 'Activo';
        $statusClass = 'text-emerald-600';
    } elseif ($horario->status_id == \App\Models\salud\Horario::STATUS_INACTIVE) {
        $statusLabel = 'Inactivo';
        $statusClass = 'text-red-500';
    }
@endphp
<p class="text-gray-600"><span class="font-semibold">Estado:</span> <span class="font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></p>
                            @if ($horario->descripcion_hpr)
                                <p class="text-gray-600"><span class="font-semibold">Descripción:</span> {{ $horario->descripcion_hpr }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
