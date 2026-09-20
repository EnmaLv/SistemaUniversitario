<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <div class="lg:col-span-5 rounded-3xl border shadow-sm p-6 sm:p-7 flex flex-col justify-between relative overflow-hidden transition-all"
            style="background-color: var(--bg-card); border-color: var(--border-color);">

            <div class="flex justify-between items-start z-10">
                <div class="max-w-[80%]">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 mb-3 border border-indigo-100 dark:border-indigo-800">
                        <i class="fas fa-brain text-[10px]"></i> Psico-Guía
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight"
                        style="color: var(--text-main);">
                        {{ $saludo ?? 'Buenos días' }},<br>
                        <span class="text-indigo-600 dark:text-indigo-400">
                            {{ auth()->user()->persona?->nombre_persona ?? (auth()->user()->nombres ?? auth()->user()->name) }}
                        </span>
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 leading-relaxed">
                        En Psico-Guía estamos aquí para apoyarte en tu bienestar mental y emocional.
                    </p>
                </div>
                <div
                    class="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 shrink-0">
                    <i class="fas fa-heart-pulse text-xl"></i>
                </div>
            </div>

            <div class="relative h-48 sm:h-56 my-4 rounded-2xl overflow-hidden">
                <img src="{{ asset('img/' . (auth()->user()->persona?->genero_persona === 'Masculino' || auth()->user()->persona?->genero_persona === 'M' ? 'therapy_illustration_masc.png' : 'therapy_illustration.png')) }}"
                    alt="Ilustración Bienestar"
                    class="w-full h-full object-cover object-[50%_60%] transition-transform duration-500 hover:scale-105">
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent pointer-events-none">
                </div>
            </div>

            <div class="z-10 pt-2">
                <a href="{{ route('admin.psicologia.maestros.citas.create') }}"
                    class="w-full inline-flex items-center justify-center gap-2.5 py-3.5 px-5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-2xl shadow-md active:scale-95 transition-all text-sm">
                    <span>Agendar Nueva Cita</span>
                    <i class="fas fa-calendar-plus text-xs"></i>
                </a>
            </div>
        </div>

        <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div class="rounded-3xl border shadow-sm p-5 flex flex-col justify-between transition-all"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div
                            class="w-8 h-8 rounded-xl bg-teal-50 dark:bg-teal-900/30 border border-teal-100 dark:border-teal-800 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <i class="fas fa-calendar-check text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider" style="color: var(--text-main);">
                            Citas en Gestión
                        </h3>
                    </div>

                    <div class="min-h-[100px] flex flex-col justify-center">
                        @if (empty($proximaCita) && empty($citaPendiente))
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-minus text-2xl text-gray-300 dark:text-gray-600 mb-2"></i>
                                <p class="text-xs text-gray-500 font-medium">No tienes citas en proceso.</p>
                            </div>
                        @else
                            <div class="relative pl-4 border-l-2 border-gray-100 dark:border-gray-800 space-y-3 my-2">
                                @if (!empty($proximaCita))
                                    <div class="relative">
                                        <div
                                            class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-teal-500 ring-4 ring-white dark:ring-gray-900">
                                        </div>
                                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                            {{ $proximaCita->psicologo_nombre ?? 'Psicólogo Asignado' }}
                                        </p>
                                        <p class="text-[11px] font-medium text-gray-500">
                                            {{ \Carbon\Carbon::parse($proximaCita->fecha)->format('d M') }} -
                                            {{ $proximaCita->hora ? \Carbon\Carbon::parse($proximaCita->hora)->format('h:i A') : 'Por definir' }}
                                            <span class="text-teal-600 font-bold ml-1">(Confirmada)</span>
                                        </p>
                                    </div>
                                @endif

                                @if (!empty($citaPendiente))
                                    <div class="relative">
                                        <div
                                            class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-amber-500 ring-4 ring-white dark:ring-gray-900">
                                        </div>
                                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                            {{ $citaPendiente->psicologo_nombre ?? 'Psicólogo' }}
                                        </p>
                                        <p class="text-[11px] font-medium text-gray-500">
                                            {{ \Carbon\Carbon::parse($citaPendiente->fecha_sugerida ?? $citaPendiente->fecha)->format('d M') }}
                                            <span class="text-amber-600 font-bold ml-1">(Pendiente)</span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="relative mt-4">
                    <a href="{{ route('admin.psicologia.maestros.citas.index') }}"
                        class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/40 dark:hover:bg-teal-900/50 text-teal-700 dark:text-teal-300 font-bold rounded-xl text-xs transition-all border border-teal-100 dark:border-teal-900/50 active:scale-95">
                        <span>Ir a mis citas</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>

                    @if (isset($notificacionCita) && $notificacionCita)
                        @php
                            $esAprobada = str_contains($notificacionCita->type, 'Confirmed');
                            $mensajeNotif = $esAprobada ? '¡Cita aprobada!' : 'Cita rechazada';
                            $colorMascota = $esAprobada ? 'text-amber-400' : 'text-indigo-400';
                        @endphp
                        <div
                            class="absolute left-1/2 -translate-x-1/2 -top-24 z-20 flex flex-col items-center @if ($esAprobada) animate-bounce @endif pointer-events-none drop-shadow-lg">
                            <div
                                class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white text-[10px] font-extrabold uppercase tracking-wider px-3 py-1.5 rounded-2xl shadow-sm relative text-center whitespace-nowrap">
                                {{ $mensajeNotif }}
                                <div
                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-white dark:bg-gray-800 border-b-2 border-r-2 border-gray-200 dark:border-gray-700 transform rotate-45">
                                </div>
                            </div>
                            <div class="mt-1 {{ $colorMascota }}">
                                <svg class="w-8 h-8 drop-shadow-md" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-3xl border shadow-sm p-5 flex flex-col justify-between transition-all"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5 mb-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-900/30 border border-sky-100 dark:border-sky-800 flex items-center justify-center text-sky-600 dark:text-sky-400">
                            <i class="fas fa-calendar-days text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider" style="color: var(--text-main);">
                            Actividad
                        </h3>
                    </div>

                    <div
                        class="bg-gray-50/60 dark:bg-black/20 rounded-2xl p-3 border border-gray-100 dark:border-gray-800/80">
                        <div class="grid grid-cols-7 gap-1 text-center mb-1.5">
                            @foreach (['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $d)
                                <div class="text-[10px] font-black text-gray-400">{{ $d }}</div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-x-1 gap-y-1 text-center">
                            @php
                                $now = \Carbon\Carbon::now();
                                $start = $now->copy()->startOfMonth();
                                $startDay = $start->dayOfWeekIso;
                                $daysInMonth = $now->daysInMonth;
                                $today = $now->day;

                                for ($i = 1; $i < $startDay; $i++) {
                                    echo '<div class="text-xs text-transparent">.</div>';
                                }
                                for ($i = 1; $i <= $daysInMonth; $i++) {
                                    $isToday = $i === $today;
                                    $classes = $isToday
                                        ? 'bg-indigo-600 text-white rounded-full font-bold shadow-sm'
                                        : 'text-gray-600 dark:text-gray-300 font-medium hover:bg-gray-200 dark:hover:bg-gray-700/50 rounded-full';
                                    echo '<div class="text-[10px] w-5 h-5 flex items-center justify-center mx-auto transition-colors ' .
                                        $classes .
                                        '">' .
                                        $i .
                                        '</div>';
                                }
                            @endphp
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400">Hoy, {{ $now->translatedFormat('d M') }}</span>
                    @if (!empty($proximaCita) && \Carbon\Carbon::parse($proximaCita->fecha)->isToday())
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">1 Sesión programada</span>
                    @else
                        <span class="text-xs font-bold text-gray-500">Sin citas hoy</span>
                    @endif
                </div>
            </div>

            <div class="rounded-3xl border shadow-sm p-5 flex flex-col justify-between transition-all"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5 mb-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i class="fas fa-face-smile text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider" style="color: var(--text-main);">
                            Estado de Ánimo
                        </h3>
                    </div>

                    <div class="flex flex-col justify-center min-h-[110px]">
                        @if ($estadoAnimoHoy)
                            @php $valorAnimo = $estadoAnimoHoy->valor ?? 5; @endphp
                            <div class="text-center py-1">
                                <p class="text-xs font-medium text-gray-500 mb-2">Registro de hoy realizado:</p>
                                <div class="inline-flex items-center justify-center text-4xl" id="saved-mood-icon">
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const el = document.getElementById('saved-mood-icon');
                                        if (el) el.innerHTML = getMoodSVG({{ $valorAnimo }});
                                    });
                                </script>
                            </div>
                        @else
                            <p class="text-xs text-gray-500 font-medium text-center mb-3">
                                ¿Cómo te sientes hoy? (1 al 10)
                            </p>
                            <form action="{{ route('admin.psicologia.maestros.estado_animo_diario.store') }}"
                                method="POST">
                                @csrf
                                <div class="text-center mb-2">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-300">
                                        Valor seleccionado:
                                    </span>
                                    <span id="mood-value"
                                        class="text-sm font-black text-indigo-600 dark:text-indigo-300">
                                        7
                                    </span>
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-300">/ 10</span>
                                </div>

                                <div class="px-2">
                                    <input type="range" name="valor" min="1" max="10" value="7"
                                        aria-label="Selecciona tu estado de ánimo"
                                        class="mood-range w-full h-2 rounded-lg appearance-none cursor-pointer"
                                        oninput="updateMood(this.value);">
                                    <div
                                        class="flex justify-between text-[10px] font-bold text-gray-500 dark:text-gray-300 mt-1">
                                        <span>1</span><span>10</span>
                                    </div>
                                </div>

                                <div class="my-2 text-center" id="mood-emoji"></div>
                                <script>
                                    function updateMood(value) {
                                        document.getElementById('mood-value').textContent = value;
                                        document.getElementById('mood-emoji').innerHTML = getMoodSVG(value);
                                    }

                                    document.addEventListener('DOMContentLoaded', function() {
                                        updateMood(7);
                                    });
                                </script>

                                <button type="submit"
                                    class="w-full mt-2 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl active:scale-95 transition-all shadow-sm">
                                    Guardar Ánimo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border shadow-sm p-5 flex flex-col justify-between transition-all"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div>
                    <div class="flex items-center gap-2.5 mb-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i class="fas fa-bullhorn text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider" style="color: var(--text-main);">
                            Anuncios
                        </h3>
                    </div>

                    <div class="max-h-[130px] overflow-y-auto pr-1 space-y-2.5 custom-scrollbar">
                        @if (isset($publicaciones) && $publicaciones->count() > 0)
                            @foreach ($publicaciones as $pub)
                                <div
                                    class="pb-2 border-b border-gray-100 dark:border-gray-800 last:border-0 last:pb-0">
                                    <div class="flex items-start gap-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                                        <div class="flex-1 flex items-start justify-between gap-2">
                                            <span class="text-xs font-bold leading-snug line-clamp-2"
                                                style="color: var(--text-main);">
                                                {{ $pub->titulo }}
                                            </span>
                                            <a href="{{ route('admin.psicologia.maestros.publicaciones.mural') }}#pub-{{ $pub->id }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 shrink-0 text-xs"
                                                title="Ver en mural">
                                                <i class="fas fa-external-link-alt text-[10px]"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-inbox text-gray-300 dark:text-gray-600 text-xl mb-1"></i>
                                <p class="text-xs text-gray-500 font-medium">Sin anuncios recientes.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function getMoodSVG(value) {
        value = parseInt(value);
        const baseClass = "w-10 h-10 mx-auto transition-colors duration-300";
        switch (value) {
            case 1:
                return `<svg class="${baseClass} text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
            case 2:
                return `<svg class="${baseClass} text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
            case 3:
            case 4:
                return `<svg class="${baseClass} text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 15l8 -1.5"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
            case 5:
            case 6:
                return `<svg class="${baseClass} text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="8" y1="15" x2="16" y2="15"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
            case 7:
            case 8:
                return `<svg class="${baseClass} text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
            case 9:
            case 10:
                return `<svg class="${baseClass} text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 3 4 3 4-3 4-3v-1H8v1z" fill="currentColor"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3"/></svg>`;
        }
        return '';
    }
</script>

<style>
    .mood-range {
        background: #cbd5e1;
    }

    .dark .mood-range {
        background: #64748b;
    }

    .mood-range::-webkit-slider-thumb {
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 9999px;
        background: #4338ca;
        border: 2px solid white;
        box-shadow: 0 1px 4px rgb(0 0 0 / 30%);
    }

    .dark .mood-range::-webkit-slider-thumb {
        background: #a5b4fc;
        border-color: #1e293b;
    }

    .mood-range::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 9999px;
        background: #4338ca;
        border: 2px solid white;
    }

    .dark .mood-range::-moz-range-thumb {
        background: #a5b4fc;
        border-color: #1e293b;
    }
</style>
