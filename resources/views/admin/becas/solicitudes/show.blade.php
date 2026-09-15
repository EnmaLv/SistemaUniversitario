<x-app-layout>

    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- CABECERA UNIFICADA --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <a href="{{ route('admin.becas.solicitudes.index') }}" class="inline-flex items-center text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-widest hover:text-red-650 transition-colors mb-2">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a solicitudes
                    </a>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Detalle de Solicitud de Beca
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Expediente socioeconómico del estudiante C.I. <strong>{{ $solicitud->persona->cedula_persona }}</strong>.
                    </p>
                </div>
                
                {{-- Badge de Estado Actual --}}
                <div class="flex items-center">
                    <span class="text-xs font-black px-4 py-2 rounded-xl border uppercase tracking-widest {{ $solicitud->estado_badge }}">
                        Estado: {{ $solicitud->estado_texto }}
                    </span>
                </div>
            </div>

            @include('components.alert')

            {{-- SECCIÓN 1: DATOS GENERALES Y ACADÉMICOS --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border-l-8 border-red-700 overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700 bg-slate-50/50 dark:bg-gray-900/10">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-user-graduate text-red-700"></i> Datos Académicos y de Postulación
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Estudiante</span>
                        <span class="font-bold text-slate-800 dark:text-white">
                            {{ $solicitud->persona->nombre_persona }} {{ $solicitud->persona->apellido_persona }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Cédula de Identidad</span>
                        <span class="font-bold text-slate-800 dark:text-white">
                            C.I. {{ $solicitud->persona->cedula_persona }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Programa / PNF</span>
                        <span class="font-bold text-slate-850 dark:text-slate-200">
                            {{ $solicitud->persona->personaPnf->first()->pnf->nombre_pnf ?? 'No registrado' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Jornada</span>
                        <span class="font-semibold text-slate-800 dark:text-white">
                            {{ $solicitud->jornada->nombre_jornada ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Beneficio Ofrecido</span>
                        <span class="font-bold text-red-700 dark:text-red-400">
                            {{ $solicitud->beneficio->nombre_beneficio ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Lapso Académico</span>
                        <span class="font-semibold text-slate-800 dark:text-white">
                            {{ $solicitud->lapso->codigo ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Tipo de Solicitud</span>
                        <span class="font-semibold capitalize text-slate-850 dark:text-slate-200">
                            {{ $solicitud->tipo_solicitud }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Índice Académico</span>
                        <span class="font-black text-slate-800 dark:text-white text-base">
                            {{ $solicitud->indice_academico }} pts
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Registro Patria</span>
                        <span class="font-semibold text-slate-800 dark:text-white">
                            {{ $solicitud->registro_patria ? 'Sí' : 'No' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 2: VIVIENDA Y TRANSPORTE --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border-l-8 border-slate-650 overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700 bg-slate-50/50 dark:bg-gray-900/10">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-route text-slate-650"></i> Datos de Vivienda y Transporte
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Paga Alquiler / Residencia</span>
                        <span class="font-semibold text-slate-800 dark:text-white">
                            {{ ($solicitud->vivienda_transporte['residencia']['paga'] ?? false) ? 'Sí' : 'No' }}
                        </span>
                    </div>
                    @if ($solicitud->vivienda_transporte['residencia']['paga'] ?? false)
                        <div>
                            <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Monto Alquiler</span>
                            <span class="font-semibold text-slate-800 dark:text-white">
                                {{ number_format($solicitud->vivienda_transporte['residencia']['monto'] ?? 0, 2) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Viaje Diario a la Universidad</span>
                        <span class="font-semibold text-slate-800 dark:text-white">
                            {{ ($solicitud->vivienda_transporte['viaje_diario']['es_diario'] ?? true) ? 'Sí' : 'No' }}
                        </span>
                    </div>
                    @if (!($solicitud->vivienda_transporte['viaje_diario']['es_diario'] ?? true))
                        <div>
                            <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Frecuencia de Viaje (Semanal)</span>
                            <span class="font-semibold text-slate-800 dark:text-white">
                                {{ $solicitud->vivienda_transporte['viaje_diario']['frecuencia_semanal'] ?? 'N/A' }} días
                            </span>
                        </div>
                    @endif
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Tiempo de Traslado (Ida)</span>
                        <span class="font-semibold text-slate-800 dark:text-white">
                            {{ $solicitud->vivienda_transporte['tiempo_traslado']['horas'] ?? 0 }} hrs, 
                            {{ $solicitud->vivienda_transporte['tiempo_traslado']['minutos'] ?? 0 }} min
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Gasto Mensual de Pasaje</span>
                        <span class="font-bold text-slate-800 dark:text-white text-base">
                            {{ number_format($solicitud->gasto_pasaje, 2) }}
                        </span>
                    </div>
                    @if ($solicitud->direccion_temporal)
                        <div class="sm:col-span-2 md:col-span-3">
                            <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Dirección Temporal</span>
                            <span class="font-semibold text-slate-800 dark:text-white bg-slate-50 dark:bg-gray-700/30 px-3 py-2 rounded-xl block border dark:border-gray-700 mt-1">
                                {{ $solicitud->direccion_temporal }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- SECCIÓN 3: DATOS SOCIOECONÓMICOS --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border-l-8 border-slate-650 overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700 bg-slate-50/50 dark:bg-gray-900/10">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-chart-bar text-slate-650"></i> Datos Socioeconómicos del Estudiante
                    </h3>
                </div>
                <div class="p-6 text-sm">
                    {{-- Vivienda --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-6 pb-6 border-b dark:border-gray-700">
                        <div>
                            <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Tipo de Vivienda</span>
                            <span class="font-bold text-slate-800 dark:text-white">
                                {{ $solicitud->datos_socioeconomicos['vivienda']['tipo'] ?? 'N/A' }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Tenencia</span>
                            <span class="font-bold text-slate-800 dark:text-white">
                                {{ $solicitud->datos_socioeconomicos['vivienda']['tenencia'] ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Distribución Interna</span>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach($solicitud->datos_socioeconomicos['vivienda']['distribucion'] ?? [] as $dKey => $dVal)
                                    @if($dVal > 0)
                                        <span class="text-xs bg-slate-100 dark:bg-gray-700 px-2 py-1 rounded-lg border dark:border-gray-600">
                                            {{ ucfirst($dKey) }}: <strong>{{ $dVal }}</strong>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Equipamiento --}}
                    <div class="mb-6 pb-6 border-b dark:border-gray-700">
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-2">Equipamiento (Electrodomésticos / Mobiliario)</span>
                        <div class="flex flex-wrap gap-2">
                            @php $hasEquip = false; @endphp
                            @foreach($solicitud->datos_socioeconomicos['equipamiento'] ?? [] as $eKey => $eVal)
                                @if($eVal > 0)
                                    @php $hasEquip = true; @endphp
                                    <span class="text-xs bg-slate-50 dark:bg-gray-700/40 px-2.5 py-1 rounded-xl border dark:border-gray-600/50">
                                        {{ str_replace('_', ' ', ucfirst($eKey)) }}: <strong>{{ $eVal }}</strong>
                                    </span>
                                @endif
                            @endforeach
                            @if(!$hasEquip)
                                <span class="text-xs text-gray-500">Ningún equipamiento registrado.</span>
                            @endif
                        </div>
                    </div>

                    {{-- Servicios --}}
                    <div class="mb-6 pb-6 border-b dark:border-gray-700">
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-2">Servicios Públicos Disponibles</span>
                        <div class="flex flex-wrap gap-2">
                            @php $hasServ = false; @endphp
                            @foreach($solicitud->datos_socioeconomicos['servicios'] ?? [] as $sKey => $sVal)
                                @if($sVal)
                                    @php $hasServ = true; @endphp
                                    <span class="text-xs bg-emerald-50 border border-emerald-200/50 text-emerald-700 dark:bg-emerald-950/20 dark:border-emerald-900/30 dark:text-emerald-400 px-2.5 py-1 rounded-xl font-medium">
                                        <i class="fas fa-check-circle mr-1 text-[10px]"></i> {{ str_replace('_', ' ', ucfirst($sKey)) }}
                                    </span>
                                @endif
                            @endforeach
                            @if(!$hasServ)
                                <span class="text-xs text-gray-500">Ningún servicio público disponible.</span>
                            @endif
                        </div>
                    </div>

                    {{-- Carga Familiar --}}
                    <div>
                        <span class="block text-xs font-bold text-gray-450 uppercase mb-3">Carga Familiar del Estudiante</span>
                        <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-800">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-gray-700 text-xs">
                                <thead class="bg-slate-55/40 dark:bg-gray-700/40 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Parentesco</th>
                                        <th class="px-4 py-3 text-left">Nombre y Apellido</th>
                                        <th class="px-4 py-3 text-center w-20">Edad</th>
                                        <th class="px-4 py-3 text-left">Nivel Educativo</th>
                                        <th class="px-4 py-3 text-left">Ocupación</th>
                                        <th class="px-4 py-3 text-right w-36">Ingreso Mensual</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                                    @php $totalIngresos = 0; @endphp
                                    @forelse($solicitud->datos_socioeconomicos['carga_familiar'] ?? [] as $familiar)
                                        @php $totalIngresos += doubleval($familiar['ingreso_mens'] ?? 0); @endphp
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/10 transition-all">
                                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $familiar['parentesco'] ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $familiar['nombre_apellido'] ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300">{{ $familiar['edad'] ?? 'N/A' }} años</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $familiar['nivel_instituto'] ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $familiar['ocupacion'] ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-slate-850 dark:text-slate-200">{{ number_format(doubleval($familiar['ingreso_mens'] ?? 0), 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">Ningún miembro registrado en la carga familiar.</td>
                                        </tr>
                                    @endforelse
                                    @if(count($solicitud->datos_socioeconomicos['carga_familiar'] ?? []) > 0)
                                        <tr class="bg-slate-50/50 dark:bg-gray-700/20 font-bold">
                                            <td colspan="5" class="px-4 py-3 text-right text-slate-800 dark:text-slate-200 uppercase tracking-wide">Total Ingreso Familiar:</td>
                                            <td class="px-4 py-3 text-right text-base text-red-700 dark:text-red-400">{{ number_format($totalIngresos, 2) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN DE VERIFICACIÓN / AUDITORÍA --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border-l-8 {{ $solicitud->estado === 0 ? 'border-yellow-500' : ($solicitud->estado === 1 ? 'border-green-600' : 'border-red-600') }} overflow-hidden">
                <div class="p-6 border-b dark:border-gray-700 bg-slate-50/50 dark:bg-gray-900/10">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-check-double {{ $solicitud->estado === 0 ? 'text-yellow-500' : ($solicitud->estado === 1 ? 'text-green-600' : 'text-red-600') }}"></i> Auditoría y Verificación de Solicitud
                    </h3>
                </div>
                
                <div class="p-6 text-sm">
                    @if ($solicitud->estado === 0)
                        {{-- Formulario para aprobar / rechazar --}}
                        <form action="{{ route('admin.becas.solicitudes.verificar', $solicitud->id) }}" method="POST" id="form-verificar">
                            @csrf
                            @method('PUT')

                            <p class="mb-4 text-gray-500 dark:text-gray-400 text-xs sm:text-sm">
                                Revisa los datos cargados del estudiante. Puedes aprobar directamente o rechazar la solicitud indicando los motivos.
                            </p>

                            <div class="mb-6">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Decisión *</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer bg-green-50 border border-green-200/50 hover:bg-green-100/50 dark:bg-green-950/20 dark:border-green-900/30 px-5 py-3 rounded-2xl transition-all">
                                        <input type="radio" name="estado" value="1" onchange="toggleComentario()" required class="text-green-650 focus:ring-green-500" />
                                        <span class="font-bold text-green-700 dark:text-green-400 uppercase tracking-wider text-xs">Aprobar Solicitud</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-red-50 border border-red-200/50 hover:bg-red-100/50 dark:bg-red-950/20 dark:border-red-900/30 px-5 py-3 rounded-2xl transition-all">
                                        <input type="radio" name="estado" value="2" onchange="toggleComentario()" required class="text-red-700 focus:ring-red-500" />
                                        <span class="font-bold text-red-700 dark:text-red-400 uppercase tracking-wider text-xs">Rechazar Solicitud</span>
                                    </label>
                                </div>
                                @error('estado') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-6 hidden" id="div-comentario">
                                <label for="comentario_verificador" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Motivo del rechazo *</label>
                                <textarea name="comentario_verificador" id="comentario_verificador" rows="3"
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                    placeholder="Indique detalladamente el motivo de rechazo de la solicitud..."></textarea>
                                @error('comentario_verificador') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end gap-3 mt-6">
                                <a href="{{ route('admin.becas.solicitudes.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-650 dark:text-gray-300 font-bold rounded-xl text-sm transition-all border dark:border-gray-600">
                                    Cancelar
                                </a>
                                <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-600 text-white font-bold rounded-xl text-sm transition-all shadow-md">
                                    Guardar Decisión <i class="fas fa-check-circle ml-2 text-xs"></i>
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Detalle del auditor que verificó la solicitud --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                            <div>
                                <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Verificado Por</span>
                                <span class="font-bold text-slate-800 dark:text-white">
                                    {{ $solicitud->verificador->persona->nombre_persona ?? 'N/A' }} {{ $solicitud->verificador->persona->apellido_persona ?? '' }}
                                </span>
                                <span class="block text-[10px] text-gray-400 font-medium">({{ $solicitud->verificador->username ?? 'N/A' }})</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Fecha de Verificación</span>
                                <span class="font-semibold text-slate-800 dark:text-white">
                                    {{ $solicitud->fecha_verificacion ? $solicitud->fecha_verificacion->format('d/m/Y g:i A') : 'N/A' }}
                                </span>
                            </div>
                            @if ($solicitud->estado === 2)
                                <div class="sm:col-span-2 md:col-span-3">
                                    <span class="block text-xs font-bold text-gray-450 uppercase mb-1">Comentario o Motivo del Rechazo</span>
                                    <span class="font-semibold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/20 px-3 py-2 rounded-xl block border border-rose-200/50 dark:border-rose-900/30 mt-1">
                                        {{ $solicitud->comentario_verificador ?? 'Sin comentarios registrados.' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            function toggleComentario() {
                const radioRechazar = document.querySelector('input[name="estado"][value="2"]');
                const divComentario = document.getElementById('div-comentario');
                const inputComentario = document.getElementById('comentario_verificador');

                if (radioRechazar && radioRechazar.checked) {
                    divComentario.classList.remove('hidden');
                    inputComentario.setAttribute('required', 'required');
                } else {
                    divComentario.classList.add('hidden');
                    inputComentario.removeAttribute('required');
                    inputComentario.value = '';
                }
            }
        </script>
    @endpush

</x-app-layout>
