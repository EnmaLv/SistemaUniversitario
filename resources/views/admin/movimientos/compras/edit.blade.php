<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-800 bg-red-950/30 p-4 text-sm text-red-300">
                    <strong>Error:</strong>
                    <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                        Requisición N.º {{ $compra->id }}
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Agrega productos y completa la requisición de compra.
                    </p>
                </div>
                @if ($compra->estado == 'Pendiente')
                    <form action="{{ route('admin.movimientos.compras.cancelar', $compra) }}" method="POST"
                        onsubmit="event.preventDefault(); AppModal.confirm('¿Cancelar requisición?', 'Se perderán los productos agregados.').then(ok => { if(ok) this.submit(); });">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-700 bg-red-800 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-900">
                            <i class="fas fa-arrow-left text-xs"></i> Cancelar y volver
                        </button>
                    </form>
                @else
                    <a href="{{ route('admin.movimientos.compras.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-red-700 bg-red-800 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-900">
                        <i class="fas fa-arrow-left text-xs"></i> Volver
                    </a>
                @endif
            </div>

            <div class="mb-4 overflow-hidden rounded-2xl border shadow-sm" style="background:var(--bg-card);border-color:var(--border-color);">
                <div class="border-b px-6 py-5" style="border-color:var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color:var(--text-main);">Paso 1 · Requisición creada</h2>
                </div>
                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-4">
                    <div><label class="mb-2 block text-sm font-bold" style="color:var(--text-main);">Proveedor</label><input value="{{ $compra->proveedor->nombre }}" disabled class="w-full rounded-xl border px-4 py-3 text-sm" style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);"></div>
                    <div><label class="mb-2 block text-sm font-bold" style="color:var(--text-main);">Fecha</label><input value="{{ $compra->fecha }}" disabled class="w-full rounded-xl border px-4 py-3 text-sm" style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);"></div>
                    <div><label class="mb-2 block text-sm font-bold" style="color:var(--text-main);">Observaciones</label><input value="{{ $compra->observaciones ?: 'Sin observaciones' }}" disabled class="w-full rounded-xl border px-4 py-3 text-sm" style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);"></div>
                    <div><label class="mb-2 block text-sm font-bold" style="color:var(--text-main);">Estado</label><input value="{{ $compra->estado }}" disabled class="w-full rounded-xl border px-4 py-3 text-sm" style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);"></div>
                </div>
            </div>

            <div class="mb-4 overflow-hidden rounded-2xl border shadow-sm" style="background:var(--bg-card);border-color:var(--border-color);">
                <div class="border-b px-6 py-5" style="border-color:var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color:var(--text-main);">Paso 2 · Agregar productos</h2>
                </div>
                <div class="p-6"><livewire:admin.movimientos.compras.items-compra :compra="$compra" /></div>
            </div>

            @if ($compra->estado == 'Enviado al proveedor')
                <div class="overflow-hidden rounded-2xl border shadow-sm" style="background:var(--bg-card);border-color:var(--border-color);">
                    <div class="border-b px-6 py-5" style="border-color:var(--border-color);"><h2 class="text-lg font-extrabold" style="color:var(--text-main);">Paso 3 · Registrar fechas de vencimiento</h2></div>
                    <div class="p-6">
                        <livewire:admin.movimientos.compras.fechas-compra :compra="$compra" />
                    </div>
                </div>
            @endif
        </div>
    </div>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal', (data) => {
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                    confirmButtonColor: '#991b1b',
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        });
    </script>
</x-app-layout>
