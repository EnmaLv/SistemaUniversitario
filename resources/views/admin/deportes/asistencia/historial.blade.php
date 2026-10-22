<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.calendarios.show', $actividad->id_cld) }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a la actividad
            </a>

            <div class="mt-2 mb-6 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Asistencia por persona</h1>
                    <p class="mt-1 text-sm font-semibold" style="color:var(--text-main);">{{ $actividad->nombre_cld }} · {{ $actividad->descripcionCuando() }}</p>
                    <p class="mt-1 text-sm" style="color:var(--text-muted);">Cuántas sesiones asistió cada persona de las que le correspondían. Solo cuentan las sesiones con la asistencia cerrada.</p>
                </div>
                @canRoute('admin.deportes.asistencia.historial_pdf')
                <a href="{{ route('admin.deportes.asistencia.historial_pdf', $actividad->id_cld) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                    <i class="fas fa-file-pdf text-xs"></i> Descargar PDF
                </a>
                @endcanRoute
            </div>

            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border p-4 text-center shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <p class="text-[10px] font-black uppercase tracking-wider" style="color:var(--text-muted);">Sesiones cerradas</p>
                    <p class="text-3xl font-black" style="color:var(--text-main);">{{ $historial['totalSesiones'] }}</p>
                </div>
                <div class="rounded-2xl border p-4 text-center shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <p class="text-[10px] font-black uppercase tracking-wider" style="color:var(--text-muted);">Personas</p>
                    <p class="text-3xl font-black" style="color:var(--text-main);">{{ $historial['filas']->count() }}</p>
                </div>
                <div class="col-span-2 rounded-2xl border p-4 text-center shadow-sm sm:col-span-1" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <p class="text-[10px] font-black uppercase tracking-wider" style="color:var(--text-muted);">Asistencias registradas</p>
                    <p class="text-3xl font-black text-emerald-600">{{ $historial['filas']->sum('presentes') }}</p>
                </div>
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[12px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-4 py-3 text-center">#</th>
                                <th class="px-4 py-3">Cédula</th>
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3 text-center">Asistió</th>
                                <th class="px-4 py-3 text-center">Faltó</th>
                                <th class="px-4 py-3">Resumen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historial['filas'] as $fila)
                                <tr class="border-b text-sm" style="border-color:var(--border-color);color:var(--text-main);">
                                    <td class="px-4 py-3 text-center font-bold" style="color:var(--text-muted);">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $fila->cedula_per }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $fila->nombres_per }} {{ $fila->apellidos_per }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-emerald-700">{{ $fila->presentes }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-red-700">{{ $fila->inasistentes }}</td>
                                    <td class="px-4 py-3">
                                        <p class="text-xs font-semibold">Asistió {{ $fila->presentes }} de {{ $fila->sesiones }} sesiones ({{ $fila->porcentaje }}%)</p>
                                        <div class="mt-1 h-2 w-40 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-2 rounded-full {{ $fila->porcentaje >= 75 ? 'bg-emerald-500' : ($fila->porcentaje >= 50 ? 'bg-amber-500' : 'bg-red-600') }}" style="width: {{ $fila->porcentaje }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm" style="color:var(--text-muted);">
                                        Todavía no hay sesiones con la asistencia cerrada. Toma y cierra la asistencia de cada sesión desde "Tomar asistencia".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
