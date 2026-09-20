<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Criterios de Beneficios
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.becas.criterios.create') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 hover:text-white text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Crear Criterio</span>
                    </a>
                </div>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">

                <form action="{{ route('admin.becas.criterios.index') }}" method="GET"
                    class="relative w-full">
                    <input type="hidden" name="beneficio" value="{{ request('beneficio') }}">
                    <input type="hidden" name="eliminatoria" value="{{ request('eliminatoria') }}">

                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar por pregunta o beneficio..."
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                </form>

                <div class="flex items-center gap-3 shrink-0">
                    <button type="button" id="filtersToggle"
                        class="w-10 h-10 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-800/50 text-gray-600 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-950/50 hover:text-red-600 hover:border-red-300 dark:hover:border-red-800 shadow-sm active:scale-95 transition-all"
                        title="Filtros">
                        <i class="fas fa-filter text-sm"></i>
                    </button>
                </div>
            </div>

            <div id="filters" style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="{{ request('beneficio') || request('eliminatoria') !== null && request('eliminatoria') !== '' ? '' : 'hidden' }} p-3 rounded-2xl border shadow-sm mb-3">
                <form action="{{ route('admin.becas.criterios.index') }}" method="GET"
                    class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <input type="hidden" name="buscar" value="{{ request('buscar') }}">

                    <div class="flex-1">
                        <label
                            class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                            Beneficio
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
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

                    <div class="flex-1">
                        <label
                            class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                            Tipo de criterio
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-shield-alt text-xs"></i>
                            </span>
                            <select name="eliminatoria"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                <option value="">Todos</option>
                                <option value="1" @if (request('eliminatoria') === '1') selected @endif>Eliminatorios</option>
                                <option value="0" @if (request('eliminatoria') === '0') selected @endif>Informativos</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2 mb-0.5">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-extrabold text-xs shadow-md shadow-red-600/20 active:scale-95 transition-all">
                            <i class="fas fa-check text-[10px]"></i> Aplicar
                        </button>
                        <a href="{{ route('admin.becas.criterios.index') }}"
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
                            <tr
                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center">Beneficio</th>
                                <th class="px-6 py-4 text-center">Pregunta</th>
                                <th class="px-6 py-4 text-center">Regla</th>
                                <th class="px-6 py-4 text-center">Tipo</th>
                                <th class="px-6 py-4 text-center">Peso</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse($criterios as $criterio)
                                <x-table-row :id="$criterio->id">
                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                        style="color: var(--text-main);">
                                        {{ $criterio->beneficio->nombre_beneficio ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="font-bold" style="color: var(--text-main);">
                                                {{ $criterio->pregunta->etiqueta ?? '—' }}
                                            </span>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 text-[10px] font-black rounded-md text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-800">
                                                {{ $criterio->pregunta->codigo ?? '' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($criterio->operador)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-black rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-900 font-mono">
                                                {{ $criterio->operador }}
                                                <span class="text-sky-900 dark:text-sky-200">{{ $criterio->valor_esperado }}</span>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-3 py-1 text-[10px] font-black rounded-lg text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-800">
                                                Sin regla
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($criterio->es_eliminatoria)
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900">
                                                <i class="fas fa-shield-alt text-[8px]"></i> Eliminatorio
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                <i class="fas fa-info-circle text-[8px]"></i> Informativo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400 font-mono">
                                        {{ rtrim(rtrim(number_format($criterio->peso, 2, '.', ''), '0'), '.') ?: '0' }}
                                    </td>

                                    <x-table-actions :id="$criterio->id" baseUrl="admin/becas/criterios"
                                        :status="1" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                        <i
                                            class="fas fa-filter-circle-xmark text-3xl mb-3 block text-gray-300 dark:text-gray-700"></i>
                                        No hay criterios registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($criterios->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                        {{ $criterios->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        document.getElementById('filtersToggle').addEventListener('click', function() {
            document.getElementById('filters').classList.toggle('hidden');
        });
    </script>
</x-app-layout>