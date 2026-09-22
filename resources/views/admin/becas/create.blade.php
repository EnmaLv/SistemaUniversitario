<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Crear nueva beca
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Registra una beca y configura sus preguntas, beneficios y tutores.
                    </p>
                </div>
                <a href="{{ route('admin.becas.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Volver
                </a>
            </div>

                <form action="{{ route('admin.becas.store') }}" method="POST"
                    class="space-y-3 rd-prevent-double-submit">
                    @csrf
                    @include('admin.becas._form_fields')

                    <div class="flex justify-end gap-3 border-t pt-5" style="border-color: var(--border-color);">
                        <a href="{{ route('admin.becas.index') }}"
                            class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-save text-xs"></i>
                            Guardar beca
                        </button>
                    </div>
                </form>
        </div>
    </div>
</x-app-layout>
