@php
    $vehiculo    = $vehiculo    ?? null;
    $modelos     = $modelos     ?? collect();
    $marcas      = $marcas      ?? collect();
    $tipos       = $tipos       ?? collect();
    $sedes       = $sedes       ?? collect();
    $rutaVolver  = $rutaVolver  ?? url()->previous();
    $metodo      = $metodo      ?? 'POST';
    $action      = $action      ?? '#';

    $esEdicion = !is_null($vehiculo);

    // Valor viejo o del modelo
    $v = fn(string $campo, $default = null) =>
        old($campo, optional($vehiculo)->{$campo} ?? $default);
@endphp

<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-8">

    <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit">
        @csrf
        @if ($metodo !== 'POST')
            @method($metodo)
        @endif

        <div class="flex flex-col gap-6">

            {{-- Fila 1: Placa, Modelo, Año, Color --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <div class="md:col-span-3">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Placa
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-id-card text-sm"></i>
                        </span>
                        <input type="text" name="placa" placeholder="Ej: ABC-123" maxlength="20"
                            value="{{ $v('placa') }}"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('placa') is-invalid @enderror">
                    </div>
                    @error('placa')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-4">
                    <div class="flex justify-between items-end mb-1.5">
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400">
                            Modelo
                        </label>
                        <button type="button" data-toggle="modal" data-target="#modalAddModelo"
                            class="text-[10px] font-bold text-rose-700 hover:text-rose-800 transition-colors flex items-center gap-1">
                            <i class="fas fa-plus-circle"></i> Añadir
                        </button>
                    </div>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-car text-sm"></i>
                        </span>
                        <select id="selectModelo" name="modelo_id"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('modelo_id') is-invalid @enderror">
                            <option value="" selected disabled>-- Seleccione --</option>
                            @foreach ($modelos as $modelo)
                                <option value="{{ $modelo->id }}"
                                    {{ $v('modelo_id') == $modelo->id ? 'selected' : '' }}>
                                    {{ $modelo->busMarca->nombre ?? '' }} - {{ $modelo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('modelo_id')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Año
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-calendar text-sm"></i>
                        </span>
                        <select name="anio"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('anio') is-invalid @enderror">
                            <option value="">Selecciona el año</option>
                            @for ($year = 1990; $year <= date('Y'); $year++)
                                <option value="{{ $year }}"
                                    {{ $v('anio') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    @error('anio')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Color
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-palette text-sm"></i>
                        </span>
                        <input type="text" name="color" placeholder="Ej: Blanco" maxlength="50"
                            inputmode="text" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s-]+"
                            value="{{ $v('color') }}"
                            oninput="this.value = this.value.replace(/[0-9]/g, '').replace(/\s{2,}/g, ' ')"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('color') is-invalid @enderror">
                    </div>
                    @error('color')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Fila 2: Peso, Pasajeros, Cilindros --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <div class="md:col-span-4">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Peso del Vehículo
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-weight-hanging text-sm"></i>
                        </span>
                        <input type="text" name="peso" placeholder="Ej: 3500" maxlength="10"
                            value="{{ $v('peso') }}"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('peso') is-invalid @enderror">
                        <span class="px-3 bg-gray-50 dark:bg-black/20 text-xs font-bold text-gray-500 border-l flex items-center"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            Kg
                        </span>
                    </div>
                    @error('peso')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-4">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Cantidad de Pasajeros
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-users text-sm"></i>
                        </span>
                        <input type="number" name="cantidad_pasajeros" placeholder="Ej: 40" min="1"
                            value="{{ $v('cantidad_pasajeros') }}"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,3)"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('cantidad_pasajeros') is-invalid @enderror">
                    </div>
                    @error('cantidad_pasajeros')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-4">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Cantidad de Cilindros
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-plug text-sm"></i>
                        </span>
                        <input type="number" name="cantidad_cilindros" placeholder="Ej: 4" min="1"
                            value="{{ $v('cantidad_cilindros', 1) }}"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,2)"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('cantidad_cilindros') is-invalid @enderror">
                    </div>
                    @error('cantidad_cilindros')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Fila 3: Combustible, Capacidad, Nivel Actual, Sede --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <div class="md:col-span-3">
                    <div class="flex justify-between items-end mb-1.5">
                        <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400">
                            Tipo de Combustible
                        </label>
                        <button type="button" data-toggle="modal" data-target="#modalAddCombustible"
                            class="text-[10px] font-bold text-rose-700 hover:text-rose-800 transition-colors flex items-center gap-1">
                            <i class="fas fa-plus-circle"></i> Añadir
                        </button>
                    </div>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-gas-pump text-sm"></i>
                        </span>
                        <select id="selectCombustible" name="tipo_combustible_id"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('tipo_combustible_id') is-invalid @enderror">
                            <option value="" selected disabled>-- Seleccione --</option>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->id }}"
                                    {{ $v('tipo_combustible_id') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('tipo_combustible_id')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Capacidad Tanque (L)
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-fill-drip text-sm"></i>
                        </span>
                        <input type="text" inputmode="decimal" id="capacidad_tanque" name="capacidad_tanque_litros"
                            step="0.01" placeholder="Ej: 120.00" min="0"
                            value="{{ $v('capacidad_tanque_litros') }}"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6); actualizarNivelCombustible();"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('capacidad_tanque_litros') is-invalid @enderror">
                    </div>
                    @error('capacidad_tanque_litros')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Nivel de Combustible (L)
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-battery-half text-sm"></i>
                        </span>
                        <input type="text" inputmode="decimal" id="nivel_combustible" name="nivel_combustible_actual"
                            step="0.01" placeholder="Ej: 80.00" min="0"
                            value="{{ $v('nivel_combustible_actual', 0) }}"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6); actualizarNivelCombustible();"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('nivel_combustible_actual') is-invalid @enderror">
                    </div>
                    {{-- Barra visual de nivel --}}
                    <div class="mt-2 flex items-center gap-2">
                        <div class="flex-1 h-1.5 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden">
                            <div id="barraNivel" class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                                style="width: 0%;"></div>
                        </div>
                        <span id="textoNivel"
                            class="text-[10px] font-bold text-gray-500 dark:text-gray-400 min-w-[38px] text-right">
                            0%
                        </span>
                    </div>
                    @error('nivel_combustible_actual')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Estado Operativo
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-info-circle text-sm"></i>
                        </span>
                        <select name="estado"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('estado') is-invalid @enderror">
                            <option value="disponible"   {{ $v('estado') == 'disponible'   ? 'selected' : '' }}>Disponible</option>
                            <option value="mantenimiento"{{ $v('estado') == 'mantenimiento'? 'selected' : '' }}>Mantenimiento</option>
                            <option value="inactivo"     {{ $v('estado') == 'inactivo'     ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    @error('estado')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Fila 4: Estado --}}
            

            {{-- Fila 5: Kilometrajes y consumos --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <div class="md:col-span-3">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        KM Actual
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-road text-sm"></i>
                        </span>
                        <input type="text" inputmode="decimal" name="km_actual" step="0.01"
                            placeholder="Ej: 50000.00" value="{{ $v('km_actual') }}"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,9)"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('km_actual') is-invalid @enderror">
                    </div>
                    @error('km_actual')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        KM Próx. Mantenimiento
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-wrench text-sm"></i>
                        </span>
                        <input type="text" inputmode="decimal" name="km_proximo_mantenimiento" step="0.01"
                            placeholder="Ej: 55000.00" value="{{ $v('km_proximo_mantenimiento') }}"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,9)"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('km_proximo_mantenimiento') is-invalid @enderror">
                    </div>
                    @error('km_proximo_mantenimiento')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Urbano (L/km)
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-city text-sm"></i>
                        </span>
                        <input type="text" inputmode="decimal" name="consumo_urbano" step="0.001"
                            placeholder="Ej: 0.350" value="{{ $v('consumo_urbano') }}"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('consumo_urbano') is-invalid @enderror">
                    </div>
                    @error('consumo_urbano')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Carretera (L/km)
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-route text-sm"></i>
                        </span>
                        <input type="text" inputmode="decimal" name="consumo_carretera" step="0.001"
                            placeholder="Ej: 0.280" value="{{ $v('consumo_carretera') }}"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('consumo_carretera') is-invalid @enderror">
                    </div>
                    @error('consumo_carretera')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Ralentí (L/h)
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-clock text-sm"></i>
                        </span>
                        <input type="text" inputmode="decimal" name="consumo_relenti" step="0.001"
                            placeholder="Ej: 1.500" value="{{ $v('consumo_relenti') }}"
                            oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)"
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('consumo_relenti') is-invalid @enderror">
                    </div>
                    @error('consumo_relenti')
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
                class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                <i class="fas fa-check text-xs"></i>
                {{ $esEdicion ? 'Actualizar' : 'Guardar' }}
            </button>
        </div>
    </form>
