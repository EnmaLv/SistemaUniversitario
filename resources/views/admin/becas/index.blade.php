<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Gestión de Becas
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <a href="{{ route('admin.becas.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-lg active:scale-95 transition-all">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Nueva beca</span>
                </a>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">
                <form action="{{ route('admin.becas.index') }}" method="GET"
                    class="flex flex-col sm:flex-row items-center gap-3 w-full">
                    <input type="hidden" name="activo" value="{{ request('activo', 1) }}">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </div>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                            placeholder="Buscar beca..."
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full pl-10 pr-10 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                        <button type="submit"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-600 transition-colors"
                            title="Buscar">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </form>

                <div class="flex items-center gap-2 px-3 py-2 rounded-xl border shrink-0"
                    style="border-color: var(--border-color);">
                    <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Activas
                    </span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="estadoToggle" class="sr-only peer"
                            {{ request('activo', 1) == 1 ? 'checked' : '' }}>
                        <span
                            class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer-checked:bg-red-700 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4">
                        </span>
                    </label>
                </div>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">
                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center" style="width: 80px;">#</th>
                                <th class="px-6 py-4 text-center">Código</th>
                                <th class="px-6 py-4 text-center">Nombre</th>
                                <th class="px-6 py-4 text-center">Beneficios</th>
                                <th class="px-6 py-4 text-center">Tutores</th>
                                <th class="px-6 py-4 text-center" style="width: 140px;">Estado</th>
                                <th class="px-6 py-4 text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse($becas as $beca)
                                <x-table-row :id="$beca->id">
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ ($becas->currentPage() - 1) * $becas->perPage() + $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                        style="color: var(--text-main);">
                                        {{ $beca->codigo }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap" style="color: var(--text-main);">
                                        {{ $beca->nombre }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $beca->beneficios->count() }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $beca->tutores->count() }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($beca->activo)
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

                                    <x-table-actions :id="$beca->id" baseUrl="admin/becas"
                                        :status="$beca->activo" :toggle="false">
                                        <form action="{{ route('admin.becas.toggle', $beca) }}" method="POST"
                                            class="inline" onclick="event.stopPropagation()">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 {{ $beca->activo ? 'hover:text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-950/50' : 'hover:text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-950/50' }} transition-colors"
                                                title="{{ $beca->activo ? 'Inactivar' : 'Activar' }}">
                                                <i class="fas {{ $beca->activo ? 'fa-trash-alt' : 'fa-check' }} text-xs"></i>
                                            </button>
                                        </form>
                                    </x-table-actions>
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-xs font-bold uppercase text-gray-400">
                                        No hay becas registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
                    {{ $becas->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.getElementById('estadoToggle')?.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('activo', this.checked ? '1' : '0');
                window.location.href = url.toString();
            });
        </script>
    @endpush
</x-app-layout>
