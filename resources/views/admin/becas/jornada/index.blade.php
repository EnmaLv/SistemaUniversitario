<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Jornadas de Becas
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click.prevent="$dispatch('open-avanzar-lapso')"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-forward text-xs"></i>
                        <span>Avanzar Lapso</span>
                    </button>
                    <a href="{{ route('admin.becas.jornada.create') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Crear Jornada</span>
                    </a>
                </div>
            </div>

            @if(isset($lapsoActual))
                <div class="mb-4 bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800/50 rounded-2xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-sky-100 dark:bg-sky-800 text-sky-600 dark:text-sky-300 flex items-center justify-center">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="text-sky-800 dark:text-sky-400 font-bold text-sm">Lapso Académico Actual: {{ $lapsoActual->codigo }}</h3>
                            <p class="text-sky-700 dark:text-sky-500 text-xs font-medium">Del {{ $lapsoActual->fecha_inicio->format('d/m/Y') }} al {{ $lapsoActual->fecha_fin->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">

                <form action="{{ route('admin.becas.jornada.index') }}" method="GET" class="relative w-full">
                    <input type="hidden" name="activa" value="{{ request('activa', 1) }}">
                    <input type="hidden" name="beneficio" value="{{ request('beneficio') }}">

                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar jornada..."
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                </form>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl border"
                        style="border-color: var(--border-color);">
                        <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Activas
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="estadoToggle" class="sr-only peer"
                                {{ request('activa', 1) == 1 ? 'checked' : '' }}>
                            <div class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-red-700 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                        </label>
                    </div>

                    <button type="button" id="filtersToggle"
                        class="w-10 h-10 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-800/50 text-gray-600 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-950/50 hover:text-red-600 hover:border-red-300 dark:hover:border-red-800 shadow-sm active:scale-95 transition-all"
                        title="Filtros">
                        <i class="fas fa-filter text-sm"></i>
                    </button>
                </div>
            </div>

            <div id="filters" style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="{{ request('beneficio') ? '' : 'hidden' }} p-3 rounded-2xl border shadow-sm mb-3">
                <form action="{{ route('admin.becas.jornada.index') }}" method="GET"
                    class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <input type="hidden" name="activa" value="{{ request('activa', 1) }}">
                    <input type="hidden" name="buscar" value="{{ request('buscar') }}">

                    <div class="flex-1">
                        <label class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                            Beneficio
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-award text-xs"></i>
                            </span>
                            <select name="beneficio"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                <option value="">Todos los beneficios</option>
                                @foreach ($beneficios as $ben)
                                    <option value="{{ $ben->id }}"
                                        @if (request('beneficio') == $ben->id) selected @endif>
                                        {{ $ben->nombre_beneficio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2 mb-0.5">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-extrabold text-xs shadow-md shadow-red-600/20 active:scale-95 transition-all">
                            <i class="fas fa-check text-[10px]"></i> Aplicar
                        </button>
                        <a href="{{ route('admin.becas.jornada.index', ['activa' => request('activa', 1)]) }}"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 font-extrabold text-xs hover:bg-gray-200 dark:hover:bg-gray-700 active:scale-95 transition-all">
                            <i class="fas fa-times text-[10px]"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">

                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-4 py-4 text-center w-12">#</th>
                                <th class="px-6 py-4 text-center">Nombre</th>
                                <th class="px-6 py-4 text-center">Beneficio</th>
                                <th class="px-6 py-4 text-center">Fechas solicitud</th>
                                <th class="px-6 py-4 text-center">Cupos</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse($jornadas as $jornada)
                                @php
                                    $expirada = $jornada->fecha_fin_solicitud && $jornada->fecha_fin_solicitud->isPast();
                                    $iniciada = $jornada->fecha_inicio_solicitud && $jornada->fecha_inicio_solicitud->isPast();
                                @endphp

                                <x-table-row :id="$jornada->id">
                                    <td class="px-4 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400 font-bold">
                                        {{ ($jornadas->currentPage() - 1) * $jornadas->perPage() + $loop->iteration }}
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                        style="color: var(--text-main);">
                                        {{ $jornada->nombre_jornada }}
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $jornada->beneficio->nombre_beneficio ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span class="text-[11px] font-mono">
                                                {{ $jornada->fecha_inicio_solicitud?->format('d/m/Y') ?? '—' }}
                                                <span class="text-gray-400">→</span>
                                                {{ $jornada->fecha_fin_solicitud?->format('d/m/Y') ?? '—' }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-black rounded-md text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-800">
                                                {{ $jornada->lapso->codigo ?? '—' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-black rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-900 font-mono">
                                            {{ $jornada->cupos_asignados }}
                                            <span class="text-sky-900 dark:text-sky-200">/ {{ $jornada->cupos_maximos }}</span>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($jornada->activa)
                                            <span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                <i class="fas fa-circle text-[6px]"></i> Activa
                                            </span>
                                        @elseif ($expirada)
                                            <span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                                <i class="fas fa-clock text-[8px]"></i> Finalizada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                                <i class="fas fa-circle-xmark text-[8px]"></i> Inactiva
                                            </span>
                                        @endif
                                    </td>

                                    <x-table-actions :id="$jornada->id" baseUrl="admin/becas/jornada"
                                        :status="1" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                        <i class="fas fa-bullhorn text-3xl mb-3 block text-gray-300 dark:text-gray-700"></i>
                                        No hay jornadas registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($jornadas->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                        {{ $jornadas->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        document.getElementById('estadoToggle').addEventListener('change', function () {
            const params = new URLSearchParams(window.location.search);
            params.set('activa', this.checked ? 1 : 0);
            window.location.href = "{{ route('admin.becas.jornada.index') }}?" + params.toString();
        });

        document.getElementById('filtersToggle').addEventListener('click', function () {
            document.getElementById('filters').classList.toggle('hidden');
        });

        document.querySelectorAll('[data-action="inactivar"]').forEach(btn => {
            btn.addEventListener('click', function () {
                const form = this.closest('form');
                AppModal.show('¿Estás seguro?', 'Desea inactivar la jornada?', {
                    type: 'confirm',
                    icon: 'warning',
                    confirmText: 'Sí, inactivar',
                    cancelText: 'Cancelar'
                }).then(confirmed => { if (confirmed) form.submit(); });
            });
        });

        document.querySelectorAll('[data-action="activar"]').forEach(btn => {
            btn.addEventListener('click', function () {
                const form = this.closest('form');
                AppModal.show('¿Estás seguro?', 'Desea activar la jornada?', {
                    type: 'confirm',
                    icon: 'warning',
                    confirmText: 'Sí, activar',
                    cancelText: 'Cancelar'
                }).then(confirmed => { if (confirmed) form.submit(); });
            });
        });
    </script>
</x-app-layout>

<style>[x-cloak] { display: none !important; }</style>
<div x-data="{ showAvanzarModal: false }"
    @open-avanzar-lapso.window="showAvanzarModal = true"
    x-show="showAvanzarModal" class="fixed inset-0 overflow-y-auto" style="z-index: 9999;" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div x-show="showAvanzarModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm"
            @click="showAvanzarModal = false"></div>

        <div x-show="showAvanzarModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="relative inline-block w-full max-w-lg p-6 overflow-hidden text-left transition-all transform shadow-2xl rounded-2xl border z-10">

            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 text-xl border border-amber-200 dark:border-amber-800">
                        <i class="fas fa-forward"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold tracking-tight" style="color: var(--text-main);">Avanzar Lapso</h3>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Establece un nuevo lapso académico</p>
                    </div>
                </div>
                <button type="button" @click="showAvanzarModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.becas.lapsos.avanzar') }}" method="POST">
                @csrf
                
                <div class="mb-5 bg-amber-50 dark:bg-amber-950/20 p-4 rounded-xl border border-amber-100 dark:border-amber-900/50">
                    <p class="text-sm text-amber-800 dark:text-amber-400 font-medium">
                        <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Atención:</strong> Esta acción archivará el lapso actual ({{ isset($lapsoActual) ? $lapsoActual->codigo : 'N/A' }}) y activará el nuevo lapso en todo el módulo de becas. <strong>No se puede deshacer.</strong>
                    </p>
                </div>

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Código del Lapso *
                        </label>
                        <input type="text" name="codigo" required placeholder="Ej. 2024-2"
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Fecha de Inicio *
                            </label>
                            <input type="date" name="fecha_inicio" required
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full px-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Fecha de Fin *
                            </label>
                            <input type="date" name="fecha_fin" required
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full px-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <button type="button" @click="showAvanzarModal = false"
                        class="px-5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all active:scale-95">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-amber-500 text-white font-bold text-sm rounded-xl hover:bg-amber-600 shadow-md transition-all active:scale-95 inline-flex items-center gap-2">
                        <i class="fas fa-check text-xs"></i> Confirmar Avance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 