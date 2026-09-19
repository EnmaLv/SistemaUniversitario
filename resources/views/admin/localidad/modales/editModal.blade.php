<div id="modalEditarLocalidad" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-2xl rounded-2xl border shadow-2xl" style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--border-color);">
            <h3 class="text-lg font-semibold" style="color: var(--text-main);">Editar Localidad</h3>
            <button type="button" class="text-gray-400 hover:text-red-600" onclick="document.getElementById('modalEditarLocalidad').classList.add('hidden'); document.getElementById('modalEditarLocalidad').classList.remove('flex');">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form wire:submit.prevent="update" id="formEditarLocalidad" class="space-y-4 p-5">
            <div id="contenedorAlertaEditar"></div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium" style="color: var(--text-main);">Estado</label>
                    <div class="flex items-center gap-2 rounded-xl border px-3 py-2" style="background-color: var(--input-bg); border-color: var(--border-color);">
                        <span class="text-slate-500"><i class="fas fa-globe"></i></span>
                        <select name="estado_id" wire:model.live="estado_id" id="estado_id_editar" class="w-full border-0 bg-transparent text-sm outline-none" style="color: var(--text-main);" required>
                            <option value="">Seleccione un estado</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}" {{ $estado_id == $estado->id ? 'selected' : '' }}>
                                    {{ $estado->nombre_estado }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium" style="color: var(--text-main);">Municipio</label>
                    <div class="flex items-center gap-2 rounded-xl border px-3 py-2" style="background-color: var(--input-bg); border-color: var(--border-color);">
                        <span class="text-slate-500"><i class="fas fa-city"></i></span>
                        <select name="municipio_id" wire:model.live="municipio_id" id="municipio_id_editar" class="w-full border-0 bg-transparent text-sm outline-none" style="color: var(--text-main);" {{ empty($municipios) ? 'disabled' : '' }} required>
                            <option value="">Seleccione un municipio</option>
                            @foreach ($municipios as $municipio)
                                <option value="{{ $municipio->id }}" {{ $municipio_id == $municipio->id ? 'selected' : '' }}>
                                    {{ $municipio->nombre_municipio }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" style="color: var(--text-main);">Nombre de la Localidad</label>
                <div class="flex items-center gap-2 rounded-xl border px-3 py-2" style="background-color: var(--input-bg); border-color: var(--border-color);">
                    <span class="text-slate-500"><i class="fas fa-hashtag"></i></span>
                    <input type="text" class="w-full border-0 bg-transparent text-sm outline-none placeholder:text-gray-400" style="color: var(--text-main);" id="nombre_localidad_editar" wire:model.defer="nombre_localidad" inputmode="text" maxlength="100" placeholder="Edite el nombre de la localidad" required>
                </div>
                @error('nombre_localidad')
                    <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-xl border px-4 py-2 text-sm font-medium" style="border-color: var(--border-color); color: var(--text-main);" onclick="document.getElementById('modalEditarLocalidad').classList.add('hidden'); document.getElementById('modalEditarLocalidad').classList.remove('flex');">
                    Cancelar
                </button>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>