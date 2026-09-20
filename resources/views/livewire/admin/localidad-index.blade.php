<div x-data="{ abrirCrear: false, abrirEditar: false }">
    @include('admin.localidad.modales.createModal')
    @include('admin.localidad.modales.editModal')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', data => Swal.fire({ icon:data.icon,title:data.title,text:data.text,confirmButtonColor:'#991b1b',timer:3000,timerProgressBar:true }));
            Livewire.on('confirm-delete', data => Swal.fire({ title:'¿Estás seguro?',text:'¿Desea inactivar la localidad?',icon:'warning',showCancelButton:true,confirmButtonText:'Sí, inactivar',cancelButtonText:'Cancelar',confirmButtonColor:'#991b1b',cancelButtonColor:'#4b5563' }).then(result => { if(result.isConfirmed) Livewire.dispatch('destroy-localidad',{id:data.id}); }));
        });
    </script>
    <div style="background-color:var(--bg-card);border-color:var(--border-color);" class="rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-2.5 border-b" style="border-color:var(--border-color);">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="relative w-full"><i class="fas fa-search absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400"></i><input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar localidad..." style="background-color:var(--input-bg);border-color:var(--border-color);color:var(--text-main);" class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all"></div>
                <label class="flex items-center gap-3 px-3 py-2 rounded-xl border shrink-0 cursor-pointer" style="border-color:var(--border-color);"><span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Activos</span><input wire:model.live="filtroEstado" type="checkbox" class="sr-only peer"><span class="w-10 h-6 rounded-full bg-gray-300 dark:bg-gray-700 peer-checked:bg-red-700 relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></span></label>
            </div>
        </div>
        <div class="overflow-x-auto" id="printArea"><table class="w-full text-left border-collapse">
            <thead><tr class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider"><th class="px-6 py-4 text-center" style="width:80px;">#</th><th class="px-6 py-4 text-center">Localidad</th><th class="px-6 py-4 text-center">Municipio</th><th class="px-6 py-4 text-center">Estado</th><th class="px-6 py-4 text-center">Estado del registro</th><th class="px-6 py-4 text-center" style="width:120px;">Acciones</th></tr></thead>
            <tbody class="text-xs font-medium">
                @forelse($localidades as $datos)
                    <x-table-row :id="$datos->id" class="border-b" style="border-color: var(--border-color);">
                        <td class="px-6 py-4 text-center whitespace-nowrap"><span class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">{{ ($localidades->currentPage()-1)*$localidades->perPage()+$loop->iteration }}</span></td>
                        <td class="px-6 py-4 text-center whitespace-nowrap font-bold" style="color:var(--text-main);">{{ $datos->nombre_localidad }}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $datos->municipio->nombre_municipio ?? 'Sin municipio' }}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $datos->municipio->estado->nombre_estado ?? 'Sin estado' }}</td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">@if($datos->status)<span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900"><i class="fas fa-check-circle"></i> Activo</span>@else<span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900"><i class="fas fa-times-circle"></i> Inactivo</span>@endif</td>
                        <x-table-actions :id="$datos->id" baseUrl="admin/localidad" :status="$datos->status" :show="false" :edit="false" :toggle="false"><button wire:click="edit({{ $datos->id }})" @click="abrirEditar=true" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-950/50" title="Editar"><i class="fas fa-edit text-xs"></i></button>@if($datos->status)<button wire:click="confirmDestroy({{ $datos->id }})" type="button" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-950/50" title="Inactivar"><i class="fas fa-trash-alt text-xs"></i></button>@endif</x-table-actions>
                    </x-table-row>
                @empty<tr><td colspan="6" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">No hay localidades registradas</td></tr>@endforelse
            </tbody></table></div>
        <div class="flex justify-center border-t p-4" style="border-color:var(--border-color);">{{ $localidades->onEachSide(1)->links('components.pagination-livewire') }}</div>
    </div>
</div>
