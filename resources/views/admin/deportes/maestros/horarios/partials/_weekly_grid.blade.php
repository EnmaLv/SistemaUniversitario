@php
use Carbon\Carbon;

$themeColor = 'green';
$DIAS = $dias ?? ['Lunes','Martes','Miércoles','Jueves','Viernes'];
$bloquesJornada = $bloques ?? [];
$diasSemana = $diasSemana ?? collect($DIAS)->map(function ($nombre, $index) {
    return ['nombre' => $nombre, 'fecha' => null];
})->values()->all();

$normalizeDay = function ($str) {
    $map = ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','Ñ'=>'N','ñ'=>'n'];
    $s = strtr($str, $map);
    $s = mb_strtolower($s, 'UTF-8');
    return trim($s);
};

$resolveDayKey = function ($item) use ($normalizeDay) {
    if (!is_null($item->fecha_especifica)) {
        return $normalizeDay(Carbon::parse($item->fecha_especifica)->locale('es')->translatedFormat('l'));
    }

    return $normalizeDay($item->dia_semana ?? $item->dia ?? '');
};
@endphp

<div class="rounded-2xl border shadow-sm overflow-x-auto" style="border-color: var(--border-color); background-color: var(--bg-card);">
    <div class="grid min-w-[780px]" style="grid-template-columns: 130px repeat(5, minmax(0, 1fr));">
        <div class="p-2.5 border-b border-r bg-gray-50/70 dark:bg-black/30 flex items-center justify-center font-bold text-[10px] text-gray-400 uppercase tracking-wider" style="border-color: var(--border-color);">
            <i class="far fa-clock mr-1.5"></i> BLOQUE
        </div>

        @foreach($diasSemana as $diaInfo)
            <div class="p-2.5 text-center text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b bg-gray-50/70 dark:bg-black/30 {{ !$loop->last ? 'border-r' : '' }}" style="border-color: var(--border-color);">
                {{ strtoupper($diaInfo['nombre'] ?? $diaInfo['label'] ?? '') }}
            </div>
        @endforeach

        @foreach($bloquesJornada as $bloque)
            <div class="p-2 flex items-center justify-center text-center text-[10px] font-bold text-gray-700 dark:text-gray-200 whitespace-nowrap border-r bg-gray-50/30 dark:bg-black/10 {{ !$loop->last ? 'border-b' : '' }}" style="border-color: var(--border-color); min-height: 86px;">
                {{ Carbon::parse($bloque['inicio'])->format('g:i A') }} - {{ Carbon::parse($bloque['fin'])->format('g:i A') }}
            </div>

            @foreach($diasSemana as $diaInfo)
                @php
                    $diaLabel = $diaInfo['nombre'] ?? ($diaInfo['label'] ?? '');
                    $diaFecha = $diaInfo['fecha'] ?? null;
                    $diaKey = $normalizeDay($diaLabel);
                    $registro = collect($horarios)->first(function ($item) use ($diaKey, $diaFecha, $bloque, $resolveDayKey) {
                        $itemDia = $resolveDayKey($item);

                        if (empty($itemDia)) {
                            return false;
                        }

                        $horaInicioItem = Carbon::parse($item->hora_inicio);
                        $inicioBloque = Carbon::parse($bloque['inicio']);
                        $finBloque = Carbon::parse($bloque['fin']);

                        $fechaCoincide = false;
                        if (!empty($item->fecha_especifica)) {
                            $fechaCoincide = !empty($diaFecha) && Carbon::parse($item->fecha_especifica)->format('Y-m-d') === $diaFecha;
                        } else {
                            $fechaCoincide = $itemDia === $diaKey;
                        }

                        return $fechaCoincide
                            && $horaInicioItem->greaterThanOrEqualTo($inicioBloque)
                            && $horaInicioItem->lt($finBloque);
                    });
                @endphp

                <div class="p-1.5 border-b {{ !$loop->last ? 'border-r' : '' }}" style="border-color: var(--border-color); min-height: 86px; max-width: 100%; overflow-hidden;">
                    @if($registro)
                        @include('admin.deportes.maestros.horarios.partials._horario_card', ['h' => $registro])
                    @else
                        <div class="flex h-full min-h-[72px] items-center justify-center rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-black/10 text-[10px] font-semibold text-gray-500 dark:text-gray-500 select-none">
                            Disponible
                        </div>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>
</div>
