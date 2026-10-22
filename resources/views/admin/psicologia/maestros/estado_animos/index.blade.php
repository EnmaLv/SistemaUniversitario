<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            @if (session('error'))
                <div class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span><strong class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Encabezado de Página -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Estados de Ánimo
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Gestión de la escala emocional del 1 al 10 para el módulo de <strong class="text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">Salud Mental</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    @canRoute('admin.psicologia.maestros.estado_animos.create')
                    <a href="{{ route('admin.psicologia.maestros.estado_animos.create') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Nuevo Estado de Ánimo</span>
                    </a>
                    @endcanRoute
                </div>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-4 rounded-2xl border shadow-sm mb-6 flex items-center justify-between gap-4">
                <form action="{{ route('admin.psicologia.maestros.estado_animos.index') }}" method="GET" class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar por nombre o nivel..."
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all">
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($estados as $estado)
                    @php
                        $colorClass = match (true) {
                            $estado->valor_esa <= 2 => 'bg-rose-500',
                            $estado->valor_esa <= 4 => 'bg-amber-500',
                            $estado->valor_esa <= 6 => 'bg-yellow-400',
                            $estado->valor_esa <= 8 => 'bg-emerald-400',
                            default => 'bg-emerald-600',
                        };
                        $barWidth = ($estado->valor_esa / 10) * 100;
                        $enUso = \App\Models\salud\EstadoAnimo::enUsoUltimaNota($estado->id_esa);
                    @endphp

                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="p-6 rounded-2xl border shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full relative group">

                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 flex items-center justify-center shrink-0 font-black text-lg shadow-sm">
                                {{ $estado->valor_esa }}
                            </div>
                            <h3 class="text-lg font-bold tracking-tight uppercase leading-tight" style="color: var(--text-main);">
                                {{ $estado->nombre_esa }}
                            </h3>
                        </div>

                        <div class="flex-grow my-3">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider w-14">Valor:</span>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2.5 overflow-hidden flex-grow border border-gray-200/50 dark:border-gray-700/50">
                                    <div class="h-full rounded-full {{ $colorClass }} transition-all duration-500" style="width: {{ $barWidth }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 w-10 text-right">{{ $estado->valor_esa }}/10</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-800/80 mt-auto">
                            @if (!$enUso)
                                @canRoute('admin.psicologia.maestros.estado_animos.edit')
                                <a href="{{ route('admin.psicologia.maestros.estado_animos.edit', $estado->id_esa) }}"
                                    class="p-2.5 bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 rounded-xl hover:bg-sky-600 hover:text-white transition-all text-xs font-bold flex items-center justify-center"
                                    title="Editar Estado de Ánimo">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @endcanRoute
                                @canRoute('admin.psicologia.maestros.estado_animos.destroy')
                                <form action="{{ route('admin.psicologia.maestros.estado_animos.destroy', $estado->id_esa) }}"
                                    method="POST" class="inline-block"
                                    onsubmit="event.preventDefault(); AppModal.confirm('Confirmar', '¿Estás seguro de eliminar este estado de ánimo? El valor quedará libre.').then(c => { if(c) this.submit(); });">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-2.5 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-600 hover:text-white transition-all text-xs font-bold flex items-center justify-center"
                                        title="Eliminar Estado de Ánimo">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcanRoute
                            @else
                                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2.5 py-1 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center gap-1.5"
                                    title="Este estado está siendo usado en la última nota de evolución de un paciente y no puede editarse ni eliminarse.">
                                    <i class="fas fa-lock text-[10px]"></i> En uso (Bloqueado)
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                            class="rounded-2xl border-2 border-dashed p-12 text-center shadow-sm">
                            <div class="w-16 h-16 bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                                <i class="fas fa-smile-beam"></i>
                            </div>
                            <h3 class="text-lg font-bold mb-1" style="color: var(--text-main);">
                                No hay estados de ánimo registrados
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                Crea estados de ánimo para asignar una escala emocional del 1 al 10 y usarla en las citas.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($estados->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                    {{ $estados->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>