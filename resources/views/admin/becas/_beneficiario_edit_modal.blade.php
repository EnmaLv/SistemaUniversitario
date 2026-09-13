<div id="editBeneficiarioModal-{{ $beneficiario->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
    <div class="w-full max-w-4xl rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <form action="{{ route('admin.becas.beneficiarios.update', [$beca, $beneficiario]) }}" method="POST" class="rd-prevent-double-submit">
            @csrf
            @method('PUT')
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                <h5 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Editar Beneficiario</h5>
                <button type="button" class="text-slate-500 hover:text-slate-700 dark:text-slate-300" onclick="document.getElementById('editBeneficiarioModal-{{ $beneficiario->id }}').classList.add('hidden'); document.getElementById('editBeneficiarioModal-{{ $beneficiario->id }}').classList.remove('flex');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="space-y-4 p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Estudiante</label>
                        <p class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><strong>{{ $beneficiario->persona?->nombre_persona }} {{ $beneficiario->persona?->apellido_persona }} - {{ $beneficiario->persona?->cedula_persona }}</strong></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Área</label>
                        <input type="text" name="area" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" value="{{ $beneficiario->area }}">
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Horario</label>
                        <input type="text" name="horario" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" value="{{ $beneficiario->horario }}" placeholder="Ej: Lunes a Viernes 08:00-12:00...">
                        <small class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Formato sugerido: de lunes a viernes con horas.</small>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tutor</label>
                        <select name="tutor_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <option value="">Seleccione tutor</option>
                            @foreach($tutores as $t)
                                <option value="{{ $t->id_persona }}" {{ $beneficiario->tutor_id == $t->id_persona ? 'selected' : '' }}>{{ trim($t->nombre_persona . ' ' . $t->apellido_persona) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Observaciones</label>
                    <textarea name="observaciones" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $beneficiario->observaciones }}</textarea>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                        <select name="estado" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <option value="activo" {{ $beneficiario->estado === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="suspendido" {{ $beneficiario->estado === 'suspendido' ? 'selected' : '' }}>Suspendido</option>
                            <option value="finalizado" {{ $beneficiario->estado === 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                        </select>
                    </div>
                </div>

                <div class="{{ $beneficiario->estado === 'suspendido' ? '' : 'hidden' }}">
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Razón de suspensión</label>
                    <textarea name="motivo_suspension" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $beneficiario->motivo_suspension }}</textarea>
                </div>

                <input type="hidden" name="activo" value="{{ $beneficiario->activo ? 1 : 0 }}">
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-200 px-5 py-4 dark:border-slate-700">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" onclick="document.getElementById('editBeneficiarioModal-{{ $beneficiario->id }}').classList.add('hidden'); document.getElementById('editBeneficiarioModal-{{ $beneficiario->id }}').classList.remove('flex');">Cancelar</button>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">Guardar</button>
            </div>
        </form>
    </div>
</div>
