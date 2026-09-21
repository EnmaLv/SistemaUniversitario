@php
    $misSolicitudesBecas = collect([]);
    if (auth()->check() && auth()->user()->tieneRol(['paciente', 'becario', 'estudiante']) && auth()->user()->id_persona) {
        $misSolicitudesBecas = \App\Models\Becas\SolicitudBeca::with('jornada.beneficio')
            ->where('id_persona', auth()->user()->id_persona)
            ->orderBy('created_at', 'desc')
            ->get();
    }
@endphp
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-5 rounded-3xl border shadow-sm p-6 sm:p-7 flex flex-col justify-between relative overflow-hidden transition-all"
            style="background-color: var(--bg-card); border-color: var(--border-color);">

            <div class="flex justify-between items-start z-10">
                <div class="max-w-[80%]">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 mb-3 border border-red-100 dark:border-red-800">
                        <i class="fas fa-graduation-cap text-[10px]"></i> Área de Becas
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight"
                        style="color: var(--text-main);">
                        Hola,<br>
                        <span class="text-red-600 dark:text-red-400">
                            {{ auth()->user()->persona?->nombre_persona ?? (auth()->user()->nombres ?? auth()->user()->name) }}
                        </span>
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 leading-relaxed">
                        Gestiona tus solicitudes y mantente informado sobre los beneficios estudiantiles que ofrecemos
                        para ti.
                    </p>
                </div>
                <div
                    class="p-3 rounded-2xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/50 shrink-0">
                    <i class="fas fa-award text-xl"></i>
                </div>
            </div>

            <div
                class="relative h-48 sm:h-56 my-4 rounded-2xl overflow-hidden flex items-center justify-center bg-gradient-to-br from-red-600 to-red-800 shadow-inner">
                <i
                    class="fas fa-user-graduate text-white/20 text-9xl absolute -bottom-4 -right-4 transform rotate-12"></i>
                <div class="text-white text-center z-10 px-4 relative">
                    <div
                        class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md mx-auto mb-3 flex items-center justify-center shadow-lg">
                        <i class="fas fa-hand-holding-heart text-xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-1 shadow-sm">Tu futuro, nuestro apoyo</h3>
                    <p class="text-xs text-red-100 font-medium">Explora las oportunidades de becas disponibles para tu
                        desarrollo académico</p>
                </div>
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent pointer-events-none">
                </div>
            </div>

            <div class="z-10 pt-2 grid grid-cols-1 gap-3">
                @if(isset($jornadasRenovables) && $jornadasRenovables->count() > 0)
                    @foreach($jornadasRenovables as $jornadaRenovable)
                        <button type="button" @click.prevent="$dispatch('open-renovacion', { jornadaId: {{ $jornadaRenovable->id }}, nombreBeneficio: '{{ $jornadaRenovable->beneficio->nombre_beneficio ?? '' }}' })"
                            class="w-full inline-flex items-center justify-center gap-2.5 py-3.5 px-5 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-bold rounded-2xl shadow-md hover:shadow-lg active:scale-95 transition-all text-sm mb-2">
                            <i class="fas fa-bolt text-amber-200"></i>
                            <span>Renovar Beca: {{ $jornadaRenovable->beneficio->nombre_beneficio ?? 'N/A' }}</span>
                        </button>
                    @endforeach
                @endif
                <a href="{{ route('admin.becas.solicitar') }}"
                    class="w-full inline-flex items-center justify-center gap-2.5 py-3.5 px-5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold rounded-2xl shadow-md hover:shadow-lg active:scale-95 transition-all text-sm">
                    <span>Nueva Solicitud de Beca</span>
                    <i class="fas fa-arrow-right text-[11px] opacity-70"></i>
                </a>
            </div>
        </div>

        <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-5">

            <!-- Card: Mis Solicitudes -->
            <div class="rounded-3xl border shadow-sm p-5 flex flex-col justify-between transition-all"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i class="fas fa-file-invoice text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider" style="color: var(--text-main);">
                            Mis Solicitudes
                        </h3>
                    </div>

                    <div class="min-h-[120px] flex flex-col justify-center gap-3 w-full">

                        @if(isset($misSolicitudesBecas) && $misSolicitudesBecas->count() > 0)
                            <div class="space-y-3 w-full">
                                @foreach($misSolicitudesBecas->take(2) as $solicitud)
                                    <div
                                        class="flex flex-col p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 w-full transition-all hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <div class="flex items-center justify-between w-full">
                                            <div class="flex items-center gap-3">
                                                @php
                                                    $estadoConfig = match ((int) $solicitud->estado) {
                                                        0 => ['icon' => 'fa-clock', 'text' => 'Pendiente', 'icon_bg' => 'bg-amber-100 dark:bg-amber-900/30', 'icon_text' => 'text-amber-600 dark:text-amber-400', 'badge_bg' => 'bg-amber-50 dark:bg-amber-950', 'badge_border' => 'border-amber-200 dark:border-amber-800', 'badge_text' => 'text-amber-700 dark:text-amber-400'],
                                                        1 => ['icon' => 'fa-check-circle', 'text' => 'Aprobada', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'icon_text' => 'text-emerald-600 dark:text-emerald-400', 'badge_bg' => 'bg-emerald-50 dark:bg-emerald-950', 'badge_border' => 'border-emerald-200 dark:border-emerald-800', 'badge_text' => 'text-emerald-700 dark:text-emerald-400'],
                                                        2 => ['icon' => 'fa-times-circle', 'text' => 'Rechazada', 'icon_bg' => 'bg-rose-100 dark:bg-rose-900/30', 'icon_text' => 'text-rose-600 dark:text-rose-400', 'badge_bg' => 'bg-rose-50 dark:bg-rose-950', 'badge_border' => 'border-rose-200 dark:border-rose-800', 'badge_text' => 'text-rose-700 dark:text-rose-400'],
                                                        default => ['icon' => 'fa-question-circle', 'text' => 'Desconocido', 'icon_bg' => 'bg-gray-100 dark:bg-gray-900/30', 'icon_text' => 'text-gray-600 dark:text-gray-400', 'badge_bg' => 'bg-gray-50 dark:bg-gray-950', 'badge_border' => 'border-gray-200 dark:border-gray-800', 'badge_text' => 'text-gray-700 dark:text-gray-400'],
                                                    };
                                                @endphp
                                                <div
                                                    class="w-10 h-10 rounded-full {{ $estadoConfig['icon_bg'] }} {{ $estadoConfig['icon_text'] }} flex items-center justify-center text-sm shadow-sm">
                                                    <i class="fas {{ $estadoConfig['icon'] }}"></i>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200">Beca:
                                                        {{ $solicitud->jornada->beneficio->nombre_beneficio ?? 'N/A' }}</p>
                                                    <p class="text-[10px] text-gray-500 font-medium">Jornada:
                                                        {{ $solicitud->jornada->nombre_jornada ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span
                                                    class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md border {{ $estadoConfig['badge_bg'] }} {{ $estadoConfig['badge_border'] }} {{ $estadoConfig['badge_text'] }}">
                                                    {{ $estadoConfig['text'] }}
                                                </span>
                                                <p class="text-[9px] text-gray-400 mt-1">
                                                    {{ $solicitud->created_at->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        @if($solicitud->estado == 2 && $solicitud->comentario_verificador)
                                            <div class="mt-3 text-[10px] text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 p-2.5 rounded-lg border border-rose-100 dark:border-rose-900/50">
                                                <span class="font-bold uppercase tracking-wider block mb-0.5"><i class="fas fa-info-circle mr-1"></i>Motivo del rechazo:</span>
                                                {{ $solicitud->comentario_verificador }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center text-center">
                                <div class="relative mb-2">
                                    <i class="fas fa-folder-open text-3xl text-gray-200 dark:text-gray-700"></i>
                                    <div
                                        class="absolute -bottom-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white dark:border-gray-900 animate-pulse hidden">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 font-medium px-4">
                                    Si tienes solicitudes en proceso, aparecerán aquí para que les des seguimiento.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="relative mt-4">
                    <a href="#" @click.prevent="$dispatch('open-historial')"
                        class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300 font-bold rounded-xl text-xs transition-all border border-amber-100 dark:border-amber-900/50 active:scale-95">
                        <span>Ver Historial Completo</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card: Beneficios Activos -->
            @php
                $beneficiosActivos = isset($misSolicitudesBecas) ? $misSolicitudesBecas->unique('id_beneficio')->where('estado', 1) : collect();
            @endphp
            <div class="rounded-3xl border shadow-sm p-5 flex flex-col justify-between transition-all"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div
                            class="w-8 h-8 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800 flex items-center justify-center text-red-600 dark:text-red-400">
                            <i class="fas fa-gift text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider" style="color: var(--text-main);">
                            Beneficios Activos
                        </h3>
                    </div>

                    <div class="min-h-[120px] flex flex-col justify-center w-full">
                        @if($beneficiosActivos->count() > 0)
                            <div class="space-y-3 w-full">
                                @foreach($beneficiosActivos as $beneficioActivo)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-red-50/50 dark:bg-gray-800/50 border border-red-100/50 dark:border-gray-700/80 w-full transition-all">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center text-sm shadow-sm">
                                                <i class="fas fa-medal"></i>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                                    {{ $beneficioActivo->jornada->beneficio->nombre_beneficio ?? 'Beneficio Activo' }}
                                                </p>
                                                <p class="text-[10px] text-gray-500 font-medium">
                                                    Aprobado el {{ $beneficioActivo->updated_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center text-center">
                                <div class="relative mb-2">
                                    <i class="fas fa-box-open text-3xl text-gray-200 dark:text-gray-700"></i>
                                </div>
                                <p class="text-xs text-gray-500 font-medium px-4">
                                    Tus beneficios otorgados se listarán aquí para consulta rápida.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4">
                    @if($beneficiosActivos->count() > 0)
                    @else
                        <a href="#"
                            class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 font-bold rounded-xl text-xs transition-all border border-red-100 dark:border-red-900/50 active:scale-95 opacity-50 cursor-not-allowed">
                            <span>Sin beneficios activos</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Card: Anuncios Completos -->
            <div class="rounded-3xl border shadow-sm p-5 flex flex-col justify-between transition-all sm:col-span-2"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5 mb-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <i class="fas fa-bullhorn text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider" style="color: var(--text-main);">
                            Anuncios y Convocatorias
                        </h3>
                    </div>

                    @if(isset($jornadasActivas) && $jornadasActivas->count() > 0)
                        <div class="space-y-3 mt-4">
                            @foreach($jornadasActivas as $jornada)
                                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-black/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-indigo-200 dark:hover:border-indigo-800/50 transition-colors">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                            {{ $jornada->nombre_jornada }}
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Beneficio: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $jornada->beneficio->nombre_beneficio ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                    <div class="flex flex-col sm:items-end gap-2 shrink-0">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800/50">
                                            <i class="fas fa-calendar-check"></i> Hasta {{ \Carbon\Carbon::parse($jornada->fecha_fin_solicitud)->format('d/m/Y') }}
                                        </span>
                                        @canMenu('solicitar')
                                        <a href="{{ route('admin.becas.solicitar') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline underline-offset-2">Solicitar ahora</a>
                                        @endcanMenu
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="min-h-[80px] flex items-center justify-center">
                            <div class="flex items-center gap-3 text-gray-500">
                                <i class="fas fa-bell-slash text-xl text-gray-300 dark:text-gray-600"></i>
                                <p class="text-xs font-medium">Por el momento no hay convocatorias activas.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
<div x-data="{ showHistorialModal: false }"
    @open-historial.window="showHistorialModal = true"
    x-show="showHistorialModal" class="fixed inset-0 overflow-y-auto" style="z-index: 9999;" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div x-show="showHistorialModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm"
            @click="showHistorialModal = false"></div>

        <div x-show="showHistorialModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="relative inline-block w-full max-w-lg p-6 overflow-hidden text-left transition-all transform shadow-2xl rounded-2xl border z-10">

            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 text-xl border border-amber-100 dark:border-amber-900/30">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold tracking-tight" style="color: var(--text-main);">Historial de Solicitudes</h3>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tus 3 postulaciones más recientes</p>
                    </div>
                </div>
                <button type="button" @click="showHistorialModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="max-h-[50vh] overflow-y-auto pr-2 space-y-3 mb-6">
                @if(isset($misSolicitudesBecas) && $misSolicitudesBecas->count() > 0)
                    @foreach($misSolicitudesBecas->take(3) as $solicitud)
                        <div class="flex flex-col p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/80 transition-all hover:bg-gray-100 dark:hover:bg-gray-800">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex items-center gap-3">
                                    @php
                                        $estadoConfig = match((int)$solicitud->estado) {
                                            0 => ['icon' => 'fa-clock', 'text' => 'Pendiente', 'icon_bg' => 'bg-amber-100 dark:bg-amber-900/30', 'icon_text' => 'text-amber-600 dark:text-amber-400', 'badge_bg' => 'bg-amber-50 dark:bg-amber-950', 'badge_border' => 'border-amber-200 dark:border-amber-800', 'badge_text' => 'text-amber-700 dark:text-amber-400'],
                                            1 => ['icon' => 'fa-check-circle', 'text' => 'Aprobada', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'icon_text' => 'text-emerald-600 dark:text-emerald-400', 'badge_bg' => 'bg-emerald-50 dark:bg-emerald-950', 'badge_border' => 'border-emerald-200 dark:border-emerald-800', 'badge_text' => 'text-emerald-700 dark:text-emerald-400'],
                                            2 => ['icon' => 'fa-times-circle', 'text' => 'Rechazada', 'icon_bg' => 'bg-rose-100 dark:bg-rose-900/30', 'icon_text' => 'text-rose-600 dark:text-rose-400', 'badge_bg' => 'bg-rose-50 dark:bg-rose-950', 'badge_border' => 'border-rose-200 dark:border-rose-800', 'badge_text' => 'text-rose-700 dark:text-rose-400'],
                                            default => ['icon' => 'fa-question-circle', 'text' => 'Desconocido', 'icon_bg' => 'bg-gray-100 dark:bg-gray-900/30', 'icon_text' => 'text-gray-600 dark:text-gray-400', 'badge_bg' => 'bg-gray-50 dark:bg-gray-950', 'badge_border' => 'border-gray-200 dark:border-gray-800', 'badge_text' => 'text-gray-700 dark:text-gray-400'],
                                        };
                                    @endphp
                                    <div class="w-10 h-10 rounded-full {{ $estadoConfig['icon_bg'] }} {{ $estadoConfig['icon_text'] }} flex items-center justify-center text-sm shadow-sm">
                                        <i class="fas {{ $estadoConfig['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $solicitud->jornada->beneficio->nombre_beneficio ?? 'N/A' }}</p>
                                        <p class="text-[10px] text-gray-500 font-medium">Jornada: {{ $solicitud->jornada->nombre_jornada ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end gap-1">
                                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded-md border {{ $estadoConfig['badge_bg'] }} {{ $estadoConfig['badge_border'] }} {{ $estadoConfig['badge_text'] }}">
                                        {{ $estadoConfig['text'] }}
                                    </span>
                                    <p class="text-[9px] text-gray-400 font-medium">{{ $solicitud->created_at->format('d/m/Y - h:i A') }}</p>
                                </div>
                            </div>
                            @if($solicitud->estado == 2 && $solicitud->comentario_verificador)
                                <div class="mt-3 text-[10px] text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 p-2.5 rounded-lg border border-rose-100 dark:border-rose-900/50">
                                    <span class="font-bold uppercase tracking-wider block mb-0.5"><i class="fas fa-info-circle mr-1"></i>Motivo del rechazo:</span>
                                    {{ $solicitud->comentario_verificador }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="flex flex-col items-center text-center py-6">
                        <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                            <i class="fas fa-folder-open text-2xl text-gray-300 dark:text-gray-600"></i>
                        </div>
                        <p class="text-xs text-gray-500 font-medium">No tienes solicitudes previas.</p>
                    </div>
                @endif
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700/50">
                <button type="button" @click="showHistorialModal = false"
                    class="px-5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs sm:text-sm rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all active:scale-95">
                    Cerrar Historial
                </button>
            </div>
        </div>
    </div>
</div>

<div x-data="{ showRenovacionModal: false, jornadaId: null, nombreBeneficio: '' }"
    @open-renovacion.window="showRenovacionModal = true; jornadaId = $event.detail.jornadaId; nombreBeneficio = $event.detail.nombreBeneficio"
    x-show="showRenovacionModal" class="fixed inset-0 overflow-y-auto" style="z-index: 9999;" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div x-show="showRenovacionModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm"
            @click="showRenovacionModal = false"></div>

        <div x-show="showRenovacionModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="relative inline-block w-full max-w-lg p-6 overflow-hidden text-left transition-all transform shadow-2xl rounded-2xl border z-10">

            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 text-xl border border-amber-200 dark:border-amber-800">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold tracking-tight" style="color: var(--text-main);">Renovación Rápida</h3>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Actualiza tus notas para continuar con el beneficio</p>
                    </div>
                </div>
                <button type="button" @click="showRenovacionModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.becas.solicitudes.renovar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="jornada_id" :value="jornadaId">
                
                <div class="mb-5 bg-amber-50 dark:bg-amber-950/20 p-4 rounded-xl border border-amber-100 dark:border-amber-900/50">
                    <p class="text-sm text-amber-800 dark:text-amber-400 mb-2 font-medium">
                        Como becario de <strong><span x-text="nombreBeneficio"></span></strong> en el lapso anterior, calificas para la renovación automática. 
                        Solo necesitas subir tus notas certificadas actualizadas correspondientes al nuevo semestre.
                    </p>
                    <p class="text-[11px] text-amber-700 dark:text-amber-500 font-bold">
                        <i class="fas fa-info-circle mr-1"></i>Tus datos y respuestas anteriores se conservarán.
                    </p>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        Notas Certificadas (PDF) *
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-amber-500 transition-all"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-file-pdf text-sm text-amber-500"></i>
                        </span>
                        <input type="file" name="archivo_notas" accept="application/pdf" required
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2 text-sm font-medium border-none focus:ring-0 focus:outline-none file:mr-4 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-amber-900/30 dark:file:text-amber-400 transition-all cursor-pointer">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <button type="button" @click="showRenovacionModal = false"
                        class="px-5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all active:scale-95">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-amber-500 text-white font-bold text-sm rounded-xl hover:bg-amber-600 shadow-md transition-all active:scale-95 inline-flex items-center gap-2">
                        <i class="fas fa-paper-plane text-xs"></i> Enviar Renovación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>