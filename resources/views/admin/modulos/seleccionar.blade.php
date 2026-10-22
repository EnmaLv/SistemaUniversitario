@php
    $user = auth()->user();
    $esEstudiante = $user->tieneRol('paciente') || $user->tieneRol('becado');
    $modulosEstudiante = ['psicologia', 'beca'];

    $moduloActivo = session('modulo_activo', 'general');
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'salud']);

    $headerGradient = $esPsicologia
        ? 'from-blue-600 via-indigo-700 to-slate-900'
        : 'from-[var(--color-primary,#c52222)] to-[var(--color-tertiary,#800000)]';

    $primaryColor = $esPsicologia ? '#2563eb' : 'var(--color-primary, #dc2626)';
    $badgeBg = $esPsicologia ? 'bg-blue-600' : 'bg-red-600';
    $iconBg = $esPsicologia
        ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400'
        : 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400';
    $textAccent = $esPsicologia ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-600 dark:text-red-400';
    $hoverBorder = $esPsicologia ? 'hover:border-indigo-500/50' : 'hover:border-red-500/50';
    $ringActive = $esPsicologia ? 'ring-2 ring-indigo-500/30' : 'ring-2 ring-red-500/30';

    $moduloConfig = [
        'administracion' => ['icon' => 'fas fa-cog'],
        'comedor' => ['icon' => 'fas fa-utensils'],
        'salud' => ['icon' => 'fas fa-heartbeat'],
        'psicologia' => ['icon' => 'fas fa-brain'],
        'beca' => ['icon' => 'fas fa-graduation-cap'],
        'transporte' => ['icon' => 'fas fa-bus'],
        'deporte' => ['icon' => 'fas fa-futbol'],
    ];
    $fallback = ['icon' => 'fas fa-cubes'];
@endphp

<x-app-layout>
    <x-slot name="header">
        @include('components.alert')
        <div
            class="dashboard-header bg-gradient-to-r {{ $headerGradient }} rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden mb-6">
            <div class="absolute -top-1/2 -right-10 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none">
            </div>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight flex items-center gap-3 m-0">
                        <i class="fas fa-layer-group"></i> Módulos del Sistema
                    </h1>
                    <p class="mt-2 mb-0 text-base opacity-95">
                        Selecciona el módulo que deseas gestionar durante la sesión actual haciendo clic directamente
                        sobre él.
                    </p>
                </div>

                <div class="hidden md:flex items-center gap-6">
                    <div class="text-right">
                        <div class="text-xs opacity-90 mb-1">📅 Hoy es</div>
                        <div class="font-bold text-xl">
                            {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
                        </div>
                        <div class="text-xs opacity-80 capitalize">
                            {{ \Carbon\Carbon::now()->translatedFormat('l') }}
                        </div>
                    </div>

                    <div
                        class="w-16 h-16 rounded-full overflow-hidden border-4 border-white/30 shadow-lg bg-white flex-shrink-0">
                        <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                            class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <form action="{{ route('admin.modulos.cambiar') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mb-6">

            @foreach ($modulos as $m)
                {{-- Si el usuario es paciente o becado y el módulo no está permitido, lo salta --}}
                @if ($esEstudiante && !in_array($m->key_mod, $modulosEstudiante))
                    @continue
                @endif

                @php
                    $conf = $moduloConfig[$m->key_mod] ?? $fallback;
                    $esActivo = $moduloActivo == $m->key_mod;

                    $activeBorderColor = $esActivo ? $primaryColor : 'var(--border-color)';
                    $ringClass = $esActivo ? $ringActive : '';
                @endphp

                <button type="submit" name="modulo" value="{{ $m->key_mod }}"
                    style="background-color: var(--bg-card); border-color: {{ $activeBorderColor }}; color: var(--text-main);"
                    class="relative p-6 rounded-2xl border shadow-sm hover:shadow-lg {{ $hoverBorder }} transition-all text-center flex flex-col items-center justify-center gap-3 group w-full cursor-pointer {{ $ringClass }}">

                    @if ($esActivo)
                        <span
                            class="absolute top-3 right-3 px-2.5 py-0.5 text-[11px] font-bold rounded-full {{ $badgeBg }} text-white flex items-center gap-1 shadow-sm">
                            <i class="fas fa-check-circle"></i> Activo
                        </span>
                    @endif

                    <div
                        class="w-16 h-16 rounded-2xl {{ $iconBg }} flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                        <i class="{{ $conf['icon'] }}"></i>
                    </div>

                    <div>
                        <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">
                            {{ $m->nombre_mod }}
                        </h5>
                        <span
                            class="text-xs font-semibold {{ $textAccent }} flex items-center justify-center gap-1 opacity-80 group-hover:opacity-100 group-hover:translate-x-1 transition-all">
                            Entrar <i class="fas fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </button>
            @endforeach
        </div>
    </form>
</x-app-layout>
