<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] pt-6 pb-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">
                        Nueva sede o anexo
                    </h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-sm">
                        Registra una sede o anexo para la institución.
                    </p>
                </div>
                <a href="{{ route('admin.maestros.sedes.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Volver
                </a>
            </div>

            <div class="overflow-hidden rounded-2xl border shadow-sm"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="border-b px-6 py-5" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">
                        Datos de la sede
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Completa la información requerida.
                    </p>
                </div>

                <form action="{{ route('admin.maestros.sedes.store') }}" method="POST" class="space-y-6 p-6">
                    @csrf
                    <input type="hidden" name="from" value="{{ request('from') }}">

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="nombre" class="mb-2 block text-sm font-bold"
                                style="color: var(--text-main);">
                                Nombre de la sede
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                    <i class="fas fa-building"></i>
                                </span>
                                <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}"
                                    required autofocus maxlength="255" placeholder="Ej. Sede principal"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                            </div>
                            @error('nombre')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="telefono" class="mb-2 block text-sm font-bold"
                                style="color: var(--text-main);">
                                Teléfono
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input id="telefono" name="telefono" type="text" value="{{ old('telefono') }}"
                                    maxlength="30" placeholder="(123) 456-7890"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                            </div>
                            @error('telefono')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="mb-2 block text-sm font-bold"
                            style="color: var(--text-main);">
                            Dirección
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <input id="direccion" name="direccion" type="text" value="{{ old('direccion') }}"
                                maxlength="255" placeholder="Ingrese la dirección de la sede"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                        </div>
                        @error('direccion')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-5" style="border-color: var(--border-color);">
                        <a href="{{ route('admin.maestros.sedes.index') }}"
                            class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-save text-xs"></i>
                            Guardar sede
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
