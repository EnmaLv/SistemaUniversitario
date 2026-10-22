@props([
    'modalId' => 'modal-create-edit',
    'storeRoute' => route('admin.enfermedades.store'),
    'tipo' => request('tipo', 'general'),
    'returnTo' => request('return_to', ''),
    'editing' => request('editing', ''),
    'categoriaTexto' => $categoriaTexto ?? 'Enfermedad / Diagnóstico',
    'btnClass' => $btnClass ?? 'bg-sky-600 hover:bg-sky-700',
    'focusRingClass' => $focusRingClass ?? 'focus:ring-sky-500',
])

<div id="{{ $modalId }}"
    class="fixed inset-0 z-50 {{ $errors->has('nombre') || $errors->has('codigo') || $errors->has('nivel') ? 'flex' : 'hidden' }} items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('{{ $modalId }}')"></div>

    {{-- Dialog --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="relative w-full max-w-lg rounded-2xl border shadow-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--border-color);">
            <h3 class="flex items-center gap-2 text-base font-extrabold tracking-tight" style="color: var(--text-main);">
                <i id="{{ $modalId }}Icon" class="fas fa-plus-circle text-sky-600"></i>
                <span id="{{ $modalId }}Title">Nueva Enfermedad</span>
            </h3>
            <button type="button" onclick="closeModal('{{ $modalId }}')"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="{{ $modalId }}Form" action="{{ $storeRoute }}" method="POST" class="rd-prevent-double-submit">
            @csrf
            <input type="hidden" name="_method" id="{{ $modalId }}Method" value="POST">
            <input type="hidden" name="tipo_contexto" value="{{ $tipo }}">
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
            <input type="hidden" name="editing" value="{{ $editing }}">
            <input type="hidden" name="from" value="{{ url()->current() }}">

            <div class="px-5 py-5 flex flex-col gap-4">

                {{-- Nombre --}}
                <div>
                    <label class="block text-[16px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Nombre del Diagnóstico / Enfermedad <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span
                            class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-stethoscope text-sm"></i>
                        </span>
                        <input type="text" id="{{ $modalId }}Nombre" name="nombre" maxlength="150" autofocus
                            placeholder="Ej: Trastorno de Ansiedad Generalizada..." value="{{ old('nombre') }}"
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

                {{-- Grid Código CIE-10 y Nivel --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Código --}}
                    <div>
                        <label class="block text-[16px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                            Código CIE-10 / DSM-5
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-hashtag text-sm"></i>
                            </span>
                            <input type="text" id="{{ $modalId }}Codigo" name="codigo" maxlength="20"
                                placeholder="Ej: F41.1, E11..." value="{{ old('codigo') }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-mono font-medium border-none focus:ring-0 focus:outline-none transition-all">
                        </div>
                        @error('codigo')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                                <i class="fas fa-circle-exclamation text-xs"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Nivel de Gravedad --}}
                    <div>
                        <label class="block text-[16px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                            Nivel Gravedad (0 - 5)
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-layer-group text-sm"></i>
                            </span>
                            <input type="number" id="{{ $modalId }}Nivel" name="nivel" min="0"
                                max="5" placeholder="0" value="{{ old('nivel', 0) }}"
                                oninput="if(this.value > 5) this.value = 5; if(this.value < 0) this.value = 0; if(this.value.length > 1) this.value = this.value.slice(0, 1);"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                        </div>
                        @error('nivel')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                                <i class="fas fa-circle-exclamation text-xs"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 px-5 py-4 border-t"
                style="border-color: var(--border-color);">
                <button type="button" onclick="closeModal('{{ $modalId }}')"
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
    const DEFAULT_STORE_ROUTE_ENFERMEDAD = "{{ $storeRoute }}";
    const ICON_COLOR_CLASS_ENFERMEDAD = "text-sky-600";

    function abrirModalCrearEnfermedad(modalId = '{{ $modalId }}') {
        const form = document.getElementById(modalId + 'Form');
        if (form) form.action = DEFAULT_STORE_ROUTE_ENFERMEDAD;

        document.getElementById(modalId + 'Method').value = 'POST';
        document.getElementById(modalId + 'Title').innerText = 'Nueva Enfermedad';
        document.getElementById(modalId + 'BtnText').innerText = 'Guardar';

        const icon = document.getElementById(modalId + 'Icon');
        if (icon) icon.className = `fas fa-plus-circle ${ICON_COLOR_CLASS_ENFERMEDAD}`;

        document.getElementById(modalId + 'Nombre').value = '';
        document.getElementById(modalId + 'Codigo').value = '';
        document.getElementById(modalId + 'Nivel').value = '0';

        openModal(modalId);
    }

    function abrirModalEditarEnfermedad(enfermedad, updateUrl, modalId = '{{ $modalId }}') {
        const form = document.getElementById(modalId + 'Form');
        if (form) form.action = updateUrl;

        document.getElementById(modalId + 'Method').value = 'PUT';
        document.getElementById(modalId + 'Title').innerText = 'Editar Enfermedad';
        document.getElementById(modalId + 'BtnText').innerText = 'Actualizar';

        const icon = document.getElementById(modalId + 'Icon');
        if (icon) icon.className = `fas fa-edit ${ICON_COLOR_CLASS_ENFERMEDAD}`;

        document.getElementById(modalId + 'Nombre').value = enfermedad.nombre_efm || '';
        document.getElementById(modalId + 'Codigo').value = enfermedad.codigo_efm || '';

        const nivelVal = (enfermedad.nivel_gravedad_efm !== undefined && enfermedad.nivel_gravedad_efm !== null) ? enfermedad.nivel_gravedad_efm : 0;
        document.getElementById(modalId + 'Nivel').value = nivelVal;

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
