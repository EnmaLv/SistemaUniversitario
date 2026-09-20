<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)] comedor-modern">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Registro de comida
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="registro-comida-panel">
                <livewire:registro-comida />
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden mt-6">
                <div class="px-6 py-5 border-b" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Registros de sobrantes</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Últimos movimientos del día</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center">#</th>
                                <th class="px-6 py-4 text-center">Fecha del registro</th>
                                <th class="px-6 py-4 text-center">Cantidad sobrante</th>
                                <th class="px-6 py-4 text-center">Motivo</th>
                                <th class="px-6 py-4 text-center">Acción tomada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse ($sobrantes as $sobrante)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-black/20 transition-colors">
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $sobrante->fecha }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                        style="color: var(--text-main);">
                                        {{ $sobrante->cantidad_sobrante }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        {{ $sobrante->motivo ?: 'Sin motivo' }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        {{ $sobrante->accion_tomada ?: 'Sin acción registrada' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-6 py-12 text-center text-xs font-bold uppercase text-gray-400">
                                        No hay registros de sobrantes
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
                    {{ $sobrantes->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('css')
        <link rel="stylesheet" href="{{ asset('css/registro-comida.css') }}">
        <style>
            .comedor-modern .registro-comida-panel .rd-wrapper {
                padding: 0;
                background: transparent;
            }

            .comedor-modern .registro-comida-panel .rd-card {
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: 1rem;
                box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
            }

            .comedor-modern .registro-comida-panel .rd-card-header,
            .comedor-modern .registro-comida-panel .rd-card-body,
            .comedor-modern .registro-comida-panel .rd-card-footer {
                background: transparent;
                border-color: var(--border-color);
            }

            .comedor-modern .registro-comida-panel .rd-title,
            .comedor-modern .registro-comida-panel .rd-title-sm {
                color: var(--text-main);
            }

            .comedor-modern .registro-comida-panel .rd-sub,
            .comedor-modern .registro-comida-panel .rd-sub-sm {
                color: var(--text-muted);
            }
        </style>
    @endpush
</x-app-layout>
