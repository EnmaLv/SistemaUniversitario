@php
    $datos = $deportesData ?? app(\App\Services\Deporte\DeporteHomeService::class)->datos();
    $hoyEventos = $datos['hoy'];
    $proximos = $datos['proximos'];
    $activos = $datos['prestamosActivos'];
    $vencidos = $datos['prestamosVencidos'];
    $sanciones = $datos['sanciones'];
    $implementos = $datos['implementos'];
    $alertasStock = $implementos->where('alerta', true)->count();
    $nombrePersona = fn ($persona) => $persona ? trim($persona->nombres_per . ' ' . $persona->apellidos_per) : '-';
    $accesos = [
        ['ruta' => 'admin.deportes.prestamos.create', 'url' => route('admin.deportes.prestamos.create'), 'icono' => 'fa-basketball-ball', 'titulo' => 'Prestar implemento', 'texto' => 'Entrega un balón o malla a una persona por cédula.'],
        ['ruta' => 'admin.deportes.prestamos.index', 'url' => route('admin.deportes.prestamos.index', ['estado' => 'activo']), 'icono' => 'fa-undo', 'titulo' => 'Registrar devolución', 'texto' => 'Busca el préstamo y marca cómo volvió el implemento.'],
        ['ruta' => 'admin.deportes.asistencia.index', 'url' => route('admin.deportes.asistencia.index'), 'icono' => 'fa-clipboard-check', 'titulo' => 'Tomar asistencia', 'texto' => 'Elige el evento y marca a los presentes por cédula.'],
        ['ruta' => 'admin.deportes.calendarios.create', 'url' => route('admin.deportes.calendarios.create'), 'icono' => 'fa-plus-circle', 'titulo' => 'Crear actividad', 'texto' => 'Registra una jornada o un entrenamiento semanal.'],
    ];
    $tarjetaEvento = fn ($evento) => $evento->torneo
        ? route('admin.deportes.torneos.show', $evento->torneo->id_tde)
        : route('admin.deportes.calendarios.show', $evento->actividad->id_cld);
