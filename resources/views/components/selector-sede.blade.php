@php
    $usuarioSede = auth()->user();
    $puedeElegirSede = \App\Support\Sedes\SedeActiva::puedeElegir($usuarioSede);
    $sedeActualId = $usuarioSede ? \App\Support\Sedes\SedeActiva::id() : null;
    $sedesDisponibles = $puedeElegirSede
        ? \Illuminate\Support\Facades\DB::table('sede')->where('activo', 1)->orderBy('nombre')->get(['id', 'nombre'])
        : collect();
    $nombreSedeActual = $sedeActualId
        ? (\Illuminate\Support\Facades\DB::table('sede')->where('id', $sedeActualId)->value('nombre') ?? 'Sede')
        : 'Todas las sedes';
@endphp

@if ($usuarioSede)
    @if ($puedeElegirSede)
        <div class="relative" x-data="{ abierto: false }" @click.away="abierto = false">
            <button type="button" @click="abierto = !abierto"
                class="flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/40"
                title="Sede activa">
                <i class="fas fa-building"></i>
                <span class="max-w-[140px] truncate">{{ $nombreSedeActual }}</span>
                <i class="fas fa-chevron-down text-[10px]"></i>
            </button>
            <div x-show="abierto" x-transition style="display: none; background-color: var(--bg-card); border-color: var(--border-color);"
                class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-xl border shadow-xl">
                <p class="px-4 pt-3 pb-1 text-[10px] font-black uppercase tracking-wider" style="color: var(--text-muted);">Sede activa</p>
                <form method="POST" action="{{ route('sede.activa.cambiar') }}" class="pb-2">
                    @csrf
                    <button type="submit" name="sede_id" value=""
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm font-semibold hover:bg-red-800 hover:text-white {{ $sedeActualId === null ? 'text-red-800' : '' }}"
                        style="{{ $sedeActualId === null ? '' : 'color: var(--text-main);' }}">
                        <i class="fas fa-globe-americas w-4"></i> Todas las sedes
                    </button>
                    @foreach ($sedesDisponibles as $sedeOpcion)
                        <button type="submit" name="sede_id" value="{{ $sedeOpcion->id }}"
                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm font-semibold hover:bg-red-800 hover:text-white {{ $sedeActualId === (int) $sedeOpcion->id ? 'text-red-800' : '' }}"
                            style="{{ $sedeActualId === (int) $sedeOpcion->id ? '' : 'color: var(--text-main);' }}">
                            <i class="fas fa-building w-4"></i> {{ $sedeOpcion->nombre }}
                        </button>
                    @endforeach
                </form>
            </div>
        </div>
    @else
        <span class="flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-white" title="Tu sede">
            <i class="fas fa-building"></i>
            <span class="max-w-[140px] truncate">{{ $nombreSedeActual }}</span>
        </span>
    @endif
@endif
