<x-app-layout>
    <x-slot name="header">
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--text-main);">Editar Asignación de Viaje</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-gray-400">
            Modifique la unidad, la ruta o el chofer asignado para este viaje.
            </p>
        </div>
    </x-slot>

    @include('components.alert')

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
            <div class="rounded-2xl border p-6 shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
                <form id="formEditar" action="{{ route('admin.transporte.maestros.bus_viajes.update', $busViaje) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-bold" style="color: var(--text-main);">Autobús / Unidad Asignada</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-bus"></i></span>
                            <select name="vehiculo_id" id="vehiculoSelect"
                                class="w-full rounded-xl border py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 @error('vehiculo_id') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar Vehículo --</option>
                                @foreach ($vehiculos as $vehiculo)
                                    <option value="{{ $vehiculo->id }}"
                                        {{ old('vehiculo_id', $busViaje->vehiculo_id) == $vehiculo->id ? 'selected' : '' }}>
                                        {{ $vehiculo->unidad ?? 'Unidad sin nombre' }} (Placa: {{ $vehiculo->placa }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('vehiculo_id')
                            <div class="mt-1 text-sm font-bold text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-bold" style="color: var(--text-main);">Ruta de Transporte</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-route"></i></span>
                            <select name="bus_ruta_id" id="rutaSelect"
                                class="w-full rounded-xl border py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 @error('bus_ruta_id') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar Ruta --</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}" data-distancia="{{ $ruta->distancia_km }}"
                                        data-paradas="{{ $ruta->paradas_count ?? $ruta->paradas->count() }}"
                                        {{ old('bus_ruta_id', $busViaje->bus_ruta_id) == $ruta->id ? 'selected' : '' }}>
                                        {{ $ruta->nombre }} ({{ $ruta->distancia_km }} km)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('bus_ruta_id')
                            <div class="mt-1 text-sm font-bold text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-bold" style="color: var(--text-main);">Conductor Asignado</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-id-card"></i></span>
                            <select name="conductor_id" id="conductorSelect"
                                class="w-full rounded-xl border py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 @error('conductor_id') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar Chofer --</option>
                                @foreach ($conductores as $conductor)
                                    <option value="{{ $conductor->id_usuario }}"
                                        {{ old('conductor_id', $busViaje->conductor_id) == $conductor->id_usuario ? 'selected' : '' }}>
                                        {{ $conductor->persona->nombre_persona ?? '' }}
                                        {{ $conductor->persona->apellido_persona ?? '' }} (C.I:
                                        {{ $conductor->persona->cedula ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('conductor_id')
                            <div class="mt-1 text-sm font-bold text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="mb-1 block text-sm font-bold" style="color: var(--text-main);">Turno Correspondiente</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-clock"></i></span>
                            <select name="turno" id="turnoSelect"
                                class="w-full rounded-xl border py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 @error('turno') border-red-500 @enderror" required>
                                <option value="mañana" {{ old('turno', $busViaje->turno) === 'mañana' ? 'selected' : '' }}>
                                    Mañana (06:00 AM - 12:59 AM)</option>
                                <option value="tarde" {{ old('turno', $busViaje->turno) === 'tarde' ? 'selected' : '' }}>
                                    Tarde (01:00 PM - 05:59 PM)</option>
                                <option value="noche" {{ old('turno', $busViaje->turno) === 'noche' ? 'selected' : '' }}>
                                    Noche (06:00 PM - 11:59 PM)</option>
                            </select>
                        </div>
                        @error('turno')
                            <div class="mt-1 text-sm font-bold text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-6 flex items-center justify-between border-t pt-5" style="border-color: var(--border-color);">
                        <a href="{{ route('admin.transporte.maestros.bus_viajes.index') }}"
                            class="rounded-xl border px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</a>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-bold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-save text-xs"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="rounded-2xl border p-6 shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
                <h3 class="mb-4 text-lg font-bold" style="color: var(--text-main);">
                    <i class="fas fa-info-circle mr-1 text-red-600"></i> Resumen del Viaje #{{ $busViaje->id }}
                </h3>

                <div class="mb-4 rounded-xl border p-4" style="background-color: var(--bg-body); border-color: var(--border-color);">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Estado Actual:</span>
                        @if($busViaje->estado === 'programado')
                            <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">Programado</span>
                        @elseif($busViaje->estado === 'en_curso')
                            <span class="rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800">En Curso</span>
                        @elseif($busViaje->estado === 'finalizado')
                            <span class="rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Finalizado</span>
                        @else
                            <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ ucfirst($busViaje->estado) }}</span>
                        @endif
                    </div>
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Identificador Firebase:</span>
                        <code style="color:#0f172a; font-weight:600;">{{ $busViaje->firebase_id ?? 'N/A' }}</code>
                    </div>
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Distancia de la Ruta:</span>
                        <strong id="infoDistancia" style="color:#0f172a;">-- km</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Cantidad de Paradas:</span>
                        <strong id="infoParadas" style="color:#0f172a;">--</strong>
                    </div>
                </div>

                <div class="rounded-xl bg-blue-50 p-4 text-sm text-blue-800 dark:bg-blue-950/40 dark:text-blue-200">
                    <i class="fas fa-lightbulb mr-1"></i>
                    <strong>Nota:</strong> Los cambios realizados se reflejarán de inmediato en la aplicación del conductor asignado.
                </div>
            </div>
        </div>
    </div>
    </div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rutaSelect = document.getElementById('rutaSelect');
            const infoDistancia = document.getElementById('infoDistancia');
            const infoParadas = document.getElementById('infoParadas');

            function actualizarInfoRuta() {
                const selectedOption = rutaSelect.options[rutaSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const distancia = selectedOption.getAttribute('data-distancia') || '0';
                    const paradas = selectedOption.getAttribute('data-paradas') || '0';
                    infoDistancia.textContent = `${distancia} km`;
                    infoParadas.textContent = `${paradas} paradas`;
                } else {
                    infoDistancia.textContent = '-- km';
                    infoParadas.textContent = '--';
                }
            }

            rutaSelect.addEventListener('change', actualizarInfoRuta);
            actualizarInfoRuta();
        });
    </script>
@endpush
</x-app-layout>