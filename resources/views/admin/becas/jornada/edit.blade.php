<x-app-layout>
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
    @endpush

    @php
        $yaInicio = $jornada->fecha_inicio_solicitud && $jornada->fecha_inicio_solicitud->isPast();
        $haExpirado = $jornada->fecha_fin_solicitud && $jornada->fecha_fin_solicitud->isPast();
    @endphp

    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- CABECERA UNIFICADA --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        {{ $haExpirado ? 'Ver' : 'Editar' }} Jornada de Beca
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ url('admin/becas/jornada') }}"
                        class="inline-flex shrink-0 whitespace-nowrap items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-slate-200 text-sm font-bold rounded-2xl transition-all shadow-sm border border-gray-200 dark:border-gray-700">
                        <i class="fas fa-arrow-left text-xs mr-2"></i>
                        <span>Volver</span>
                    </a>
                </div>
            </div>

            {{-- CONTENEDOR PRINCIPAL CON EL ESTILO UNIFICADO DE MEDICINA/PSICOLOGIA --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border-l-8 border-red-700 overflow-hidden">
                <div class="p-8 text-gray-900 dark:text-gray-100">

                    <h3
                        class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight text-left mb-6 border-b pb-4 dark:border-gray-700">
                        Modificar Detalles de la Jornada
                    </h3>

                    @if($haExpirado)
                        <div class="rd-alert rd-alert-warning mb-6">
                            <div class="rd-alert-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="rd-alert-content">
                                <h6 class="rd-alert-title">Jornada Expirada</h6>
                                <p class="rd-alert-text">
                                    Esta jornada ha expirado el
                                    <strong>{{ $jornada->fecha_fin_solicitud->format('d/m/Y') }}</strong>. Su edición ha
                                    sido inhabilitada.
                                </p>
                            </div>
                        </div>
                    @elseif($yaInicio)
                        <div class="rd-alert rd-alert-warning mb-6">
                            <div class="rd-alert-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="rd-alert-content">
                                <h6 class="rd-alert-title">Jornada en Curso</h6>
                                <p class="rd-alert-text">
                                    Esta jornada ya ha iniciado su período de solicitudes. Por seguridad, algunos campos críticos (beneficio, lapso académico, fecha de inicio y estado activo) no pueden modificarse.
                                </p>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="rd-alert rd-alert-danger mb-4">
                            <div class="rd-alert-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="rd-alert-content">
                                <h6 class="rd-alert-title">Ocurrió un error</h6>
                                <p class="rd-alert-text">{{ session('error') }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rd-alert rd-alert-danger mb-4">
                            <div class="rd-alert-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="rd-alert-content">
                                <h6 class="rd-alert-title">Por favor corrige los siguientes errores:</h6>
                                <ul class="rd-alert-list">
                                    @foreach ($errors->all() as $error)
                                        <li><i class="fas fa-dot-circle"></i> {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.becas.jornada.update', $jornada->id) }}" method="POST"
                        class="space-y-6 rd-prevent-double-submit">
                        @csrf
                        @method('PUT')

                        @if($yaInicio)
                            <input type="hidden" name="beneficio_id" value="{{ $jornada->beneficio_id }}">
                            <input type="hidden" name="lapsos_id" value="{{ $jornada->lapsos_id }}">
                            <input type="hidden" name="fecha_inicio_solicitud"
                                value="{{ $jornada->fecha_inicio_solicitud ? $jornada->fecha_inicio_solicitud->format('Y-m-d') : '' }}">
                            @if($jornada->activa)
                                <input type="hidden" name="activa" value="1">
                            @endif
                        @endif

                        <fieldset {{ $haExpirado ? 'disabled' : '' }} class="space-y-6">

                            {{-- Nombre de la Jornada --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Nombre de la Jornada <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-signature text-sm"></i>
                                    </div>
                                    <input type="text" name="nombre_jornada"
                                        value="{{ old('nombre_jornada', $jornada->nombre_jornada) }}"
                                        class="w-full pl-10 pr-4 py-3 bg-slate-50/50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none disabled:bg-gray-100 dark:disabled:bg-gray-800/40 disabled:text-slate-400 dark:disabled:text-slate-500 disabled:border-slate-250 dark:disabled:border-gray-800 disabled:cursor-not-allowed"
                                        placeholder="Ej. Convocatoria Comedor Universitario 2026-1" required>
                                </div>
                                @error('nombre_jornada')
                                    <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Beneficio --}}
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Beneficio <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-award text-sm"></i>
                                        </div>
                                        <select name="beneficio_id"
                                            class="w-full pl-10 pr-4 py-3 bg-slate-50/50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                            {{ $yaInicio ? 'disabled' : 'required' }}>
                                            <option value="" disabled>Seleccione el beneficio a ofrecer</option>
                                            @foreach ($beneficios as $beneficio)
                                                <option value="{{ $beneficio->id }}" {{ old('beneficio_id', $jornada->beneficio_id) == $beneficio->id ? 'selected' : '' }}>
                                                    {{ $beneficio->nombre_beneficio }} (Disponibles:
                                                    {{ $beneficio->cupones_disponibles }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('beneficio_id')
                                        <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Lapso Académico --}}
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Lapso Académico <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-calendar-alt text-sm"></i>
                                        </div>
                                        <select name="lapsos_id"
                                            class="w-full pl-10 pr-4 py-3 bg-slate-50/50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                            {{ $yaInicio ? 'disabled' : 'required' }}>
                                            <option value="" disabled>Seleccione el lapso académico</option>
                                            @foreach ($lapsos as $lapso)
                                                <option value="{{ $lapso->id }}" {{ old('lapsos_id', $jornada->lapsos_id) == $lapso->id ? 'selected' : '' }}>
                                                    {{ $lapso->codigo }} {{ $lapso->es_actual ? '(Actual)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('lapsos_id')
                                        <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Fecha de Inicio --}}
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Fecha de Inicio de Solicitudes <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-calendar-day text-sm"></i>
                                        </div>
                                        <input type="date" name="fecha_inicio_solicitud"
                                            value="{{ old('fecha_inicio_solicitud', $jornada->fecha_inicio_solicitud ? $jornada->fecha_inicio_solicitud->format('Y-m-d') : '') }}"
                                            min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                            class="w-full pl-10 pr-4 py-3 bg-slate-50/50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                            {{ $yaInicio ? 'disabled' : 'required' }}>
                                    </div>
                                    @error('fecha_inicio_solicitud')
                                        <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Fecha de Fin --}}
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Fecha de Fin de Solicitudes <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-calendar-check text-sm"></i>
                                        </div>
                                        <input type="date" name="fecha_fin_solicitud"
                                            value="{{ old('fecha_fin_solicitud', $jornada->fecha_fin_solicitud ? $jornada->fecha_fin_solicitud->format('Y-m-d') : '') }}"
                                            class="w-full pl-10 pr-4 py-3 bg-slate-50/50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                            required>
                                    </div>
                                    @error('fecha_fin_solicitud')
                                        <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Cupos Máximos --}}
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Cupos Máximos <span class="text-rose-500">*</span>
                                        <span class="ml-1"  
                                            title="El número de cupos máximos de la jornada no puede superar los cupos disponibles del beneficio seleccionado.">
                                            <i class="fas fa-info-circle text-gray-400" style="cursor: help;"></i>
                                        </span>
                                    </label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <i class="fas fa-users text-sm"></i>
                                        </div>
                                        <input type="number" name="cupos_maximos"
                                            value="{{ old('cupos_maximos', $jornada->cupos_maximos) }}" min="1"
                                            placeholder="Ej. 100"
                                            class="w-full pl-10 pr-4 py-3 bg-slate-50/50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none"
                                            required>
                                    </div>
                                    @error('cupos_maximos')
                                        <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Estado Activo (Toggle Switch) --}}
                                <div class="flex items-center pt-8">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="activa" value="1" class="sr-only peer" id="activa"
                                            {{ old('activa', $jornada->activa) ? 'checked' : '' }} {{ $yaInicio ? 'disabled' : '' }}>
                                        <div
                                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600">
                                        </div>
                                        <div class="ms-3">
                                            <span
                                                class="block text-sm font-bold text-slate-700 dark:text-slate-200">Jornada
                                                Activa</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Las
                                                jornadas inactivas no permiten a los estudiantes postularse.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Descripción --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Descripción de la Jornada
                                </label>
                                <textarea name="descripcion_jornada" rows="4"
                                    placeholder="Ingrese detalles o requisitos de esta jornada..."
                                    class="w-full bg-slate-50/50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none resize-none"
                                    style="resize: none;">{{ old('descripcion_jornada', $jornada->descripcion_jornada) }}</textarea>
                                @error('descripcion_jornada')
                                    <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                @enderror
                            </div>
                        </fieldset>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                            <a href="{{ url('admin/becas/jornada') }}"
                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold text-sm transition">
                                {{ $haExpirado ? 'Volver' : 'Cancelar' }}
                            </a>
                            @if(!$haExpirado)
                                <button type="submit"
                                    class="px-6 py-2.5 bg-red-700 hover:bg-red-600 text-white rounded-xl font-bold text-sm transition shadow-lg shadow-red-500/20">
                                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('[]').tooltip();

                const fechaInicio = $('input[name="fecha_inicio_solicitud"]');
                const fechaFin = $('input[name="fecha_fin_solicitud"]');

                function actualizarMinFechaFin() {
                    const val = fechaInicio.val();
                    if (val) {
                        fechaFin.attr('min', val);
                        if (fechaFin.val() && fechaFin.val() < val) {
                            fechaFin.val(val);
                        }
                    }
                }

                // Inicializar y vincular eventos
                actualizarMinFechaFin();
                fechaInicio.on('change', actualizarMinFechaFin);
            });
        </script>
    @endpush
</x-app-layout>