<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Registrar Nuevo Vehículo
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.transporte.maestros.bus_vehiculos.index') }}" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver
                </a>
            </div>


            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-8">

                <form action="{{ route('admin.transporte.maestros.bus_vehiculos.update', $busVehiculo) }}" method="POST"
                    class="rd-prevent-double-submit">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-col gap-6">

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            <div class="md:col-span-3">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Placa
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-id-card text-sm"></i>
                                    </span>
                                    <input type="text" name="placa" placeholder="Ej: ABC-123"
                                        value="{{ old('placa', $busVehiculo->placa) }}" maxlength="20"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('placa') is-invalid @enderror">
                                </div>
                                @error('placa')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-4">
                                <div class="flex justify-between items-end mb-1.5">
                                    <label
                                        class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400">
                                        Modelo
                                    </label>
                                    <button type="button" data-toggle="modal" data-target="#modalAddModelo"
                                        class="text-[10px] font-bold text-rose-700 hover:text-rose-800 transition-colors flex items-center gap-1">
                                        <i class="fas fa-plus-circle"></i> Añadir
                                    </button>
                                </div>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-car text-sm"></i>
                                    </span>
                                    <select id="selectModelo" name="modelo_id"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('modelo_id') is-invalid @enderror">
                                        <option value="" selected disabled>-- Seleccione --</option>
                                        @foreach ($modelos as $modelo)
                                            <option value="{{ $modelo->id }}"
                                                {{ old('modelo_id', $busVehiculo->modelo_id) == $modelo->id ? 'selected' : '' }}>
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
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Año
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-calendar text-sm"></i>
                                    </span>
                                    <select name="anio"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('anio') is-invalid @enderror">
                                        <option value="">Selecciona el año</option>
                                        @for ($year = 1990; $year <= date('Y'); $year++)
                                            <option value="{{ $year }}"
                                                {{ old('anio', $busVehiculo->anio) == $year ? 'selected' : '' }}>
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
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Color
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-palette text-sm"></i>
                                    </span>
                                    <input type="text" name="color" placeholder="Ej: Blanco"
                                        value="{{ old('color', $busVehiculo->color) }}" maxlength="50" inputmode="text"
                                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s-]+"
                                        oninput="this.value = this.value.replace(/[0-9]/g, '').replace(/\s{2,}/g, ' ')"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('color') is-invalid @enderror">
                                </div>
                                @error('color')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            <div class="md:col-span-4">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Peso del Vehículo
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-weight-hanging text-sm"></i>
                                    </span>

                                    <input type="text" name="peso" placeholder="Ej: 3.5 ó 3500"
                                        value="{{ old('peso', $busVehiculo->peso) }}" maxlength="10"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('peso') is-invalid @enderror">

                                    <span
                                        class="px-3 bg-gray-50 dark:bg-black/20 text-xs font-bold text-gray-500 border-l flex items-center"
                                        style="border-color: var(--border-color); color: var(--text-main);">
                                        Kg
                                    </span>
                                </div>
                                @error('peso')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-4">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Cantidad de Pasajeros
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-users text-sm"></i>
                                    </span>
                                    <input type="number" name="cantidad_pasajeros" placeholder="Ej: 40"
                                        value="{{ old('cantidad_pasajeros', $busVehiculo->cantidad_pasajeros) }}" min="1"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,3)"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('cantidad_pasajeros') is-invalid @enderror">
                                </div>
                                @error('cantidad_pasajeros')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-4">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Cantidad de Cilindros
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-plug text-sm"></i>
                                    </span>
                                    <input type="number" name="cantidad_cilindros" placeholder="Ej: 1"
                                        value="{{ old('cantidad_cilindros', $busVehiculo->cantidad_cilindros) }}" min="1"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,2)"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('cantidad_cilindros') is-invalid @enderror">
                                </div>
                                @error('cantidad_cilindros')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            <div class="md:col-span-4">
                                <div class="flex justify-between items-end mb-1.5">
                                    <label
                                        class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400">
                                        Tipo de Combustible
                                    </label>
                                    <button type="button" data-toggle="modal" data-target="#modalAddCombustible"
                                        class="text-[10px] font-bold text-rose-700 hover:text-rose-800 transition-colors flex items-center gap-1">
                                        <i class="fas fa-plus-circle"></i> Añadir
                                    </button>
                                </div>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-gas-pump text-sm"></i>
                                    </span>
                                    <select id="selectCombustible" name="tipo_combustible_id"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('tipo_combustible_id') is-invalid @enderror">
                                        <option value="" selected disabled>-- Seleccione --</option>
                                        @foreach ($tipos as $tipo)
                                            <option value="{{ $tipo->id }}"
                                                {{ old('tipo_combustible_id', $busVehiculo->tipo_combustible_id) == $tipo->id ? 'selected' : '' }}>
                                                {{ $tipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('tipo_combustible_id')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-4">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Capacidad Tanque (L)
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-fill-drip text-sm"></i>
                                    </span>
                                    <input type="text" inputmode="decimal" name="capacidad_tanque_litros"
                                        step="0.01" placeholder="Ej: 120.00"
                                        value="{{ old('capacidad_tanque_litros', $busVehiculo->capacidad_tanque_litros) }}" min="0"
                                        oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('capacidad_tanque_litros') is-invalid @enderror">
                                </div>
                                @error('capacidad_tanque_litros')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-4">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Estado Operativo
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-info-circle text-sm"></i>
                                    </span>
                                    <select name="estado"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('estado') is-invalid @enderror">
                                        <option value="disponible"
                                            {{ old('estado', $busVehiculo->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                        <option value="mantenimiento"
                                            {{ old('estado', $busVehiculo->estado) == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento
                                        </option>
                                        <option value="inactivo" {{ old('estado', $busVehiculo->estado) == 'inactivo' ? 'selected' : '' }}>
                                            Inactivo</option>
                                    </select>
                                </div>
                                @error('estado')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            <div class="md:col-span-3">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    KM Actual
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-road text-sm"></i>
                                    </span>
                                    <input type="text" inputmode="decimal" name="km_actual" step="0.01"
                                        placeholder="Ej: 50000.00" value="{{ old('km_actual', $busVehiculo->km_actual) }}" min="0"
                                        oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,9)"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('km_actual') is-invalid @enderror">
                                </div>
                                @error('km_actual')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-3">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    KM Próx. Mantenimiento
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-wrench text-sm"></i>
                                    </span>
                                    <input type="text" inputmode="decimal" name="km_proximo_mantenimiento"
                                        step="0.01" placeholder="Ej: 55000.00"
                                        value="{{ old('km_proximo_mantenimiento', $busVehiculo->km_proximo_mantenimiento) }}" min="0"
                                        oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,9)"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('km_proximo_mantenimiento') is-invalid @enderror">
                                </div>
                                @error('km_proximo_mantenimiento')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Urbano (L/km)
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-city text-sm"></i>
                                    </span>
                                    <input type="text" inputmode="decimal" name="consumo_urbano" step="0.001"
                                        placeholder="Ej: 0.350" value="{{ old('consumo_urbano', $busVehiculo->consumo_urbano) }}" min="0"
                                        oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('consumo_urbano') is-invalid @enderror">
                                </div>
                                @error('consumo_urbano')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Carretera (L/km)
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-route text-sm"></i>
                                    </span>
                                    <input type="text" inputmode="decimal" name="consumo_carretera"
                                        step="0.001" placeholder="Ej: 0.280"
                                        value="{{ old('consumo_carretera', $busVehiculo->consumo_carretera) }}" min="0"
                                        oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all @error('consumo_carretera') is-invalid @enderror">
                                </div>
                                @error('consumo_carretera')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                    Ralentí (L/h)
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                    style="border-color: var(--border-color);">
                                    <span
                                        class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-clock text-sm"></i>
                                    </span>
                                    <input type="text" inputmode="decimal" name="consumo_relenti" step="0.001"
                                        placeholder="Ej: 1.500" value="{{ old('consumo_relenti', $busVehiculo->consumo_relenti) }}" min="0"
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
                        <a href="{{ route('admin.transporte.maestros.bus_vehiculos.index') }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                            <i class="fas fa-check text-xs"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>

            <div id="modalAddModelo"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
                <div class="relative w-full max-w-md rounded-2xl border shadow-xl transition-all"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">

                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 sm:p-5 border-b"
                        style="border-color: var(--border-color);">
                        <h5 class="text-base font-extrabold uppercase tracking-wider flex items-center gap-2"
                            style="color: var(--text-main);">
                            <i class="fas fa-car text-rose-700"></i>
                            Nuevo Modelo
                        </h5>
                        <button type="button" onclick="closeModal('modalAddModelo')"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors text-2xl font-bold leading-none"
                            aria-label="Close">
                            &times;
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-6 space-y-4">
                        <div>
                            <label
                                class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Marca
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span
                                    class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-industry text-sm"></i>
                                </span>
                                <select id="newModeloMarca"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                    <option value="">-- Seleccione una marca --</option>
                                    @foreach ($marcas as $marca)
                                        <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="errorModeloMarca" class="mt-1.5 text-xs font-semibold text-rose-500"
                                style="display:none;"></div>
                        </div>

                        <div>
                            <label
                                class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Nombre del Modelo
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span
                                    class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-tag text-sm"></i>
                                </span>
                                <input type="text" id="newModeloNombre" placeholder="Ej: Corolla" maxlength="100"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                            </div>
                            <div id="errorModeloNombre" class="mt-1.5 text-xs font-semibold text-rose-500"
                                style="display:none;"></div>
                        </div>

                        <div>
                            <label
                                class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Descripción <span class="text-xs font-normal lowercase text-gray-400">(opcional)</span>
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span
                                    class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-align-left text-sm"></i>
                                </span>
                                <input type="text" id="newModeloDescripcion" placeholder="Ej: Sedán compacto"
                                    maxlength="255"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 p-4 sm:p-5 border-t"
                        style="border-color: var(--border-color);">
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


            <div id="modalAddCombustible"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
                <div class="relative w-full max-w-md rounded-2xl border shadow-xl transition-all"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">

                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 sm:p-5 border-b"
                        style="border-color: var(--border-color);">
                        <h5 class="text-base font-extrabold uppercase tracking-wider flex items-center gap-2"
                            style="color: var(--text-main);">
                            <i class="fas fa-gas-pump text-rose-700"></i>
                            Nuevo Tipo de Combustible
                        </h5>
                        <button type="button" onclick="closeModal('modalAddCombustible')"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors text-2xl font-bold leading-none"
                            aria-label="Close">
                            &times;
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-6 space-y-4">
                        <div>
                            <label
                                class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Nombre
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span
                                    class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-gas-pump text-sm"></i>
                                </span>
                                <input type="text" id="newCombustibleNombre" placeholder="Ej: Gasolina"
                                    maxlength="100"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                            </div>
                            <div id="errorCombustibleNombre" class="mt-1.5 text-xs font-semibold text-rose-500"
                                style="display:none;"></div>
                        </div>

                        <div>
                            <label
                                class="block text-[14px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                                Descripción <span class="text-xs font-normal lowercase text-gray-400">(opcional)</span>
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span
                                    class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-align-left text-sm"></i>
                                </span>
                                <input type="text" id="newCombustibleDescripcion"
                                    placeholder="Ej: Combustible de 95 octanos" maxlength="255"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 p-4 sm:p-5 border-t"
                        style="border-color: var(--border-color);">
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
        </div>
    </div>
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">

    <script>
        const CSRF = '{{ csrf_token() }}';

        // --- Funciones para manejo de Modales (Vanilla JS + Tailwind) ---
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        // Listener global para activar los botones con data-target
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

        document.getElementById('btnGuardarModelo').addEventListener('click', function() {
            const marca = document.getElementById('newModeloMarca').value;
            const nombre = document.getElementById('newModeloNombre').value.trim();
            const descripcion = document.getElementById('newModeloDescripcion').value.trim();
            const errMarca = document.getElementById('errorModeloMarca');
            const errNombre = document.getElementById('errorModeloNombre');
            errMarca.style.display = 'none';
            errNombre.style.display = 'none';

            let valido = true;
            if (!marca) {
                errMarca.textContent = 'Seleccione una marca.';
                errMarca.style.display = 'block';
                valido = false;
            }
            if (!nombre) {
                errNombre.textContent = 'El nombre es obligatorio.';
                errNombre.style.display = 'block';
                valido = false;
            }
            if (!valido) return;

            fetch('/admin/transporte/maestros/bus_modelos/store', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        marca_id: marca,
                        nombre,
                        descripcion
                    }),
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const select = document.getElementById('selectModelo');
                        const option = new Option(
                            `${res.modelo.marca_nombre} - ${res.modelo.nombre}`,
                            res.modelo.id, true, true
                        );
                        select.appendChild(option);
                        select.value = res.modelo.id;
                        document.getElementById('newModeloMarca').value = '';
                        document.getElementById('newModeloNombre').value = '';
                        document.getElementById('newModeloDescripcion').value = '';

                        closeModal('modalAddModelo');
                        toastExito(`Modelo "${res.modelo.nombre}" agregado y seleccionado.`);
                    } else {
                        if (res.errors?.marca_id) {
                            errMarca.textContent = res.errors.marca_id[0];
                            errMarca.style.display = 'block';
                        }
                        if (res.errors?.nombre) {
                            errNombre.textContent = res.errors.nombre[0];
                            errNombre.style.display = 'block';
                        }
                    }
                })
                .catch(() => {
                    errNombre.textContent = 'Error inesperado, intente de nuevo.';
                    errNombre.style.display = 'block';
                });
        });

        document.getElementById('btnGuardarCombustible').addEventListener('click', function() {
            const nombre = document.getElementById('newCombustibleNombre').value.trim();
            const descripcion = document.getElementById('newCombustibleDescripcion').value.trim();
            const errNombre = document.getElementById('errorCombustibleNombre');
            errNombre.style.display = 'none';

            if (!nombre) {
                errNombre.textContent = 'El nombre es obligatorio.';
                errNombre.style.display = 'block';
                return;
            }

            fetch('/admin/transporte/maestros/bus_tipo_combustibles/store', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nombre,
                        descripcion
                    }),
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const select = document.getElementById('selectCombustible');
                        const option = new Option(res.tipo.nombre, res.tipo.id, true, true);
                        select.appendChild(option);
                        select.value = res.tipo.id;

                        document.getElementById('newCombustibleNombre').value = '';
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

        const reglasInput = {
            placa: {
                max: 20,
                msg: 'Máximo 20 caracteres.'
            },
            anio: {
                min: 1990,
                max: {{ date('Y') }},
                msg: 'Año entre 1990 y {{ date('Y') }}.'
            },
            color: {
                max: 50,
                msg: 'Máximo 50 caracteres.'
            },
            peso: {
                min: 0.1,
                max: 100000,
                msg: 'Ingrese un peso válido.'
            },
            cantidad_pasajeros: {
                min: 1,
                max: 150,
                msg: 'Entre 1 y 150 pasajeros.'
            },
            cantidad_cilindros: {
                min: 1,
                max: 10,
                msg: 'Entre 1 y 10 Cilindros.'
            },
            capacidad_tanque_litros: {
                min: 1,
                max: 1000,
                msg: 'Entre 1 y 1000 litros.'
            },
            consumo_urbano: {
                min: 0.001,
                max: 5,
                msg: 'Entre 0.001 y 5 L/km.'
            },
            consumo_carretera: {
                min: 0.001,
                max: 5,
                msg: 'Entre 0.001 y 5 L/km.'
            },
            consumo_relenti: {
                min: 0.001,
                max: 50,
                msg: 'Entre 0.001 y 50 L/h.'
            },
            km_actual: {
                min: 0,
                max: 9999999,
                msg: 'Máximo 9,999,999 km.'
            },
            km_proximo_mantenimiento: {
                min: 0,
                max: 9999999,
                msg: 'Máximo 9,999,999 km.'
            },
        };

        function mostrarErrorInline(input, msg) {
            limpiarErrorInline(input);
            input.classList.add('is-invalid');
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
                if (!val) {
                    limpiarErrorInline(this);
                    return;
                }
                if (regla.max && typeof regla.min === 'undefined') {
                    val.length > regla.max ?
                        mostrarErrorInline(this, regla.msg) :
                        limpiarErrorInline(this);
                } else {
                    const num = parseFloat(val);
                    (num < regla.min || num > regla.max) ?
                    mostrarErrorInline(this, regla.msg): limpiarErrorInline(this);
                }
            });
        });

        let placaTimer = null;
        const inputPlaca = document.querySelector('[name="placa"]');
        if (inputPlaca) {
            inputPlaca.addEventListener('input', function() {
                const val = this.value.trim();
                limpiarErrorInline(this);
                if (!val) return;

                clearTimeout(placaTimer);
                placaTimer = setTimeout(() => {
                    fetch(`/admin/transporte/maestros/bus_vehiculos/verificar-placa?placa=${encodeURIComponent(val)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF
                            }
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.existe) {
                                mostrarErrorInline(inputPlaca, 'Esta placa ya está registrada.');
                            }
                        });
                }, 500);
            });
        }
    </script>
</x-app-layout>