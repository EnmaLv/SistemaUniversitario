<div class="space-y-6" x-data="{ abrirCrear: false, abrirEditar: false }">
    @include('admin.localidad.modales.createModal')
    @include('admin.localidad.modales.editModal')

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', data => {
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                    confirmButtonColor: '#991b1b',
                    timer: 3000,
                    timerProgressBar: true
                });
            });

            Livewire.on('confirm-delete', data => {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Desea inactivar la localidad?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, inactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#991b1b',
                    cancelButtonColor: '#4b5563',
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('destroy-localidad', { id: data.id });
                    }
                });
            });
        });
    </script>

    <div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="border-b px-5 py-4" style="background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold" style="color: var(--text-main);">Localidades Registradas</h3>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3 text-sm" style="color: var(--text-main);">
                        <span class="font-semibold">Filtrar por estado:</span>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input wire:model.live="filtroEstado" type="checkbox" class="peer sr-only" id="estadoToggle">
                            <span class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-red-700 dark:bg-gray-700"></span>
                            <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                        </label>
                    </div>
                    <div class="flex items-center gap-2 rounded-xl border px-3 py-2" style="background-color: var(--input-bg); border-color: var(--border-color);">
                        <input type="text" class="w-full border-0 bg-transparent text-sm outline-none placeholder:text-gray-400" style="color: var(--text-main);" placeholder="Escriba una localidad" wire:model.live.debounce.300ms="search" />
                        <button class="text-gray-400 hover:text-red-600" type="button" title="Buscar">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="printArea" class="overflow-x-auto p-4">
            <table class="min-w-full divide-y text-sm" style="divide-color: var(--border-color);">
                <thead style="background-color: var(--bg-card);">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--text-main);">#</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--text-main);">Localidad</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--text-main);">Municipio</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--text-main);">Estado</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--text-main);">Status</th>
                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--text-main);">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color: var(--border-color);">
                    @if ($localidades->isEmpty())
                        <tr>
                            <td colspan="6" class="px-4 py-10">
                                <div class="flex flex-col items-center justify-center gap-3" style="color: var(--text-muted);">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full text-xl" style="background-color: var(--input-bg);">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold">No hay localidades registradas</h4>
                                    <p class="text-sm">Agrega una nueva localidad con el botón superior</p>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @foreach ($localidades as $index => $datos)
                        <x-table-row :id="$datos->id">
                            <td class="px-4 py-3" style="color: var(--text-muted);">{{ ($localidades->currentPage() - 1) * $localidades->perPage() + $loop->iteration }}</td>
                            <td class="px-4 py-3" style="color: var(--text-main);">{{ $datos->nombre_localidad }}</td>
                            <td class="px-4 py-3" style="color: var(--text-muted);">{{ $datos->municipio->nombre_municipio }}</td>
                            <td class="px-4 py-3" style="color: var(--text-muted);">{{ $datos->municipio->estado->nombre_estado }}</td>
                            <td class="px-4 py-3">
                                @if ($datos->status)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        <i class="fas fa-check-circle"></i> Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400">
                                        <i class="fas fa-times-circle"></i> Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="acciones-wrap relative flex h-8 items-center justify-center">
                                    <div id="trigger-localidad-{{ $datos->id }}" class="acciones-trigger flex h-8 w-8 items-center justify-center rounded-xl border text-gray-500 shadow-sm transition-all hover:bg-rose-50 hover:text-rose-600 dark:border-gray-600/50 dark:text-gray-400 dark:hover:bg-rose-950/50">
                                        <i class="fas fa-ellipsis-vertical text-xs"></i>
                                    </div>
                                    <div id="panel-localidad-{{ $datos->id }}" class="acciones-panel">
                                        <button wire:click="edit({{ $datos->id }})" @click="abrirEditar = true" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-amber-100 hover:text-amber-500 dark:hover:bg-amber-950/50" title="Editar">
                                        <i class="fas fa-edit"></i>
                                        </button>

                                        @if ($datos->status)
                                            <button class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50" wire:click="confirmDestroy({{ $datos->id }})" type="button" title="Inactivar">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </x-table-row>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center border-t px-4 py-3" style="border-color: var(--border-color);">
            {{ $localidades->onEachSide(1)->links('components.pagination-livewire') }}
        </div>
    </div>
</div>