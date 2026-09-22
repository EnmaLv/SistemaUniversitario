@php
    $modelo = $modelo ?? null;
    $opcionesInit = old(
        'opciones',
        $modelo?->opciones->map(fn ($o) => ['etiqueta' => $o->etiqueta, 'valor' => $o->valor])->toArray() ?? []
    );

    $regexPresets = [
        ''                 => ['label' => '— Sin restricción —',                          'pattern' => ''],
        'solo_enteros'     => ['label' => 'Solo números enteros (0, 1, 25…)',             'pattern' => '/^\d+$/'],
        'decimal_2'        => ['label' => 'Decimales con hasta 2 decimales (1.50)',       'pattern' => '/^\d+(\.\d{1,2})?$/'],
        'rango_1_7'        => ['label' => 'Número del 1 al 7 (días de la semana)',        'pattern' => '/^[1-7]$/'],
        'rango_0_23'       => ['label' => 'Horas del día (0 a 23)',                       'pattern' => '/^(?:[0-9]|1[0-9]|2[0-3])$/'],
        'rango_0_59'       => ['label' => 'Minutos (0 a 59)',                             'pattern' => '/^(?:[0-5]?[0-9])$/'],
        'solo_letras'      => ['label' => 'Solo letras y espacios',                       'pattern' => '/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
        'alfanumerico'     => ['label' => 'Letras y números (sin espacios)',              'pattern' => '/^[A-Za-z0-9]+$/'],
        'alfanumerico_esp' => ['label' => 'Letras, números y espacios',                   'pattern' => '/^[A-Za-z0-9\s]+$/'],
        'cedula_ve'        => ['label' => 'Cédula venezolana (V-12345678)',               'pattern' => '/^[VEJvej]-?\d{6,9}$/'],
        'telefono'         => ['label' => 'Teléfono (7 a 15 dígitos)',                    'pattern' => '/^[0-9+\-\s()]{7,15}$/'],
        'email'            => ['label' => 'Correo electrónico (nombre@dominio.com)',      'pattern' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/'],
        'fecha_iso'        => ['label' => 'Fecha en formato YYYY-MM-DD',                  'pattern' => '/^\d{4}-\d{2}-\d{2}$/'],
    ];

    $regexActual = old('regex', $modelo?->regex ?? '');

    // Detecta si el regex del modelo coincide con algún preset
    $presetSeleccionado = '';
    foreach ($regexPresets as $key => $preset) {
        if ($key === '') continue;
        if ($preset['pattern'] === $regexActual) { $presetSeleccionado = $key; break; }
    }
    $esCustom = $regexActual !== '' && $presetSeleccionado === '';
@endphp

<div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.alert')

        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                {{ $titulo }}
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ $subtitulo }}
            </p>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-8">

            <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                {{-- Beneficio / Código / Orden --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">

                    <div class="md:col-span-6">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Beneficio
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-award text-sm"></i>
                            </span>
                            <select name="id_be_beneficio"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                <option value="" disabled {{ old('id_be_beneficio', $modelo?->id_be_beneficio) ? '' : 'selected' }}>
                                    Seleccione un beneficio...
                                </option>
                                @foreach ($beneficios as $ben)
                                    <option value="{{ $ben->id }}"
                                        @selected(old('id_be_beneficio', $modelo?->id_be_beneficio) == $ben->id)>
                                        {{ $ben->nombre_beneficio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('id_be_beneficio')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Código interno
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-key text-sm"></i>
                            </span>
                            <input type="text" name="codigo"
                                value="{{ old('codigo', $modelo?->codigo) }}"
                                placeholder="nota_promedio"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Solo minúsculas, números y _
                        </p>
                        @error('codigo')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Orden
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-sort-numeric-down text-sm"></i>
                            </span>
                            <input type="number" name="orden" min="0"
                                value="{{ old('orden', $modelo?->orden ?? 0) }}"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('orden')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Etiqueta / Placeholder --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">

                    <div class="md:col-span-7">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Pregunta visible al estudiante
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-question text-sm"></i>
                            </span>
                            <input type="text" name="etiqueta"
                                value="{{ old('etiqueta', $modelo?->etiqueta) }}"
                                placeholder="¿Cuál es su índice académico?"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('etiqueta')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-5">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Texto de ayuda (placeholder)
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-info-circle text-sm"></i>
                            </span>
                            <input type="text" name="placeholder"
                                value="{{ old('placeholder', $modelo?->placeholder) }}"
                                placeholder="Ej: Entre 0 y 20"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('placeholder')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tipo / Obligatoria / Estado --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">

                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Tipo de campo
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-list text-sm"></i>
                            </span>
                            <select name="tipo" id="tipo"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                @foreach ($tipos as $key => $label)
                                    <option value="{{ $key }}"
                                        @selected(old('tipo', $modelo?->tipo ?? 'text') === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('tipo')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            ¿Es obligatoria?
                        </label>
                        <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl border"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                El estudiante debe responderla
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="obligatoria" value="0">
                                <input type="checkbox" name="obligatoria" value="1" class="sr-only peer"
                                    {{ old('obligatoria', $modelo?->obligatoria ?? true) ? 'checked' : '' }}>
                                <div class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-sky-600 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                        </div>
                        @error('obligatoria')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Estado
                        </label>
                        <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl border"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Pregunta activa
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1" class="sr-only peer"
                                    {{ old('activo', $modelo?->activo ?? true) ? 'checked' : '' }}>
                                <div class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-600 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                        </div>
                        @error('activo')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Reglas numéricas --}}
                <div id="reglasNumericas" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 hidden">
                    <div>
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Valor mínimo permitido
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-arrow-down text-sm"></i>
                            </span>
                            <input type="number" name="valor_min" step="0.01"
                                value="{{ old('valor_min', $modelo?->valor_min) }}"
                                placeholder="Ej: 0"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('valor_min')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Valor máximo permitido
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-arrow-up text-sm"></i>
                            </span>
                            <input type="number" name="valor_max" step="0.01"
                                value="{{ old('valor_max', $modelo?->valor_max) }}"
                                placeholder="Ej: 20"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('valor_max')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Reglas texto --}}
                <div id="reglasTexto" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 hidden">
                    <div>
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Largo mínimo (caracteres)
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-text-width text-sm"></i>
                            </span>
                            <input type="number" name="min_length" min="0"
                                value="{{ old('min_length', $modelo?->min_length) }}"
                                placeholder="Ej: 3"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('min_length')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                            Largo máximo (caracteres)
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-text-width text-sm"></i>
                            </span>
                            <input type="number" name="max_length" min="0"
                                value="{{ old('max_length', $modelo?->max_length) }}"
                                placeholder="Ej: 255"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('max_length')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Opciones --}}
                <div id="seccionOpciones" class="mb-6 hidden">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400">
                                Opciones de respuesta
                            </label>
                            <p class="text-[11px] font-medium text-gray-400 mt-0.5">
                                Etiqueta es lo que ve el estudiante · Valor es lo que se guarda en base de datos
                            </p>
                        </div>
                        <button type="button" onclick="addOption()"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md active:scale-95 transition-all">
                            <i class="fas fa-plus text-[10px]"></i> Agregar opción
                        </button>
                    </div>

                    <div id="opcionesContainer"
                        class="rounded-2xl border p-3"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                        <p id="sinOpciones" class="text-center text-xs font-bold uppercase tracking-wider text-gray-400 py-6">
                            Sin opciones · Agrega al menos una para continuar
                        </p>
                    </div>

                    @error('opciones')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Regex --}}
                <div class="mb-6">
                    <label class="block text-[15px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Regla de formato del campo
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        {{-- Selector de plantilla --}}
                        <div class="md:col-span-6">
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-filter text-sm"></i>
                                </span>
                                <select id="regex_preset"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                    @foreach ($regexPresets as $key => $preset)
                                        <option value="{{ $key }}" @selected($presetSeleccionado === $key)>
                                            {{ $preset['label'] }}
                                        </option>
                                    @endforeach
                                    <option value="custom" @selected($esCustom)>Personalizada…</option>
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                Elige una plantilla común. Si no aplica, elige "Personalizada".
                            </p>
                        </div>

                        {{-- Input visible solo si es personalizada --}}
                        <div class="md:col-span-6" id="regexCustomWrapper"
                            style="{{ $esCustom ? '' : 'display:none;' }}">
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-code text-sm"></i>
                                </span>
                                <input type="text" id="regex_input" name="regex"
                                    value="{{ $regexActual }}"
                                    placeholder="Ej: /^[0-9]{10}$/"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none font-mono">
                            </div>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                Debe incluir las barras <code>/</code> al inicio y al final.
                            </p>
                        </div>
                    </div>

                    {{-- Hidden input para enviar el valor final cuando NO es custom --}}
                    <input type="hidden" id="regex_hidden" name="regex"
                        value="{{ $esCustom ? '' : $regexActual }}"
                        {{ $esCustom ? 'disabled' : '' }}>

                    @error('regex')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-8 pt-6 border-t flex items-center justify-end gap-3"
                    style="border-color: var(--border-color);">
                    <a href="{{ route('admin.becas.preguntas.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                        <i class="fas fa-save text-xs"></i> {{ $modelo ? 'Actualizar' : 'Guardar' }} pregunta
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    (function () {
        const presets = @json(collect($regexPresets)->map(fn($p) => $p['pattern'])->toArray());
        const select  = document.getElementById('regex_preset');
        const wrapper = document.getElementById('regexCustomWrapper');
        const input   = document.getElementById('regex_input');
        const hidden  = document.getElementById('regex_hidden');

        if (!select || !wrapper || !input || !hidden) return;

        function sync() {
            const val = select.value;

            if (val === 'custom') {
                // Modo personalizado: mostrar input, activar name, desactivar hidden
                wrapper.style.display = '';
                input.disabled = false;
                hidden.disabled = true;
                input.focus();
            } else {
                // Modo preset: ocultar input, desactivar su name, enviar valor por hidden
                wrapper.style.display = 'none';
                input.disabled = true;
                hidden.disabled = false;
                hidden.value = presets[val] ?? '';
            }
        }

        select.addEventListener('change', sync);
        sync(); // estado inicial
    })();
    
    let opcionesCount = 0;

    function addOption(etiqueta = '', valor = '') {
        const container = document.getElementById('opcionesContainer');
        const sinOpciones = document.getElementById('sinOpciones');
        if (sinOpciones) sinOpciones.style.display = 'none';

        const idx = opcionesCount++;

        const row = document.createElement('div');
        row.className = 'option-row flex items-center gap-3 mb-3';
        row.innerHTML = `
            <div class="flex items-stretch flex-1 rounded-xl border overflow-hidden" style="border-color: var(--border-color);">
                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r" style="border-color: var(--border-color);">
                    <i class="fas fa-tag text-xs"></i>
                </span>
                <input type="text" name="opciones[${idx}][etiqueta]" value="${etiqueta}" placeholder="Etiqueta visible · Ej: Sí"
                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none"
                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);">
            </div>
            <div class="flex items-stretch flex-1 rounded-xl border overflow-hidden" style="border-color: var(--border-color);">
                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r" style="border-color: var(--border-color);">
                    <i class="fas fa-key text-xs"></i>
                </span>
                <input type="text" name="opciones[${idx}][valor]" value="${valor}" placeholder="Valor en BD · Ej: 1"
                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none"
                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);">
            </div>
            <button type="button" onclick="removeOption(this)"
                class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl border border-rose-200 dark:border-rose-900 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 active:scale-95 transition-all">
                <i class="fas fa-times text-xs"></i>
            </button>
        `;
        container.appendChild(row);
    }

    function removeOption(btn) {
        btn.closest('.option-row').remove();
        const container = document.getElementById('opcionesContainer');
        const sinOpciones = document.getElementById('sinOpciones');
        if (sinOpciones && container.querySelectorAll('.option-row').length === 0) {
            sinOpciones.style.display = 'block';
        }
    }

    function updateConditionalFields() {
        const tipo = document.getElementById('tipo').value;

        const numericos = ['number', 'decimal'];
        const textos    = ['text', 'textarea', 'email'];
        const conOpcion = ['select', 'radio', 'checkbox'];

        document.getElementById('reglasNumericas').classList.toggle('hidden', !numericos.includes(tipo));
        document.getElementById('reglasTexto').classList.toggle('hidden', !textos.includes(tipo));
        document.getElementById('seccionOpciones').classList.toggle('hidden', !conOpcion.includes(tipo));
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('tipo').addEventListener('change', updateConditionalFields);
        updateConditionalFields();

        @foreach ($opcionesInit as $op)
            addOption(@json($op['etiqueta'] ?? ''), @json($op['valor'] ?? ''));
        @endforeach
    });
</script>