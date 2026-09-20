@php
    $modelo = $modelo ?? null;
@endphp

<div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.alert')

        <div class="mb-6">
            <a href="{{ route('admin.becas.beneficios.index') }}"
                class="inline-flex items-center text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-widest hover:text-red-800 transition-colors mb-2">
                <i class="fas fa-arrow-left mr-2"></i> Volver a beneficios
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                {{ $titulo }}
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ $subtitulo }}
            </p>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm p-6 sm:p-8 mb-8">

            <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <div class="grid grid-cols-1 gap-6">

                    {{-- Nombre --}}
                    <div>
                        <label for="nombre_beneficio" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">
                            Nombre del beneficio
                        </label>
                        <div class="flex items-center rounded-xl border transition focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/30"
                            style="background-color: var(--input-bg); border-color: var(--border-color);">
                            <span class="px-3 text-gray-400"><i class="fas fa-gift"></i></span>
                            <input id="nombre_beneficio" type="text" name="nombre_beneficio"
                                value="{{ old('nombre_beneficio', $modelo?->nombre_beneficio ?? '') }}"
                                placeholder="Ej. Ayuda económica"
                                class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none"
                                style="color: var(--text-main);" required>
                        </div>
                        @error('nombre_beneficio')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label for="descripcion" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">
                            Descripción <span class="font-normal text-gray-400">(opcional)</span>
                        </label>
                        <textarea id="descripcion" name="descripcion" rows="4"
                            placeholder="Descripción del beneficio"
                            class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/30"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main); resize: vertical;">{{ old('descripcion', $modelo?->descripcion ?? '') }}</textarea>
                        @error('descripcion')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Estado --}}
                    <div>
                        <span class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Estado</span>
                        <label class="inline-flex cursor-pointer items-center gap-3">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" id="status" name="status" value="1" class="peer sr-only"
                                {{ old('status', $modelo?->status ?? true) ? 'checked' : '' }}>
                            <span class="relative h-6 w-10 rounded-full bg-gray-300 transition peer-checked:bg-red-700 dark:bg-gray-700">
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-4"></span>
                            </span>
                            <span class="text-sm font-semibold" style="color: var(--text-main);">
                                Activo
                            </span>
                        </label>
                        @error('status')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Metadata (solo en edit) --}}
                    @if ($modelo)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t" style="border-color: var(--border-color);">

                            <div>
                                <span class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    Slug
                                </span>
                                <div class="flex items-center rounded-xl border opacity-60"
                                    style="background-color: var(--input-bg); border-color: var(--border-color);">
                                    <span class="px-3 text-gray-400"><i class="fas fa-link text-xs"></i></span>
                                    <input type="text" value="{{ $modelo->slug }}" disabled
                                        class="w-full rounded-xl border-0 bg-transparent px-3 py-2.5 text-xs font-mono outline-none"
                                        style="color: var(--text-main);">
                                </div>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    Generado automáticamente
                                </p>
                            </div>

                            <div>
                                <span class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    Cupones disponibles
                                </span>
                                <div class="flex items-center rounded-xl border opacity-60"
                                    style="background-color: var(--input-bg); border-color: var(--border-color);">
                                    <span class="px-3 text-gray-400"><i class="fas fa-ticket text-xs"></i></span>
                                    <input type="text" value="{{ $modelo->cupones_disponibles }}" disabled
                                        class="w-full rounded-xl border-0 bg-transparent px-3 py-2.5 text-xs font-mono outline-none"
                                        style="color: var(--text-main);">
                                </div>
                            </div>

                            <div>
                                <span class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    Cupones ocupados
                                </span>
                                <div class="flex items-center rounded-xl border opacity-60"
                                    style="background-color: var(--input-bg); border-color: var(--border-color);">
                                    <span class="px-3 text-gray-400"><i class="fas fa-ticket-simple text-xs"></i></span>
                                    <input type="text" value="{{ $modelo->cupones_ocupados }}" disabled
                                        class="w-full rounded-xl border-0 bg-transparent px-3 py-2.5 text-xs font-mono outline-none"
                                        style="color: var(--text-main);">
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Acciones --}}
                    <div class="mt-4 pt-6 border-t flex flex-col sm:flex-row items-center justify-end gap-3"
                        style="border-color: var(--border-color);">
                        <a href="{{ route('admin.becas.beneficios.index') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="w-full sm:w-auto rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                            <i class="fas fa-save text-xs"></i> {{ $modelo ? 'Actualizar' : 'Guardar' }} beneficio
                        </button>
                    </div>

                </div>
            </form>
        </div>

    </div>
</div>