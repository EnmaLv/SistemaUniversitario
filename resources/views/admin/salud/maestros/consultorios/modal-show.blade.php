@props([
    'modalId' => 'modal-show',
    'sedes' => [],
])

<div id="{{ $modalId }}" class="fixed inset-0 z-50 hidden items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModalShowConsultorio('{{ $modalId }}')">
    </div>

    {{-- Dialog --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="relative w-full max-w-lg rounded-2xl border shadow-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--border-color);">
            <h3 class="flex items-center gap-2 text-base font-extrabold tracking-tight" style="color: var(--text-main);">
                <i class="fas fa-info-circle text-sky-600"></i>
                <span>Detalles del Consultorio</span>
            </h3>
            <button type="button" onclick="closeModalShowConsultorio('{{ $modalId }}')"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-5 py-5 flex flex-col gap-4">

            {{-- Sede --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-1.5">
                    Sede
                </label>
                <div class="flex items-stretch rounded-xl border overflow-hidden"
                    style="border-color: var(--border-color);">
                    <span
                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-building text-sm"></i>
                    </span>
                    <input type="text" id="{{ $modalId }}Sede" readonly
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-semibold border-none focus:ring-0 focus:outline-none cursor-default">
                </div>
            </div>

            {{-- Nombre --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-1.5">
                    Nombre del Consultorio
                </label>
                <div class="flex items-stretch rounded-xl border overflow-hidden"
                    style="border-color: var(--border-color);">
                    <span
                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-clinic-medical text-sm"></i>
                    </span>
                    <input type="text" id="{{ $modalId }}Nombre" readonly
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-semibold border-none focus:ring-0 focus:outline-none cursor-default">
                </div>
            </div>

            {{-- Descripción --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-1.5">
                    Descripción / Ubicación detallada
                </label>
                <div class="flex items-stretch rounded-xl border overflow-hidden"
                    style="border-color: var(--border-color);">
                    <textarea id="{{ $modalId }}Descripcion" rows="3" readonly
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none cursor-default resize-none"></textarea>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 px-5 py-4 border-t" style="border-color: var(--border-color);">
            <button type="button" onclick="closeModalShowConsultorio('{{ $modalId }}')"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                style="border-color: var(--border-color); color: var(--text-main);">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
    async function abrirModalVerConsultorio(event, id, modalId) {
        if (event) event.preventDefault();

        const targetModalId = modalId || '{{ $modalId }}';
        
        const url = `{{ url('admin/salud/maestros/consultorios') }}/${id}`;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Error al obtener los datos');

            const data = await response.json();

            // Nombre
            const nombreInput = document.getElementById(targetModalId + 'Nombre');
            if (nombreInput) nombreInput.value = data.nombre || '-';

            // Sede
            const sedeInput = document.getElementById(targetModalId + 'Sede');
            if (sedeInput) sedeInput.value = data.sede ? data.sede.nombre_sed : '-';

            // Descripción
            const descTextarea = document.getElementById(targetModalId + 'Descripcion');
            if (descTextarea) descTextarea.value = data.descripcion || 'Sin descripción registrada.';

            openModalShowConsultorio(targetModalId);

        } catch (error) {
            console.error('Error:', error);
        }
    }

    function openModalShowConsultorio(id) {
        const targetId = id || '{{ $modalId }}';
        const modal = document.getElementById(targetId);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModalShowConsultorio(id) {
        const targetId = id || '{{ $modalId }}';
        const modal = document.getElementById(targetId);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
</script>
