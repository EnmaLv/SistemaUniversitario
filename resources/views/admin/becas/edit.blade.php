<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] pb-12 pt-6">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl"
                        style="color: var(--text-main);">
                        Editar beca
                    </h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-sm">
                        Actualiza la beca <span class="font-bold">{{ $beca->codigo }}</span> y configura sus
                        preguntas, beneficios y tutores.
                    </p>
                </div>
                @if (request('from') == 'show')
                    <a href="{{ route('admin.becas.show', $beca) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                @else
                    <a href="{{ route('admin.becas.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                @endif
                        <i class="fas fa-arrow-left text-xs"></i>
                        Volver
                    </a>
            </div>

            <form action="{{ route('admin.becas.update', $beca) }}" method="POST"
                class="space-y-3 rd-prevent-double-submit">
                @if (request('from') == 'show')
                    <input type="hidden" name="from" value="show">
                @endif
                @csrf
                @method('PUT')
                @include('admin.becas._form_fields')

                <div class="flex justify-end gap-3 border-t pt-5"
                    style="border-color: var(--border-color);">
                    @if (request('from') == 'show')
                        <a href="{{ route('admin.becas.show', $beca) }}"
                            class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    @else
                        <a href="{{ route('admin.becas.index') }}"
                            class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    @endif
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
</x-app-layout>

@if (session('beneficios_alerta'))
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Beneficios modificados',
                    text: 'La configuración de beneficios de esta beca fue actualizada.',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endpush
@endif
