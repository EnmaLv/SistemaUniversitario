<x-app-layout>
    @include('components.alert')

    <div class="min-h-[calc(100vh-4rem)] pb-12 pt-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div class="flex items-center gap-4">
                    <span class="journey-page-icon"><i class="fas fa-bus"></i></span>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">Crear viaje</h1>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-sm">Registra y traza el recorrido de un nuevo viaje de transporte.</p>
                    </div>
                </div>
                <a href="{{ route('admin.transporte.maestros.bus_viajes.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-500 hover:text-red-600" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            <form id="formCrear" action="{{ route('admin.transporte.maestros.bus_viajes.store') }}" method="POST" class="journey-form">
                @csrf
                <div class="grid grid-cols-1 gap-5">
                    <div class="mx-auto w-full max-w-4xl space-y-5">
                        <section class="journey-card rounded-2xl border p-5 shadow-sm">
                            <div class="journey-card-heading mb-5">
                                <span class="journey-section-icon"><i class="fas fa-bus"></i></span>
                                <div><h2 class="text-base font-extrabold" style="color:var(--text-main);">Información del viaje</h2><p>Selecciona la unidad, ruta y conductor responsable.</p></div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="journey-label">Autobús / Unidad disponible <b>*</b></label>
                                    <div class="journey-input mt-1"><span><i class="fas fa-bus"></i></span>
                                        <select name="vehiculo_id" required>
                                            <option value="">-- Seleccionar vehículo --</option>
                                            @foreach ($vehiculos as $vehiculo)
                                                <option value="{{ $vehiculo->id }}" {{ old('vehiculo_id') == $vehiculo->id ? 'selected' : '' }}>
                                                    {{ $vehiculo->unidad ?? 'Unidad sin nombre' }} (Placa: {{ $vehiculo->placa }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('vehiculo_id')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label class="journey-label">Ruta de transporte <b>*</b></label>
                                    <div class="journey-input mt-1"><span><i class="fas fa-route"></i></span>
                                        <select name="bus_ruta_id" id="rutaSelect" required>
                                            <option value="">-- Seleccionar ruta --</option>
                                            @foreach ($rutas as $ruta)
                                                <option value="{{ $ruta->id }}" data-distancia="{{ $ruta->distancia_km }}" {{ old('bus_ruta_id') == $ruta->id ? 'selected' : '' }}>
                                                    {{ $ruta->nombre }} ({{ $ruta->distancia_km }} km)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('bus_ruta_id')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label class="journey-label">Conductor asignado <b>*</b></label>
                                    <div class="journey-input mt-1"><span><i class="fas fa-user"></i></span>
                                        <select name="conductor_id" required>
                                            <option value="">-- Seleccionar chofer --</option>
                                            @foreach ($conductores as $conductor)
                                                <option value="{{ $conductor->id_usuario }}" {{ old('conductor_id') == $conductor->id_usuario ? 'selected' : '' }}>
                                                    {{ $conductor->persona->nombre_persona ?? '' }} {{ $conductor->persona->apellido_persona ?? '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('conductor_id')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label class="journey-label">Turno correspondiente <b>*</b></label>
                                    <div class="journey-input mt-1"><span><i class="fas fa-clock"></i></span>
                                        <select name="turno" required>
                                            @foreach (['mañana' => 'Mañana (06:00 AM - 12:59 PM)', 'tarde' => 'Tarde (01:00 PM - 05:59 PM)', 'noche' => 'Noche (06:00 PM - 11:59 PM)'] as $value => $label)
                                                <option value="{{ $value }}" {{ old('turno', $turnoSugerido) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('turno')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </section>

                        <section class="journey-card rounded-2xl border p-5 shadow-sm">
                            <div class="journey-card-heading mb-5">
                                <span class="journey-section-icon"><i class="fas fa-calendar-alt"></i></span>
                                <div><h2 class="text-base font-extrabold" style="color:var(--text-main);">Planificación de horarios</h2><p>Define los horarios en los que se realizará el viaje.</p></div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div><label class="journey-label">Hora de inicio <b>*</b></label><div class="journey-input mt-1"><span><i class="fas fa-clock"></i></span><input type="time" value="06:00"></div></div>
                                <div><label class="journey-label">Hora de fin <b>*</b></label><div class="journey-input mt-1"><span><i class="fas fa-clock"></i></span><input type="time" value="18:00"></div></div>
                                <div><label class="journey-label">Días de operación <b>*</b></label><div class="journey-days mt-1">@foreach(['L','M','M','J','V','S','D'] as $day)<span>{{ $day }}</span>@endforeach</div></div>
                            </div>
                        </section>

                        <section class="journey-card rounded-2xl border p-5 shadow-sm">
                            <div class="journey-card-heading mb-4"><span class="journey-section-icon"><i class="far fa-file-alt"></i></span><div><h2 class="text-base font-extrabold" style="color:var(--text-main);">Descripción <small>(opcional)</small></h2><p>Agrega observaciones o detalles adicionales del viaje.</p></div></div>
                            <textarea rows="3" maxlength="500" placeholder="Agrega observaciones o detalles adicionales del viaje..."></textarea>
                            <div class="mt-1 text-right text-xs text-gray-500">0/500</div>
                        </section>
                    </div>

                </div>
                <div class="mx-auto flex w-full max-w-4xl justify-end gap-3 border-t pt-5" style="border-color:var(--border-color);">
                    <a href="{{ route('admin.transporte.maestros.bus_viajes.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold transition hover:bg-gray-100 dark:hover:bg-gray-800" style="border-color:var(--border-color);color:var(--text-main);"><i class="fas fa-times text-xs"></i> Cancelar</a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95"><i class="fas fa-save text-xs"></i> Guardar viaje</button>
                </div>
            </form>
        </div>
    </div>

@push('styles')
    <style>
        .journey-card { background:var(--bg-card); border-color:var(--border-color); }
        .journey-page-icon { display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;border-radius:.75rem;background:linear-gradient(145deg,#dc2626,#991b1b);color:#fff;box-shadow:0 8px 18px rgba(153,27,27,.2); }
        .journey-card-heading { display:flex;align-items:center;gap:.75rem; }
        .journey-card-heading p { margin-top:.15rem;color:var(--text-muted);font-size:.65rem; }
        .journey-card-heading small { color:var(--text-muted);font-size:.65rem;font-weight:500; }
        .journey-section-icon { display:inline-flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;flex:0 0 auto;border-radius:.7rem;background:linear-gradient(145deg,#7f1d1d,#450a0a);color:#fff; }
        .journey-label { color:var(--text-main);font-size:.72rem;font-weight:700; }
        .journey-label b { color:#ef4444; }
        .journey-input { display:flex;align-items:center;min-height:2.6rem;overflow:hidden;border:1px solid var(--input-border);border-radius:.65rem;background:var(--input-bg); }
        .journey-input > span { padding:0 .75rem;color:var(--text-muted);font-size:.8rem; }
        .journey-input input,.journey-input select { width:100%;border:0!important;background:transparent!important;color:var(--text-main)!important;outline:0;font-size:.7rem; }
        .journey-form textarea { width:100%;resize:vertical;border:1px solid var(--input-border);border-radius:.65rem;background:var(--input-bg)!important;color:var(--text-main)!important;font-size:.75rem; }
        .journey-days { display:flex;gap:.25rem; }
        .journey-days span { display:inline-flex;align-items:center;justify-content:center;width:1.9rem;height:1.9rem;border:1px solid var(--border-color);border-radius:.5rem;background:var(--input-bg);color:var(--text-main);font-size:.65rem;font-weight:800; }
        .journey-days span:nth-child(-n+5) { background:#450a0a;border-color:#7f1d1d;color:#fff; }
        .field-error { margin-top:.25rem;color:#ef4444;font-size:.7rem;font-weight:700; }
    </style>
@endpush

</x-app-layout>