@endphp

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">Inicio de Deportes</h1>
        <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
            Lo que pasa hoy, los préstamos por devolver, las sanciones activas y el stock de implementos{{ $datos['sedeNombre'] ? ' de la sede ' . $datos['sedeNombre'] : '' }}.
        </p>
    </div>
    <p class="text-sm font-bold" style="color: var(--text-main);">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach ($accesos as $acceso)
        @canRoute($acceso['ruta'])
        <a href="{{ $acceso['url'] }}" style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="group flex items-start gap-3 rounded-2xl border p-4 shadow-sm transition-all hover:shadow-md">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300">
                <i class="fas {{ $acceso['icono'] }} text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-extrabold group-hover:underline" style="color: var(--text-main);">{{ $acceso['titulo'] }}</p>
                <p class="text-xs" style="color: var(--text-muted);">{{ $acceso['texto'] }}</p>
            </div>
        </a>
        @endcanRoute
    @endforeach
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Eventos hoy</p>
        <p class="text-2xl font-black" style="color: var(--text-main);">{{ $hoyEventos->count() }}</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Préstamos activos</p>
        <p class="text-2xl font-black text-sky-600">{{ $activos->count() }}</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Préstamos vencidos</p>
        <p class="text-2xl font-black {{ $vencidos->isNotEmpty() ? 'text-red-600' : '' }}" style="{{ $vencidos->isEmpty() ? 'color: var(--text-main);' : '' }}">{{ $vencidos->count() }}</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Sanciones activas</p>
        <p class="text-2xl font-black {{ $sanciones->isNotEmpty() ? 'text-amber-600' : '' }}" style="{{ $sanciones->isEmpty() ? 'color: var(--text-main);' : '' }}">{{ $sanciones->count() }}</p>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="col-span-2 md:col-span-1 p-4 rounded-2xl border shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Implementos en alerta</p>
        <p class="text-2xl font-black {{ $alertasStock > 0 ? 'text-red-600' : '' }}" style="{{ $alertasStock === 0 ? 'color: var(--text-main);' : '' }}">{{ $alertasStock }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="rounded-2xl border shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b px-5 py-3" style="border-color: var(--border-color);">
            <h3 class="text-base font-extrabold" style="color: var(--text-main);"><i class="fas fa-calendar-day mr-1 text-red-700"></i> Eventos de hoy</h3>
            @canRoute('admin.deportes.maestros.horarios.index')
            <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="text-xs font-bold text-red-700 hover:underline">Ver la semana</a>
            @endcanRoute
        </div>
        <ul class="divide-y" style="border-color: var(--border-color);">
            @forelse ($hoyEventos as $evento)
                <li class="flex items-center justify-between gap-3 px-5 py-3">
                    <a href="{{ $tarjetaEvento($evento) }}" class="min-w-0">
                        <p class="truncate text-sm font-bold" style="color: var(--text-main);">{{ $evento->nombre }}</p>
                        <p class="text-xs" style="color: var(--text-muted);">{{ $evento->horas }} · {{ $evento->disciplina }} · {{ $evento->espacio }}</p>
                    </a>
                    @if ($evento->sesion)
                        <span class="shrink-0 rounded-lg px-2 py-1 text-[10px] font-extrabold uppercase {{ $evento->sesion->cerrada_sad ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $evento->sesion->cerrada_sad ? 'Asistencia cerrada' : 'Asistencia en curso' }}</span>
                    @endif
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm" style="color: var(--text-muted);">No hay eventos hoy. Para programar uno usa "Crear actividad".</li>
            @endforelse
        </ul>
        @if ($proximos->isNotEmpty())
            <div class="border-t px-5 py-3" style="border-color: var(--border-color);">
                <p class="mb-2 text-[10px] font-black uppercase tracking-wider text-gray-400">Próximos 7 días</p>
                <ul class="space-y-1.5">
                    @foreach ($proximos as $evento)
                        <li class="text-xs" style="color: var(--text-main);">
                            <span class="font-bold">{{ \App\Models\Deporte\DeporteCalendario::DIAS_LEGIBLES[\App\Models\Deporte\DeporteCalendario::diaDeFecha($evento->fecha)] }} {{ $evento->fecha->format('d/m') }}</span>
                            · {{ $evento->bloque->horaInicio('g:i A') }} · {{ $evento->nombre }} ({{ $evento->disciplina }}, {{ $evento->espacio }})
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="rounded-2xl border shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b px-5 py-3" style="border-color: var(--border-color);">
            <h3 class="text-base font-extrabold" style="color: var(--text-main);"><i class="fas fa-basketball-ball mr-1 text-red-700"></i> Préstamos por devolver</h3>
            @canRoute('admin.deportes.prestamos.index')
            <a href="{{ route('admin.deportes.prestamos.index') }}" class="text-xs font-bold text-red-700 hover:underline">Ver todos</a>
            @endcanRoute
        </div>
        <ul class="divide-y" style="border-color: var(--border-color);">
            @forelse ($vencidos->concat($activos)->take(8) as $prestamo)
                @php $vencido = $prestamo->estado_prd === \App\Models\Deporte\PrestamoDeportivo::ESTADO_VENCIDO; @endphp
                <li class="flex items-center justify-between gap-3 px-5 py-3">
                    <a href="{{ route('admin.deportes.prestamos.show', $prestamo->id_prd) }}" class="min-w-0">
                        <p class="truncate text-sm font-bold" style="color: var(--text-main);">{{ $prestamo->producto?->nombre_pdt ?? 'Implemento' }} x{{ $prestamo->cantidad_prd }}</p>
                        <p class="text-xs" style="color: var(--text-muted);">{{ $nombrePersona($prestamo->persona) }} · devolver el {{ $prestamo->fecha_devolucion_esperada_prd?->format('d/m/Y h:i A') }}</p>
                    </a>
                    <span class="shrink-0 rounded-lg px-2 py-1 text-[10px] font-extrabold uppercase {{ $vencido ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-700' }}">{{ $vencido ? 'Vencido' : 'Activo' }}</span>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm" style="color: var(--text-muted);">No hay implementos prestados en este momento.</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="rounded-2xl border shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b px-5 py-3" style="border-color: var(--border-color);">
            <h3 class="text-base font-extrabold" style="color: var(--text-main);"><i class="fas fa-ban mr-1 text-red-700"></i> Sanciones activas</h3>
            @canRoute('admin.deportes.sanciones.index')
            <a href="{{ route('admin.deportes.sanciones.index') }}" class="text-xs font-bold text-red-700 hover:underline">Ver todas</a>
            @endcanRoute
        </div>
        <ul class="divide-y" style="border-color: var(--border-color);">
            @forelse ($sanciones->take(6) as $sancion)
                <li class="px-5 py-3">
                    <a href="{{ route('admin.deportes.sanciones.show', $sancion->id_snd) }}">
                        <p class="text-sm font-bold" style="color: var(--text-main);">{{ $nombrePersona($sancion->persona) }} <span class="font-mono text-xs font-normal" style="color: var(--text-muted);">{{ $sancion->persona?->cedula_per }}</span></p>
                        <p class="text-xs" style="color: var(--text-muted);">{{ $sancion->motivo_snd }} · desde {{ $sancion->fecha_sancion_snd?->format('d/m/Y') }}</p>
                    </a>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm" style="color: var(--text-muted);">Nadie tiene sanciones activas.</li>
            @endforelse
        </ul>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="rounded-2xl border shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b px-5 py-3" style="border-color: var(--border-color);">
            <h3 class="text-base font-extrabold" style="color: var(--text-main);"><i class="fas fa-boxes mr-1 text-red-700"></i> Stock de implementos</h3>
            @if (\Illuminate\Support\Facades\Route::has('admin.deportes.implementos.index'))
                @canRoute('admin.deportes.implementos.index')
                <a href="{{ route('admin.deportes.implementos.index') }}" class="text-xs font-bold text-red-700 hover:underline">Ver implementos</a>
                @endcanRoute
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b text-[11px] font-black uppercase tracking-wider" style="border-color: var(--border-color); color: var(--text-muted);">
                        <th class="px-5 py-2">Implemento</th>
                        <th class="px-3 py-2 text-center">Disponible</th>
                        <th class="px-3 py-2 text-center">Prestados</th>
                        <th class="px-3 py-2 text-center">Mínimo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($implementos as $implemento)
                        <tr class="border-b {{ $implemento->alerta ? 'bg-red-50 dark:bg-red-950/20' : '' }}" style="border-color: var(--border-color); color: var(--text-main);">
                            <td class="px-5 py-2 font-semibold">
                                {{ $implemento->nombre_pdt }}
                                @if ($implemento->alerta)
                                    <span class="ml-1 rounded-md bg-red-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-red-700">Reponer</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center font-bold">{{ rtrim(rtrim(number_format($implemento->cantidad, 2, ',', '.'), '0'), ',') }}</td>
                            <td class="px-3 py-2 text-center">{{ $implemento->prestados }}</td>
                            <td class="px-3 py-2 text-center" style="color: var(--text-muted);">{{ rtrim(rtrim(number_format($implemento->minimo, 2, ',', '.'), '0'), ',') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-sm" style="color: var(--text-muted);">No hay implementos deportivos registrados. Se agregan como productos de tipo "Implemento deportivo" y entran al inventario con una compra.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
