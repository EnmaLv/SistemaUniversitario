<div class="grid grid-cols-1 gap-6">
    <div>
        <label for="nombre_beneficio" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">
            Nombre del beneficio
        </label>
        <div class="flex items-center rounded-xl border"
            style="background-color: var(--input-bg); border-color: var(--border-color);">
            <span class="px-3 text-gray-400"><i class="fas fa-gift"></i></span>
            <input id="nombre_beneficio" type="text" name="nombre_beneficio"
                value="{{ old('nombre_beneficio', $beneficio->nombre_beneficio ?? '') }}"
                placeholder="Ej. Ayuda económica"
                class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none transition focus:ring-2 focus:ring-red-500/30"
                style="color: var(--text-main);" required>
        </div>
        @error('nombre_beneficio')
            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="descripcion" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">
            Descripción <span class="font-normal text-gray-400">(opcional)</span>
        </label>
        <textarea id="descripcion" name="descripcion" rows="4" placeholder="Descripción del beneficio"
            class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/30"
            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main); resize: vertical;">{{ old('descripcion', $beneficio->descripcion ?? '') }}</textarea>
        @error('descripcion')
            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <span class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Estado</span>
        <label class="inline-flex cursor-pointer items-center gap-3">
            <input type="checkbox" id="status" name="status" value="1" class="peer sr-only"
                {{ old('status', $beneficio->status ?? true) ? 'checked' : '' }}>
            <span class="relative h-6 w-10 rounded-full bg-gray-300 transition peer-checked:bg-red-700 dark:bg-gray-700">
                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-4"></span>
            </span>
            <span class="text-sm font-semibold" style="color: var(--text-muted);">Activo</span>
        </label>
    </div>
</div>
