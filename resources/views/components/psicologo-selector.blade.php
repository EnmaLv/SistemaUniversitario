@if (auth()->user()->esSuperAdmin() && isset($psicologos) && $psicologos->count() > 0)
    <div
        class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-3xl border border-slate-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest">Vista de
                    Administrador</p>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Gestionando agenda de psicólogo</h3>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <label for="admin-psicologo-selector"
                class="text-xs font-bold text-slate-500 dark:text-gray-400 uppercase">Seleccionar:</label>
            <select id="admin-psicologo-selector"
                onchange="window.location.href = '{{ url()->current() }}?psicologo_id=' + this.value"
                class="min-w-[200px] px-4 py-2 bg-slate-50 dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-2xl text-sm font-semibold text-slate-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all appearance-none cursor-pointer">
                @foreach ($psicologos as $psi)
                    <option value="{{ $psi->id }}"
                        {{ isset($psicologoId) && $psicologoId == $psi->id ? 'selected' : '' }}>
                        {{ $psi->name ?? $psi->nombres . ' ' . $psi->apellidos }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
@endif
