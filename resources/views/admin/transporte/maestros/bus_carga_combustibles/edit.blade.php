@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 rd-title-sm" style="font-size:1.4rem;">Editar Carga de Combustible</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem;color:#475569;">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center" style="gap:14px;">
            <div class="text-right d-none d-sm-block">
                <small class="text-muted d-block" style="font-size:0.75rem;">Hoy</small>
                <span style="font-weight:600;font-size:0.95rem;">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
            </div>
            <div style="width:46px;height:46px;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(15,23,42,0.08);">
                <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario" style="width:100%;height:100%;object-fit:cover;">
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="rd-card p-4">
        <div class="rd-card-header mb-3">
            <h3 class="rd-title-sm">Editar Registro de Carga #{{ $busCargaCombustible->id }}</h3>
            <a href="{{ route('admin.transporte.maestros.bus_carga_combustibles.index') }}" class="rd-btn rd-btn-default">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <form action="{{ route('admin.transporte.maestros.bus_carga_combustibles.update', $busCargaCombustible) }}" method="POST"
            class="rd-prevent-double-submit">
            @csrf
            @method('PUT')

            {{-- Fila 1: VehÃ­culo, Viaje, Tipo Combustible --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">VehÃ­culo</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-bus"></i></span>
                            <select name="bus_vehiculo_id" id="selectVehiculo"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('bus_vehiculo_id') border-red-300 @enderror">
                                <option value="">-- Seleccione VehÃ­culo --</option>
                                @foreach($vehiculos as $v)
                                    <option value="{{ $v->id }}"
                                        {{ old('bus_vehiculo_id', $busCargaCombustible->bus_vehiculo_id) == $v->id ? 'selected' : '' }}>
                                        {{ $v->placa }} â€” {{ $v->modelo->nombre ?? '' }} (KM: {{ number_format($v->km_actual, 0) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('bus_vehiculo_id') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Viaje Asociado</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-route"></i></span>
                            <select name="bus_viaje_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('bus_viaje_id') border-red-300 @enderror">
                                <option value="">-- Seleccione Viaje --</option>
                                @foreach($viajes as $viaje)
                                    <option value="{{ $viaje->id }}"
                                        {{ old('bus_viaje_id', $busCargaCombustible->bus_viaje_id) == $viaje->id ? 'selected' : '' }}>
                                        #{{ $viaje->id }} | {{ $viaje->vehiculo->placa ?? '' }} - {{ $viaje->ruta->nombre ?? '' }} ({{ $viaje->fecha_inicio ? $viaje->fecha_inicio->format('d/m/Y') : 'Prog.' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('bus_viaje_id') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Tipo de Combustible</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-gas-pump"></i></span>
                            <select name="bus_tipo_combustible_id" id="selectTipoCombustible"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('bus_tipo_combustible_id') border-red-300 @enderror">
                                <option value="">-- Seleccione Tipo --</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id }}"
                                        {{ old('bus_tipo_combustible_id', $busCargaCombustible->bus_tipo_combustible_id) == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('bus_tipo_combustible_id') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>
            </div>

            {{-- Fila 2: Fecha, Boca, KM al Cargar --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Fecha de Carga</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" name="fecha"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('fecha') border-red-300 @enderror"
                                value="{{ old('fecha', $busCargaCombustible->fecha ? $busCargaCombustible->fecha->format('Y-m-d') : date('Y-m-d')) }}"
                                max="{{ date('Y-m-d') }}">
                        </div>
                        @error('fecha') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Boca / Tanque #</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-plug"></i></span>
                            <input type="number" name="boca_numero" id="inputBocaNumero"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('boca_numero') border-red-300 @enderror"
                                value="{{ old('boca_numero', $busCargaCombustible->boca_numero) }}" min="1" max="10">
                        </div>
                        @error('boca_numero') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">KM al Cargar (OdÃ³metro)</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-tachometer-alt"></i></span>
                            <input type="number" name="km_al_cargar" id="inputKmCargar" step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('km_al_cargar') border-red-300 @enderror"
                                value="{{ old('km_al_cargar', $busCargaCombustible->km_al_cargar) }}" min="0" max="9999999"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'').slice(0,10)">
                        </div>
                        @error('km_al_cargar') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>
            </div>

            {{-- Fila 3: Litros, Precio/L, Total Calculado --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Litros Cargados</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-fill-drip"></i></span>
                            <input type="number" name="litros" id="inputLitros" step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('litros') border-red-300 @enderror"
                                value="{{ old('litros', $busCargaCombustible->litros) }}" min="0.1" max="1000"
                                oninput="calcularTotalCombustible()">
                        </div>
                        @error('litros') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Precio por Litro ($)</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-dollar-sign"></i></span>
                            <input type="number" name="precio_litros" id="inputPrecio" step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('precio_litros') border-red-300 @enderror"
                                value="{{ old('precio_litros', $busCargaCombustible->precio_litros) }}" min="0.01" max="999999"
                                oninput="calcularTotalCombustible()">
                        </div>
                        @error('precio_litros') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Total a Pagar ($) <span class="text-muted font-weight-normal">(auto)</span></label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-receipt"></i></span>
                            <input type="number" id="inputTotal" step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input"
                                value="{{ old('total', $busCargaCombustible->total) }}" readonly
                                style="background:#f8fafc;cursor:not-allowed;font-weight:700;color:var(--color-primary);">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fila 4: Observaciones --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="font-weight-bold">Observaciones <span class="text-muted font-weight-normal">(opcional)</span></label>
                        <textarea name="observaciones" rows="3"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('observaciones') border-red-300 @enderror"
                            placeholder="EstaciÃ³n de servicio, nÃºmero de factura o ticket..."
                            maxlength="2000"
                            oninput="this.value=this.value.slice(0,2000); document.getElementById('contadorObs').textContent=this.value.length"
                            style="resize:none;">{{ old('observaciones', $busCargaCombustible->observaciones) }}</textarea>
                        <small class="text-muted"><span id="contadorObs">{{ strlen($busCargaCombustible->observaciones ?? '') }}</span>/2000 caracteres</small>
                        @error('observaciones') <div class="text-danger mt-1"><b>{{ $message }}</b></div> @enderror
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end" style="gap:12px;">
                <a href="{{ route('admin.transporte.maestros.bus_carga_combustibles.index') }}" class="rd-btn rd-btn-default">
                    Cancelar
                </a>
                <button type="submit" class="rd-btn rd-btn-primary rd-submit-btn" style="color:white;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
@stop

@push('js')
<script>
function calcularTotalCombustible() {
    const litros = parseFloat(document.getElementById('inputLitros').value) || 0;
    const precio = parseFloat(document.getElementById('inputPrecio').value) || 0;
    const total  = (litros * precio).toFixed(2);
    document.getElementById('inputTotal').value = total;
}

calcularTotalCombustible();
</script>
@endpush

