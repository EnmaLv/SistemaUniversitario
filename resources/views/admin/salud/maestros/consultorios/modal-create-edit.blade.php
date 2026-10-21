@props([
    'modalId' => 'modal-consultorio',
    'storeRoute' => route('admin.salud.maestros.consultorios.store'),
    'sedes' => [],
    'returnTo' => request('return_to', ''),
    'editing' => request('editing', ''),
])

<div id="{{ $modalId }}"
    class="fixed inset-0 z-50 {{ $errors->has('nombre') || $errors->has('sed_id') ? 'flex' : 'hidden' }} items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModalConsultorio('{{ $modalId }}')">
    </div>

    {{-- Dialog --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="relative w-full max-w-lg rounded-2xl border shadow-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--border-color);">
            <h3 class="flex items-center gap-2 text-base font-extrabold tracking-tight" style="color: var(--text-main);">
                <i id="{{ $modalId }}Icon" class="fas fa-plus-circle text-sky-600"></i>
                <span id="{{ $modalId }}Title">Nuevo Consultorio</span>
            </h3>
            <button type="button" onclick="closeModalConsultorio('{{ $modalId }}')"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="{{ $modalId }}Form" action="{{ $storeRoute }}" method="POST" class="rd-prevent-double-submit">
            @csrf
            <input type="hidden" name="_method" id="{{ $modalId }}Method" value="POST">
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
            <input type="hidden" name="editing" value="{{ $editing }}">
            <input type="hidden" name="from" value="{{ url()->current() }}">

            <div class="px-5 py-5 flex flex-col gap-4">

                {{-- Sede --}}
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Sede <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span
                            class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-building text-sm"></i>
                        </span>
                        <select id="{{ $modalId }}SedeId" name="sed_id" required
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                            <option value="">Seleccione una sede...</option>
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id_sed }}"
                                    {{ old('sed_id', 1) == $sede->id_sed ? 'selected' : '' }}>
                                    {{ $sede->nombre_sed }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('sed_id')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                            <i class="fas fa-circle-exclamation text-xs"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Nombre --}}
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Nombre del Consultorio <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span
                            class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-clinic-medical text-sm"></i>
                        </span>
                        <input type="text" id="{{ $modalId }}Nombre" name="nombre" maxlength="100" autofocus
                            placeholder="Ej: Consultorio 101, Cardiología..." value="{{ old('nombre') }}"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all"
                            required>
                    </div>
                    @error('nombre')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                            <i class="fas fa-circle-exclamation text-xs"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Descripción / Ubicación detallada
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <textarea id="{{ $modalId }}Descripcion" name="descripcion" rows="3" maxlength="255"
                            placeholder="Ej: Ubicado en el primer piso del ala norte..."
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">{{ old('descripcion') }}</textarea>
                    </div>
                    @error('descripcion')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                            <i class="fas fa-circle-exclamation text-xs"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 px-5 py-4 border-t"
                style="border-color: var(--border-color);">
                <button type="button" onclick="closeModalConsultorio('{{ $modalId }}')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    Cancelar
                </button>
                <button type="submit"
                    class="rd-submit-btn inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                    <i class="fas fa-check text-xs"></i> <span id="{{ $modalId }}BtnText">Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const DEFAULT_STORE_ROUTE_CONSULTORIO = "{{ $storeRoute }}";

    function abrirModalCrearConsultorio(modalId) {
        const id = modalId || '{{ $modalId }}';

        const form = document.getElementById(id + 'Form');
        if (form) form.action = DEFAULT_STORE_ROUTE_CONSULTORIO;

        const methodInput = document.getElementById(id + 'Method');
        if (methodInput) methodInput.value = 'POST';

        const titleSpan = document.getElementById(id + 'Title');
        if (titleSpan) titleSpan.innerText = 'Nuevo Consultorio';

        const btnTextSpan = document.getElementById(id + 'BtnText');
        if (btnTextSpan) btnTextSpan.innerText = 'Guardar';

        const icon = document.getElementById(id + 'Icon');
        if (icon) icon.className = "fas fa-plus-circle text-sky-600";

        const sedeSelect = document.getElementById(id + 'SedeId');
        if (sedeSelect) sedeSelect.value = '1';

        const nombreInput = document.getElementById(id + 'Nombre');
        if (nombreInput) nombreInput.value = '';

        const descTextarea = document.getElementById(id + 'Descripcion');
        if (descTextarea) descTextarea.value = '';

        openModalConsultorio(id);
    }

    function abrirModalEditarConsultorio(consultorio, updateUrl, modalId) {
        const id = modalId || '{{ $modalId }}';
        const data = (typeof consultorio === 'string') ? JSON.parse(consultorio) : consultorio;

        const form = document.getElementById(id + 'Form');
        if (form) form.action = updateUrl;

        const methodInput = document.getElementById(id + 'Method');
        if (methodInput) methodInput.value = 'PUT';

        const titleSpan = document.getElementById(id + 'Title');
        if (titleSpan) titleSpan.innerText = 'Editar Consultorio';

        const btnTextSpan = document.getElementById(id + 'BtnText');
        if (btnTextSpan) btnTextSpan.innerText = 'Actualizar';

        const icon = document.getElementById(id + 'Icon');
        if (icon) icon.className = "fas fa-edit text-sky-600";

        const sedeSelect = document.getElementById(id + 'SedeId');
        if (sedeSelect) sedeSelect.value = data.sed_id || '1';

        const nombreInput = document.getElementById(id + 'Nombre');
        if (nombreInput) nombreInput.value = data.nombre_csr || '';

        const descTextarea = document.getElementById(id + 'Descripcion');
        if (descTextarea) descTextarea.value = data.descripcion_csr || '';

        openModalConsultorio(id);
    }

    function openModalConsultorio(id) {
        const targetId = id || '{{ $modalId }}';
        const modal = document.getElementById(targetId);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModalConsultorio(id) {
        const targetId = id || '{{ $modalId }}';
        const modal = document.getElementById(targetId);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
</script>
