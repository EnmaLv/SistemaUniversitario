<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Crear Nueva Requisición
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ url('admin/movimientos/compras') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver
                </a>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-8">
                <form action="{{ route('admin.movimientos.compras.store') }}" method="POST"
                    class="rd-prevent-double-submit">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                        {{-- Proveedor --}}
                        <div class="md:col-span-3">
                            <div class="flex justify-between items-end mb-1.5">
                                <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400">
                                    Proveedor
                                </label>
                                <a href="{{ route('admin.maestros.proveedores.create', ['from' => url()->current()]) }}"
                                    class="text-[10px] font-bold text-rose-700 hover:text-rose-800 transition-colors">
                                    + Nuevo
                                </a>
                            </div>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </span>
                                <select name="proveedor_id" id="proveedor_id"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                    <option value="" selected disabled>Seleccione proveedor</option>
                                    @foreach ($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}"
                                            {{ old('proveedor_id', request('proveedor_id')) == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('proveedor_id')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Módulo / Área --}}
                        <div class="md:col-span-3">
                            <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Módulo / Área
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-cubes text-sm"></i>
                                </span>
                                <select name="modulo_id" id="modulo_id"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                    <option value="" selected disabled>Seleccione módulo</option>
                                    @foreach ($modulos as $modulo)
                                        <option value="{{ $modulo->id }}"
                                            {{ old('modulo_id') == $modulo->id ? 'selected' : '' }}>
                                            {{ $modulo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('modulo_id')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fecha de la Requisición --}}
                        <div class="md:col-span-3">
                            <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Fecha Requisición
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden opacity-70"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-calendar-alt text-sm"></i>
                                </span>
                                <input type="datetime-local" id="fecha" name="fecha"
                                    value="{{ \Carbon\Carbon::now('America/Caracas')->format('Y-m-d\TH:i') }}" readonly
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none cursor-not-allowed">
                            </div>
                            @error('fecha')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Observaciones --}}
                        <div class="md:col-span-3">
                            <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Observaciones
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-sticky-note text-sm"></i>
                                </span>
                                <input type="text" id="observaciones" name="observaciones" placeholder="Ingrese observaciones"
                                    value="{{ old('observaciones') }}"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                            </div>
                            @error('observaciones')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Botones de Acción --}}
                    <div class="mt-8 pt-6 border-t flex items-center justify-end gap-3"
                        style="border-color: var(--border-color);">
                        <a href="{{ url('admin/movimientos/compras') }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            Cancelar
                        </a>

                        <button type="submit" @disabled($proveedores->isEmpty())
                            class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-plus text-xs"></i> Crear Requisición
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>