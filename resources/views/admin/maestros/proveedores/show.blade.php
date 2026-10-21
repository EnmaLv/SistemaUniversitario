<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">{{ $proveedor->empresa ?: $proveedor->nombre }}</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium" style="color:var(--text-muted);">
                        <i class="fas fa-truck mr-1" style="color:var(--color-primary)"></i>
                        {{ $proveedor->nombre ?: 'Sin contacto' }} · {{ $proveedor->telefono }} · {{ $proveedor->email }}
                    </p>
                </div>
                <a href="{{ route('admin.maestros.proveedores.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-4 border-b" style="border-color:var(--border-color);">
                    <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Compras del proveedor</h3>
                    <p class="text-xs" style="color:var(--text-muted);">{{ $proveedor->direccion ?: 'Sin dirección registrada' }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-4">N.º</th>
                                <th class="px-6 py-4">Fecha</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($proveedor->compras as $compra)
                                <tr class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 font-bold" style="color:var(--text-muted);">{{ $compra->id }}</td>
                                    <td class="px-6 py-4" style="color:var(--text-main);">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4" style="color:var(--text-main);">{{ ucfirst($compra->estado) }}</td>
                                    <td class="px-6 py-4 text-right font-bold" style="color:var(--text-main);">{{ number_format((float) $compra->total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center" style="color:var(--text-muted);">El proveedor no tiene compras registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
