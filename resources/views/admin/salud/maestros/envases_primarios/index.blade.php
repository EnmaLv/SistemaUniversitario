<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- Encabezado de la página --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Presentacion 
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombres_per }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="abrirModalCrearEnvasePrimario()"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-sm shadow-lg active:scale-95 transition-all">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Nueva Presentacion</span>
                    </button>
                </div>
            </div>

            {{-- Card de Buscador + Estado --}}
            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">

                <form action="{{ route('admin.salud.maestros.envases_primarios.index') }}" method="GET"
                    class="relative w-full">
                    <input type="hidden" name="estado" value="{{ request('estado', 1) }}">

                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar envase primario..."
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                </form>

                <div class="flex items-center gap-3 shrink-0">
                    {{-- Toggle estado --}}
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl border"
                        style="border-color: var(--border-color);">
                        <span
                            class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">estados</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="estadoToggle" class="sr-only peer"
                                {{ request('estado', 1) == 1 ? 'checked' : '' }}>
                            <div
                                class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-sky-600 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4">
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Card de la Tabla --}}
            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">

                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center" style="width: 80px;">#</th>
                                <th class="px-6 py-4 text-center">Nombre</th>
                                <th class="px-6 py-4 text-center" style="width: 140px;">Estado</th>
                                <th class="px-6 py-4 text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse($envases as $envase)
                                <x-table-row :id="$envase->id_enp">
                                    {{-- Numeración --}}
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ ($envases->currentPage() - 1) * $envases->perPage() + $loop->iteration }}
                                        </span>
                                    </td>

                                    {{-- Nombre --}}
                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                        style="color: var(--text-main);">
                                        {{ $envase->nombre_enp }}
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($envase->status_id == 1)
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                <i class="fas fa-check-circle"></i> Activo
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                                <i class="fas fa-times-circle"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Componente de Acciones --}}
                                    <x-table-actions 
                                        :id="$envase->id_enp" 
                                        baseUrl="admin/salud/maestros/envases_primarios"
                                        :status="$envase->status_id == 1" 
                                        :show="false" 
                                        :on-edit="'abrirModalEditarEnvasePrimario(' .
                                            json_encode($envase, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP) .
                                            ', `' .
                                            route('admin.salud.maestros.envases_primarios.update', $envase->id_enp) .
                                            '`)'" 
                                    />

                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                        <i class="fas fa-box-open text-3xl mb-3 block text-gray-300 dark:text-gray-700"></i>
                                        No hay envases primarios registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($envases->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                        {{ $envases->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Modal Crear/Editar --}}
    @include('admin.salud.maestros.envases_primarios.modal-create-edit')

    <script>
        // Toggle de estado (estado/inestado)
        document.getElementById('estadoToggle').addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('estado', this.checked ? 1 : 0);
            window.location.href = "{{ route('admin.salud.maestros.envases_primarios.index') }}?" + params.toString();
        });
    </script>
</x-app-layout>