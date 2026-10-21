<x-app-layout> 
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Listado de Prioridades
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Niveles de gravedad en la agenda de citas.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.psicologia.maestros.prioridades.create') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass ?? 'bg-sky-600 hover:bg-sky-700' }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Nueva Prioridad</span>
                    </a>
                </div>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-4 rounded-2xl border shadow-sm mb-6 flex items-center justify-between gap-4">

                <form action="{{ route('admin.psicologia.maestros.prioridades.index') }}" method="GET"
                    class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar prioridad..."
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 {{ $focusRingClass ?? 'focus:ring-sky-500' }} transition-all">
                </form>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[11px] font-black uppercase tracking-wider text-gray-400">
                                <th class="px-6 py-4 text-center">
                                    Nombre
                                </th>
                                <th class="px-6 py-4 text-center">
                                    Nivel de Gravedad
                                </th>
                                <th class="px-6 py-4 text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse($prioridades as $prioridad)
                                <tr
                                    class="disease-row hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-2.5 h-2.5 rounded-full {{ $prioridad->nombre_pat === 'crítica' ? 'bg-rose-500' : ($prioridad->nombre_pat === 'alta' ? 'bg-amber-500' : ($prioridad->nombre_pat === 'media' ? 'bg-sky-500' : ($prioridad->nombre_pat === 'baja' ? 'bg-emerald-500' : 'bg-indigo-500'))) }}">
                                            </div>
                                            <span class="font-bold uppercase tracking-wider text-xs"
                                                style="color: var(--text-main);">
                                                {{ $prioridad->nombre_pat }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-[10px] font-black rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                            Nivel {{ $prioridad->nivel_gravedad_pat }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-end gap-2">
                                            @if ($prioridad->psicologo_use_id === null)
                                                <span
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed"
                                                    title="Prioridad predefinida del sistema (no modificable)">
                                                    <i class="fas fa-lock text-xs"></i>
                                                </span>
                                            @else
                                                <form id="form-delete-{{ $prioridad->id_pat }}"
                                                    action="{{ route('admin.psicologia.maestros.prioridades.destroy', $prioridad->id_pat) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        onclick="confirmDeletePrioridad({{ $prioridad->id_pat }}, '{{ $prioridad->nivel_gravedad_pat }}', {{ $prioridad->uso_count }})"
                                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors"
                                                        title="Eliminar Prioridad">
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"
                                        class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                        No hay prioridades registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($prioridades->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                        {{ $prioridades->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

<script>
    function confirmDeletePrioridad(id, nivel, usoCount) {
        if (usoCount > 0) {
            window.AppModal.show(
                'Acción no permitida',
                'No puedes eliminar esta prioridad porque está siendo utilizada por uno o más pacientes.', {
                    type: 'alert',
                    btnText: 'Entendido'
                }
            );
        } else {
            window.AppModal.show(
                '¿Eliminar Prioridad?',
                '¿Seguro que deseas eliminar esta prioridad? El nivel ' + nivel + ' quedará libre.', {
                    type: 'confirm',
                    btnText: 'Sí, eliminar',
                    intent: 'danger'
                }
            ).then(result => {
                if (result) document.getElementById('form-delete-' + id).submit();
            });
        }
    }
</script>
