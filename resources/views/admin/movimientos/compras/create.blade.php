<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Crear nueva requisición
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Registra una solicitud de compra para el módulo de Administración.
                    </p>
                </div>
                <a href="{{ route('admin.movimientos.compras.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Volver
                </a>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">
                <div class="border-b px-6 py-5" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">
                        Datos de la requisición
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Completa la información necesaria para crear la requisición.
                    </p>
                </div>

                <form action="{{ route('admin.movimientos.compras.store') }}" method="POST"
                    class="space-y-6 p-6 rd-prevent-double-submit">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div>
                            <label for="proveedor_id" class="mb-2 block text-sm font-bold"
                                style="color: var(--text-main);">
                                Proveedor
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <select name="proveedor_id" id="proveedor_id" required
                                    class="w-full rounded-xl border bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:bg-gray-900/50 dark:text-gray-100"
                                    style="border-color: var(--border-color);">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach ($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}"
                                            {{ old('proveedor_id', request('proveedor_id')) == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('proveedor_id')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                ¿No encuentras el proveedor?
                                <a href="{{ route('admin.maestros.proveedores.create', ['from' => url()->current()]) }}"
                                    class="font-bold text-red-600 transition hover:text-red-800">
                                    Créalo aquí
                                </a>
                            </p>
                        </div>

                        <div>
                            <label for="fecha" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">
                                Fecha de la requisición
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <input type="datetime-local" id="fecha" name="fecha"
                                    value="{{ \Carbon\Carbon::now('America/Caracas')->format('Y-m-d\TH:i') }}"
                                    readonly
                                    class="w-full rounded-xl border bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:bg-gray-900/50 dark:text-gray-100"
                                    style="border-color: var(--border-color);">
                            </div>
                            @error('fecha')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="observaciones" class="mb-2 block text-sm font-bold"
                                style="color: var(--text-main);">
                                Observaciones <span class="font-normal text-gray-400">(opcional)</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                    <i class="fas fa-sticky-note"></i>
                                </span>
                                <input type="text" id="observaciones" name="observaciones"
                                    value="{{ old('observaciones') }}" placeholder="Ingrese observaciones"
                                    class="w-full rounded-xl border bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:bg-gray-900/50 dark:text-gray-100"
                                    style="border-color: var(--border-color);">
                            </div>
                            @error('observaciones')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-5" style="border-color: var(--border-color);">
                        <a href="{{ route('admin.movimientos.compras.index') }}"
                            class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                            Cancelar
                        </a>
                        <button type="submit" @disabled($proveedores->isEmpty())
                            class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                            <i class="fas fa-save text-xs"></i>
                            Crear requisición
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
