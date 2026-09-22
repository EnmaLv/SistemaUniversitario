<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] pb-12 pt-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl"
                        style="color: var(--text-main);">
                        Editar beneficio
                    </h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-sm">
                        Actualiza la información del beneficio seleccionado.
                    </p>
                </div>
                <a href="{{ route('admin.becas.beneficios.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Volver
                </a>
            </div>

            <div class="overflow-hidden rounded-2xl border shadow-sm"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="border-b px-6 py-5" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">
                        Datos del beneficio
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Modifica la información necesaria del beneficio.
                    </p>
                </div>

                <form action="{{ route('admin.becas.beneficios.update', $beneficio) }}" method="POST"
                    class="space-y-6 p-6 rd-prevent-double-submit">
                    @csrf
                    @method('PUT')
                    @include('admin.becas.beneficios._form')

                    <div class="flex justify-end gap-3 border-t pt-5"
                        style="border-color: var(--border-color);">
                        <a href="{{ route('admin.becas.beneficios.index') }}"
                            class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-save text-xs"></i>
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
