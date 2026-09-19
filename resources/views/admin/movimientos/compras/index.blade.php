<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] px-4 pb-12 pt-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">

            @include('components.alert')
            <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">Requisiciones</h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.movimientos.compras.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Crear requisición
                </a>
            </div>

            @livewire('compra-index')
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('click', function (event) {
        if (event.target.closest('#pdfBtn')) {
            window.open(@json(route('admin.movimientos.compras.export_pdf')), '_blank');
        }
    });
</script>
