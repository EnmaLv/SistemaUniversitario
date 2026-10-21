@props([
    'action',
    'rutaVolver',
    'titulo' => 'Crear producto',
    'categorias',
    'unidades' => [],
    'envases' => [],
    'esMedicamento' => false,
    'modelo' => null,
    'metodo' => 'POST',
])

<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-8">

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="rd-prevent-double-submit">
        @csrf
        @if ($metodo !== 'POST')
            @method($metodo)
        @endif

        <input type="hidden" name="from" value="{{ request('from') }}">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-6">

            {{-- Columna principal (Formulario) --}}
            <div class="lg:col-span-9 flex flex-col gap-6">

                {{-- SECCIÓN 1: Identificación --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    {{-- Código --}}
                    <div class="md:col-span-3">
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Código
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden opacity-60"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-barcode text-sm"></i>
                            </span>
                            <input type="text" value="{{ optional($modelo)->codigo ?? 'Automático' }}" disabled
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('codigo')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Categoría --}}
                    <div class="md:col-span-4">
                        <div class="flex justify-between items-end mb-1.5">
                            <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400">
                                Categoría
                            </label>
                            <a href="{{ route('admin.maestros.categorias.index') }}"
                                class="text-[10px] font-bold transition-colors {{ $esMedicamento ? 'text-sky-600 hover:text-sky-700' : 'text-rose-700 hover:text-rose-800' }}">
                                + Nueva
                            </a>
                        </div>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-tags text-sm"></i>
                            </span>
                            <select id="categoria_id" name="categoria_id"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                <option value="" selected disabled>Seleccione...</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}"
                                        {{ old('categoria_id', optional($modelo)->categoria_id ?? request('categoria_id')) == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('categoria_id')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nombre --}}
                    <div class="md:col-span-5">
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Nombre del producto
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-tag text-sm"></i>
                            </span>
                            <input type="text" name="nombre" placeholder="Ej: Paracetamol 500mg"
                                value="{{ old('nombre', optional($modelo)->nombre) }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                        </div>
                        @error('nombre')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- SECCIÓN 2: Medidas y Presentación --}}
                @php
                    $colClass = $esMedicamento && count($envases) > 0 ? 'md:col-span-4' : 'md:col-span-6';
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">

                    {{-- Unidad de Medida --}}
                    <div class="{{ $colClass }} min-w-0">
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Unidad de Medida
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-balance-scale text-sm"></i>
                            </span>
                            <select name="unidad_id" id="unidad_id"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                <option value="" selected disabled>Seleccione...</option>
                                @foreach ($unidades as $unidad)
                                    <option value="{{ $unidad->id }}" data-abreviatura="{{ $unidad->abreviatura }}"
                                        {{ old('unidad_id', optional($modelo)->unidad_id) == $unidad->id ? 'selected' : '' }}>
                                        {{ $unidad->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('unidad_id')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Peso del contenido --}}
                    <div class="{{ $colClass }} min-w-0">
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5"
                            id="label-peso">
                            Peso / Cantidad
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-weight text-sm"></i>
                            </span>
                            <input type="number" name="peso_contenido" min="0" step="0.01" placeholder="0.00"
                                value="{{ old('peso_contenido', optional($modelo)->peso_contenido) }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                        </div>
                        @error('peso_contenido')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Presentación (Solo Medicamentos) --}}
                    @if ($esMedicamento && count($envases) > 0)
                        <div class="md:col-span-4 min-w-0">
                            <label
                                class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Presentación (Envase)
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span
                                    class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-pills text-sm"></i>
                                </span>
                                <select name="envase_primario_id"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                    <option value="" selected disabled>Seleccione...</option>
                                    @foreach ($envases as $envase)
                                        <option value="{{ $envase->id }}"
                                            {{ old('envase_primario_id', optional($modelo)->presentacion_id) == $envase->id ? 'selected' : '' }}>
                                            {{ $envase->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('envase_primario_id')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                {{-- SECCIÓN 3: Inventario y Precio --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    {{-- Precio --}}
                    <div>
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Precio base (USD)
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r font-bold text-sm"
                                style="border-color: var(--border-color);">
                                $
                            </span>
                            <input type="number" name="costo_usd" min="0" step="0.01" placeholder="0.00"
                                value="{{ old('costo_usd', optional($modelo)->precioProducto) }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                        </div>
                        @error('costo_usd')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Stock Mínimo --}}
                    <div>
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Stock Mínimo
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-arrow-down text-sm"></i>
                            </span>
                            <input type="number" name="stock_minimo" min="0" placeholder="Ej: 10"
                                value="{{ old('stock_minimo', optional($modelo)->stock_minimo ? intval($modelo->stock_minimo) : '') }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                        </div>
                        @error('stock_minimo')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Stock Máximo --}}
                    <div>
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Stock Máximo
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-arrow-up text-sm"></i>
                            </span>
                            <input type="number" name="stock_maximo" min="0" placeholder="Ej: 100"
                                value="{{ old('stock_maximo', optional($modelo)->stock_maximo ? intval($modelo->stock_maximo) : '') }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                        </div>
                        @error('stock_maximo')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- SECCIÓN 4: Descripción --}}
                <div>
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Descripción detallada
                    </label>
                    <textarea name="descripcion" id="descripcion">{{ old('descripcion', optional($modelo)->descripcion) }}</textarea>
                    @error('descripcion')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Columna lateral (Imagen) --}}
            <div class="lg:col-span-3">
                <div class="sticky top-6">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Imagen del producto
                    </label>

                    @php
                        $imagenActual = optional($modelo)->imagen;
                    @endphp

                    <label for="imagen"
                        class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed p-6 text-center cursor-pointer hover:border-sky-400 hover:bg-sky-50/40 dark:hover:bg-sky-950/10 transition-colors"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02); min-height: 250px;">

                        <img id="imgPreview" src="{{ $imagenActual ? asset('storage/' . $imagenActual) : '#' }}"
                            style="{{ $imagenActual ? 'display:block;' : 'display:none;' }}"
                            class="w-full rounded-xl shadow-sm mb-2 object-cover">

                        <div id="imgPlaceholderIcon" style="{{ $imagenActual ? 'display:none;' : 'display:flex;' }}"
                            class="flex-col items-center justify-center gap-2">
                            <div
                                class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-2">
                                <i class="fas fa-cloud-upload-alt text-2xl text-gray-400"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-500 dark:text-gray-400">Subir imagen</span>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">PNG, JPG hasta
                                2MB</span>
                        </div>

                        <em id="fileName"
                            class="text-xs text-sky-600 dark:text-sky-400 font-medium not-italic mt-2 truncate max-w-full"></em>

                        <input type="file" name="imagen" id="imagen" accept="image/*"
                            onchange="previewImage(event)" class="hidden">
                    </label>

                    @error('imagen')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t flex items-center justify-end gap-3"
            style="border-color: var(--border-color);">
            <a href="{{ $rutaVolver }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                style="border-color: var(--border-color); color: var(--text-main);">
                Cancelar
            </a>

            <button type="submit"
                class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all 
                {{ $esMedicamento ? 'bg-sky-500 hover:bg-sky-700' : 'bg-red-800 hover:bg-red-900' }}">
                <i class="fas fa-save text-xs"></i> {{ $modelo ? 'Actualizar' : 'Guardar' }}
            </button>
        </div>
    </form>
