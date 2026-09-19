<div id="viewBeneficiarioModal-{{ $beneficiario->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
    <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
            <h5 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Ver Beneficiario</h5>
            <button type="button" class="text-slate-500 hover:text-slate-700 dark:text-slate-300" onclick="document.getElementById('viewBeneficiarioModal-{{ $beneficiario->id }}').classList.add('hidden'); document.getElementById('viewBeneficiarioModal-{{ $beneficiario->id }}').classList.remove('flex');">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="space-y-5 p-5">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Estudiante</label>
                    <p class="text-sm text-slate-800 dark:text-slate-100"><strong>{{ $beneficiario->persona?->nombre_persona }} {{ $beneficiario->persona?->apellido_persona }} - {{ $beneficiario->persona?->cedula_persona }}</strong></p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Área</label>
                    <p class="text-sm text-slate-800 dark:text-slate-100">{{ $beneficiario->area }}</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Horario</label>
                    <p class="text-sm text-slate-800 dark:text-slate-100">{{ $beneficiario->horario }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tutor</label>
                    <p class="text-sm text-slate-800 dark:text-slate-100">{{ $beneficiario->tutor?->nombre_persona }} {{ $beneficiario->tutor?->apellido_persona }}</p>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Observaciones</label>
                <p class="text-sm text-slate-800 dark:text-slate-100">{{ $beneficiario->observaciones ?: '—' }}</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                    <p class="text-sm text-slate-800 dark:text-slate-100">{{ ucfirst($beneficiario->estado) }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Motivo de suspensión</label>
                    <p class="text-sm text-slate-800 dark:text-slate-100">{{ $beneficiario->motivo_suspension ?: '—' }}</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end border-t border-slate-200 px-5 py-4 dark:border-slate-700">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" onclick="document.getElementById('viewBeneficiarioModal-{{ $beneficiario->id }}').classList.add('hidden'); document.getElementById('viewBeneficiarioModal-{{ $beneficiario->id }}').classList.remove('flex');">Cerrar</button>
        </div>
    </div>
</div>
