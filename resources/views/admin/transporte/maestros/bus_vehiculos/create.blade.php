@extends('adminlte::page')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 rd-title-sm" style="font-size:1.4rem;">Registrar Nuevo Vehículo</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem;color:#475569;">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center" style="gap:14px;">
            <div class="text-right d-none d-sm-block">
                <small class="text-muted d-block" style="font-size:0.75rem;">Hoy</small>
                <span style="font-weight:600;font-size:0.95rem;">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
            </div>
            <div
                style="width:46px;height:46px;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(15,23,42,0.08);">
                <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                    style="width:100%;height:100%;object-fit:cover;">
            </div>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="rd-card p-4">
        <div class="rd-card-header mb-3">
            <h3 class="rd-title-sm">Datos del Vehículo</h3>
        </div>
        <form action="{{ route('admin.transporte.maestros.bus_vehiculos.store') }}" method="POST"
            class="rd-prevent-double-submit">
            @csrf
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Placa</label>
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" name="placa"
                                class="form-control rd-filter-input @error('placa') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-car"></i></span>
                            <select id="selectModelo" name="modelo_id"
                                class="form-control rd-filter-input @error('modelo_id') is-invalid @enderror">
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
                                <button type="button" data-toggle="modal" data-target="#modalAddModelo"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="number" name="anio"
                                class="form-control rd-filter-input @error('anio') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-palette"></i></span>
                            <input type="text" name="color"
                                class="form-control rd-filter-input @error('color') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-weight-hanging"></i></span>
                            <input type="text" name="peso"
                                class="form-control rd-filter-input @error('peso') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-users"></i></span>
                            <input type="number" name="cantidad_pasajeros"
                                class="form-control rd-filter-input @error('cantidad_pasajeros') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-plug"></i></span>
                            <input type="number" name="cantidad_cilindros"
                                class="form-control rd-filter-input @error('cantidad_cilindros') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select name="sede_id"
                                class="form-control rd-filter-input @error('sede_id') is-invalid @enderror">
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-gas-pump"></i></span>
                            <select id="selectCombustible" name="tipo_combustible_id"
                                class="form-control rd-filter-input @error('tipo_combustible_id') is-invalid @enderror">
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
                                <button type="button" data-toggle="modal" data-target="#modalAddCombustible"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-fill-drip"></i></span>
                            <input type="text" inputmode="decimal" name="capacidad_tanque_litros" step="0.01"
                                class="form-control rd-filter-input @error('capacidad_tanque_litros') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-road"></i></span>
                            <input type="text" inputmode="decimal" name="km_actual" step="0.01"
                                class="form-control rd-filter-input @error('km_actual') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-wrench"></i></span>
                            <input type="text" inputmode="decimal" name="km_proximo_mantenimiento" step="0.01"
                                class="form-control rd-filter-input @error('km_proximo_mantenimiento') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-city"></i></span>
                            <input type="text" inputmode="decimal" name="consumo_urbano" step="0.001"
                                class="form-control rd-filter-input @error('consumo_urbano') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-route"></i></span>
                            <input type="text" inputmode="decimal" name="consumo_carretera" step="0.001"
                                class="form-control rd-filter-input @error('consumo_carretera') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                            <input type="text" inputmode="decimal" name="consumo_relenti" step="0.001"
                                class="form-control rd-filter-input @error('consumo_relenti') is-invalid @enderror"
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                            <select name="estado"
                                class="form-control rd-filter-input @error('estado') is-invalid @enderror">
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
            <div class="d-flex justify-content-end" style="gap:12px;">
                <a href="{{ route('admin.transporte.maestros.bus_vehiculos.index') }}" class="rd-btn rd-btn-default">
                    Cancelar
                </a>
                <button type="submit" class="rd-btn rd-btn-primary rd-submit-btn" style="color:white;">
                    <i class="fas fa-check"></i> Guardar
                </button>
            </div>
        </form>
    </div>

    <div class="modal fade" id="modalAddModelo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rd-card" style="border-radius:12px;border:1px solid #e5e7eb;">
                <div class="modal-header" style="border-bottom:1px solid #e5e7eb;">
                    <h5 class="modal-title rd-title-sm">
                        <i class="fas fa-car mr-2" style="color:var(--color-primary)"></i>Nuevo Modelo
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Marca</label>
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-industry"></i></span>
                            <select id="newModeloMarca" class="form-control rd-filter-input">
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
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" id="newModeloNombre" class="form-control rd-filter-input"
                                placeholder="Ej: Corolla" maxlength="100">
                        </div>
                        <div id="errorModeloNombre" class="text-danger mt-1" style="display:none;"></div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Descripción <span
                                class="text-muted font-weight-normal">(opcional)</span></label>
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <input type="text" id="newModeloDescripcion" class="form-control rd-filter-input"
                                placeholder="Ej: Sedán compacto" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                    <button type="button" class="rd-btn rd-btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarModelo" class="rd-btn rd-btn-primary">
                        <i class="fas fa-check"></i> Guardar y Seleccionar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAddCombustible" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rd-card" style="border-radius:12px;border:1px solid #e5e7eb;">
                <div class="modal-header" style="border-bottom:1px solid #e5e7eb;">
                    <h5 class="modal-title rd-title-sm">
                        <i class="fas fa-gas-pump mr-2" style="color:var(--color-primary)"></i>Nuevo Tipo de Combustible
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre</label>
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-gas-pump"></i></span>
                            <input type="text" id="newCombustibleNombre" class="form-control rd-filter-input"
                                placeholder="Ej: Gasolina" maxlength="100">
                        </div>
                        <div id="errorCombustibleNombre" class="text-danger mt-1" style="display:none;"></div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Descripción <span
                                class="text-muted font-weight-normal">(opcional)</span></label>
                        <div class="input-group mt-1">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <input type="text" id="newCombustibleDescripcion" class="form-control rd-filter-input"
                                placeholder="Ej: Combustible de 95 octanos" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                    <button type="button" class="rd-btn rd-btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarCombustible" class="rd-btn rd-btn-primary">
                        <i class="fas fa-check"></i> Guardar y Seleccionar
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
@stop

@push('js')
    <script>
        const CSRF = '{{ csrf_token() }}';

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

        // --- Registro Dinámico de Modelo via Modal ---
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
                        $('#modalAddModelo').modal('hide');
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

        // --- Registro Dinámico de Tipo de Combustible via Modal ---
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
                        $('#modalAddCombustible').modal('hide');
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
            input.classList.add('is-invalid');
            const div = document.createElement('div');
            div.className = 'text-danger mt-1 error-inline';
            div.innerHTML = `<b>${msg}</b>`;
            input.closest('.form-group').appendChild(div);
        }

        function limpiarErrorInline(input) {
            input.classList.remove('is-invalid');
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