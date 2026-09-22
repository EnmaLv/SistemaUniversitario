<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] pb-12 pt-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div class="flex items-center gap-4">
                    <span class="route-page-icon"><i class="fas fa-map-marker-alt"></i></span>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">Editar parada</h1>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-sm">Actualiza los datos y la ubicación de la parada.</p>
                    </div>
                </div>
                <a href="{{ route('admin.transporte.maestros.bus_paradas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-500 hover:text-red-600" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <div class="route-card rounded-2xl border p-5 shadow-sm">
                <form id="formEditar" action="{{ route('admin.transporte.maestros.bus_paradas.update', $busParada->id) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <div class="route-card-heading mb-5">
                        <span class="route-section-icon"><i class="fas fa-map-pin"></i></span>
                        <div><h2 class="text-base font-extrabold" style="color:var(--text-main);">Información de la parada</h2><p>Actualiza los datos y selecciona el punto exacto.</p></div>
                    </div>

                    <div class="form-group">
                        <label class="rd-label">Nombre de la parada <b class="text-red-500">*</b></label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-map-pin"></i></span>
                            <input type="text" name="nombre" id="crearNombre"
                                value="{{ old('nombre', $busParada->nombre) }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('nombre') border-red-300 @enderror"
                                placeholder="Ej: Hiper Sol Acarigua" maxlength="100" required autofocus>
                        </div>
                        <div id="errorNombreUnico" class="text-danger mt-1" style="display:none;"></div>
                        @error('nombre')
                            <div class="text-danger font-weight-bold mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="rd-label">Dirección descriptiva</label>
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mt-1">
                            <span class="px-3 text-slate-500"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" name="direccion" value="{{ old('direccion', $busParada->direccion) }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('direccion') border-red-300 @enderror"
                                placeholder="Ej: Av. Circunvalación, frente al centro comercial">
                        </div>
                        @error('direccion')
                            <div class="text-danger font-weight-bold mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <div class="form-group">
                                <label class="rd-label">Latitud</label>
                                <input type="text" id="latInput" name="lat"
                                    value="{{ old('lat', $busParada->lat) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('lat') border-red-300 @enderror" readonly
                                    placeholder="Haga clic en el mapa" required>
                                @error('lat')
                                    <div class="text-danger font-weight-bold mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label class="rd-label">Longitud</label>
                                <input type="text" id="lngInput" name="lng"
                                    value="{{ old('lng', $busParada->lng) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-filter-input @error('lng') border-red-300 @enderror" readonly
                                    placeholder="Haga clic en el mapa" required>
                                @error('lng')
                                    <div class="text-danger font-weight-bold mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-3 border-t pt-5" style="border-color:var(--border-color);">
                        <a href="{{ route('admin.transporte.maestros.bus_paradas.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold transition-all hover:bg-gray-100 dark:hover:bg-gray-800" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95"><i class="fas fa-save text-xs"></i> Guardar parada</button>
                    </div>
                </form>
            </div>
        </div>

                <div class="lg:col-span-3">
                    <div class="route-card rounded-2xl border p-5 shadow-sm">
                        <div class="route-card-heading mb-4">
                            <span class="route-section-icon"><i class="fas fa-map"></i></span>
                            <div><h2 class="text-base font-extrabold" style="color:var(--text-main);">Ubicación de la parada</h2><p>Haz clic o arrastra el marcador para actualizar la ubicación.</p></div>
                        </div>
                        <div id="map" style="height:440px; border-radius:12px; border:2px solid var(--border-color); box-shadow: inset 0 2px 4px rgba(0,0,0,.06);">
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .route-card { background-color: var(--bg-card); border-color: var(--border-color); }
        .route-page-icon {
            display:inline-flex; align-items:center; justify-content:center; width:3rem; height:3rem;
            flex:0 0 auto; border-radius:.75rem; background:linear-gradient(145deg,#dc2626,#991b1b);
            color:#fff; box-shadow:0 8px 18px rgba(153,27,27,.2);
        }
        .route-card-heading { display:flex; align-items:center; gap:.75rem; }
        .route-card-heading p { margin-top:.15rem; color:var(--text-muted); font-size:.65rem; }
        .route-section-icon {
            display:inline-flex; align-items:center; justify-content:center; width:2.25rem; height:2.25rem;
            flex:0 0 auto; border-radius:.7rem; background:linear-gradient(145deg,#7f1d1d,#450a0a); color:#fff;
        }
        .route-card .form-group { margin-bottom:1rem; }
        .route-card .rd-label { color:var(--text-main); font-size:.75rem; font-weight:700; }
        .route-card input {
            background-color:var(--input-bg)!important;
            border-color:var(--input-border)!important;
            color:var(--text-main)!important;
        }
        .leaflet-container {
            font-family: inherit;
        }
    </style>
@endpush

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let map;
        let marker = null;
        const BASE_URL = '/admin/transporte/maestros/bus_paradas';
        const busParadaId = @json($busParada->id);

        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });

        function initMap() {
            // Prioriza las coordenadas antiguas por si falló la validación.
            const oldLat = "{{ old('lat') }}";
            const oldLng = "{{ old('lng') }}";
            const dbLat = "{{ $busParada->lat }}";
            const dbLng = "{{ $busParada->lng }}";

            let initialLat = oldLat ? parseFloat(oldLat) : parseFloat(dbLat);
            let initialLng = oldLng ? parseFloat(oldLng) : parseFloat(dbLng);

            const posicionInicial = L.latLng(initialLat, initialLng);

            // Centrar mapa en la ubicación de la parada
            map = L.map('map').setView(posicionInicial, 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Inicializar el marcador directamente en la parada actual
            colocarMarcador(posicionInicial);

            // Permitir cambiar la ubicación haciendo clic en otra zona del mapa
            map.on("click", (event) => {
                colocarMarcador(event.latlng);
            });
        }

        function colocarMarcador(location) {
            if (marker) {
                marker.setLatLng(location);
            } else {
                marker = L.marker(location, {
                    draggable: true
                }).addTo(map);

                marker.on('dragend', function(e) {
                    actualizarInputs(marker.getLatLng());
                });
            }

            actualizarInputs(location);
        }

        function actualizarInputs(location) {
            document.getElementById("latInput").value = location.lat.toFixed(7);
            document.getElementById("lngInput").value = location.lng.toFixed(7);
        }

        // Validación AJAX enviando el id actual para ignorar esta misma parada.
        let timerNombre = null;
        document.getElementById('crearNombre').addEventListener('input', function() {
            const errorDiv = document.getElementById('errorNombreUnico');
            errorDiv.style.display = 'none';
            const val = this.value.trim();
            if (!val) return;

            clearTimeout(timerNombre);
            timerNombre = setTimeout(() => {
                fetch(`${BASE_URL}/verificar-nombre?nombre=${encodeURIComponent(val)}&id=${busParadaId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.existe) {
                            errorDiv.textContent = 'Ya existe una parada registrada con este nombre.';
                            errorDiv.style.display = 'block';
                        }
                    });
            }, 450);
        });

        document.addEventListener("DOMContentLoaded", function() {
            initMap();
        });
    </script>
@endpush
</x-app-layout>
