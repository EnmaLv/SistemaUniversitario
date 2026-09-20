<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Solicitudes de Becas
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bandeja de control de solicitudes registradas por el personal de bienestar.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.becas.solicitudes.create') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Registrar Solicitud</span>
                    </a>
                </div>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">

                <form action="{{ route('admin.becas.solicitudes.index') }}" method="GET"
                    class="relative w-full">
                    <input type="hidden" name="beneficio_id" value="{{ request('beneficio_id') }}">
                    <input type="hidden" name="estado" value="{{ request('estado') }}">

                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar por cédula, nombre o apellido..."
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
                class="{{ request('beneficio_id') || request('estado') !== null && request('estado') !== '' ? '' : 'hidden' }} p-3 rounded-2xl border shadow-sm mb-3">
                <form action="{{ route('admin.becas.solicitudes.index') }}" method="GET"
                    class="flex flex-col sm:flex-row sm:items-end gap-4">
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
                            <select name="beneficio_id"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                <option value="">Todos los beneficios</option>
                                @foreach ($beneficios as $ben)
                                    <option value="{{ $ben->id }}"
                                        @if (request('beneficio_id') == $ben->id) selected @endif>
                                        {{ $ben->nombre_beneficio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex-1">
                        <label class="block text-[13px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                            Estado
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-circle-half-stroke text-xs"></i>
                            </span>
                            <select name="estado"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                <option value="">Todos</option>
                                <option value="0" @if (request('estado') === '0') selected @endif>Pendiente</option>
                                <option value="1" @if (request('estado') === '1') selected @endif>Aprobada</option>
                                <option value="2" @if (request('estado') === '2') selected @endif>Rechazada</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2 mb-0.5">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-extrabold text-xs shadow-md shadow-red-600/20 active:scale-95 transition-all">
                            <i class="fas fa-check text-[10px]"></i> Aplicar
                        </button>
                        <a href="{{ route('admin.becas.solicitudes.index') }}"
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
                                <th class="px-6 py-4 text-center">Estudiante</th>
                                <th class="px-6 py-4 text-center">Beneficio</th>
                                <th class="px-6 py-4 text-center">Jornada / Lapso</th>
                                <th class="px-6 py-4 text-center">Tipo</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse($solicitudes as $solicitud)
                                <x-table-row :id="$solicitud->id">
                                    <td class="px-4 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400 font-bold">
                                        {{ ($solicitudes->currentPage() - 1) * $solicitudes->perPage() + $loop->iteration }}
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span class="font-bold" style="color: var(--text-main);">
                                                {{ $solicitud->persona->nombre_persona }}
                                                {{ $solicitud->persona->apellido_persona }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-black rounded-md text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-800 font-mono">
                                                {{ $solicitud->persona->cedula_persona }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                        style="color: var(--text-main);">
                                        {{ $solicitud->beneficio->nombre_beneficio ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span class="font-semibold text-[11px]" style="color: var(--text-main);">
                                                {{ $solicitud->jornada->nombre_jornada ?? '—' }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-black rounded-md text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-800 font-mono">
                                                {{ $solicitud->lapso->codigo ?? '—' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($solicitud->tipo_solicitud === 'nueva')
                                            <span class="inline-flex items-center px-3 py-1 text-[10px] font-black rounded-lg uppercase tracking-wider
                                                bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400
                                                border border-blue-200 dark:border-blue-900">
                                                Nueva
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 text-[10px] font-black rounded-lg uppercase tracking-wider
                                                bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400
                                                border border-purple-200 dark:border-purple-900">
                                                Renovación
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="text-[10px] font-black px-3 py-1.5 rounded-xl uppercase tracking-widest border {{ $solicitud->estado_badge }}">
                                            {{ $solicitud->estado_texto }}
                                        </span>
                                    </td>

                                    <x-table-actions :id="$solicitud->id" baseUrl="admin/becas/solicitudes"
                                        :status="1" :edit="false" :toggle="false"/>
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                        <i class="fas fa-inbox text-3xl mb-3 block text-gray-300 dark:text-gray-700"></i>
                                        No hay solicitudes registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($solicitudes->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                        {{ $solicitudes->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        document.getElementById('filtersToggle').addEventListener('click', function () {
            document.getElementById('filters').classList.toggle('hidden');
        });
    </script>
</x-app-layout>