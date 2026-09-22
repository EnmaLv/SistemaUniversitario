<x-app-layout>
    @include('components.alert')

    <div class="min-h-[calc(100vh-4rem)] pb-12 pt-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Encabezado --}}
            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div class="flex items-center gap-4">
                    <span class="journey-page-icon"><i class="fas fa-bus"></i></span>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color:var(--text-main);">
                            Editar viaje #{{ $busViaje->id }}
                        </h1>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-sm">
                            Modifica la unidad, ruta o conductor asignado para este viaje.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.transporte.maestros.bus_viajes.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-500 hover:text-red-600"
                    style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            <form id="formEditar"
                action="{{ route('admin.transporte.maestros.bus_viajes.update', $busViaje) }}"
                method="POST" class="journey-form rd-prevent-double-submit">
                @csrf
                @method('PUT')

                <div class="mx-auto w-full max-w-4xl space-y-5">

                    {{-- Card: Información del viaje --}}
                    <section class="journey-card rounded-2xl border p-5 shadow-sm">
                        <div class="journey-card-heading mb-5">
                            <span class="journey-section-icon"><i class="fas fa-bus"></i></span>
                            <div>
                                <h2 class="text-base font-extrabold" style="color:var(--text-main);">
                                    Información del viaje
                                </h2>
                                <p>Modifica la unidad, ruta y conductor responsable.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            {{-- Vehículo --}}
                            <div>
                                <label class="journey-label">
                                    Autobús / Unidad asignada <b class="text-red-500">*</b>
                                </label>
                                <div class="journey-input mt-1">
                                    <span><i class="fas fa-bus"></i></span>
                                    <select name="vehiculo_id" required>
                                        <option value="">-- Seleccionar vehículo --</option>
                                        @foreach ($vehiculos as $vehiculo)
                                            <option value="{{ $vehiculo->id }}"
                                                {{ old('vehiculo_id', $busViaje->vehiculo_id) == $vehiculo->id ? 'selected' : '' }}>
                                                {{ $vehiculo->unidad ?? 'Unidad sin nombre' }} — {{ $vehiculo->placa }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('vehiculo_id')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Ruta --}}
                            <div>
                                <label class="journey-label">
                                    Ruta de transporte <b class="text-red-500">*</b>
                                </label>
                                <div class="journey-input mt-1">
                                    <span><i class="fas fa-route"></i></span>
                                    <select name="bus_ruta_id" id="rutaSelect" required>
                                        <option value="">-- Seleccionar ruta --</option>
                                        @foreach ($rutas as $ruta)
                                            <option value="{{ $ruta->id }}"
                                                data-distancia="{{ $ruta->distancia_km }}"
                                                data-paradas="{{ $ruta->paradas->count() }}"
                                                {{ old('bus_ruta_id', $busViaje->bus_ruta_id) == $ruta->id ? 'selected' : '' }}>
                                                {{ $ruta->nombre }} ({{ $ruta->distancia_km }} km)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('bus_ruta_id')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Conductor --}}
                            <div>
                                <label class="journey-label">
                                    Conductor asignado <b class="text-red-500">*</b>
                                </label>
                                <div class="journey-input mt-1">
                                    <span><i class="fas fa-user"></i></span>
                                    <select name="conductor_id" required>
                                        <option value="">-- Seleccionar chofer --</option>
                                        @foreach ($conductores as $conductor)
                                            <option value="{{ $conductor->id_usuario }}"
                                                {{ old('conductor_id', $busViaje->conductor_id) == $conductor->id_usuario ? 'selected' : '' }}>
                                                {{ $conductor->persona->nombre_persona ?? '' }}
                                                {{ $conductor->persona->apellido_persona ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('conductor_id')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Turno --}}
                            <div>
                                <label class="journey-label">
                                    Turno del viaje <b class="text-red-500">*</b>
                                </label>
                                <div class="journey-input mt-1">
                                    <span><i class="fas fa-clock"></i></span>
                                    <select name="turno" id="turnoSelect" required>
                                        <option value="mañana" {{ old('turno', $busViaje->turno) === 'mañana' ? 'selected' : '' }}>
                                            Mañana · 06:00 AM – 12:59 PM
                                        </option>
                                        <option value="tarde" {{ old('turno', $busViaje->turno) === 'tarde' ? 'selected' : '' }}>
                                            Tarde · 01:00 PM – 05:59 PM
                                        </option>
                                        <option value="noche" {{ old('turno', $busViaje->turno) === 'noche' ? 'selected' : '' }}>
                                            Noche · 06:00 PM – 11:59 PM
                                        </option>
                                    </select>
                                </div>
                                @error('turno')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- Card: Info de la ruta --}}
                    <section id="cardInfoRuta" class="journey-card rounded-2xl border p-5 shadow-sm hidden">
                        <div class="journey-card-heading mb-4">
                            <span class="journey-section-icon"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h2 class="text-base font-extrabold" style="color:var(--text-main);">
                                    Detalles de la ruta
                                </h2>
                                <p>Horarios y paradas registradas para la ruta seleccionada.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
                            <div class="rounded-xl border p-3"
                                style="border-color:var(--border-color); background-color:rgba(0,0,0,0.02);">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    Distancia
                                </span>
                                <span id="infoDistancia" class="text-lg font-extrabold" style="color:var(--text-main);">—</span>
                            </div>
                            <div class="rounded-xl border p-3"
                                style="border-color:var(--border-color); background-color:rgba(0,0,0,0.02);">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    Paradas
                                </span>
                                <span id="infoParadas" class="text-lg font-extrabold" style="color:var(--text-main);">—</span>
                            </div>
                            <div class="rounded-xl border p-3"
                                style="border-color:var(--border-color); background-color:rgba(0,0,0,0.02);">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    Horarios configurados
                                </span>
                                <span id="infoHorariosCount" class="text-lg font-extrabold" style="color:var(--text-main);">—</span>
                            </div>
                        </div>

                        <div>
                            <label class="journey-label mb-2 block">Horarios registrados en la ruta</label>
                            <div id="listaHorariosRuta" class="flex flex-wrap gap-2"></div>
                        </div>
                    </section>

                    {{-- Card: Estado del viaje (solo lectura) --}}
                    <section class="journey-card rounded-2xl border p-5 shadow-sm">
                        <div class="journey-card-heading mb-4">
                            <span class="journey-section-icon"><i class="fas fa-clipboard-check"></i></span>
                            <div>
                                <h2 class="text-base font-extrabold" style="color:var(--text-main);">
                                    Estado del viaje
                                </h2>
                                <p>Este dato no se puede modificar desde aquí.</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            @php
                                $estadoBadge = match ($busViaje->estado) {
                                    'programado' => ['Programado', 'amber'],
                                    'en_curso'   => ['En curso', 'blue'],
                                    'finalizado' => ['Finalizado', 'emerald'],
                                    'cancelado'  => ['Cancelado', 'rose'],
                                    default      => [ucfirst($busViaje->estado), 'gray'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-bold
                                bg-{{ $estadoBadge[1] }}-100 text-{{ $estadoBadge[1] }}-800">
                                <i class="fas fa-circle text-[6px]"></i>
                                {{ $estadoBadge[0] }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Firebase ID:
                                <code style="color:var(--text-main);">{{ $busViaje->firebase_id ?? 'N/A' }}</code>
                            </span>
                        </div>
                    </section>
                </div>

                <div class="mx-auto mt-5 flex w-full max-w-4xl justify-end gap-3 border-t pt-5"
                    style="border-color:var(--border-color);">
                    <a href="{{ route('admin.transporte.maestros.bus_viajes.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold transition hover:bg-gray-100 dark:hover:bg-gray-800"
                        style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-times text-xs"></i> Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                        <i class="fas fa-save text-xs"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .journey-card {
            background: var(--bg-card);
            border-color: var(--border-color);
        }

        .journey-page-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: .75rem;
            background: linear-gradient(145deg, #dc2626, #991b1b);
            color: #fff;
            box-shadow: 0 8px 18px rgba(153, 27, 27, .2);
        }

        .journey-card-heading {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .journey-card-heading p {
            margin-top: .15rem;
            color: var(--text-muted);
            font-size: .65rem;
        }

        .journey-section-icon {
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

        .journey-label {
            color: var(--text-main);
            font-size: .72rem;
            font-weight: 700;
        }

        .journey-input {
            display: flex;
            align-items: center;
            min-height: 2.6rem;
            overflow: hidden;
            border: 1px solid var(--input-border);
            border-radius: .65rem;
            background: var(--input-bg);
        }

        .journey-input > span {
            padding: 0 .75rem;
            color: var(--text-muted);
            font-size: .8rem;
        }

        .journey-input input,
        .journey-input select {
            width: 100%;
            border: 0 !important;
            background: transparent !important;
            color: var(--text-main) !important;
            outline: 0;
            font-size: .75rem;
        }

        .field-error {
            margin-top: .25rem;
            color: #ef4444;
            font-size: .7rem;
            font-weight: 700;
        }

        .horario-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .7rem;
            border-radius: .5rem;
            font-size: .72rem;
            font-weight: 700;
            border: 1px solid var(--border-color);
            background: rgba(0, 0, 0, 0.02);
        }

        .horario-badge.entrada {
            border-color: #16a34a;
            color: #15803d;
            background: rgba(22, 163, 74, .08);
        }

        .horario-badge.salida {
            border-color: #dc2626;
            color: #b91c1c;
            background: rgba(220, 38, 38, .08);
        }
    </style>

    <script>
        window.RUTAS_DATA = @json($rutas->mapWithKeys(function ($r) {
            return [$r->id => [
                'distancia' => $r->distancia_km,
                'paradas'   => $r->paradas->count(),
                'horarios'  => $r->horarios->map(fn($h) => [
                    'hora' => $h->hora_salida,
                    'tipo' => $h->tipo_viaje,
                ])->values(),
            ]];
        }));

        document.addEventListener('DOMContentLoaded', function () {
            const rutaSelect  = document.getElementById('rutaSelect');
            const cardInfo    = document.getElementById('cardInfoRuta');
            const infoDist    = document.getElementById('infoDistancia');
            const infoPar     = document.getElementById('infoParadas');
            const infoHorCnt  = document.getElementById('infoHorariosCount');
            const listaHor    = document.getElementById('listaHorariosRuta');

            function renderInfoRuta() {
                const id = rutaSelect.value;
                const data = window.RUTAS_DATA[id];

                if (!data) {
                    cardInfo.classList.add('hidden');
                    return;
                }

                cardInfo.classList.remove('hidden');
                infoDist.textContent   = data.distancia + ' km';
                infoPar.textContent    = data.paradas;
                infoHorCnt.textContent = data.horarios.length;

                if (data.horarios.length === 0) {
                    listaHor.innerHTML = `<span class="text-xs text-gray-500">Esta ruta no tiene horarios configurados todavía.</span>`;
                } else {
                    listaHor.innerHTML = data.horarios.map(h => {
                        const tipoLabel = h.tipo === 'entrada' ? 'Entrada' : 'Salida';
                        const icono = h.tipo === 'entrada' ? 'fa-sign-in-alt' : 'fa-sign-out-alt';
                        return `<span class="horario-badge ${h.tipo}">
                            <i class="fas ${icono} text-[10px]"></i>
                            ${h.hora} · ${tipoLabel}
                        </span>`;
                    }).join('');
                }
            }

            rutaSelect.addEventListener('change', renderInfoRuta);
            renderInfoRuta();
        });
    </script>
</x-app-layout>