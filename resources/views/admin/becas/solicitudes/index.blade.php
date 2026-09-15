<x-app-layout>

    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- CABECERA UNIFICADA --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Solicitudes de Becas
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bandeja de control de solicitudes cargadas por el personal de bienestar.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.becas.solicitudes.create') }}"
                        class="inline-flex shrink-0 whitespace-nowrap items-center px-5 py-2.5 bg-red-700 hover:bg-red-600 text-white text-sm font-bold rounded-2xl transition-all shadow-md shadow-red-100 dark:shadow-red-900/30">
                        <i class="fas fa-plus text-xs mr-2"></i>
                        <span>Registrar Nueva Solicitud</span>
                    </a>
                </div>
            </div>

            @include('components.alert')

            {{-- CONTENEDOR PRINCIPAL CON EL ESTILO UNIFICADO --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border-l-8 border-red-700 overflow-hidden">
                <div class="p-8 text-gray-900 dark:text-gray-100">

                    {{-- FILTROS DE BÚSQUEDA --}}
                    <div class="mb-6 pb-6 border-b dark:border-gray-700">
                        <form action="{{ route('admin.becas.solicitudes.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                            <div class="flex-1 min-w-[250px]">
                                <label for="buscar" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Buscar Estudiante</label>
                                <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}"
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                    placeholder="Escriba cédula, nombre o apellido..." />
                            </div>

                            <div class="w-full sm:w-48">
                                <label for="beneficio_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Beneficio</label>
                                <select name="beneficio_id" id="beneficio_id"
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none">
                                    <option value="">Todos</option>
                                    @foreach ($beneficios as $beneficio)
                                        <option value="{{ $beneficio->id }}" {{ request('beneficio_id') == $beneficio->id ? 'selected' : '' }}>
                                            {{ $beneficio->nombre_beneficio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full sm:w-48">
                                <label for="estado" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Estado</label>
                                <select name="estado" id="estado"
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none">
                                    <option value="">Todos</option>
                                    <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="2" {{ request('estado') === '2' ? 'selected' : '' }}>Rechazado</option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit"
                                    class="h-10 px-5 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold transition-all shadow-md">
                                    <i class="fas fa-search mr-2"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.becas.solicitudes.index') }}"
                                    class="h-10 px-5 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all border border-gray-200 dark:border-gray-600">
                                    Limpiar
                                </a>
                            </div>
                        </form>
                    </div>

                    {{-- TABLA DE SOLICITUDES --}}
                    <div class="overflow-x-auto rounded-[24px] border border-slate-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                        <table class="min-w-full divide-y divide-slate-100 dark:divide-gray-700 text-sm">
                            <thead class="bg-slate-50/50 dark:bg-gray-700/30">
                                <tr>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">#</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Estudiante</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Beneficio / Tipo</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Jornada / Lapso</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Índice</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Tipo Solicitud</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Estado</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-gray-700 text-slate-700 dark:text-slate-300">
                                @forelse($solicitudes as $solicitud)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-all">
                                        <td class="px-6 py-4 text-center font-semibold text-slate-500">
                                            {{ ($solicitudes->currentPage() - 1) * $solicitudes->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800 dark:text-white">
                                                {{ $solicitud->persona->nombre_persona }} {{ $solicitud->persona->apellido_persona }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                                C.I. {{ $solicitud->persona->cedula_persona }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-slate-700 dark:text-slate-200">
                                                {{ $solicitud->beneficio->nombre_beneficio ?? 'N/A' }}
                                            </div>
                                            <div class="text-[10px] font-bold text-red-650 dark:text-red-400 uppercase tracking-wider">
                                                {{ $solicitud->jornada->beneficio->slug ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="font-medium text-slate-700 dark:text-slate-200">
                                                {{ $solicitud->jornada->nombre_jornada ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-gray-400 font-medium">
                                                Código Lapso: {{ $solicitud->lapso->codigo ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                            {{ $solicitud->indice_academico }} pts
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg border {{ $solicitud->tipo_solicitud === 'nueva' ? 'bg-blue-50 border-blue-100 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400' : 'bg-purple-50 border-purple-100 text-purple-700 dark:bg-purple-950/20 dark:text-purple-400' }}">
                                                {{ ucfirst($solicitud->tipo_solicitud) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <span class="text-[10px] font-black px-3 py-1.5 rounded-xl uppercase tracking-widest border {{ $solicitud->estado_badge }}">
                                                {{ $solicitud->estado_texto }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('admin.becas.solicitudes.show', $solicitud->id) }}"
                                                    class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-gray-700 dark:hover:bg-gray-600 border border-slate-200 dark:border-gray-600 flex items-center justify-center text-slate-600 dark:text-gray-300 transition-all"
                                                    title="Revisar Solicitud / Auditar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No se encontraron solicitudes registradas que coincidan con los filtros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINACIÓN --}}
                    @if($solicitudes->hasPages())
                        <div class="mt-6">
                            {{ $solicitudes->links() }}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
