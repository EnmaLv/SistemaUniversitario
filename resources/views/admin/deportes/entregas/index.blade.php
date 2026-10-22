<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Entregas de material</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-paperclip mr-1" style="color:var(--color-primary)"></i>
                        Material de oficina entregado (no se devuelve) · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                @canRoute('admin.deportes.entregas.create')
                <a href="{{ route('admin.deportes.entregas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Nueva entrega
                </a>
                @endcanRoute
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-3 border-b" style="border-color:var(--border-color);">
                    <form action="{{ route('admin.deportes.entregas.index') }}" method="GET">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Listado de entregas</h3>
                            </div>
                            <div class="flex w-full gap-2 lg:w-auto">
                                <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-96" style="background-color:var(--input-bg);border-color:var(--input-border);">
                                    <i class="fas fa-search text-gray-400"></i>
                                    <input type="text" name="q" value="{{ $buscar }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Buscar por cédula, nombre, dependencia o motivo...">
                                </div>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
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
                                <th class="px-6 py-4">Fecha</th>
                                <th class="px-6 py-4">Recibe</th>
                                <th class="px-6 py-4">Motivo</th>
                                <th class="px-6 py-4 text-center">Productos</th>
                                <th class="px-6 py-4">Entregó</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="fade-in">
                            @forelse ($entregas as $entrega)
                                <x-table-row :id="$entrega->id_ema" class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 text-center font-bold" style="color:var(--text-muted);">
                                        {{ ($entregas->currentPage() - 1) * $entregas->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-main);">{{ $entrega->fecha_ema?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold" style="color:var(--text-main);">{{ $entrega->destinatario() }}</td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-muted);">{{ \Illuminate\Support\Str::limit($entrega->motivo_ema, 60) }}</td>
                                    <td class="px-6 py-4 text-center text-sm font-bold" style="color:var(--text-main);">{{ $entrega->detalles_count }}</td>
                                    <td class="px-6 py-4 text-sm" style="color:var(--text-muted);">{{ $entrega->usuario?->nombre_completo }}</td>
                                    <x-table-actions :id="$entrega->id_ema" baseUrl="admin/deportes/entregas" :edit="false" :toggle="false" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-paperclip fa-3x mb-3" style="opacity: 0.1"></i>
                                        <p>No se encontraron entregas registradas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t flex justify-center" style="border-color:var(--border-color);">
                    {{ $entregas->appends(request()->query())->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
