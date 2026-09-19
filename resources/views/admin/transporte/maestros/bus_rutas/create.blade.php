@extends('layouts.app')

@section('content_header')
    <div class="mb-6 flex flex-col gap-4 rounded-2xl border p-5 shadow-sm md:flex-row md:items-center md:justify-between"
        style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="font-size:1.4rem; color:#0f172a; font-weight:700;">Crear Nueva Ruta</h1>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">Registre y trace el recorrido de una nueva ruta de
                transporte.</p>
        </div>
        <a href="{{ route('admin.transporte.maestros.bus_rutas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold transition-all hover:bg-gray-100 dark:hover:bg-gray-800" style="border-color:var(--border-color);color:var(--text-main);"><i
                class="fas fa-arrow-left"></i> Volver</a>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm" style="border-radius: 8px;">
            <b class="d-block mb-1"><i class="fas fa-exclamation-triangle mr-2"></i> Por favor verifique los siguientes
                errores:</b>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->has('error'))
        <div class="alert alert-danger"><b>{{ $errors->first('error') }}</b></div>
    @endif

    <form action="{{ route('admin.transporte.maestros.bus_rutas.store') }}" method="POST" class="rd-prevent-double-submit">
        @csrf

        <div id="hidden-paradas-inputs"></div>

        <div class="row">
            <div class="col-lg-5">
                <div class="rd-card p-4 mb-4"
                    style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <h3 class="rd-title-sm mb-3" style="font-size:1.1rem;color:#0f172a;font-weight:700;">Datos de la Ruta
                    </h3>

                    <div class="form-group">
                        <label class="rd-label">Nombre de la Ruta</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-route"></i></span>
                            <input type="text" name="nombre" id="inputNombre"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input @error('nombre') border-red-300 @enderror"
                                placeholder="Ej: Zona Sur - Directo" value="{{ old('nombre') }}" maxlength="100" required>
                        </div>
                        <div id="errorNombreUnico" class="text-danger mt-1" style="display:none;"></div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="rd-label">Distancia (km)</label>
                                <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                                    <span class="px-3 text-slate-500"><i class="fas fa-road"></i></span>
                                    <input type="number" name="distancia_km" id="inputDistancia" step="0.01"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input" placeholder="Calculando..."
                                        value="{{ old('distancia_km') }}" min="0.1" required readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="rd-label">Sede</label>
                                <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                                    <span class="px-3 text-slate-500"><i class="fas fa-building"></i></span>
                                    <select name="sede_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input" required>
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($sedes as $sede)
                                            <option value="{{ $sede->id }}"
                                                {{ old('sede_id') == $sede->id ? 'selected' : '' }}>
                                                {{ $sede->nombre_sede ?? $sede->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rd-card p-4 mb-4"
                    style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="m-0 font-weight-bold" style="font-size:1rem; color:#0f172a;"><i
                                class="fas fa-clock mr-2 text-primary"></i>PlanificaciÃ³n Horarios</h4>
                        <button type="button" id="btn-add-horario" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-600 px-4 py-2 text-sm font-bold text-emerald-700 transition-all hover:bg-emerald-50 dark:hover:bg-emerald-950/30 btn-sm"><i
                                class="fas fa-plus"></i></button>
                    </div>
                    <table class="table table-sm table-bordered">
                        <tbody id="contenedor-horarios"></tbody>
                    </table>
                </div>

                <div class="rd-card p-4 mb-4"
                    style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="form-group mb-0">
                        <label class="rd-label">DescripciÃ³n</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-sticky-note"></i></span>
                            <input name="descripcion" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input" style="resize:none; height: auto;"
                                placeholder="Transporte a la zona sur" value="{{ old('descripcion') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="rd-card p-4 mb-4"
                    style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="mb-3">
                        <h3 class="rd-title-sm m-0" style="font-size:1.1rem;color:#0f172a;font-weight:700;">Trazado e
                            Itinerario de Paradas</h3>
                        <small class="text-danger font-weight-bold">Haga clic en los marcadores del mapa para agregar o
                            alterar el orden.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <div id="mapa-constructor"
                                style="height:440px; border-radius:12px; border:2px solid #cbd5e1; box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="rd-label" style="font-size: 0.85rem;"><i class="fas fa-list-ol mr-1"></i>
                                Secuencia (Arrastra para reordenar)</label>
                            <div id="lista-secuencia-paradas" class="list-group style-scroll"
                                style="max-height:400px; overflow-y:auto; border:1px solid #dce1e6; border-radius:8px; padding:6px; min-height: 60px;">
                            </div>
                        </div>
                    </div>
                    @error('paradas')
                        <div class="text-danger mt-2"><b>Debe aÃ±adir al menos 2 paradas al trazado en el mapa.</b></div>
                    @enderror
                    <div class="d-flex justify-content-end" style="gap:12px;">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95 rd-submit-btn"
                            style="color:white; width:200px;"><i class="fas fa-save mr-2"></i> Guardar Ruta</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/diseÃ±o.css') }}">
    <!-- Estilos de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .parada-item {
            cursor: grab;
            padding: 8px 12px;
            margin-bottom: 6px;
            background-color: var(--bg-card);
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .parada-item:active {
            cursor: grabbing;
            background: #f1f5f9;
        }

        .badge-orden {
            background: #3b82f6;
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            margin-right: 6px;
        }

        .leaflet-container {
            font-family: inherit;
        }
    </style>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const paradasDisponibles = @json($paradas);
        let secuenciaRuta = [];
        let map;
        let polyline;
        let marcadoresMap = {};

        function initMap() {
            map = L.map('mapa-constructor').setView([9.56, -69.20], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            polyline = L.polyline([], {
                color: '#B71C1C',
                opacity: 0.85,
                weight: 5
            }).addTo(map);

            paradasDisponibles.forEach(parada => {
                if (!parada.lat || !parada.lng) return;

                let marker = L.circleMarker([parseFloat(parada.lat), parseFloat(parada.lng)], {
                    radius: 7,
                    fillColor: '#64748b',
                    color: '#ffffff',
                    weight: 2,
                    fillOpacity: 0.9
                }).addTo(map);

                marker.bindPopup(`<strong>${parada.nombre}</strong>`, {
                    closeButton: false
                });
                marker.on('mouseover', function() {
                    this.openPopup();
                });
                marker.on('mouseout', function() {
                    this.closePopup();
                });

                marcadoresMap[parada.id] = marker;

                marker.on('click', () => {
                    agregarParadaASecuencia(parada);
                });
            });

            const oldParadas = @json(old('paradas'));
            if (oldParadas && oldParadas.length > 0) {
                oldParadas.forEach(id => {
                    let pEncontrada = paradasDisponibles.find(x => x.id == id);
                    if (pEncontrada) secuenciaRuta.push(pEncontrada);
                });
            }

            actualizarInterfazYPolilinea();
        }

        function agregarParadaASecuencia(parada) {
            secuenciaRuta.push(parada);
            actualizarInterfazYPolilinea();
        }

        async function actualizarInterfazYPolilinea() {
            const listaHTML = document.getElementById('lista-secuencia-paradas');
            const inputsHidden = document.getElementById('hidden-paradas-inputs');
            const inputDistancia = document.getElementById('inputDistancia');

            listaHTML.innerHTML = '';
            inputsHidden.innerHTML = '';

            paradasDisponibles.forEach(p => {
                if (marcadoresMap[p.id]) marcadoresMap[p.id].setStyle({
                    fillColor: '#64748b'
                });
            });

            secuenciaRuta.forEach((parada, index) => {
                if (marcadoresMap[parada.id]) {
                    marcadoresMap[parada.id].setStyle({
                        fillColor: '#3b82f6'
                    });
                }

                listaHTML.innerHTML += `
                    <div class="parada-item" data-id="${parada.id}">
                        <div>
                            <span class="badge-orden">${index + 1}</span>
                            <span>${parada.nombre}</span>
                        </div>
                        <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 xs text-danger" onclick="eliminarPuntoSecuencia(${index})"><i class="fas fa-times"></i></button>
                    </div>
                `;

                inputsHidden.innerHTML += `<input type="hidden" name="paradas[]" value="${parada.id}">`;
            });

            if (secuenciaRuta.length < 2) {
                polyline.setLatLngs([]);
                if (inputDistancia) inputDistancia.value = '';
                return;
            }

            const coordenadasOSRM = secuenciaRuta.map(p => `${p.lng},${p.lat}`).join(';');
            const url =
                `https://router.project-osrm.org/route/v1/driving/${coordenadasOSRM}?overview=full&geometries=geojson`;

            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Respuesta errÃ³nea OSRM');

                const data = await response.json();
                if (data.code === 'Ok' && data.routes.length > 0) {
                    const rutaEncontrada = data.routes[0];

                    const coordenadasLinea = rutaEncontrada.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    polyline.setLatLngs(coordenadasLinea);

                    const kilometrajeReal = (rutaEncontrada.distance / 1000).toFixed(2);
                    if (inputDistancia) {
                        inputDistancia.value = kilometrajeReal;
                    }
                }
            } catch (error) {
                console.error('OSRM Fallback en Create:', error);
                const coordenadasLineaFallback = secuenciaRuta.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
                polyline.setLatLngs(coordenadasLineaFallback);
            }
        }

        function eliminarPuntoSecuencia(index) {
            secuenciaRuta.splice(index, 1);
            actualizarInterfazYPolilinea();
        }

        const elLista = document.getElementById('lista-secuencia-paradas');
        Sortable.create(elLista, {
            animation: 150,
            onEnd: function() {
                let nuevoOrdenIds = Array.from(elLista.querySelectorAll('.parada-item')).map(item => item
                    .getAttribute('data-id'));
                let mapaTemporal = [];
                nuevoOrdenIds.forEach(id => {
                    let pEncontrada = secuenciaRuta.find(x => x.id == id);
                    if (pEncontrada) mapaTemporal.push(pEncontrada);
                });
                secuenciaRuta = mapaTemporal;
                actualizarInterfazYPolilinea();
            }
        });

        let indiceHorario = 0;

        function agregarFilaHorario(hora = '', tipo = 'entrada') {
            const fila = document.createElement('tr');
            fila.setAttribute('id', `fila-horario-${indiceHorario}`);
            fila.innerHTML = `
                <td><input type="time" name="horarios[${indiceHorario}][hora_salida]" value="${hora}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input-sm" required></td>
                <td>
                    <select name="horarios[${indiceHorario}][tipo_viaje]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input-sm" required>
                        <option value="entrada" ${tipo === 'entrada' ? 'selected' : ''}>Entrada Ã¢Ëœâ‚¬Ã¯Â¸Â</option>
                        <option value="salida" ${tipo === 'salida' ? 'selected' : ''}>Salida Ã°Å¸ÂÂ </option>
                    </select>
                </td>
                <td class="text-center"><button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 xs btn-danger" onclick="document.getElementById('fila-horario-${indiceHorario}').remove()"><i class="fas fa-trash"></i></button></td>
            `;
            document.getElementById('contenedor-horarios').appendChild(fila);
            indiceHorario++;
        }

        document.getElementById('btn-add-horario').addEventListener('click', () => agregarFilaHorario());

        const oldHorarios = @json(old('horarios'));
        if (oldHorarios && Object.keys(oldHorarios).length > 0) {
            Object.values(oldHorarios).forEach(h => {
                agregarFilaHorario(h.hora_salida, h.tipo_viaje);
            });
        } else {
            agregarFilaHorario();
        }

        document.addEventListener("DOMContentLoaded", function() {
            initMap();
        });
    </script>
@endpush