</div>

{{-- ======================= MODAL: Nuevo Modelo ======================= --}}
<div id="modalAddModelo" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="relative w-full max-w-md rounded-2xl border shadow-xl transition-all"
        style="background-color: var(--bg-card); border-color: var(--border-color);">

        <div class="flex items-center justify-between p-4 sm:p-5 border-b" style="border-color: var(--border-color);">
            <h5 class="text-base font-extrabold uppercase tracking-wider flex items-center gap-2" style="color: var(--text-main);">
                <i class="fas fa-car text-rose-700"></i> Nuevo Modelo
            </h5>
            <button type="button" onclick="closeModal('modalAddModelo')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors text-2xl font-bold leading-none"
                aria-label="Close">&times;</button>
        </div>

        <div class="p-4 sm:p-6 space-y-4">
            <div>
                <label class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">Marca</label>
                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                    style="border-color: var(--border-color);">
                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-industry text-sm"></i>
                    </span>
                    <select id="newModeloMarca" style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                        <option value="">-- Seleccione una marca --</option>
                        @foreach ($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="errorModeloMarca" class="mt-1.5 text-xs font-semibold text-rose-500" style="display:none;"></div>
            </div>

            <div>
                <label class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">Nombre del Modelo</label>
                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                    style="border-color: var(--border-color);">
                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-tag text-sm"></i>
                    </span>
                    <input type="text" id="newModeloNombre" placeholder="Ej: Corolla" maxlength="100"
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                </div>
                <div id="errorModeloNombre" class="mt-1.5 text-xs font-semibold text-rose-500" style="display:none;"></div>
            </div>

            <div>
                <label class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                    Descripción <span class="text-xs font-normal lowercase text-gray-400">(opcional)</span>
                </label>
                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                    style="border-color: var(--border-color);">
                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-align-left text-sm"></i>
                    </span>
                    <input type="text" id="newModeloDescripcion" placeholder="Ej: Sedán compacto" maxlength="255"
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 p-4 sm:p-5 border-t" style="border-color: var(--border-color);">
            <button type="button" onclick="closeModal('modalAddModelo')"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                style="border-color: var(--border-color); color: var(--text-main);">
                Cancelar
            </button>
            <button type="button" id="btnGuardarModelo"
                class="inline-flex items-center justify-center gap-2 px-5 py-2 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                <i class="fas fa-check text-xs"></i> Guardar y Seleccionar
            </button>
        </div>
    </div>
</div>

{{-- ======================= MODAL: Nuevo Combustible ======================= --}}
<div id="modalAddCombustible" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="relative w-full max-w-md rounded-2xl border shadow-xl transition-all"
        style="background-color: var(--bg-card); border-color: var(--border-color);">

        <div class="flex items-center justify-between p-4 sm:p-5 border-b" style="border-color: var(--border-color);">
            <h5 class="text-base font-extrabold uppercase tracking-wider flex items-center gap-2" style="color: var(--text-main);">
                <i class="fas fa-gas-pump text-rose-700"></i> Nuevo Tipo de Combustible
            </h5>
            <button type="button" onclick="closeModal('modalAddCombustible')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors text-2xl font-bold leading-none"
                aria-label="Close">&times;</button>
        </div>

        <div class="p-4 sm:p-6 space-y-4">
            <div>
                <label class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">Nombre</label>
                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                    style="border-color: var(--border-color);">
                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-gas-pump text-sm"></i>
                    </span>
                    <input type="text" id="newCombustibleNombre" placeholder="Ej: Gasolina" maxlength="100"
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                </div>
                <div id="errorCombustibleNombre" class="mt-1.5 text-xs font-semibold text-rose-500" style="display:none;"></div>
            </div>

            <div>
                <label class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                    Descripción <span class="text-xs font-normal lowercase text-gray-400">(opcional)</span>
                </label>
                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                    style="border-color: var(--border-color);">
                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-align-left text-sm"></i>
                    </span>
                    <input type="text" id="newCombustibleDescripcion" placeholder="Ej: Combustible de 95 octanos" maxlength="255"
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 p-4 sm:p-5 border-t" style="border-color: var(--border-color);">
            <button type="button" onclick="closeModal('modalAddCombustible')"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                style="border-color: var(--border-color); color: var(--text-main);">
                Cancelar
            </button>
            <button type="button" id="btnGuardarCombustible"
                class="inline-flex items-center justify-center gap-2 px-5 py-2 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                <i class="fas fa-check text-xs"></i> Guardar y Seleccionar
            </button>
        </div>
    </div>
</div>

<script>
    const CSRF = '{{ csrf_token() }}';

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    }
    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
    }

    document.querySelectorAll('[data-toggle="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target')?.replace('#', '');
            if (targetId) openModal(targetId);
        });
    });

    function toastExito(mensaje) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: mensaje,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    // ===== Barra de nivel de combustible =====
    function actualizarNivelCombustible() {
        const cap   = parseFloat(document.getElementById('capacidad_tanque')?.value) || 0;
        const nivel = parseFloat(document.getElementById('nivel_combustible')?.value) || 0;
        const barra = document.getElementById('barraNivel');
        const texto = document.getElementById('textoNivel');
        if (!barra || !texto) return;

        let pct = 0;
        if (cap > 0) {
            pct = Math.min(100, Math.max(0, (nivel / cap) * 100));
        }
        barra.style.width = pct + '%';
        texto.textContent = Math.round(pct) + '%';

        barra.classList.remove('bg-emerald-500', 'bg-amber-500', 'bg-rose-500');
        if (pct < 20)      barra.classList.add('bg-rose-500');
        else if (pct < 50) barra.classList.add('bg-amber-500');
        else               barra.classList.add('bg-emerald-500');
    }
    document.addEventListener('DOMContentLoaded', actualizarNivelCombustible);

    // ===== Modal: guardar Modelo =====
    document.getElementById('btnGuardarModelo')?.addEventListener('click', function() {
        const marca       = document.getElementById('newModeloMarca').value;
        const nombre      = document.getElementById('newModeloNombre').value.trim();
        const descripcion = document.getElementById('newModeloDescripcion').value.trim();
        const errMarca    = document.getElementById('errorModeloMarca');
        const errNombre   = document.getElementById('errorModeloNombre');
        errMarca.style.display = 'none';
        errNombre.style.display = 'none';

        let valido = true;
        if (!marca)  { errMarca.textContent  = 'Seleccione una marca.';   errMarca.style.display  = 'block'; valido = false; }
        if (!nombre) { errNombre.textContent = 'El nombre es obligatorio.'; errNombre.style.display = 'block'; valido = false; }
        if (!valido) return;

        fetch('/admin/transporte/maestros/bus_modelos/store', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ marca_id: marca, nombre, descripcion }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const select = document.getElementById('selectModelo');
                const option = new Option(`${res.modelo.marca_nombre} - ${res.modelo.nombre}`, res.modelo.id, true, true);
                select.appendChild(option);
                select.value = res.modelo.id;
                document.getElementById('newModeloMarca').value       = '';
                document.getElementById('newModeloNombre').value      = '';
                document.getElementById('newModeloDescripcion').value = '';
                closeModal('modalAddModelo');
                toastExito(`Modelo "${res.modelo.nombre}" agregado y seleccionado.`);
            } else {
                if (res.errors?.marca_id) { errMarca.textContent  = res.errors.marca_id[0]; errMarca.style.display  = 'block'; }
                if (res.errors?.nombre)   { errNombre.textContent = res.errors.nombre[0];   errNombre.style.display = 'block'; }
            }
        })
        .catch(() => {
            errNombre.textContent = 'Error inesperado, intente de nuevo.';
            errNombre.style.display = 'block';
        });
    });

    // ===== Modal: guardar Combustible =====
    document.getElementById('btnGuardarCombustible')?.addEventListener('click', function() {
        const nombre      = document.getElementById('newCombustibleNombre').value.trim();
        const descripcion = document.getElementById('newCombustibleDescripcion').value.trim();
        const errNombre   = document.getElementById('errorCombustibleNombre');
        errNombre.style.display = 'none';

        if (!nombre) { errNombre.textContent = 'El nombre es obligatorio.'; errNombre.style.display = 'block'; return; }

        fetch('/admin/transporte/maestros/bus_tipo_combustibles/store', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, descripcion }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const select = document.getElementById('selectCombustible');
                const option = new Option(res.tipo.nombre, res.tipo.id, true, true);
                select.appendChild(option);
                select.value = res.tipo.id;
                document.getElementById('newCombustibleNombre').value      = '';
                document.getElementById('newCombustibleDescripcion').value = '';
                closeModal('modalAddCombustible');
                toastExito(`"${res.tipo.nombre}" agregado y seleccionado.`);
            } else if (res.errors?.nombre) {
                errNombre.textContent = res.errors.nombre[0];
                errNombre.style.display = 'block';
            }
        })
        .catch(() => {
            errNombre.textContent = 'Error inesperado, intente de nuevo.';
            errNombre.style.display = 'block';
        });
    });

    // ===== Validaciones inline =====
    const reglasInput = {
        placa:                      { max: 20, msg: 'Máximo 20 caracteres.' },
        color:                      { max: 50, msg: 'Máximo 50 caracteres.' },
        peso:                       { min: 0.1, max: 100000, msg: 'Ingrese un peso válido.' },
        cantidad_pasajeros:         { min: 1, max: 150, msg: 'Entre 1 y 150 pasajeros.' },
        cantidad_cilindros:         { min: 1, max: 10, msg: 'Entre 1 y 10 Cilindros.' },
        capacidad_tanque_litros:    { min: 1, max: 1000, msg: 'Entre 1 y 1000 litros.' },
        nivel_combustible_actual:   { min: 0, max: 1000, msg: 'No puede superar la capacidad del tanque.' },
        consumo_urbano:             { min: 0.001, max: 5, msg: 'Entre 0.001 y 5 L/km.' },
        consumo_carretera:          { min: 0.001, max: 5, msg: 'Entre 0.001 y 5 L/km.' },
        consumo_relenti:            { min: 0.001, max: 50, msg: 'Entre 0.001 y 50 L/h.' },
        km_actual:                  { min: 0, max: 9999999, msg: 'Máximo 9,999,999 km.' },
        km_proximo_mantenimiento:   { min: 0, max: 9999999, msg: 'Máximo 9,999,999 km.' },
    };

    function mostrarErrorInline(input, msg) {
        limpiarErrorInline(input);
        input.classList.add('border-red-300');
        const div = document.createElement('div');
        div.className = 'text-rose-500 text-xs font-semibold mt-1 error-inline';
        div.innerHTML = `<b>${msg}</b>`;
        input.parentElement.appendChild(div);
    }
    function limpiarErrorInline(input) {
        input.classList.remove('is-invalid');
        const prev = input.parentElement.querySelector('.error-inline');
        if (prev) prev.remove();
    }

    Object.keys(reglasInput).forEach(function(name) {
        const input = document.querySelector(`[name="${name}"]`);
        if (!input) return;
        const regla = reglasInput[name];
        input.addEventListener('input', function() {
            const val = this.value.trim();
            if (!val) { limpiarErrorInline(this); return; }
            if (regla.max && typeof regla.min === 'undefined') {
                val.length > regla.max ? mostrarErrorInline(this, regla.msg) : limpiarErrorInline(this);
            } else {
                const num = parseFloat(val);
                (num < regla.min || num > regla.max) ? mostrarErrorInline(this, regla.msg) : limpiarErrorInline(this);
            }
        });
    });

    // ===== Verificación de placa en tiempo real =====
    let placaTimer = null;
    const inputPlaca = document.querySelector('[name="placa"]');
    const excludeId  = {{ $vehiculo->id ?? 'null' }};
    if (inputPlaca) {
        inputPlaca.addEventListener('input', function() {
            const val = this.value.trim();
            limpiarErrorInline(this);
            if (!val) return;
            clearTimeout(placaTimer);
            placaTimer = setTimeout(() => {
                const url = `/admin/transporte/maestros/bus_vehiculos/verificar-placa?placa=${encodeURIComponent(val)}`
                    + (excludeId ? `&exclude=${excludeId}` : '');
                fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } })
                    .then(r => r.json())
                    .then(res => {
                        if (res.existe) mostrarErrorInline(inputPlaca, 'Esta placa ya está registrada.');
                    });
            }, 500);
        });
    }
</script>