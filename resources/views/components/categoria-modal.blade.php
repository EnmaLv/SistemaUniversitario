@props([
    'modalId' => 'modalCategoria',
    'storeRoute' => '',
    'tipoProductoId' => 1,
])

@php
    $isSalud = (int) $tipoProductoId === 1;

    // Colores según el módulo
    $btnBgClass    = $isSalud ? 'bg-red-800 hover:bg-red-900' : 'bg-sky-600 hover:bg-sky-700';
    $focusRingClass = $isSalud ? 'focus-within:ring-red-500' : 'focus-within:ring-sky-500';
    $iconColorClass = $isSalud ? 'text-red-800' : 'text-sky-600';
@endphp

<div id="{{ $modalId }}"
    class="fixed inset-0 z-50 {{ $errors->has('nombre') || $errors->has('descripcion') ? 'flex' : 'hidden' }} items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('{{ $modalId }}')"></div>

    {{-- Dialog --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="relative w-full max-w-md rounded-2xl border shadow-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--border-color);">
            <h3 class="flex items-center gap-2 text-base font-extrabold tracking-tight" style="color: var(--text-main);">
                <i id="{{ $modalId }}Icon" class="fas fa-plus-circle {{ $iconColorClass }}"></i>
                <span id="{{ $modalId }}Title">Nueva Categoría</span>
            </h3>
            <button type="button" onclick="closeModal('{{ $modalId }}')"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="{{ $modalId }}Form" action="{{ $storeRoute }}" method="POST" class="rd-prevent-double-submit">
            @csrf
            <input type="hidden" name="_method" id="{{ $modalId }}Method" value="POST">
            <input type="hidden" name="from" value="{{ url()->current() }}">

            <div class="px-5 py-5 flex flex-col gap-4">
                {{-- Nombre --}}
                <div>
                    <label class="block text-[16px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Nombre
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 {{ $focusRingClass }} transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-tag text-sm"></i>
                        </span>
                        <input type="text" id="{{ $modalId }}Nombre" name="nombre" maxlength="100" autofocus placeholder="Ej: Analgésicos"
                            value="{{ old('nombre') }}"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all" required>
                    </div>
                    @error('nombre')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-[16px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Descripción <span class="normal-case font-medium text-gray-400">(opcional)</span>
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 {{ $focusRingClass }} transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-align-left text-sm"></i>
                        </span>
                        <input type="text" id="{{ $modalId }}Descripcion" name="descripcion" maxlength="255"
                            placeholder="Ej: Medicamentos para el dolor" value="{{ old('descripcion') }}"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                    </div>
                    @error('descripcion')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 px-5 py-4 border-t" style="border-color: var(--border-color);">
                <button type="button" onclick="closeModal('{{ $modalId }}')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    Cancelar
                </button>
                <button type="submit"
                    class="rd-submit-btn inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnBgClass }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                    <i class="fas fa-check text-xs"></i> <span id="{{ $modalId }}BtnText">Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const DEFAULT_STORE_ROUTE = "{{ $storeRoute }}";
    const ICON_COLOR_CLASS = "{{ $iconColorClass }}";

    function abrirModalCrearCategoria(modalId = '{{ $modalId }}') {
        const form = document.getElementById(modalId + 'Form');
        if (form) form.action = DEFAULT_STORE_ROUTE;

        document.getElementById(modalId + 'Method').value = 'POST';
        document.getElementById(modalId + 'Title').innerText = 'Nueva Categoría';
        document.getElementById(modalId + 'BtnText').innerText = 'Guardar';
        
        const icon = document.getElementById(modalId + 'Icon');
        if (icon) icon.className = `fas fa-plus-circle ${ICON_COLOR_CLASS}`;

        document.getElementById(modalId + 'Nombre').value = '';
        document.getElementById(modalId + 'Descripcion').value = '';

        openModal(modalId);
    }

    function abrirModalEditarCategoria(categoria, updateUrl, modalId = '{{ $modalId }}') {
        const form = document.getElementById(modalId + 'Form');
        if (form) form.action = updateUrl;

        document.getElementById(modalId + 'Method').value = 'PUT';
        document.getElementById(modalId + 'Title').innerText = 'Editar Categoría';
        document.getElementById(modalId + 'BtnText').innerText = 'Actualizar';

        const icon = document.getElementById(modalId + 'Icon');
        if (icon) icon.className = `fas fa-edit ${ICON_COLOR_CLASS}`;

        document.getElementById(modalId + 'Nombre').value = categoria.nombre_cap || '';
        document.getElementById(modalId + 'Descripcion').value = categoria.descripcion_cap || '';

        openModal(modalId);
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="modal"]:not(.hidden)').forEach(function(modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
            document.body.style.overflow = '';
        }
    });
</script>