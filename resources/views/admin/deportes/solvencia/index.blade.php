<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Solvencia</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-file-signature mr-1" style="color:var(--color-primary)"></i>
                        Revisa si una persona debe algo prestado (implementos deportivos o material de oficina) o tiene sanciones · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border p-5 shadow-sm mb-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <form action="{{ route('admin.deportes.solvencia.index') }}" method="GET" class="flex flex-col gap-2 sm:flex-row">
                    <div class="flex flex-1 items-center gap-2 rounded-xl border px-3 py-2.5" style="background-color:var(--input-bg);border-color:var(--input-border);">
                        <i class="fas fa-id-card text-gray-400"></i>
                        <input type="text" name="cedula" value="{{ $cedula }}" required class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Cédula de la persona">
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                        <i class="fas fa-search text-xs"></i> Consultar
                    </button>
                </form>

                @if ($noEncontrada)
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
                        <i class="fas fa-user-times mr-1"></i> No existe ninguna persona registrada con esa cédula.
                    </div>
                @endif
            </div>

            @if ($resultado)
                @php $persona = $resultado['persona']; @endphp
                <div class="rounded-2xl border p-5 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-lg font-extrabold" style="color:var(--text-main);">{{ $persona->nombres_per }} {{ $persona->apellidos_per }}</p>
                            <p class="text-xs" style="color:var(--text-muted);">C.I. {{ $persona->cedula_per }}</p>
                        </div>
                        <div class="flex flex-col items-start gap-2 md:items-end">
                            @if ($resultado['solvente'])
                                <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-lg font-black uppercase tracking-wide text-white">
                                    <i class="fas fa-check-circle"></i> Solvente
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-xl bg-rose-700 px-5 py-2 text-lg font-black uppercase tracking-wide text-white">
                                    <i class="fas fa-times-circle"></i> No solvente
                                </span>
                            @endif
                            @canRoute('admin.deportes.solvencia.pdf')
                                <a href="{{ route('admin.deportes.solvencia.pdf', ['cedula' => $persona->cedula_per]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2 text-sm font-extrabold text-white hover:bg-red-900">
                                    <i class="fas fa-file-pdf text-xs"></i> Constancia en PDF
                                </a>
                            @endcanRoute
                        </div>
                    </div>

                    @if ($resultado['bloquea_titulo'])
                        <div class="mt-5 rounded-xl border-2 border-rose-300 bg-rose-50 p-4 text-sm font-bold text-rose-800 dark:border-rose-800 dark:bg-rose-950/30 dark:text-rose-300">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            La persona tiene una sanción activa por préstamos: NO se puede firmar la solvencia para el título hasta que la sanción se levante por devolución o reposición de lo prestado.
                        </div>
                    @endif

                    @foreach ($resultado['modulos'] as $modulo)
                        <div class="mt-5">
                            <h3 class="text-sm font-extrabold uppercase tracking-wider" style="color:var(--text-muted);">{{ $modulo['etiqueta'] }}</h3>
                            @if ($modulo['pendientes'] === [])
                                <p class="mt-2 text-sm font-semibold text-emerald-600"><i class="fas fa-check mr-1"></i> Sin préstamos pendientes ni sanciones activas.</p>
                            @else
                                <ul class="mt-2 space-y-2">
                                    @foreach ($modulo['pendientes'] as $pendiente)
                                        <li class="rounded-xl border p-3" style="border-color:var(--border-color);">
                                            <p class="text-sm font-bold {{ $pendiente['tipo'] === 'sancion' ? 'text-rose-600' : 'text-amber-600' }}">
                                                <i class="fas {{ $pendiente['tipo'] === 'sancion' ? 'fa-ban' : 'fa-hand-holding' }} mr-1"></i>{{ $pendiente['titulo'] }}
                                            </p>
                                            <p class="text-sm" style="color:var(--text-main);">{{ $pendiente['detalle'] }}</p>
                                            @if ($pendiente['fecha'])
                                                <p class="text-xs" style="color:var(--text-muted);">Desde {{ $pendiente['fecha'] }}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
