@extends('layouts.app')

@section('content_header')
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">Rutas de Transporte</h1>
            <p class="mt-1 text-sm text-gray-500">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.transporte.maestros.bus_rutas.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white transition-colors hover:bg-red-900">
                <i class="fas fa-plus"></i> Nueva Ruta
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div class="p-4">
            <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-black" style="color:var(--text-main);">Rutas Registradas</h3>
                </div>
                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-gray-500">Activas</span>
                            <input type="checkbox" id="estadoToggle" class="peer sr-only"
                                {{ request('estado', 1) == 1 ? 'checked' : '' }}>
                            <label for="estadoToggle" class="relative h-6 w-10 cursor-pointer rounded-full bg-gray-300 peer-checked:bg-red-700 dark:bg-gray-700"><span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-4"></span></label>
                    </div>
                    <form action="{{ route('admin.transporte.maestros.bus_rutas.index') }}" method="GET"
                        class="relative w-full md:w-64" role="search">
                        <input type="hidden" name="estado" value="{{ request('estado', 1) }}">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="w-full rounded-xl border py-2.5 pl-4 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-red-500" style="background-color:var(--input-bg);border-color:var(--border-color);color:var(--text-main);" placeholder="Buscar ruta..." />
                        <button class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>

            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                        <th class="px-6 py-4 text-center">#</th>
                        <th class="px-6 py-4 text-center">Nombre</th>
                        <th class="px-6 py-4 text-center">Origen → Destino</th>
                        <th class="px-6 py-4 text-center">Distancia</th>
                        <th class="px-6 py-4 text-center">Horarios</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rutas as $ruta)
                        <x-table-row :id="$ruta->id">
                            <td class="px-6 py-4 text-center" style="color:var(--text-muted);">
                                {{ ($rutas->currentPage() - 1) * $rutas->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold" style="color:var(--text-main);">{{ $ruta->nombre }}</td>

                            <td class="px-6 py-4 text-center" style="color:var(--text-main);">
                                @if ($ruta->paradas && $ruta->paradas->isNotEmpty())
                                    {{ $ruta->paradas->first()->nombre }}
                                    <i class="fas fa-arrow-right mx-1" style="color:var(--color-primary)"></i>
                                    {{ $ruta->paradas->last()->nombre }}
                                @else
                                    <span class="text-muted" style="font-size: 0.85rem;">Sin paradas trazadas</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center" style="color:var(--text-muted);">{{ $ruta->distancia_km }} km</td>
                            <td class="px-6 py-4 text-center text-xs">
                                @forelse($ruta->horarios as $horario)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-[10px] font-black {{ $horario->tipo_viaje === 'entrada' ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : 'border-amber-200 bg-amber-50 text-amber-600' }} m-1"
                                        title="Viaje de {{ $horario->tipo_viaje }}">
                                        {{ substr($horario->hora_salida, 0, 5) }}
                                        {{ $horario->tipo_viaje === 'entrada' ? 'â˜€ï¸' : 'ðŸ ' }}
                                    </span>
                                @empty
                                    <span class="text-muted">Sin horarios</span>
                                @endforelse
                            </td>

                            <td class="text-center">
                                @if ($ruta->estado)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400"><i class="fas fa-check-circle"></i> Activa</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400"><i class="fas fa-times-circle"></i> Inactiva</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="acciones-wrap relative flex h-8 items-center justify-center">
                                    <div class="acciones-trigger flex h-8 w-8 items-center justify-center rounded-xl border text-gray-500 shadow-sm transition-all hover:bg-rose-50 hover:text-rose-600 dark:border-gray-600/50 dark:text-gray-400 dark:hover:bg-rose-950/50"><i class="fas fa-ellipsis-vertical text-xs"></i></div>
                                    <div class="acciones-panel">
                                    <a href="{{ route('admin.transporte.maestros.bus_rutas.edit', $ruta) }}"
                                        onclick="event.stopPropagation()" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-amber-100 hover:text-amber-500 dark:hover:bg-amber-950/50" title="Editar">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    @if ($ruta->estado)
                                        <form action="{{ route('admin.transporte.maestros.bus_rutas.destroy', $ruta) }}"
                                            method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50"
                                                onclick="event.stopPropagation(); confirmAccion(event, this, 'inactivar', 'ruta')" title="Inactivar">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.transporte.maestros.bus_rutas.activar', $ruta) }}"
                                            method="POST" style="display:inline;">
                                            @csrf @method('PUT')
                                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-emerald-100 hover:text-emerald-500 dark:hover:bg-emerald-950/50"
                                                onclick="event.stopPropagation(); confirmAccion(event, this, 'activar', 'ruta')" title="Activar">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                    </div>
                                </div>
                            </td>
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">No hay rutas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="flex justify-center border-t p-4" style="border-color:var(--border-color);">
                {{ $rutas->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        function confirmAccion(event, button, accion, entidad) {
            event.preventDefault();
            Swal.fire({
                title: 'Â¿EstÃ¡s seguro?',
                text: `Â¿Desea ${accion} la ${entidad}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `SÃ­, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) button.closest('form').submit();
            });
        }

        document.getElementById('estadoToggle').addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('estado', this.checked ? 1 : 0);
            window.location.href = "{{ route('admin.transporte.maestros.bus_rutas.index') }}?" + params.toString();
        });
    </script>
@endpush
