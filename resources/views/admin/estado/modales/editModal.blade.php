<div id="modalEditarEstado" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-xl rounded-2xl border shadow-2xl" style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--border-color);">
            <h3 class="text-lg font-semibold" style="color: var(--text-main);">Editar Estado</h3>
            <button type="button" class="text-gray-400 hover:text-red-600" onclick="document.getElementById('modalEditarEstado').classList.add('hidden'); document.getElementById('modalEditarEstado').classList.remove('flex');">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form wire:submit.prevent="update" id="formEditarEstado" class="space-y-4 p-5">
            <div id="contenedorAlertaEditar"></div>
            <div>
                <label class="mb-1 block text-sm font-medium" style="color: var(--text-main);">Nombre del Estado</label>
                <div class="flex items-center gap-2 rounded-xl border px-3 py-2" style="background-color: var(--input-bg); border-color: var(--border-color);">
                    <span class="text-slate-500"><i class="fas fa-globe"></i></span>
                    <input type="text"
                        class="w-full border-0 bg-transparent text-sm outline-none placeholder:text-gray-400" style="color: var(--text-main);"
                        id="nombre_estado_editar"
                        wire:model.defer="nombre_estado"
                        inputmode="text"
                        maxlength="100"
                        placeholder="Edite el nombre del estado"
                        required>
                </div>
                @error('nombre_estado')
                    <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-xl border px-4 py-2 text-sm font-medium" style="border-color: var(--border-color); color: var(--text-main);" onclick="document.getElementById('modalEditarEstado').classList.add('hidden'); document.getElementById('modalEditarEstado').classList.remove('flex');">
                    Cancelar
                </button>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>