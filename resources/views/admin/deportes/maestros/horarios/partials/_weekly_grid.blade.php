@php
use Carbon\Carbon;

$bloquesJornada = $bloques ?? [];
$diasSemana = $diasSemana ?? [];
$coleccionHorarios = collect($horarios);
@endphp

<div class="rounded-2xl border shadow-sm overflow-x-auto" style="border-color: var(--border-color); background-color: var(--bg-card);">
    <div class="grid min-w-[780px]" style="grid-template-columns: 130px repeat(5, minmax(0, 1fr));">
        <div class="p-2.5 border-b border-r bg-gray-50/70 dark:bg-black/30 flex items-center justify-center font-bold text-[10px] text-gray-400 uppercase tracking-wider" style="border-color: var(--border-color);">
            <i class="far fa-clock mr-1.5"></i> BLOQUE
        </div>

        @foreach($diasSemana as $diaInfo)
            <div class="p-2.5 text-center text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b bg-gray-50/70 dark:bg-black/30 {{ !$loop->last ? 'border-r' : '' }}" style="border-color: var(--border-color);">
                {{ mb_strtoupper($diaInfo['nombre'] ?? '') }}
                @if (!empty($diaInfo['fecha']))
                    <span class="block text-[9px] font-semibold text-gray-400">{{ Carbon::parse($diaInfo['fecha'])->format('d/m') }}</span>
                @endif
            </div>
        @endforeach

        @foreach($bloquesJornada as $bloque)
            @php
                $inicioBloque = Carbon::parse($bloque['inicio'])->format('H:i');
                $finBloque = Carbon::parse($bloque['fin'])->format('H:i');
            @endphp
            <div class="p-2 flex items-center justify-center text-center text-[10px] font-bold text-gray-700 dark:text-gray-200 whitespace-nowrap border-r bg-gray-50/30 dark:bg-black/10 {{ !$loop->last ? 'border-b' : '' }}" style="border-color: var(--border-color); min-height: 86px;">
                {{ Carbon::parse($bloque['inicio'])->format('g:i A') }} - {{ Carbon::parse($bloque['fin'])->format('g:i A') }}
            </div>

            @foreach($diasSemana as $diaInfo)
                @php
                    $diaFecha = $diaInfo['fecha'] ?? null;
                    $registros = $diaFecha
                        ? $coleccionHorarios->filter(function ($item) use ($diaFecha, $inicioBloque, $finBloque) {
                            $horaInicioItem = $item->horaInicio();

                            return $horaInicioItem >= $inicioBloque && $horaInicioItem < $finBloque && $item->aplicaEnFecha($diaFecha);
                        })
                        : collect();
                @endphp

                <div class="p-1.5 border-b space-y-1 {{ !$loop->last ? 'border-r' : '' }}" style="border-color: var(--border-color); min-height: 86px; max-width: 100%; overflow: hidden;">
                    @forelse($registros as $registro)
                        @include('admin.deportes.maestros.horarios.partials._horario_card', ['h' => $registro])
                    @empty
                        <div class="flex h-full min-h-[72px] items-center justify-center rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-black/10 text-[10px] font-semibold text-gray-500 dark:text-gray-500 select-none">
                            Disponible
                        </div>
                    @endforelse
                </div>
            @endforeach
        @endforeach
    </div>
</div>
