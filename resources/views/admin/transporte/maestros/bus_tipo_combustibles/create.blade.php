<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] pb-12 pt-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl"
                        style="color: var(--text-main);">
                        Crear tipo de combustible
                    </h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-sm">
                        Registra un tipo de combustible para los vehículos del sistema.
                    </p>
                </div>
                <a href="{{ route('admin.transporte.maestros.bus_tipo_combustibles.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Volver
                </a>
            </div>

            <div class="overflow-hidden rounded-2xl border shadow-sm"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="border-b px-6 py-5" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">
                        Datos del tipo de combustible
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Completa la información necesaria para registrarlo.
                    </p>
                </div>

                <form action="{{ route('admin.transporte.maestros.bus_tipo_combustibles.store') }}" method="POST"
                    class="space-y-6 p-6 rd-prevent-double-submit">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <label for="nombre" class="mb-2 block text-sm font-bold"
                                style="color: var(--text-main);">Nombre</label>
                            <div class="flex items-center rounded-xl border"
                                style="background-color: var(--input-bg); border-color: var(--border-color);">
                                <span class="px-3 text-gray-400"><i class="fas fa-gas-pump"></i></span>
                                <input id="nombre" name="nombre" value="{{ old('nombre') }}"
                                    placeholder="Ej. Gasolina"
                                    class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-red-500/30"
                                    style="color: var(--text-main);" required>
                            </div>
                            @error('nombre')
                                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="descripcion" class="mb-2 block text-sm font-bold"
                                style="color: var(--text-main);">Descripción <span
                                    class="font-normal text-gray-400">(opcional)</span></label>
                            <div class="flex items-center rounded-xl border"
                                style="background-color: var(--input-bg); border-color: var(--border-color);">
                                <span class="px-3 text-gray-400"><i class="fas fa-align-left"></i></span>
                                <input id="descripcion" name="descripcion" value="{{ old('descripcion') }}"
                                    placeholder="Descripción del combustible"
                                    class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-red-500/30"
                                    style="color: var(--text-main);">
                            </div>
                            @error('descripcion')
                                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-5"
                        style="border-color: var(--border-color);">
                        <a href="{{ route('admin.transporte.maestros.bus_tipo_combustibles.index') }}"
                            class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-save text-xs"></i>
                            Guardar tipo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
