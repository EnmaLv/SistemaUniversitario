<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-stop-number {
            background-color: #0ea5e9;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
    </style>

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            {{ $busViaje->ruta->nombre ?? 'Ruta N/A' }}
                        </h1>
                        <span id="badgeEstado" data-estado="{{ $busViaje->estado }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                            {{ match ($busViaje->estado) {
                                'programado' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                'en_curso' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                                'finalizado' => 'bg-gray-100 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400',
                                'cancelado' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
                                default => 'bg-gray-100 text-gray-600',
                            } }}">
                            <span id="badgeEstadoDot"
                                class="w-1.5 h-1.5 rounded-full {{ $busViaje->estado === 'en_curso' ? 'bg-emerald-500 animate-pulse' : 'bg-current' }}"></span>
                            <span id="badgeEstadoTexto">{{ ucfirst(str_replace('_', ' ', $busViaje->estado)) }}</span>
                        </span>
                        @if ($busViaje->hubo_desvio)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">
                                <i class="fas fa-exclamation-triangle"></i> Desvío reportado
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        ID Firebase: <code class="font-bold text-sky-600 dark:text-sky-400">{{ $busViaje->firebase_id }}</code>
                        &middot; Turno: <span class="font-bold capitalize">{{ $busViaje->turno }}</span>
                    </p>
                    @if ($busViaje->hubo_desvio && $busViaje->motivo_desvio)
                        <p class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                            <i class="fas fa-comment-dots mr-1"></i> Motivo reportado por el conductor: "{{ $busViaje->motivo_desvio }}"
                        </p>
                    @endif
                </div>
                <a href="{{ route('admin.transporte.maestros.bus_viajes.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm p-4">
                    <span class="text-[11px] font-black uppercase tracking-wider text-gray-400">Pasajeros a bordo</span>
                    <div class="flex items-center justify-between mt-1">
                        <h3 id="metricPasajeros" class="text-2xl font-extrabold" style="color: var(--text-main);">
                            {{ $busViaje->pasajeros ?? 0 }}
                        </h3>
                        <i class="fas fa-users text-sky-500/60 text-xl"></i>
                    </div>
                </div>

                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm p-4">
                    <span class="text-[11px] font-black uppercase tracking-wider text-gray-400">Estado del viaje</span>
                    <div class="flex items-center justify-between mt-1">
                        <h3 id="metricEstadoTexto" class="text-lg font-extrabold" style="color: var(--text-main);">
                            {{ ucfirst(str_replace('_', ' ', $busViaje->estado)) }}
                        </h3>
                        <i class="fas fa-route text-sky-500/60 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border shadow-sm overflow-hidden mb-4">
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                            style="border-bottom: 1px solid var(--border-color);">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-map-marked-alt text-sky-500"></i>
                                <span class="font-bold text-sm" style="color: var(--text-main);">Geolocalización GPS</span>
                            </div>
                            <small id="lastUpdated" class="text-xs font-semibold text-gray-400">
                                <i class="fas fa-sync-alt fa-spin mr-1"></i> Esperando señal GPS...
                            </small>
                        </div>
                        <div id="mapaGPS" style="height: 480px; width: 100%; z-index:1; background:#e5e7eb;"></div>
                    </div>

                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border shadow-sm p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-history text-sky-500"></i>
                                <span class="font-bold text-sm" style="color: var(--text-main);">Reconstrucción del recorrido</span>
                            </div>
                            <span id="playbackPuntos" class="text-[11px] font-bold text-gray-400"></span>
                        </div>

                        <div class="flex items-center gap-3">
                            <button id="btnPlayback" type="button"
                                class="w-10 h-10 flex items-center justify-center rounded-xl bg-sky-500 hover:bg-sky-600 text-white transition-all shrink-0">
                                <i id="iconPlayback" class="fas fa-play text-sm"></i>
                            </button>
                            <input id="sliderPlayback" type="range" min="0" max="0" value="0"
                                class="w-full accent-sky-500" disabled>
                        </div>
                        <p id="playbackVacio" class="text-xs text-gray-400 mt-2 hidden">
                            Aún no hay puntos GPS registrados para reconstruir este viaje.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border shadow-sm p-4 mb-4">
                        <h3 class="text-sm font-bold mb-3" style="color: var(--text-main);">
                            <i class="fas fa-user-shield text-sky-500 mr-1"></i> Asignación de unidad
                        </h3>

                        <div class="flex items-center gap-3 mb-3 p-3 rounded-xl"
                            style="background-color: rgba(0,0,0,0.02); border: 1px solid var(--border-color);">
                            <div class="w-11 h-11 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400 shrink-0">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-sm truncate" style="color: var(--text-main);">
                                    {{ $busViaje->conductor->persona->nombre_persona ?? 'Sin Asignar' }}
                                    {{ $busViaje->conductor->persona->apellido_persona ?? '' }}
                                </div>
                                <small class="text-xs text-gray-400">
                                    C.I: {{ $busViaje->conductor->persona->cedula_persona ?? 'N/A' }}
                                </small>
                            </div>
                        </div>

                        <div class="rounded-xl p-3 text-xs space-y-2"
                            style="background-color: rgba(0,0,0,0.02); border: 1px solid var(--border-color);">
                            <div class="flex justify-between">
                                <span class="text-gray-400 font-semibold">Placa:</span>
                                <span class="font-bold" style="color: var(--text-main);">{{ $busViaje->vehiculo->placa ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 font-semibold">Combustible:</span>
                                <span class="font-bold" style="color: var(--text-main);">{{ $busViaje->vehiculo->tipoCombustible->nombre ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border shadow-sm p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold" style="color: var(--text-main);">
                                <i class="fas fa-map-pin text-sky-500 mr-1"></i> Paradas de la ruta
                            </h3>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-white/5 text-gray-500">
                                {{ $busViaje->ruta->paradas->count() }}
                            </span>
                        </div>
                        <div class="max-h-[260px] overflow-y-auto space-y-3 pr-1">
                            @forelse($busViaje->ruta->paradas as $index => $parada)
                                <div class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-sky-50 dark:bg-sky-500/10 border-2 border-sky-500 text-sky-600 dark:text-sky-400 text-[11px] font-bold flex items-center justify-center shrink-0">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs truncate" style="color: var(--text-main);">
                                            {{ $parada->nombre }}
                                        </div>
                                        @if ($parada->lat && $parada->lng)
                                            <small class="text-[11px] text-gray-400">
                                                {{ number_format((float) $parada->lat, 4) }}, {{ number_format((float) $parada->lng, 4) }}
                                            </small>
                                        @else
                                            <small class="text-[11px] font-bold text-rose-500">
                                                <i class="fas fa-exclamation-circle mr-1"></i> Sin coordenadas
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 text-center py-3">Sin paradas intermedias.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
    (function () {
        const viajeId = "{{ $busViaje->id }}";
        const gpsLogsUrl = "{{ route('admin.transporte.maestros.bus_viajes.gps-logs', $busViaje) }}";
        let estadoActual = "{{ $busViaje->estado }}";

        let currentBusLat = 9.5468743;
        let currentBusLng = -69.1926348;

        const map = L.map('mapaGPS').setView([currentBusLat, currentBusLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        setTimeout(() => map.invalidateSize(), 200);
        window.addEventListener('resize', () => map.invalidateSize());

        const busIcon = L.divIcon({
            className: 'bus-live-icon',
            html: `<img src="/img/Moove_Bus.svg" style="width:68px;height:68px;">`,
            iconSize: [68, 68],
            iconAnchor: [34, 34],
            popupAnchor: [0, -34]
        });

        let busMarker = L.marker([currentBusLat, currentBusLng], { icon: busIcon })
            .addTo(map)
            .bindPopup(`<b>{{ $busViaje->vehiculo->placa ?? 'Unidad' }}</b><br>Esperando señal GPS...`);

        const paradasData = @json($busViaje->ruta->paradas ?? []);
        const routePoints = [];

        if (Array.isArray(paradasData) && paradasData.length > 0) {
            paradasData.forEach((parada, idx) => {
                const lat = parseFloat(parada.lat);
                const lng = parseFloat(parada.lng);
                if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                    routePoints.push([lat, lng]);
                    const stopNumberIcon = L.divIcon({
                        className: 'leaflet-stop-number-container',
                        html: `<div class="leaflet-stop-number">${idx + 1}</div>`,
                        iconSize: [26, 26],
                        iconAnchor: [13, 13]
                    });
                    L.marker([lat, lng], { icon: stopNumberIcon })
                        .addTo(map)
                        .bindPopup(`<b>Parada ${idx + 1}: ${parada.nombre}</b>`);
                }
            });

            if (routePoints.length >= 2) {
                const osrmCoords = routePoints.map(p => `${p[1]},${p[0]}`).join(';');
                fetch(`https://router.project-osrm.org/route/v1/driving/${osrmCoords}?overview=full&geometries=geojson`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.routes && data.routes.length > 0) {
                            const latLngs = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                            const plannedPolyline = L.polyline(latLngs, { color: '#b91c1c', weight: 5, opacity: 0.6 }).addTo(map);
                            map.fitBounds(plannedPolyline.getBounds(), { padding: [50, 50] });
                            setTimeout(() => map.invalidateSize(), 50);
                        }
                    })
                    .catch(err => console.error('Error trazando ruta OSRM:', err));
            }
        }

        function actualizarBadgeEstado(nuevoEstado) {
            if (nuevoEstado === estadoActual) return;
            estadoActual = nuevoEstado;

            const badge = document.getElementById('badgeEstado');
            const dot = document.getElementById('badgeEstadoDot');
            const texto = document.getElementById('badgeEstadoTexto');
            const metricTexto = document.getElementById('metricEstadoTexto');

            const estilos = {
                programado: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                en_curso:   'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                finalizado: 'bg-gray-100 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400',
                cancelado:  'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
            };

            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider ' + (estilos[nuevoEstado] || 'bg-gray-100 text-gray-600');
            dot.className = nuevoEstado === 'en_curso' ? 'w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse' : 'w-1.5 h-1.5 rounded-full bg-current';

            const textoLegible = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1).replace('_', ' ');
            texto.textContent = textoLegible;
            metricTexto.textContent = textoLegible;

            if (nuevoEstado === 'finalizado' || nuevoEstado === 'cancelado') {
                document.getElementById('lastUpdated').innerHTML =
                    `<span class="text-gray-400 font-bold"><i class="fas fa-flag-checkered mr-1"></i> Viaje ${nuevoEstado === 'finalizado' ? 'concluido' : 'cancelado'}</span>`;
                clearInterval(pollingInterval);
                map.removeLayer(busMarker);
                cargarHistorico();
            }
        }

        let liveTrail = [];
        let livePolyline = null;

        function actualizarPosicionGPS(lat, lng, velocidad, fechaRegistro) {
            busMarker.setLatLng([lat, lng]);

            const esNuevoPunto = currentBusLat !== lat || currentBusLng !== lng;
            currentBusLat = lat;
            currentBusLng = lng;

            if (esNuevoPunto) {
                liveTrail.push([lat, lng]);
                if (!livePolyline) {
                    livePolyline = L.polyline(liveTrail, { color: '#22c55e', weight: 4, opacity: 0.85 }).addTo(map);
                } else {
                    livePolyline.setLatLngs(liveTrail);
                }
                map.panTo([lat, lng]);
            }

            busMarker.getPopup().setContent(`
                <div class="text-center">
                    <strong class="text-sky-600">{{ $busViaje->vehiculo->placa ?? 'Autobús' }}</strong><br>
                    Velocidad: <b>${parseFloat(velocidad).toFixed(1)} km/h</b>
                </div>
            `);

            if (estadoActual !== 'en_curso') return;

            const statusElement = document.getElementById('lastUpdated');
            if (fechaRegistro) {
                const ultimaTransmision = new Date(fechaRegistro);
                const segundosDiferencia = Math.floor((new Date() - ultimaTransmision) / 1000);
                if (segundosDiferencia > 60) {
                    statusElement.innerHTML = `<span class="text-rose-500 font-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Sin señal (última: ${ultimaTransmision.toLocaleTimeString()})</span>`;
                    return;
                }
            }
            statusElement.innerHTML = `<span class="text-emerald-500 font-bold"><i class="fas fa-check-circle mr-1"></i> Transmitiendo en vivo (${new Date().toLocaleTimeString()})</span>`;
        }

        let pollingInterval;
        function consultarGPS() {
            fetch(`/api/viajes/${viajeId}/posicion`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                if (data.estado) actualizarBadgeEstado(data.estado);

                if (data.latitud && data.longitud) {
                    actualizarPosicionGPS(data.latitud, data.longitud, data.velocidad, data.fecha_registro);
                }
                if (data.pasajeros !== undefined) {
                    document.getElementById('metricPasajeros').innerText = data.pasajeros;
                }
            })
            .catch(err => console.error('Error al consultar GPS:', err));
        }

        if (estadoActual === 'en_curso' || estadoActual === 'programado') {
            pollingInterval = setInterval(consultarGPS, 3000);
            consultarGPS();
        } else {
            document.getElementById('lastUpdated').innerHTML =
                `<span class="text-gray-400 font-bold"><i class="fas fa-flag-checkered mr-1"></i> Viaje ${estadoActual === 'finalizado' ? 'concluido' : 'cancelado'}</span>`;
            map.removeLayer(busMarker);
        }

        let logs = [];
        let playbackMarker = null;
        let playbackTimer = null;
        let playbackIndex = 0;
        const slider = document.getElementById('sliderPlayback');
        const btnPlayback = document.getElementById('btnPlayback');
        const iconPlayback = document.getElementById('iconPlayback');

        function cargarHistorico() {
            fetch(gpsLogsUrl, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !Array.isArray(res.data) || res.data.length === 0) {
                        document.getElementById('playbackVacio').classList.remove('hidden');
                        btnPlayback.disabled = true;
                        btnPlayback.classList.add('opacity-40', 'cursor-not-allowed');
                        return;
                    }

                    logs = res.data;
                    slider.max = logs.length - 1;
                    slider.disabled = false;
                    document.getElementById('playbackPuntos').textContent = `${logs.length} puntos registrados`;

                    const latLngs = logs.map(l => [parseFloat(l.lat), parseFloat(l.lng)]);
                    L.polyline(latLngs, { color: '#b91c1c', weight: 4, opacity: 0.5, dashArray: '6,6' }).addTo(map);

                    if (playbackMarker) map.removeLayer(playbackMarker);
                    playbackMarker = L.marker(latLngs[0], { icon: busIcon }).addTo(map);
                })
                .catch(err => console.error('Error cargando histórico GPS:', err));
        }

        if (estadoActual === 'finalizado' || estadoActual === 'cancelado') {
            cargarHistorico();
        }

        function irAPunto(idx) {
            if (!logs[idx] || !playbackMarker) return;
            const punto = [parseFloat(logs[idx].lat), parseFloat(logs[idx].lng)];
            playbackMarker.setLatLng(punto);
            playbackMarker.bindPopup(`
                <div class="text-center">
                    <b>${new Date(logs[idx].created_at).toLocaleTimeString()}</b><br>
                    ${parseFloat(logs[idx].velocidad || 0).toFixed(1)} km/h
                </div>
            `);
            slider.value = idx;
        }

        slider.addEventListener('input', () => {
            clearInterval(playbackTimer);
            iconPlayback.className = 'fas fa-play text-sm';
            playbackIndex = parseInt(slider.value, 10);
            irAPunto(playbackIndex);
        });

        btnPlayback.addEventListener('click', () => {
            if (logs.length === 0) return;

            if (playbackTimer) {
                clearInterval(playbackTimer);
                playbackTimer = null;
                iconPlayback.className = 'fas fa-play text-sm';
                return;
            }

            if (playbackIndex >= logs.length - 1) playbackIndex = 0;
            iconPlayback.className = 'fas fa-pause text-sm';

            playbackTimer = setInterval(() => {
                if (playbackIndex >= logs.length - 1) {
                    clearInterval(playbackTimer);
                    playbackTimer = null;
                    iconPlayback.className = 'fas fa-play text-sm';
                    return;
                }
                playbackIndex++;
                irAPunto(playbackIndex);
            }, 200);
        });
    })();
    </script>
</x-app-layout>