</div>

<style>
    .ck.ck-editor {
        width: 100% !important;
    }

    .ck.ck-editor__editable {
        width: 100% !important;
        min-height: 250px;
        box-sizing: border-box;
        border-bottom-left-radius: 0.75rem !important;
        border-bottom-right-radius: 0.75rem !important;
        border-color: var(--border-color) !important;
    }

    .ck.ck-toolbar {
        border-top-left-radius: 0.75rem !important;
        border-top-right-radius: 0.75rem !important;
        border-color: var(--border-color) !important;
    }

    @media (max-width: 768px) {
        .ck.ck-editor__editable {
            min-height: 200px;
            padding: 10px;
        }
    }
</style>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imgPreview');
                const icon = document.getElementById('imgPlaceholderIcon');
                img.style.display = 'block';
                img.src = e.target.result;
                if (icon) icon.style.display = 'none';
            };
            reader.readAsDataURL(file);
            const fileName = document.getElementById('fileName');
            if (fileName) {
                fileName.textContent = file.name;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const unidadSelect = document.getElementById('unidad_id');
        if (unidadSelect) {
            unidadSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const abrev = selected.getAttribute('data-abreviatura');
                const label = document.getElementById('label-peso');

                if (label) {
                    if (abrev) {
                        label.textContent = `Peso / Cantidad (en ${abrev})`;
                    } else {
                        label.textContent = 'Peso / Cantidad';
                    }
                }
            });
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let descripcionEditor;

        // Verificamos que el textarea exista antes de inicializar
        const descripcionEl = document.querySelector('#descripcion');

        if (descripcionEl) {
            ClassicEditor.create(descripcionEl, {
                toolbar: {
                    items: [
                        'heading', '|', 'bold', 'italic', 'underline', 'strikethrough', 'subscript',
                        '|',
                        'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|',
                        'undo', 'redo'
                    ],
                    shouldNotGroupWhenFull: true
                },
                language: 'es'
            }).then(editor => {
                descripcionEditor = editor;
                const editorEl = editor.ui.view.element;
                editorEl.style.width = '100%';
                editorEl.querySelector('.ck-editor__editable').style.width = '100%';
            }).catch(error => {
                console.error('Ocurrió un error al inicializar CKEditor:', error);
            });
        }
    });
</script>
