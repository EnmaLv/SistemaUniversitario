<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @include('components.alert')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">Programas de Formación</h1>
            <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
        </div>
        <button type="button"
            onclick="const modal = document.getElementById('modalCrearPnf'); modal?.classList.remove('hidden'); modal?.classList.add('flex'); document.querySelector('#modalCrearPnf input[name=&quot;nombre&quot;]')?.focus();"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-lg active:scale-95 transition-all">
            <i class="fas fa-plus text-xs"></i><span>Nuevo programa</span>
        </button>
    </div>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4"
        style="background-color: var(--bg-card); border-color: var(--border-color);">
        <form action="{{ route('admin.maestros.pnf.index') }}" method="GET" class="relative w-full">
            <input type="hidden" name="id_estatus" value="{{ request('id_estatus', 1) }}">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400"><i class="fas fa-search text-sm"></i></div>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar programa de formación..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all"
                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
        </form>
        <div class="flex shrink-0 cursor-pointer items-center gap-2 rounded-xl border px-3 py-2"
            style="border-color: var(--border-color);"
            role="button"
            tabindex="0"
            onclick="window.location.href = this.dataset.url"
            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href = this.dataset.url; }"
            data-url="{{ request()->fullUrlWithQuery(['id_estatus' => (int) request('id_estatus', 1) === 1 ? 2 : 1]) }}">
            <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Activos</span>
            <label class="relative inline-flex cursor-pointer items-center">
                <input type="checkbox" id="estadoToggle" class="peer sr-only" tabindex="-1"
                    {{ (int) request('id_estatus', 1) === 1 ? 'checked' : '' }}
                    aria-label="Mostrar programas activos">
                <span class="h-6 w-10 rounded-full bg-gray-300 transition peer-checked:bg-red-700 dark:bg-gray-700"></span>
                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-4"></span>
            </label>
        </div>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden">
        <div class="overflow-x-auto" id="printArea">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                        <th class="px-6 py-4 text-center" style="width:80px;">#</th><th class="px-6 py-4 text-center">Programa de Formación</th><th class="px-6 py-4 text-center" style="width:140px;">Estado</th><th class="px-6 py-4 text-center" style="width:120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-medium">
                    @forelse($pnfs as $pnf)
                        <x-table-row :id="$pnf->id_pnf">
                            <td class="px-6 py-4 text-center whitespace-nowrap"><span class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">{{ ($pnfs->currentPage() - 1) * $pnfs->perPage() + $loop->iteration }}</span></td>
                            <td class="px-6 py-4 text-center whitespace-nowrap font-bold" style="color: var(--text-main);">{{ $pnf->nombre_pnf }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($pnf->id_estatus == 1)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400"><i class="fas fa-check-circle"></i> Activo</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400"><i class="fas fa-times-circle"></i> Inactivo</span>
                                @endif
                            </td>
                            <x-table-actions :id="$pnf->id_pnf" baseUrl="admin/maestros/pnf" :status="$pnf->id_estatus == 1" :show="false" :edit="false" :toggle="false">
                                        <a href="{{ route('admin.maestros.pnf.edit', ['id' => $pnf->id_pnf]) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-amber-100 hover:text-amber-500 dark:hover:bg-amber-950/50" title="Editar"><i class="fas fa-edit text-xs"></i></a>
                                        @if ($pnf->id_estatus == 1)
                                            <form action="{{ route('admin.maestros.pnf.destroy', ['id' => $pnf->id_pnf]) }}" method="POST" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50" onclick="confirmDelete(event, this)" title="Inactivar"><i class="fas fa-trash-alt text-xs"></i></button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.maestros.pnf.activar', $pnf->id_pnf) }}" method="POST" class="inline">
                                                @csrf @method('PUT')
                                                <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-emerald-100 hover:text-emerald-500 dark:hover:bg-emerald-950/50" onclick="confirmDelete(event, this)" title="Activar"><i class="fas fa-check text-xs"></i></button>
                                            </form>
                                        @endif
                            </x-table-actions>
                        </x-table-row>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400"><i class="fas fa-graduation-cap mb-3 block text-3xl text-gray-300 dark:text-gray-700"></i>No hay programas de formación registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">{{ $pnfs->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}</div>
    </div>

    <div id="modalCrearPnf" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-lg rounded-2xl border shadow-2xl" style="background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--border-color);"><h3 class="text-lg font-semibold" style="color: var(--text-main);">Crear programa de formación</h3><button type="button" class="text-gray-400 hover:text-red-600" onclick="closeModal('modalCrearPnf')"><i class="fas fa-times"></i></button></div>
            <form action="{{ route('admin.maestros.pnf.store') }}" method="POST" class="space-y-4 p-5">
                @csrf <input type="hidden" name="from" value="{{ request('from') }}">
                <div><label class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Nombre del programa</label><div class="flex items-center rounded-xl border" style="background-color: var(--input-bg); border-color: var(--border-color);"><span class="px-3 text-gray-400"><i class="fas fa-graduation-cap"></i></span><input type="text" name="nombre" class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-red-500/30" style="color: var(--text-main);" placeholder="Ingrese el nombre" value="{{ old('nombre') }}" required></div>@error('nombre')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
                <div class="flex justify-end gap-3 border-t pt-4" style="border-color: var(--border-color);"><button type="button" class="rounded-xl border px-4 py-2 text-sm font-bold" style="border-color: var(--border-color); color: var(--text-main);" onclick="closeModal('modalCrearPnf')">Cancelar</button><button type="submit" class="rounded-xl bg-red-800 px-4 py-2 text-sm font-extrabold text-white hover:bg-red-900">Guardar Programa</button></div>
            </form>
        </div>
    </div>
        </div>
    </div>
    </div>
</x-app-layout>

@push('js')
    <script>
        function openModal(id) { document.getElementById(id)?.classList.replace('hidden', 'flex'); }
        function closeModal(id) { document.getElementById(id)?.classList.replace('flex', 'hidden'); }
        function confirmDelete(event, button) {
            event.preventDefault();
            Swal.fire({ title: '¿Estás seguro?', text: button.closest('form').action.includes('/activar') ? '¿Desea activar el programa?' : '¿Desea inactivar el programa?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#991b1b', cancelButtonColor: '#4b5563', confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) button.closest('form').submit(); });
        }
    </script>
@endpush
