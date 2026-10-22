<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Calendarios deportivos</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-calendar-alt mr-1" style="color:var(--color-primary)"></i>
                        Ciclos o períodos deportivos por sede · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                @canRoute('admin.deportes.calendarios.create')
                <a href="{{ route('admin.deportes.calendarios.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Registrar calendario
                </a>
                @endcanRoute
            </div>

            @include('admin.deportes.partials.errores')

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-3 border-b" style="border-color:var(--border-color);">
                    <form action="{{ route('admin.deportes.calendarios.index') }}" method="GET">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Listado de calendarios</h3>
                            </div>
                            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                                <select name="estado" class="rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="" @selected($estado === '')>Todos</option>
                                    <option value="1" @selected($estado === '1')>Activos</option>
                                    <option value="2" @selected($estado === '2')>Inactivos</option>
                                </select>
                                <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-96" style="background-color:var(--input-bg);border-color:var(--input-border);">
                                    <i class="fas fa-search text-gray-400"></i>
                                    <input type="text" name="q" value="{{ $buscar }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Buscar por nombre...">
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                                    <i class="fas fa-search text-xs"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-4 text-center">#</th>
                                <th class="px-6 py-4">Calendario</th>
                                <th class="px-6 py-4">Vigencia</th>
                                <th class="px-6 py-4">Sede</th>
                                <th class="px-6 py-4">Coordinador</th>
                                <th class="px-6 py-4 text-center">Horarios activos</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="fade-in">
                            @forelse ($calendarios as $calendario)
                                @php
                                    $vigente = $calendario->contieneFecha(now());
                                @endphp
                                <x-table-row :id="$calendario->id_cld" class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 text-center font-bold" style="color:var(--text-muted);">
                                        {{ ($calendarios->currentPage() - 1) * $calendarios->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold" style="color:var(--text-main);">{{ $calendario->nombre_cld }}</div>
                                        <div class="text-xs" style="color:var(--text-muted);">{{ \Illuminate\Support\Str::limit($calendario->descripcion_cld, 70) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap" style="color:var(--text-main);">
                                        {{ $calendario->fecha_inicio_cld->format('d/m/Y') }} - {{ $calendario->fecha_fin_cld->format('d/m/Y') }}
                                        @if ($vigente)
                                            <span class="ml-1 rounded-md bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-emerald-700">En curso</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">{{ $calendario->sede?->nombre_sed ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-muted);">
                                        {{ trim(($calendario->coordinador?->persona?->nombres_per ?? '') . ' ' . ($calendario->coordinador?->persona?->apellidos_per ?? '')) ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                                            <i class="fas fa-clock text-[10px]" style="color:var(--color-primary);"></i>
                                            {{ $calendario->horarios_activos_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @include('admin.deportes.partials.estado', ['status' => $calendario->status_id])
                                    </td>
                                    <x-table-actions :id="$calendario->id_cld" baseUrl="admin/deportes/calendarios" :show="false" :status="(int) $calendario->status_id === 1" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-calendar-alt fa-3x mb-3" style="opacity: 0.1"></i>
                                        <p>No hay calendarios deportivos registrados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t" style="border-color:var(--border-color);">
                    {{ $calendarios->appends(request()->query())->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
