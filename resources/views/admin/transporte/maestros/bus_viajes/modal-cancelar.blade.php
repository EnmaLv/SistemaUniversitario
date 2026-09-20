<div id="modalCancelarViaje"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="cerrarModalCancelar()"></div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="relative w-full max-w-md rounded-2xl border shadow-xl overflow-hidden">

        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--border-color);">
            <h3 class="flex items-center gap-2 text-base font-extrabold tracking-tight" style="color: var(--text-main);">
                <i class="fas fa-ban text-red-800"></i>
                <span>Cancelar Viaje</span>
            </h3>
            <button type="button" onclick="cerrarModalCancelar()"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="formCancelarViaje" method="POST" action="" class="rd-prevent-double-submit">
            @csrf
            <div class="px-5 py-5 flex flex-col gap-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    Por favor, especifique el motivo por el cual se cancela este viaje. Esta información quedará registrada en el sistema.
                </p>

                <div>
                    <label for="motivo_cancelacion" class="block text-[11px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Motivo de la Cancelación <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-start rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-red-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 pt-3 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-comment-alt text-sm"></i>
                        </span>
                        <textarea id="motivo_cancelacion" name="motivo_cancelacion" rows="3" required
                            placeholder="Escriba aquí la razón detallada..."
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main); min-height: 110px; max-height: 180px; resize: vertical; overflow-y: auto;"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 px-5 py-4 border-t" style="border-color: var(--border-color);">
                <button type="button" onclick="cerrarModalCancelar()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    Cancelar
                </button>
                <button type="submit"
                    class="rd-submit-btn inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                    <i class="fas fa-check text-xs"></i>
                    <span>Confirmar Cancelación</span>
                </button>
            </div>
        </form>
    </div>
</div>
