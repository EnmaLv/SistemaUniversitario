<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] pb-12 pt-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div class="flex items-center gap-4">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-red-800 text-white shadow-lg">
                        <i class="fas fa-route"></i>
                    </span>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">Crear nueva ruta</h1>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-sm">Registra y traza el recorrido de una nueva ruta de transporte.</p>
                    </div>
                </div>
                <a href="{{ route('admin.transporte.maestros.bus_rutas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-500 hover:text-red-600" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>
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

    <form action="{{ route('admin.transporte.maestros.bus_rutas.store') }}" method="POST" class="route-create-form space-y-5 rd-prevent-double-submit">
        @csrf

        <div id="hidden-paradas-inputs"></div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <div class="route-card mb-4 rounded-2xl border p-5 shadow-sm"
                    style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="route-card-heading mb-4">
                        <span class="route-section-icon"><i class="fas fa-map-signs"></i></span>
                        <div>
                            <h3 class="text-base font-extrabold" style="color:var(--text-main);">Información de la ruta</h3>
                            <p>Completa los datos generales y la configuración de la ruta.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="rd-label">Nombre de la Ruta <b class="text-red-500">*</b></label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-route"></i></span>
                            <input type="text" name="nombre" id="inputNombre"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input @error('nombre') border-red-300 @enderror"
                                placeholder="Ej: Zona Sur - Directo" value="{{ old('nombre') }}" maxlength="100" required>
                        </div>
                        <div id="errorNombreUnico" class="text-danger mt-1" style="display:none;"></div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <div class="form-group mb-3">
                                <label class="rd-label">Distancia (km) <b class="text-red-500">*</b></label>
                                <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                                    <span class="px-3 text-slate-500"><i class="fas fa-road"></i></span>
                                    <input type="number" name="distancia_km" id="inputDistancia" step="0.01"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input" placeholder="Calculando..."
                                        value="{{ old('distancia_km') }}" min="0.1" required readonly>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="form-group mb-3">
                                <label class="rd-label">Sede <b class="text-red-500">*</b></label>
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

                <div class="route-card mb-4 rounded-2xl border p-5 shadow-sm"
                    style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="route-card-heading mb-4">
                        <span class="route-section-icon"><i class="fas fa-clock"></i></span>
                        <div>
                            <h4 class="text-sm font-extrabold" style="color:var(--text-main);">Planificación de horarios</h4>
                            <p>Define los horarios en los que se realizará la ruta.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="rd-label">Hora de inicio <b class="text-red-500">*</b></label>
                            <div class="route-input mt-1"><span><i class="fas fa-clock"></i></span><input id="hora-inicio" type="time" value="{{ old('horarios.0.hora_salida', '06:00') }}"></div>
                        </div>
                        <div>
                            <label class="rd-label">Hora de fin <b class="text-red-500">*</b></label>
                            <div class="route-input mt-1"><span><i class="fas fa-clock"></i></span><input id="hora-fin" type="time" value="{{ old('horarios.1.hora_salida', '18:00') }}"></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="rd-label">Días de operación <b class="text-red-500">*</b></label>
                        <div class="route-days mt-1">
                            @foreach(['L','M','M','J','V','S','D'] as $day)
                                <span>{{ $day }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div id="contenedor-horarios" class="hidden"></div>
                </div>

                <div class="route-card mb-4 rounded-2xl border p-5 shadow-sm"
                    style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="form-group mb-0">
                        <div class="route-card-heading mb-4">
                            <span class="route-section-icon"><i class="far fa-file-alt"></i></span>
                            <div><h4 class="text-sm font-extrabold" style="color:var(--text-main);">Descripción</h4><p>Agrega una descripción o notas adicionales de la ruta.</p></div>
                        </div>
                        <textarea name="descripcion" class="route-textarea" rows="3" maxlength="1000" placeholder="Ej: Transporte a la zona sur, pasando por la avenida principal...">{{ old('descripcion') }}</textarea>
                        <div class="mt-1 text-right text-xs text-gray-500">0/500</div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="route-card mb-4 rounded-2xl border p-5 shadow-sm"
                    style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="mb-3">
                        <div class="route-card-heading">
                            <span class="route-section-icon"><i class="fas fa-bus"></i></span>
                            <div><h3 class="text-base font-extrabold" style="color:var(--text-main);">Trazado y paradas</h3><p>Selecciona las paradas en el mapa y define el recorrido de la ruta.</p></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <div id="mapa-constructor"
                                style="height:292px; border-radius:12px; border:2px solid var(--border-color); box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);">
                            </div>
                            <p class="mt-2 text-xs" style="color:var(--text-muted);">
                                <i class="fas fa-info-circle mr-1"></i>
                                Haz clic sobre una parada existente para agregarla. Para crear una nueva, haz clic en un punto vacío del mapa.
                            </p>
                        </div>
                        <div>
                            <label class="rd-label"><i class="fas fa-list-ol mr-1"></i> Secuencia de paradas</label>
                            <div id="lista-secuencia-paradas" class="list-group style-scroll"
                                style="max-height:292px; overflow-y:auto; border:1px solid var(--border-color); border-radius:8px; padding:6px; min-height:60px;">
                            </div>
                        </div>
                    </div>
                    @error('paradas')
                        <div class="text-danger mt-2"><b>Debe añadir al menos 2 paradas al trazado en el mapa.</b></div>
                    @enderror
                    <div class="mt-4 flex items-center justify-end gap-3 border-t pt-4" style="border-color:var(--border-color);">
                        <a href="{{ route('admin.transporte.maestros.bus_rutas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold transition hover:bg-gray-100 dark:hover:bg-gray-800" style="border-color:var(--border-color);color:var(--text-main);">
                            <i class="fas fa-times text-xs"></i> Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95 rd-submit-btn">
                            <i class="fas fa-save text-xs"></i> Guardar ruta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

@push('styles')
    <!-- Estilos de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .route-card {
            background-color: var(--bg-card);
            border-color: var(--border-color);
        }

        .route-card-heading {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .route-card-heading p {
            margin-top: .15rem;
            color: var(--text-muted);
            font-size: .65rem;
        }

        .route-section-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            flex: 0 0 auto;
            border-radius: .7rem;
            background: linear-gradient(145deg, #7f1d1d, #450a0a);
            color: #fff;
        }

        .route-create-form .form-group {
            margin-bottom: 1rem;
        }

        .route-create-form .rd-label {
            color: var(--text-main);
            font-size: .75rem;
            font-weight: 700;
        }

        .route-create-form input,
        .route-create-form select {
            background-color: var(--input-bg) !important;
            border-color: var(--input-border) !important;
            color: var(--text-main) !important;
        }

        .route-input {
            display: flex;
            align-items: center;
            min-height: 2.55rem;
            overflow: hidden;
            border: 1px solid var(--input-border);
            border-radius: .65rem;
            background: var(--input-bg);
        }

        .route-input span {
            padding: 0 .75rem;
            color: var(--text-muted);
            font-size: .8rem;
        }

        .route-input input {
            width: 100%;
            border: 0 !important;
            background: transparent !important;
            outline: 0;
            font-size: .7rem;
        }

        .route-days {
            display: flex;
            gap: .3rem;
        }

        .route-days span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            border: 1px solid var(--border-color);
            border-radius: .5rem;
            color: var(--text-main);
            background: var(--input-bg);
            font-size: .7rem;
            font-weight: 800;
        }

        .route-days span:nth-child(-n+5) {
            border-color: #7f1d1d;
            background: #450a0a;
            color: #fff;
        }

        .route-textarea {
            width: 100%;
            resize: vertical;
            border: 1px solid var(--input-border);
            border-radius: .65rem;
            background: var(--input-bg) !important;
            color: var(--text-main) !important;
            font-size: .75rem;
        }


        .parada-item {
            cursor: grab;
            padding: 8px 10px;
            margin-bottom: 6px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.7rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .parada-item:active {
            cursor: grabbing;
            background: var(--input-bg);
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
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <!-- Librería JavaScript de Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const paradasDisponibles = @json($paradas);
        let secuenciaRuta = [];
        let map;
        let polyline;
        let marcadoresMap = {};

        function initMap() {
            // Inicializar mapa de Leaflet
            map = L.map('mapa-constructor').setView([9.56, -69.20], 13);

            // Capa gratuita de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            map.on('click', function(evento) {
                const lat = evento.latlng.lat.toFixed(7);
                const lng = evento.latlng.lng.toFixed(7);
                const urlCrearParada = @json(route('admin.transporte.maestros.bus_paradas.create'))
                    + `?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`;

                L.popup()
                    .setLatLng(evento.latlng)
                    .setContent(`
                        <div class="text-center">
                            <strong>¿Crear una parada aquí?</strong>
                            <p style="margin:6px 0;font-size:12px;">${lat}, ${lng}</p>
                            <a href="${urlCrearParada}"
                                style="display:inline-block;background:#991b1b;color:#fff;border-radius:6px;padding:6px 10px;text-decoration:none;font-size:12px;font-weight:700;">
                                <i class="fas fa-plus"></i> Crear nueva parada
                            </a>
                        </div>
                    `)
                    .openOn(map);
            });

            // Inicializar la polilínea del recorrido
            polyline = L.polyline([], {
                color: '#B71C1C',
                opacity: 0.85,
                weight: 5
            }).addTo(map);

            // Dibujar marcadores circulares nativos
            paradasDisponibles.forEach(parada => {
                if (parada.lat === null || parada.lat === undefined
                    || parada.lng === null || parada.lng === undefined) return;

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

            const marcadores = Object.values(marcadoresMap);
            if (marcadores.length > 0) {
                map.fitBounds(L.featureGroup(marcadores).getBounds().pad(0.15));
            }

            // Cargar paradas previas en caso de fallos de validación (old inputs)
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
            if (secuenciaRuta.some(seleccionada => String(seleccionada.id) === String(parada.id))) {
                return;
            }
            secuenciaRuta.push(parada);
            actualizarInterfazYPolilinea();
        }

        function distanciaEnLineaRecta(origen, destino) {
            const radioTierra = 6371;
            const latitud = (parseFloat(destino.lat) - parseFloat(origen.lat)) * Math.PI / 180;
            const longitud = (parseFloat(destino.lng) - parseFloat(origen.lng)) * Math.PI / 180;
            const a = Math.sin(latitud / 2) ** 2
                + Math.cos(parseFloat(origen.lat) * Math.PI / 180)
                * Math.cos(parseFloat(destino.lat) * Math.PI / 180)
                * Math.sin(longitud / 2) ** 2;
            return radioTierra * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function actualizarDistanciaAlternativa() {
            const inputDistancia = document.getElementById('inputDistancia');
            const distancia = secuenciaRuta.reduce((total, parada, index) => {
                if (index === 0) return total;
                return total + distanciaEnLineaRecta(secuenciaRuta[index - 1], parada);
            }, 0);
            if (inputDistancia) {
                inputDistancia.value = distancia.toFixed(2);
            }
        }

        async function actualizarInterfazYPolilinea() {
            const listaHTML = document.getElementById('lista-secuencia-paradas');
            const inputsHidden = document.getElementById('hidden-paradas-inputs');
            const inputDistancia = document.getElementById('inputDistancia');

            listaHTML.innerHTML = '';
            inputsHidden.innerHTML = '';

            // Reset color gris base
            paradasDisponibles.forEach(p => {
                if (marcadoresMap[p.id]) marcadoresMap[p.id].setStyle({
                    fillColor: '#64748b'
                });
            });

            // Pintar de rojo las seleccionadas y armar lista HTML
            secuenciaRuta.forEach((parada, index) => {
                if (marcadoresMap[parada.id]) {
                    marcadoresMap[parada.id].setStyle({
                        fillColor: '#dc2626'
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

            // Integración OSRM (Ruteo real por calles)
            const coordenadasOSRM = secuenciaRuta.map(p => `${p.lng},${p.lat}`).join(';');
            const url =
                `https://router.project-osrm.org/route/v1/driving/${coordenadasOSRM}?overview=full&geometries=geojson`;

            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Respuesta errónea OSRM');

                const data = await response.json();
                if (data.code === 'Ok' && data.routes.length > 0) {
                    const rutaEncontrada = data.routes[0];

                    const coordenadasLinea = rutaEncontrada.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    polyline.setLatLngs(coordenadasLinea);

                    const kilometrajeReal = (rutaEncontrada.distance / 1000).toFixed(2);
                    if (inputDistancia) {
                        inputDistancia.value = kilometrajeReal;
                    }
                } else {
                    throw new Error('OSRM no encontró una ruta para los puntos seleccionados.');
                }
            } catch (error) {
                console.warn('No fue posible consultar OSRM; se usará una distancia aproximada.', error);
                const coordenadasLineaFallback = secuenciaRuta.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
                polyline.setLatLngs(coordenadasLineaFallback);
                actualizarDistanciaAlternativa();
            }
        }

        function eliminarPuntoSecuencia(index) {
            secuenciaRuta.splice(index, 1);
            actualizarInterfazYPolilinea();
        }

        function sincronizarHorarios() {
            const inicio = document.getElementById('hora-inicio').value;
            const fin = document.getElementById('hora-fin').value;
            const contenedor = document.getElementById('contenedor-horarios');
            contenedor.innerHTML = '';
            [
                { hora: inicio, tipo: 'entrada' },
                { hora: fin, tipo: 'salida' }
            ].forEach((horario, indice) => {
                contenedor.insertAdjacentHTML('beforeend',
                    `<input type="hidden" name="horarios[${indice}][hora_salida]" value="${horario.hora}">
                     <input type="hidden" name="horarios[${indice}][tipo_viaje]" value="${horario.tipo}">`);
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            initMap();

            const elLista = document.getElementById('lista-secuencia-paradas');
            if (elLista && window.Sortable) {
                Sortable.create(elLista, {
                    animation: 150,
                    onEnd: function() {
                        const nuevoOrdenIds = Array.from(elLista.querySelectorAll('.parada-item'))
                            .map(item => item.getAttribute('data-id'));
                        secuenciaRuta = nuevoOrdenIds
                            .map(id => secuenciaRuta.find(parada => String(parada.id) === String(id)))
                            .filter(Boolean);
                        actualizarInterfazYPolilinea();
                    }
                });
            }

            document.getElementById('hora-inicio').addEventListener('change', sincronizarHorarios);
            document.getElementById('hora-fin').addEventListener('change', sincronizarHorarios);

            const oldHorarios = @json(old('horarios'));
            if (oldHorarios && Object.keys(oldHorarios).length > 0) {
                const horariosAnteriores = Object.values(oldHorarios);
                const entrada = horariosAnteriores.find(h => h.tipo_viaje === 'entrada');
                const salida = horariosAnteriores.find(h => h.tipo_viaje === 'salida');
                if (entrada) document.getElementById('hora-inicio').value = entrada.hora_salida;
                if (salida) document.getElementById('hora-fin').value = salida.hora_salida;
            }
            sincronizarHorarios();
            setTimeout(() => map.invalidateSize(), 100);
        });
    </script>
@endpush
    </div>
    </div>
    </x-app-layout>
