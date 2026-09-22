<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] pb-12 pt-6">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
    @include('components.alert')

    <style>
        .vehicle-create-form {
            color: var(--text-main);
        }

        .vehicle-page-heading {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .vehicle-page-heading-icon,
        .vehicle-card-heading-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 3rem;
            height: 3rem;
            border-radius: .75rem;
            background: linear-gradient(145deg, #dc2626, #991b1b);
            color: #fff;
            box-shadow: 0 8px 18px rgba(153, 27, 27, .2);
        }

        .vehicle-card-heading-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: .7rem;
        }

        .vehicle-page-heading p,
        .vehicle-card-heading p {
            color: var(--text-muted);
        }

        .vehicle-card-heading {
            display: flex;
            align-items: center;
            gap: .9rem;
        }

        .vehicle-create-form .row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            column-gap: 1.25rem;
            row-gap: 1.1rem;
            margin: 0;
        }

        .vehicle-create-form .row + .row {
            margin-top: 1.1rem;
        }

        .vehicle-create-form .col-md-3 {
            width: auto;
            max-width: none;
            padding: 0;
        }

        .vehicle-create-form .form-group {
            height: 100%;
            margin: 0;
        }

        .vehicle-create-form .form-group > label {
            display: block;
            margin-bottom: .4rem;
            color: var(--text-main);
            font-size: .75rem;
            font-weight: 700;
        }

        .vehicle-create-form .form-group > div.flex {
            min-height: 42px;
            background-color: var(--input-bg) !important;
            border-color: var(--border-color) !important;
            border-radius: .6rem;
        }

        .vehicle-create-form .form-group input,
        .vehicle-create-form .form-group select {
            background: transparent !important;
            border: 0 !important;
            color: var(--text-main) !important;
            font-size: .75rem;
            font-weight: 500;
        }

        .vehicle-create-form .form-group > div.flex > span {
            padding-left: .75rem;
            padding-right: .55rem;
            color: var(--text-muted);
            font-size: .8rem;
        }

        .vehicle-create-form .form-group input::placeholder {
            color: var(--text-muted);
        }

        .vehicle-create-form .form-group small {
            color: var(--text-muted) !important;
            font-size: .65rem !important;
        }

        .vehicle-create-form .form-group small button {
            color: var(--color-primary) !important;
            font-size: .65rem !important;
        }

        .vehicle-create-form > hr {
            display: none;
        }

        .vehicle-create-form .vehicle-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
        }

        .vehicle-form-actions .vehicle-cancel-button,
        .vehicle-form-actions .vehicle-submit-button {
            min-height: 2.5rem;
            border-radius: .65rem;
            padding: .65rem 1.1rem;
            font-size: .7rem;
        }

        .vehicle-form-actions .vehicle-submit-button {
            background: #dc2626;
        }

        .vehicle-form-actions .vehicle-submit-button:hover {
            background: #b91c1c;
        }

        .vehicle-modal {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(0, 0, 0, .65);
        }

        .vehicle-modal.hidden {
            display: none;
        }

        .vehicle-modal .modal-dialog {
            width: min(100%, 32rem);
            margin: 0;
        }

        .vehicle-modal .modal-content {
            overflow: hidden;
            background: var(--bg-card);
            border: 1px solid var(--border-color) !important;
            border-radius: 1rem !important;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .35);
        }

        .vehicle-modal .modal-header,
        .vehicle-modal .modal-footer {
            border-color: var(--border-color) !important;
            background: transparent;
        }

        .vehicle-modal .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
        }

        .vehicle-modal .modal-body {
            padding: 1.25rem;
        }

        .vehicle-modal .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: .65rem;
            padding: 1rem 1.25rem;
        }

        @media (max-width: 1023px) {
            .vehicle-create-form .row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 639px) {
            .vehicle-create-form .row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="mb-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="vehicle-page-heading">
            <span class="vehicle-page-heading-icon">
                <i class="fas fa-bus"></i>
            </span>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">
                    Crear nuevo vehículo
                </h1>
                <p class="mt-1 text-xs font-medium sm:text-sm">
                    Registra un vehículo y completa sus datos operativos.
                </p>
            </div>
        </div>
        <a href="{{ route('admin.transporte.maestros.bus_vehiculos.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-500 hover:text-red-600"
            style="border-color:var(--border-color);color:var(--text-main);">
            <i class="fas fa-arrow-left text-xs"></i>
            Volver
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border shadow-sm"
        style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="vehicle-card-heading border-b px-6 py-4" style="border-color: var(--border-color);">
            <span class="vehicle-card-heading-icon">
                <i class="fas fa-bus"></i>
            </span>
            <div>
                <h2 class="text-base font-extrabold" style="color: var(--text-main);">Datos del vehículo</h2>
                <p class="mt-1 text-xs">
                    Completa la información necesaria para registrar el vehículo.
                </p>
            </div>
        </div>
        <form action="{{ route('admin.transporte.maestros.bus_vehiculos.store') }}" method="POST"
            class="vehicle-create-form space-y-6 p-6 rd-prevent-double-submit">
            @csrf
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Placa</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-id-card"></i></span>
                            <input type="text" name="placa"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('placa') border-red-300 @enderror"
                                placeholder="Ej: ABC-123" value="{{ old('placa') }}" maxlength="20">
                        </div>
                        @error('placa')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Modelo</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-car"></i></span>
                            <select id="selectModelo" name="modelo_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('modelo_id') border-red-300 @enderror">
                                <option value="">-- Seleccione --</option>
                                @foreach ($modelos as $modelo)
                                    <option value="{{ $modelo->id }}"
                                        {{ old('modelo_id') == $modelo->id ? 'selected' : '' }}>
                                        {{ $modelo->busMarca->nombre ?? '' }} - {{ $modelo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('modelo_id')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                        <div class="mt-2">
                            <small style="color:#64748b;font-size:0.85rem;">
                                ¿No encuentras?
                                <button type="button" onclick="openModal('modalAddModelo')"
                                    style="background:none;border:none;padding:0;color:#a84348;font-weight:600;font-size:0.85rem;cursor:pointer;">
                                    <i class="fas fa-plus-circle"></i> Añádelo aquí
                                </button>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Año</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-calendar"></i></span>
                            <input type="number" name="anio"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('anio') border-red-300 @enderror"
                                value="{{ old('anio') }}" min="1990" max="{{ date('Y') }}"
                                placeholder="Ej: 2026" maxlength="4"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,4)">
                        </div>
                        @error('anio')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Color</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-palette"></i></span>
                            <input type="text" name="color"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('color') border-red-300 @enderror"
                                placeholder="Ej: Blanco" value="{{ old('color') }}" maxlength="50">
                        </div>
                        @error('color')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Peso del Vehículo</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-weight-hanging"></i></span>
                            <input type="text" name="peso"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('peso') border-red-300 @enderror"
                                placeholder="Ej: 3.5 Ton / 3500 kg" value="{{ old('peso') }}" maxlength="50">
                        </div>
                        @error('peso')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Cantidad de Pasajeros</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-users"></i></span>
                            <input type="number" name="cantidad_pasajeros"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('cantidad_pasajeros') border-red-300 @enderror"
                                placeholder="Ej: 40" value="{{ old('cantidad_pasajeros') }}" min="1"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,3)">
                        </div>
                        @error('cantidad_pasajeros')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Cantidad de Cilindros</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-plug"></i></span>
                            <input type="number" name="cantidad_cilindros"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('cantidad_cilindros') border-red-300 @enderror"
                                placeholder="Ej: 1" value="{{ old('cantidad_cilindros', 1) }}" min="1"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,2)">
                        </div>
                        @error('cantidad_cilindros')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Sede</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-building"></i></span>
                            <select name="sede_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('sede_id') border-red-300 @enderror">
                                <option value="">-- Seleccione --</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}"
                                        {{ old('sede_id') == $sede->id ? 'selected' : '' }}>
                                        {{ $sede->nombre_sede ?? $sede->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('sede_id')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Tipo de Combustible</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-gas-pump"></i></span>
                            <select id="selectCombustible" name="tipo_combustible_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('tipo_combustible_id') border-red-300 @enderror">
                                <option value="">-- Seleccione --</option>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->id }}"
                                        {{ old('tipo_combustible_id') == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('tipo_combustible_id')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                        <div class="mt-2">
                            <small style="color:#64748b;font-size:0.85rem;">
                                ¿No encuentras?
                                <button type="button" onclick="openModal('modalAddCombustible')"
                                    style="background:none;border:none;padding:0;color:#a84348;font-weight:600;font-size:0.85rem;cursor:pointer;">
                                    <i class="fas fa-plus-circle"></i> Añádelo aquí
                                </button>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Capacidad Tanque (L)</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-fill-drip"></i></span>
                            <input type="text" inputmode="decimal" name="capacidad_tanque_litros" step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('capacidad_tanque_litros') border-red-300 @enderror"
                                placeholder="Ej: 120.00" value="{{ old('capacidad_tanque_litros') }}" min="0"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)">
                        </div>
                        @error('capacidad_tanque_litros')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">KM Actual</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-road"></i></span>
                            <input type="text" inputmode="decimal" name="km_actual" step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('km_actual') border-red-300 @enderror"
                                placeholder="Ej: 50000.00" value="{{ old('km_actual') }}" min="0"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,9)">
                        </div>
                        @error('km_actual')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">KM Próx. Mantenimiento</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-wrench"></i></span>
                            <input type="text" inputmode="decimal" name="km_proximo_mantenimiento" step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('km_proximo_mantenimiento') border-red-300 @enderror"
                                placeholder="Ej: 55000.00" value="{{ old('km_proximo_mantenimiento') }}" min="0"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,9)">
                        </div>
                        @error('km_proximo_mantenimiento')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Consumo Urbano (L/km)</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-city"></i></span>
                            <input type="text" inputmode="decimal" name="consumo_urbano" step="0.001"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('consumo_urbano') border-red-300 @enderror"
                                placeholder="Ej: 0.350" value="{{ old('consumo_urbano') }}" min="0"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)">
                        </div>
                        @error('consumo_urbano')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Consumo Carretera (L/km)</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-route"></i></span>
                            <input type="text" inputmode="decimal" name="consumo_carretera" step="0.001"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('consumo_carretera') border-red-300 @enderror"
                                placeholder="Ej: 0.280" value="{{ old('consumo_carretera') }}" min="0"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)">
                        </div>
                        @error('consumo_carretera')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Consumo Ralentí (L/h)</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-clock"></i></span>
                            <input type="text" inputmode="decimal" name="consumo_relenti" step="0.001"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('consumo_relenti') border-red-300 @enderror"
                                placeholder="Ej: 1.500" value="{{ old('consumo_relenti') }}" min="0"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,6)">
                        </div>
                        @error('consumo_relenti')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Estado Operativo</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-info-circle"></i></span>
                            <select name="estado"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('estado') border-red-300 @enderror">
                                <option value="disponible" {{ old('estado') == 'disponible' ? 'selected' : '' }}>
                                    Disponible</option>
                                <option value="en_ruta" {{ old('estado') == 'en_ruta' ? 'selected' : '' }}>En Ruta
                                </option>
                                <option value="mantenimiento" {{ old('estado') == 'mantenimiento' ? 'selected' : '' }}>
                                    Mantenimiento</option>
                                <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo
                                </option>
                            </select>
                        </div>
                        @error('estado')
                            <div class="text-danger mt-1"><b>{{ $message }}</b></div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr>
            <div class="vehicle-form-actions">
                <a href="{{ route('admin.transporte.maestros.bus_vehiculos.index') }}" class="vehicle-cancel-button inline-flex items-center justify-center gap-2 border font-bold transition-all hover:bg-gray-100 dark:hover:bg-gray-800" style="border-color:var(--border-color);color:var(--text-main);">
                    Cancelar
                </a>
                <button type="submit" class="vehicle-submit-button inline-flex items-center justify-center gap-2 font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95 rd-submit-btn" style="color:white;">
                    <i class="fas fa-check"></i> Guardar vehículo
                </button>
            </div>
        </form>
    </div>

    <div class="vehicle-modal hidden" id="modalAddModelo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rd-card" style="border-radius:12px;border:1px solid #e5e7eb;">
                <div class="modal-header" style="border-bottom:1px solid #e5e7eb;">
                    <h5 class="modal-title rd-title-sm">
                        <i class="fas fa-car mr-2" style="color:var(--color-primary)"></i>Nuevo Modelo
                    </h5>
                    <button type="button" onclick="closeModal('modalAddModelo')" class="text-slate-500 hover:text-slate-700"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Marca</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-industry"></i></span>
                            <select id="newModeloMarca" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input">
                                <option value="">-- Seleccione una marca --</option>
                                @foreach ($marcas as $marca)
                                    <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="errorModeloMarca" class="text-danger mt-1" style="display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre del Modelo</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-tag"></i></span>
                            <input type="text" id="newModeloNombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input"
                                placeholder="Ej: Corolla" maxlength="100">
                        </div>
                        <div id="errorModeloNombre" class="text-danger mt-1" style="display:none;"></div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Descripción <span
                                class="text-muted font-weight-normal">(opcional)</span></label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-align-left"></i></span>
                            <input type="text" id="newModeloDescripcion" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input"
                                placeholder="Ej: Sedán compacto" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                    <button type="button" onclick="closeModal('modalAddModelo')" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold transition-all hover:bg-gray-100 dark:hover:bg-gray-800" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</button>
                    <button type="button" id="btnGuardarModelo" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                        <i class="fas fa-check"></i> Guardar y Seleccionar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="vehicle-modal hidden" id="modalAddCombustible" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rd-card" style="border-radius:12px;border:1px solid #e5e7eb;">
                <div class="modal-header" style="border-bottom:1px solid #e5e7eb;">
                    <h5 class="modal-title rd-title-sm">
                        <i class="fas fa-gas-pump mr-2" style="color:var(--color-primary)"></i>Nuevo Tipo de Combustible
                    </h5>
                    <button type="button" onclick="closeModal('modalAddCombustible')" class="text-slate-500 hover:text-slate-700"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-gas-pump"></i></span>
                            <input type="text" id="newCombustibleNombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input"
                                placeholder="Ej: Gasolina" maxlength="100">
                        </div>
                        <div id="errorCombustibleNombre" class="text-danger mt-1" style="display:none;"></div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Descripción <span
                                class="text-muted font-weight-normal">(opcional)</span></label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-align-left"></i></span>
                            <input type="text" id="newCombustibleDescripcion" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input"
                                placeholder="Ej: Combustible de 95 octanos" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                    <button type="button" onclick="closeModal('modalAddCombustible')" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold transition-all hover:bg-gray-100 dark:hover:bg-gray-800" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</button>
                    <button type="button" id="btnGuardarCombustible" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                        <i class="fas fa-check"></i> Guardar y Seleccionar
                    </button>
                </div>
            </div>
        </div>
    </div>
@push('js')
    <script>
        const CSRF = '{{ csrf_token() }}';

        function openModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        }

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

        // --- Registro Dinámico de Modelo vía Modal ---
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

        // --- Registro Dinámico de Tipo de Combustible vía Modal ---
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

        // --- Validación en Tiempo Real (Inline) ---
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
                max: 50,
                msg: 'Máximo 50 caracteres.'
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
            input.classList.add('border-red-300');
            const div = document.createElement('div');
            div.className = 'text-danger mt-1 error-inline';
            div.innerHTML = `<b>${msg}</b>`;
            input.closest('.form-group').appendChild(div);
        }

        function limpiarErrorInline(input) {
            input.classList.remove('border-red-300');
            const prev = input.closest('.form-group').querySelector('.error-inline');
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

        // --- Verificación Asíncrona de Placa Única ---
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
@endpush
</div>
</div>
</x-app-layout>
