<x-app-layout>

    @include('components.alert')

    @php
        $colorNivel = [
            'info'        => 'bg-sky-100 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300',
            'advertencia' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
            'critico'     => 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300',
        ];
        $consultaExportar = array_filter($filtros, fn ($valor) => $valor !== null && $valor !== '');
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Bitácora de auditoría</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-clipboard-list mr-1" style="color:var(--color-primary)"></i>
                        Registro inmutable de la actividad del sistema · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                @canAction('bitacora', 'exportar')
                    <div class="flex gap-2">
                        <a href="{{ route('admin.configuracion.bitacora.exportar_csv', $consultaExportar) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                            <i class="fas fa-file-csv text-xs"></i> Exportar CSV
                        </a>
                        <a href="{{ route('admin.configuracion.bitacora.exportar_pdf', $consultaExportar) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-extrabold shadow-sm transition-all active:scale-95" style="border-color:var(--border-color);color:var(--text-main);background-color:var(--bg-card);">
                            <i class="fas fa-file-pdf text-xs" style="color:var(--color-primary)"></i> Exportar PDF
                        </a>
                    </div>
                @endcanAction
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-4 border-b" style="border-color:var(--border-color);">
                    <form action="{{ route('admin.configuracion.bitacora.index') }}" method="GET">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide" style="color:var(--text-muted);">Usuario</label>
                                <input type="text" name="usuario" value="{{ $filtros['usuario'] ?? '' }}" placeholder="Username o ID" class="w-full rounded-xl border px-3 py-2 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide" style="color:var(--text-muted);">Módulo</label>
                                <select name="modulo" class="w-full rounded-xl border px-3 py-2 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="">Todos</option>
                                    @foreach ($modulos as $modulo)
                                        <option value="{{ $modulo }}" @selected(($filtros['modulo'] ?? '') === $modulo)>{{ $modulo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide" style="color:var(--text-muted);">Acción</label>
                                <select name="accion" class="w-full rounded-xl border px-3 py-2 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="">Todas</option>
                                    @foreach ($acciones as $accion)
                                        <option value="{{ $accion }}" @selected(($filtros['accion'] ?? '') === $accion)>{{ $accion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide" style="color:var(--text-muted);">Nivel</label>
                                <select name="nivel" class="w-full rounded-xl border px-3 py-2 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="">Todos</option>
                                    @foreach ($niveles as $nivel)
                                        <option value="{{ $nivel }}" @selected(($filtros['nivel'] ?? '') === $nivel)>{{ ucfirst($nivel) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide" style="color:var(--text-muted);">Dirección IP</label>
                                <input type="text" name="ip" value="{{ $filtros['ip'] ?? '' }}" placeholder="Ej: 192.168." class="w-full rounded-xl border px-3 py-2 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide" style="color:var(--text-muted);">Desde</label>
                                <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="w-full rounded-xl border px-3 py-2 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide" style="color:var(--text-muted);">Hasta</label>
                                <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="w-full rounded-xl border px-3 py-2 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                            </div>
                            <div class="flex items-end gap-2">
                                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-red-900">
                                    <i class="fas fa-filter text-xs"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.configuracion.bitacora.index') }}" class="inline-flex items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);" title="Limpiar filtros">
                                    <i class="fas fa-eraser text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-4 py-4 text-center">#</th>
                                <th class="px-4 py-4">Fecha y hora</th>
                                <th class="px-4 py-4">Usuario</th>
                                <th class="px-4 py-4">Módulo</th>
                                <th class="px-4 py-4">Acción</th>
                                <th class="px-4 py-4">Detalle</th>
                                <th class="px-4 py-4">IP</th>
                                <th class="px-4 py-4 text-center">Nivel</th>
                                <th class="px-4 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="fade-in">
                            @forelse ($registros as $registro)
                                <x-table-row :id="$registro->id" class="border-b text-sm" style="border-color:var(--border-color);">
                                    <td class="px-4 py-3 text-center font-bold" style="color:var(--text-muted);">{{ $registro->id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap" style="color:var(--text-main);">{{ $registro->fecha_hora?->format('d/m/Y H:i:s') }}</td>
                                    <td class="px-4 py-3" style="color:var(--text-main);">
                                        <span class="font-bold">{{ $registro->nombre_usuario ?? 'Sistema' }}</span>
                                        @if ($registro->usuario_id)
                                            <span class="block text-xs" style="color:var(--text-muted);">ID {{ $registro->usuario_id }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3" style="color:var(--text-main);">{{ $registro->modulo }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-extrabold" style="border-color:var(--border-color);color:var(--color-primary);">{{ $registro->accion }}</span>
                                    </td>
                                    <td class="px-4 py-3 max-w-xs" style="color:var(--text-muted);">
                                        @if ($registro->tabla_afectada)
                                            <span class="block text-xs font-bold">{{ $registro->tabla_afectada }}{{ $registro->registro_id ? ' #' . $registro->registro_id : '' }}</span>
                                        @endif
                                        @if ($registro->metodo_http)
                                            <span class="block truncate text-xs" title="{{ $registro->ruta }}">{{ $registro->metodo_http }} {{ $registro->ruta }} {{ $registro->codigo_respuesta ? '· ' . $registro->codigo_respuesta : '' }}</span>
                                        @endif
                                        @if ($registro->observacion)
                                            <span class="block truncate text-xs" title="{{ $registro->observacion }}">{{ \Illuminate\Support\Str::limit($registro->observacion, 70) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs" style="color:var(--text-main);">{{ $registro->ip_address ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $colorNivel[$registro->nivel] ?? $colorNivel['info'] }}">{{ ucfirst($registro->nivel) }}</span>
                                    </td>
                                    <x-table-actions :id="$registro->id" baseUrl="admin/configuracion/bitacora" :edit="false" :toggle="false" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-clipboard-list fa-3x mb-3" style="opacity: 0.1"></i>
                                        <p>No hay eventos que coincidan con los filtros.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t flex justify-center" style="border-color:var(--border-color);">
                    {{ $registros->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
