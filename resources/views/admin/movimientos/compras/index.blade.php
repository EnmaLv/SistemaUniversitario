<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Requisición
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Compras a proveedores: la sede principal las registra y, al recibirlas, se reparten entre todas las sedes.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    @canRoute('admin.movimientos.compras.export_pdf')
                        <button id="pdfBtn" type="button"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 active:scale-95 transition-all">
                            <i class="fas fa-file-pdf text-red-600"></i>
                            <span>Exportar PDF</span>
                        </button>
                    @endcanRoute

                    @canRoute('admin.movimientos.compras.create')
                        @if ($motivoSinCompra)
                            <span title="{{ $motivoSinCompra }}"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold text-sm cursor-not-allowed">
                                <i class="fas fa-lock text-xs"></i>
                                <span>Crear Requisición</span>
                            </span>
                        @else
                        <a href="{{ url('admin/movimientos/compras/create') }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass ?? 'bg-red-800 hover:bg-red-900 hover:text-white' }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Crear Requisición</span>
                        </a>
                        @endif
                    @endcanRoute
                </div>
            </div>

            @if ($motivoSinCompra)
                @canRoute('admin.movimientos.compras.create')
                    <div class="mb-4 p-4 rounded-2xl border border-amber-200 bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:border-amber-900 dark:text-amber-300 text-xs flex items-start gap-2">
                        <i class="fas fa-store-alt mt-0.5"></i>
                        <span>{{ $motivoSinCompra }}</span>
                    </div>
                @endcanRoute
            @endif

            @livewire('compra-index')

        </div>
    </div>

</x-app-layout>