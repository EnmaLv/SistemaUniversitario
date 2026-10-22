@props([
    'tipos',
    'seleccionado' => null,
    'form' => null,
    'etiqueta' => true,
    'enviarAlCambiar' => true,
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    @if ($etiqueta)
        <label for="filtro-tipo-producto"
            class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">
            Tipo de producto
        </label>
    @endif
    <select name="{{ \App\Support\Inventario\FiltroTipoProducto::PARAMETRO }}" id="filtro-tipo-producto"
        @if ($form) form="{{ $form }}" @endif
        @if ($enviarAlCambiar) onchange="this.form && this.form.submit()" @endif
        aria-label="Tipo de producto"
        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
        class="w-full sm:w-48 px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all">
        <option value="{{ \App\Support\Inventario\FiltroTipoProducto::TODOS }}" @selected($seleccionado === null)>Todos</option>
        @foreach ($tipos as $tipoOpcion)
            <option value="{{ $tipoOpcion->id_tpr }}" @selected($seleccionado !== null && (int) $seleccionado === (int) $tipoOpcion->id_tpr)>
                {{ $tipoOpcion->nombre_tpr }}
            </option>
        @endforeach
    </select>
</div>
