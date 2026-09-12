<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Seleccionar Estudiante *</label>

    @if($selectedId)
        {{-- Estudiante ya seleccionado --}}
        <div
            class="flex items-center justify-between w-full bg-gray-50 dark:bg-gray-700 text-sm px-4 py-2.5 rounded-xl border border-emerald-400/40 dark:border-emerald-600/30">
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedLabel }}</span>
            <button type="button" wire:click="clearStudent"
                class="ml-2 text-rose-500 hover:text-rose-600 transition-colors">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
    @else
        {{-- Input de búsqueda --}}
        <input type="text" wire:model.live.debounce.300ms="search" @focus="open = true"
            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
            placeholder="Buscar por cédula o nombre..." autocomplete="off" />
    @endif

    {{-- Input oculto para el POST del formulario --}}
    <input type="hidden" name="id_persona" value="{{ $selectedId }}" />

    {{-- Dropdown --}}
    <div x-show="open && !'{{ $selectedId }}'" x-transition.opacity style="display: none;"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden max-h-60 overflow-y-auto">

        <div wire:loading.remove wire:target="search">
            @forelse($estudiantes as $est)
                <div wire:click="pickStudent({{ $est->id_persona }})" @click="open = false"
                    class="px-4 py-2.5 text-sm cursor-pointer transition-colors hover:bg-red-50 dark:hover:bg-red-950/20 border-b border-gray-100 dark:border-gray-750/30">
                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $est->nombre_persona }}
                        {{ $est->apellido_persona }}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">C.I. {{ $est->cedula_persona }}</div>
                </div>
            @empty
                <div class="px-4 py-4 text-xs text-center text-gray-400 font-bold">
                    No se encontraron estudiantes
                </div>
            @endforelse
        </div>
    </div>

    @error('id_persona') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
</div>