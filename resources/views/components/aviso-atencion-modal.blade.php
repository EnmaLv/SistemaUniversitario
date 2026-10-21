@if (request()->has('avisoAtencionCita'))
    @php
        $citaAviso = \App\Models\salud\Cita::where('psicologo_use_id', auth()->id())->find(request('avisoAtencionCita'));
    @endphp
    @if ($citaAviso && $citaAviso->paciente)
        <div id="avisoAtencionModal"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 dark:bg-black/70 px-3 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-xl rounded-3xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 shadow-2xl overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-amber-500"></div>

                <div class="p-6 lg:p-8">
                    <button onclick="document.getElementById('avisoAtencionModal').style.display='none'"
                        class="absolute right-5 top-5 h-9 w-9 flex items-center justify-center rounded-2xl bg-gray-100 hover:bg-rose-50 dark:bg-gray-700/60 dark:hover:bg-rose-950/40 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 border border-gray-200 dark:border-gray-700/60 transition-colors text-lg font-black leading-none">✕</button>

                    <div class="text-center mb-6 mt-2">
                        <div
                            class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 mb-4 shadow-sm">
                            <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Aviso de Atención</h3>
                    </div>

                    <div class="text-gray-600 dark:text-gray-300 space-y-3 mb-8 text-center px-2">
                        <p class="text-base font-medium">
                            Has <strong class="font-black text-gray-900 dark:text-white">rechazado o cancelado</strong> múltiples citas relacionadas al paciente <strong
                                class="font-black text-sky-600 dark:text-sky-400">{{ $citaAviso->paciente->nombre_completo }}</strong>.
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                            Este es un recordatorio opcional del sistema. Si consideras que el paciente merece
                            preferencia por la recurrencia de cancelaciones de tu parte, puedes elevar su prioridad.
                        </p>
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-4 mt-4 border border-gray-200 dark:border-gray-700/60 shadow-sm">
                            <p class="font-extrabold text-xs uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                ¿Deseas cambiar la prioridad de sus solicitudes pendientes a "Alta"?
                            </p>
                        </div>
                    </div>

                    <div class="flex sm:flex-row flex-col justify-center gap-3 mt-8">
                        <form method="POST" action="{{ route('admin.psicologia.maestros.citas.update.prioridad', $citaAviso->id_cps) }}"
                            class="w-full sm:w-1/2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="prioridad" value="alta">
                            <button type="submit"
                                class="w-full px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs uppercase tracking-wider rounded-2xl transition-all shadow-sm active:scale-95">
                                Sí, cambiar a Alta
                            </button>
                        </form>

                        <button type="button"
                            onclick="document.getElementById('avisoAtencionModal').style.display='none'"
                            class="w-full sm:w-1/2 px-4 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700/60 font-black text-xs uppercase tracking-wider rounded-2xl transition-all shadow-sm active:scale-95">
                            No, ignorar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif