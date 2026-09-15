<x-app-layout>

    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- CABECERA UNIFICADA --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <a href="{{ route('admin.becas.solicitudes.index') }}" class="inline-flex items-center text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-widest hover:text-red-600 transition-colors mb-2">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a solicitudes
                    </a>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Registrar Solicitud de Beca
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Completa el expediente del estudiante para registrar su postulación.
                    </p>
                </div>
            </div>

            @include('components.alert')

            {{-- CONTENEDOR PRINCIPAL --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border-l-8 border-red-700 overflow-hidden mb-6">
                
                {{-- INDICADOR DE PASOS --}}
                <div class="border-b dark:border-gray-700 bg-slate-55/30 dark:bg-slate-900/10 px-8 py-5">
                    <div class="flex items-center justify-between max-w-xl mx-auto">
                        <button type="button" onclick="goToStep(1)" id="step-tab-1" class="flex flex-col items-center text-center gap-2 group focus:outline-none">
                            <span id="step-circle-1" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-red-700 border-red-700 text-white shadow-md shadow-red-100 dark:shadow-none">1</span>
                            <span id="step-text-1" class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Académico</span>
                        </button>
                        <div class="flex-1 h-0.5 bg-slate-200 dark:bg-gray-700 mx-4 mb-6"></div>
                        <button type="button" onclick="goToStep(2)" id="step-tab-2" class="flex flex-col items-center text-center gap-2 group focus:outline-none">
                            <span id="step-circle-2" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-white border-slate-200 text-slate-400 dark:bg-gray-800 dark:border-gray-700">2</span>
                            <span id="step-text-2" class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Vivienda</span>
                        </button>
                        <div class="flex-1 h-0.5 bg-slate-200 dark:bg-gray-700 mx-4 mb-6"></div>
                        <button type="button" onclick="goToStep(3)" id="step-tab-3" class="flex flex-col items-center text-center gap-2 group focus:outline-none">
                            <span id="step-circle-3" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-white border-slate-200 text-slate-400 dark:bg-gray-800 dark:border-gray-700">3</span>
                            <span id="step-text-3" class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Socioeconómico</span>
                        </button>
                    </div>
                </div>

                <form id="solicitudForm" action="{{ route('admin.becas.solicitudes.store') }}" method="POST" class="p-8">
                    @csrf

                    {{-- PASO 1: DATOS ACADÉMICOS Y BÁSICOS --}}
                    <div id="step-content-1" class="step-pane">
                        <h4 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                            <span>📝</span> Datos del Estudiante y Académicos
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            {{-- Estudiante (Livewire Search-Select) --}}
                            @livewire('buscador-estudiante', ['selectedId' => old('id_persona')])

                            {{-- Jornada --}}
                            <div>
                                <label for="jornada_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Seleccionar Jornada Activa *</label>
                                <select name="jornada_id" id="jornada_id" required onchange="actualizarJornadaInfo()"
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none">
                                    <option value="">Seleccione la jornada...</option>
                                    @foreach ($jornadas as $jornada)
                                        <option value="{{ $jornada->id }}"
                                            data-beneficio-id="{{ $jornada->beneficio_id }}"
                                            data-beneficio-nombre="{{ $jornada->beneficio->nombre_beneficio ?? '' }}"
                                            data-lapso-id="{{ $jornada->lapsos_id }}"
                                            data-lapso-codigo="{{ $jornada->lapso->codigo ?? '' }}"
                                            {{ old('jornada_id') == $jornada->id ? 'selected' : '' }}>
                                            {{ $jornada->nombre_jornada }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jornada_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            {{-- Beneficio Asociado --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Beneficio Asociado</label>
                                <input type="text" id="beneficio_nombre_disabled" readonly
                                    class="w-full bg-gray-100 dark:bg-gray-750 text-gray-500 dark:text-gray-400 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 outline-none cursor-not-allowed"
                                    placeholder="Se autocompleta al elegir jornada" />
                                <input type="hidden" name="id_beneficio" id="id_beneficio" value="{{ old('id_beneficio') }}">
                                @error('id_beneficio') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Período Académico / Lapso --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Período Académico (Lapso)</label>
                                <input type="text" id="lapso_codigo_disabled" readonly
                                    class="w-full bg-gray-100 dark:bg-gray-750 text-gray-500 dark:text-gray-400 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 outline-none cursor-not-allowed"
                                    placeholder="Se autocompleta al elegir jornada" />
                                <input type="hidden" name="id_lapso" id="id_lapso" value="{{ old('id_lapso') }}">
                                @error('id_lapso') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            {{-- Tipo Solicitud --}}
                            <div>
                                <label for="tipo_solicitud" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tipo de Solicitud *</label>
                                <select name="tipo_solicitud" id="tipo_solicitud" required
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none">
                                    <option value="nueva" {{ old('tipo_solicitud') === 'nueva' ? 'selected' : '' }}>Nueva Solicitud</option>
                                    <option value="renovacion" {{ old('tipo_solicitud') === 'renovacion' ? 'selected' : '' }}>Renovación</option>
                                </select>
                                @error('tipo_solicitud') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Índice Académico --}}
                            <div>
                                <label for="indice_academico" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Índice Académico (0 - 20) *</label>
                                <input type="number" step="0.01" min="0" max="20" name="indice_academico" id="indice_academico" required
                                    value="{{ old('indice_academico') }}"
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                    placeholder="Ej: 16.50" />
                                @error('indice_academico') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Registro Patria --}}
                            <div class="flex items-center h-full pt-6">
                                <label class="relative flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="registro_patria" id="registro_patria" value="1" 
                                        {{ old('registro_patria') ? 'checked' : '' }}
                                        class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none dark:bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-700"></div>
                                    <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">¿Registrado en Sistema Patria?</span>
                                </label>
                                @error('registro_patria') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button type="button" onclick="goToStep(2)" class="px-6 py-2.5 bg-red-700 hover:bg-red-600 text-white font-bold rounded-xl text-sm transition-all shadow-md">
                                Siguiente paso <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- PASO 2: VIVIENDA Y TRANSPORTE --}}
                    <div id="step-content-2" class="step-pane hidden">
                        <h4 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                            <span>🏠</span> Datos de Vivienda y Transporte
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            {{-- Gasto Pasaje --}}
                            <div>
                                <label for="gasto_pasaje" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Gasto Mensual Estimado en Pasaje *</label>
                                <input type="number" step="0.01" min="0" name="gasto_pasaje" id="gasto_pasaje" required
                                    value="{{ old('gasto_pasaje') }}"
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                    placeholder="Monto en pasajes" />
                                @error('gasto_pasaje') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Dirección Temporal --}}
                            <div>
                                <label for="direccion_temporal" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Dirección Temporal (Si vive alquilado fuera)</label>
                                <input type="text" name="direccion_temporal" id="direccion_temporal"
                                    value="{{ old('direccion_temporal') }}"
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                    placeholder="Indique dirección temporal..." />
                                @error('direccion_temporal') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Vivienda Transporte (Campos JSON) --}}
                        <div class="bg-gray-50/50 dark:bg-gray-900/10 p-6 rounded-2xl border dark:border-gray-700 mb-6">
                            <h5 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase mb-4 flex items-center gap-2">
                                <i class="fas fa-route text-red-700"></i> Residencia y Detalles del Traslado
                            </h5>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                {{-- Paga Residencia --}}
                                <div class="flex items-center gap-4">
                                    <label class="relative flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="vivienda_transporte[residencia][paga]" id="residencia_paga" value="1"
                                            {{ old('vivienda_transporte.residencia.paga') ? 'checked' : '' }}
                                            onchange="toggleResidenciaMonto()"
                                            class="sr-only peer" />
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none dark:bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-700"></div>
                                        <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">¿Paga residencia/alquiler?</span>
                                    </label>
                                </div>

                                {{-- Monto Residencia --}}
                                <div id="div_monto_residencia" class="hidden">
                                    <label for="residencia_monto" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Monto Mensual del Alquiler</label>
                                    <input type="number" step="0.01" min="0" name="vivienda_transporte[residencia][monto]" id="residencia_monto"
                                        value="{{ old('vivienda_transporte.residencia.monto') }}"
                                        class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                        placeholder="Monto pagado" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                {{-- Viaje Diario --}}
                                <div class="flex items-center gap-4">
                                    <label class="relative flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="vivienda_transporte[viaje_diario][es_diario]" id="viaje_diario_es_diario" value="1"
                                            {{ old('vivienda_transporte.viaje_diario.es_diario', '1') ? 'checked' : '' }}
                                            onchange="toggleViajeFrecuencia()"
                                            class="sr-only peer" />
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none dark:bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-700"></div>
                                        <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">¿Viaja diariamente a la universidad?</span>
                                    </label>
                                </div>

                                {{-- Frecuencia semanal --}}
                                <div id="div_frecuencia_viaje" class="hidden">
                                    <label for="viaje_frecuencia" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Frecuencia semanal (días de viaje)</label>
                                    <input type="number" min="1" max="7" name="vivienda_transporte[viaje_diario][frecuencia_semanal]" id="viaje_frecuencia"
                                        value="{{ old('vivienda_transporte.viaje_diario.frecuencia_semanal') }}"
                                        class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                        placeholder="Días por semana" />
                                </div>
                            </div>

                            {{-- Tiempo de traslado --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tiempo Estimado de Traslado (Trayecto de ida)</label>
                                <div class="flex items-center gap-4">
                                    <div class="flex-1">
                                        <div class="relative">
                                            <input type="number" min="0" max="23" name="vivienda_transporte[tiempo_traslado][horas]" 
                                                value="{{ old('vivienda_transporte.tiempo_traslado.horas', '0') }}"
                                                class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none pr-10" />
                                            <span class="absolute right-3 top-2.5 text-xs text-gray-450">hrs</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <input type="number" min="0" max="59" name="vivienda_transporte[tiempo_traslado][minutos]" 
                                                value="{{ old('vivienda_transporte.tiempo_traslado.minutos', '30') }}"
                                                class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none pr-10" />
                                            <span class="absolute right-3 top-2.5 text-xs text-gray-450">min</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between mt-8">
                            <button type="button" onclick="goToStep(1)" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-650 dark:text-gray-300 font-bold rounded-xl text-sm transition-all border dark:border-gray-600">
                                <i class="fas fa-arrow-left mr-2 text-xs"></i> Paso anterior
                            </button>
                            <button type="button" onclick="goToStep(3)" class="px-6 py-2.5 bg-red-700 hover:bg-red-600 text-white font-bold rounded-xl text-sm transition-all shadow-md">
                                Siguiente paso <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- PASO 3: DATOS SOCIOECONÓMICOS Y CARGA FAMILIAR --}}
                    <div id="step-content-3" class="step-pane hidden">
                        <h4 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                            <span>📊</span> Datos Socioeconómicos y Carga Familiar
                        </h4>

                        {{-- SECCIÓN VIVIENDA TIPO Y TENENCIA --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="vivienda_tipo" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tipo de Vivienda *</label>
                                <select name="datos_socioeconomicos[vivienda][tipo]" id="vivienda_tipo" required
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none">
                                    <option value="Casa" {{ old('datos_socioeconomicos.vivienda.tipo') === 'Casa' ? 'selected' : '' }}>Casa</option>
                                    <option value="Quinta" {{ old('datos_socioeconomicos.vivienda.tipo') === 'Quinta' ? 'selected' : '' }}>Quinta</option>
                                    <option value="Apartamento" {{ old('datos_socioeconomicos.vivienda.tipo') === 'Apartamento' ? 'selected' : '' }}>Apartamento</option>
                                    <option value="Rancho" {{ old('datos_socioeconomicos.vivienda.tipo') === 'Rancho' ? 'selected' : '' }}>Rancho</option>
                                    <option value="Habitación" {{ old('datos_socioeconomicos.vivienda.tipo') === 'Habitación' ? 'selected' : '' }}>Habitación</option>
                                </select>
                            </div>

                            <div>
                                <label for="vivienda_tenencia" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tenencia de la Vivienda *</label>
                                <select name="datos_socioeconomicos[vivienda][tenencia]" id="vivienda_tenencia" required
                                    class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none">
                                    <option value="Propia" {{ old('datos_socioeconomicos.vivienda.tenencia') === 'Propia' ? 'selected' : '' }}>Propia</option>
                                    <option value="Alquilada" {{ old('datos_socioeconomicos.vivienda.tenencia') === 'Alquilada' ? 'selected' : '' }}>Alquilada</option>
                                    <option value="Prestada" {{ old('datos_socioeconomicos.vivienda.tenencia') === 'Prestada' ? 'selected' : '' }}>Prestada</option>
                                    <option value="Heredada" {{ old('datos_socioeconomicos.vivienda.tenencia') === 'Heredada' ? 'selected' : '' }}>Heredada</option>
                                    <option value="Invadida" {{ old('datos_socioeconomicos.vivienda.tenencia') === 'Invadida' ? 'selected' : '' }}>Invadida</option>
                                </select>
                            </div>
                        </div>

                        {{-- SECCIÓN DISTRIBUCIÓN DE VIVIENDA --}}
                        <div class="bg-gray-50/50 dark:bg-gray-900/10 p-6 rounded-2xl border dark:border-gray-700 mb-6">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-4">
                                <i class="fas fa-home text-red-700 mr-2"></i> Distribución de Ambientes (Vivienda)
                            </h5>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @foreach (['dormitorios', 'cocina', 'comedor', 'baños', 'sala', 'patio', 'Garaje', 'lavadero'] as $ambiente)
                                    <div>
                                        <label for="ambiente_{{ $ambiente }}" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ ucfirst($ambiente) }}</label>
                                        <input type="number" min="0" name="datos_socioeconomicos[vivienda][distribucion][{{ $ambiente }}]" id="ambiente_{{ $ambiente }}"
                                            value="{{ old("datos_socioeconomicos.vivienda.distribucion.{$ambiente}", '0') }}"
                                            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-red-500 transition-all outline-none" />
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- SECCIÓN EQUIPAMIENTOS --}}
                        <div class="bg-gray-50/50 dark:bg-gray-900/10 p-6 rounded-2xl border dark:border-gray-700 mb-6">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-4">
                                <i class="fas fa-couch text-red-700 mr-2"></i> Equipamiento de la Vivienda (Cantidades)
                            </h5>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                                @foreach (['nevera', 'tv', 'Cocina', 'ventidalor', 'computadora', 'muebles', 'comedor', 'cama', 'lavadora', 'aire_acondicionado'] as $equipo)
                                    <div>
                                        <label for="equipo_{{ $equipo }}" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ str_replace('_', ' ', ucfirst($equipo)) }}</label>
                                        <input type="number" min="0" name="datos_socioeconomicos[equipamiento][{{ $equipo }}]" id="equipo_{{ $equipo }}"
                                            value="{{ old("datos_socioeconomicos.equipamiento.{$equipo}", '0') }}"
                                            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-red-500 transition-all outline-none" />
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- SECCIÓN SERVICIOS BÁSICOS --}}
                        <div class="bg-gray-50/50 dark:bg-gray-900/10 p-6 rounded-2xl border dark:border-gray-700 mb-6">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase mb-4">
                                <i class="fas fa-plug text-red-700 mr-2"></i> Servicios Públicos / Conectividad disponibles
                            </h5>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @foreach (['agua' => 'Agua potable', 'internet' => 'Internet fijo/datos', 'cloacas' => 'Alcantarillado / Cloacas', 'electricidad' => 'Electricidad', 'TV cable' => 'Televisión por Cable', 'Tlf fijo' => 'Teléfono Fijo', 'transport.Publico' => 'Transporte Público', 'Aseo_Urbano' => 'Aseo Urbano'] as $sKey => $sLabel)
                                    <div class="flex items-center">
                                        <label class="relative flex items-center cursor-pointer select-none">
                                            <input type="checkbox" name="datos_socioeconomicos[servicios][{{ $sKey }}]" value="1"
                                                {{ old("datos_socioeconomicos.servicios.{$sKey}") ? 'checked' : '' }}
                                                class="sr-only peer" />
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none dark:bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-700"></div>
                                            <span class="ml-2 text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $sLabel }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- CARGA FAMILIAR --}}
                        <div class="bg-gray-50/50 dark:bg-gray-900/10 p-6 rounded-2xl border dark:border-gray-700 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase">
                                    <i class="fas fa-users text-red-700 mr-2"></i> Carga Familiar del Estudiante
                                </h5>
                                <button type="button" onclick="agregarFamiliar()"
                                    class="inline-flex items-center px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg transition-all">
                                    <i class="fas fa-plus mr-1"></i> Agregar Familiar
                                </button>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-800">
                                <table class="min-w-full divide-y divide-slate-100 dark:divide-gray-700 text-xs">
                                    <thead class="bg-slate-50 dark:bg-gray-700/50 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-3 text-left">Parentesco</th>
                                            <th class="px-4 py-3 text-left">Nombre y Apellido</th>
                                            <th class="px-4 py-3 text-center w-20">Edad</th>
                                            <th class="px-4 py-3 text-left">Nivel Educativo</th>
                                            <th class="px-4 py-3 text-left">Ocupación</th>
                                            <th class="px-4 py-3 text-right w-32">Ingreso Mensual</th>
                                            <th class="px-4 py-3 text-center w-12">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="carga-familiar-tbody" class="divide-y divide-slate-100 dark:divide-gray-700">
                                        {{-- Las filas dinámicas se agregarán aquí por Javascript --}}
                                        <tr id="cf-empty-row">
                                            <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                                No hay familiares registrados. Haz clic en "Agregar Familiar" para ingresar la carga familiar.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-between mt-8">
                            <button type="button" onclick="goToStep(2)" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-650 dark:text-gray-300 font-bold rounded-xl text-sm transition-all border dark:border-gray-600">
                                <i class="fas fa-arrow-left mr-2 text-xs"></i> Paso anterior
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-red-700 hover:bg-red-600 text-white font-bold rounded-xl text-sm transition-all shadow-md">
                                Registrar y Guardar <i class="fas fa-save ml-2 text-xs"></i>
                            </button>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            let currentStep = 1;

            function goToStep(step) {
                // Ocultar todos los paneles
                document.querySelectorAll('.step-pane').forEach(el => el.classList.add('hidden'));
                
                // Mostrar panel activo
                document.getElementById(`step-content-${step}`).classList.remove('hidden');

                // Actualizar estilo del indicador
                for (let i = 1; i <= 3; i++) {
                    const circle = document.getElementById(`step-circle-${i}`);
                    const text = document.getElementById(`step-text-${i}`);
                    
                    if (i < step) {
                        // Pasos anteriores
                        circle.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-emerald-50 border-emerald-500 text-emerald-500 dark:bg-emerald-950/20";
                        circle.innerHTML = '<i class="fas fa-check text-xs"></i>';
                        text.className = "text-xs font-bold text-emerald-500 uppercase tracking-wider";
                    } else if (i === step) {
                        // Paso actual
                        circle.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-red-700 border-red-700 text-white shadow-md shadow-red-100 dark:shadow-none";
                        circle.innerHTML = i;
                        text.className = "text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider";
                    } else {
                        // Pasos siguientes
                        circle.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all bg-white border-slate-200 text-slate-400 dark:bg-gray-800 dark:border-gray-700";
                        circle.innerHTML = i;
                        text.className = "text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider";
                    }
                }

                currentStep = step;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function actualizarJornadaInfo() {
                const select = document.getElementById('jornada_id');
                const option = select.options[select.selectedIndex];
                
                const beneficioId = option.getAttribute('data-beneficio-id') || '';
                const beneficioNombre = option.getAttribute('data-beneficio-nombre') || '';
                const lapsoId = option.getAttribute('data-lapso-id') || '';
                const lapsoCodigo = option.getAttribute('data-lapso-codigo') || '';

                document.getElementById('id_beneficio').value = beneficioId;
                document.getElementById('beneficio_nombre_disabled').value = beneficioNombre;
                document.getElementById('id_lapso').value = lapsoId;
                document.getElementById('lapso_codigo_disabled').value = lapsoCodigo;
            }

            function toggleResidenciaMonto() {
                const paga = document.getElementById('residencia_paga').checked;
                const divMonto = document.getElementById('div_monto_residencia');
                const inputMonto = document.getElementById('residencia_monto');
                
                if (paga) {
                    divMonto.classList.remove('hidden');
                    inputMonto.setAttribute('required', 'required');
                } else {
                    divMonto.classList.add('hidden');
                    inputMonto.removeAttribute('required');
                    inputMonto.value = '';
                }
            }

            function toggleViajeFrecuencia() {
                const esDiario = document.getElementById('viaje_diario_es_diario').checked;
                const divFrecuencia = document.getElementById('div_frecuencia_viaje');
                const inputFrecuencia = document.getElementById('viaje_frecuencia');
                
                if (!esDiario) {
                    divFrecuencia.classList.remove('hidden');
                    inputFrecuencia.setAttribute('required', 'required');
                } else {
                    divFrecuencia.classList.add('hidden');
                    inputFrecuencia.removeAttribute('required');
                    inputFrecuencia.value = '';
                }
            }

            // Gestión Carga Familiar Dinámica
            let familiarCount = 0;

            function agregarFamiliar() {
                const tbody = document.getElementById('carga-familiar-tbody');
                const emptyRow = document.getElementById('cf-empty-row');
                
                if (emptyRow) {
                    emptyRow.remove();
                }

                const index = familiarCount++;
                const row = document.createElement('tr');
                row.id = `cf-row-${index}`;
                row.className = "hover:bg-slate-50/50 dark:hover:bg-gray-700/10 transition-all";
                row.innerHTML = `
                    <td class="px-4 py-2">
                        <input type="text" name="datos_socioeconomicos[carga_familiar][${index}][parentesco]" required
                            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-red-500 outline-none"
                            placeholder="Ej: Madre" />
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="datos_socioeconomicos[carga_familiar][${index}][nombre_apellido]" required
                            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-red-500 outline-none"
                            placeholder="Nombre completo" />
                    </td>
                    <td class="px-4 py-2 text-center">
                        <input type="number" min="0" max="120" name="datos_socioeconomicos[carga_familiar][${index}][edad]" required
                            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-red-500 outline-none text-center"
                            placeholder="Años" />
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="datos_socioeconomicos[carga_familiar][${index}][nivel_instituto]" required
                            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-red-500 outline-none"
                            placeholder="Nivel de estudios" />
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="datos_socioeconomicos[carga_familiar][${index}][ocupacion]" required
                            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-red-500 outline-none"
                            placeholder="Empleo / Oficio" />
                    </td>
                    <td class="px-4 py-2 text-right">
                        <input type="number" step="0.01" min="0" name="datos_socioeconomicos[carga_familiar][${index}][ingreso_mens]" required
                            class="w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-red-500 outline-none text-right"
                            placeholder="Ingreso mensual" />
                    </td>
                    <td class="px-4 py-2 text-center">
                        <button type="button" onclick="removerFamiliar(${index})"
                            class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 border border-rose-200 dark:border-rose-900 flex items-center justify-center text-rose-600 transition-all">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            }

            function removerFamiliar(index) {
                document.getElementById(`cf-row-${index}`).remove();
                
                const tbody = document.getElementById('carga-familiar-tbody');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = `
                        <tr id="cf-empty-row">
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                No hay familiares registrados. Haz clic en "Agregar Familiar" para ingresar la carga familiar.
                            </td>
                        </tr>
                    `;
                }
            }

            // Inicializar estados
            window.onload = function() {
                actualizarJornadaInfo();
                toggleResidenciaMonto();
                toggleViajeFrecuencia();
            };
        </script>
    @endpush

</x-app-layout>
