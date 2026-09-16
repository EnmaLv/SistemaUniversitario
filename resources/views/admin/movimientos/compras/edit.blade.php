<x-app-layout>
    @include('components.alert')

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="close" data-dismiss="alert">
                &times;
            </button>
        </div>
    @endif

    <div
        class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-6"
        style="
            background-color: var(--bg-card);
            border-color: var(--border-color);
        ">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-red-800 flex items-center justify-center text-white shadow-lg shadow-red-800/20 shrink-0">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>

                <div>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold tracking-tight"
                        style="color: var(--text-main);">
                        Requisición #{{ $compra->id }}
                    </h1>
                    <p class="mt-0.5 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Gestiona los pasos de la requisición
                        <span class="mx-1">·</span>
                        {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">

                <div class="text-right hidden sm:block">
                    <span class="block text-[10px] uppercase font-bold tracking-wider text-gray-400">
                        Estado actual
                    </span>

                    <span
                        class="text-sm font-extrabold"
                        style="color: var(--text-main);">
                        {{ $compra->estado }}
                    </span>
                </div>

                @if ($compra->estado == 'Pendiente')

                    <form
                        action="{{ route('admin.movimientos.compras.cancelar', $compra) }}"
                        method="POST">

                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all hover:bg-red-50 dark:hover:bg-red-950/20"
                            style="
                                border-color: var(--border-color);
                                color: var(--text-main);
                            "
                            onclick="confirmDelete(event, this)">

                            <i class="fas fa-arrow-left text-[10px]"></i>
                            Cancelar y volver
                        </button>
                    </form>
                @elseif ($compra->estado == 'Enviado al proveedor')
                    <a
                        href="{{ url('admin/movimientos/compras') }}"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-bold transition-all hover:bg-gray-50 dark:hover:bg-white/5"
                        style="
                            border-color: var(--border-color);
                            color: var(--text-main);
                        ">
                        <i class="fas fa-arrow-left text-[10px]"></i>
                        Volver
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="pb-10">
        <div class="max-w-[1400px] mx-auto px-2 sm:px-4 lg:px-6">
            @php
                $pasoActual = match ($compra->estado) {
                    'Pendiente' => 2,
                    'Enviado al proveedor' => 3,
                    default => 3,
                };
            @endphp

            <x-compra-stepper :step="$pasoActual" />

            <div
                class="rounded-2xl border shadow-sm overflow-hidden mb-6"
                style="
                    background-color: var(--bg-card);
                    border-color: var(--border-color);
                ">
                <div
                    class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b"
                    style="border-color: var(--border-color);">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/30 text-red-800 dark:text-red-400 flex items-center justify-center">
                            <i class="fas fa-file-alt text-xs"></i>
                        </div>
                        <div>
                            <h3
                                class="text-sm font-extrabold tracking-tight"
                                style="color: var(--text-main);">
                                Paso 1 · Requisición
                            </h3>
                            <p class="text-[11px] text-gray-400 font-medium">
                                Información general de la solicitud
                            </p>
                        </div>
                    </div>
                    <span
                        class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide
                        {{ $compra->estado == 'Pendiente'
                            ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400'
                            : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400' }}">

                        <i class="fas fa-circle text-[5px]"></i>
                        {{ $compra->estado }}
                    </span>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div>
                            <label class="block titulos">
                                Proveedor
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-user-tie absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                                </i>
                                <select
                                    disabled
                                    id="proveedor_id"
                                    name="proveedor_id"
                                    class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border appearance-none cursor-not-allowed opacity-70"
                                    style="
                                        background-color: rgba(0,0,0,0.02);
                                        border-color: var(--border-color);
                                        color: var(--text-main);
                                    ">
                                    @foreach ($proveedores as $proveedor)
                                        <option
                                            value="{{ $proveedor->id }}"
                                            {{ old('proveedor_id', $compra->proveedor_id) == $proveedor->id ? 'selected' : '' }}>

                                            {{ $proveedor->nombre }}

                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block titulos">
                                Módulo / Área
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-cubes absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                                </i>
                                <select
                                    disabled
                                    name="modulo_id" 
                                    id="modulo_id"
                                    class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border appearance-none cursor-not-allowed opacity-70"
                                    style="
                                        background-color: rgba(0,0,0,0.02);
                                        border-color: var(--border-color);
                                        color: var(--text-main);">
                                    @foreach ($modulos as $modulo)
                                        <option value="{{ $modulo->id }}"
                                            {{ old('modulo_id', $compra->modulo_id) == $modulo->id ? 'selected' : '' }}>
                                            {{ $modulo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block titulos">
                                Fecha de requisición
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-calendar-alt absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                                </i>
                                <input
                                    type="text"
                                    readonly
                                    value="{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y H:i') }}"
                                    class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border opacity-70 cursor-not-allowed"
                                    style="
                                        background-color: rgba(0,0,0,0.02);
                                        border-color: var(--border-color);
                                        color: var(--text-main);
                                    ">
                            </div>
                        </div>

                        <div>
                            <label class="block titulos">
                                Observaciones
                            </label>
                            <div class="relative">
                                <i
                                    class="fas fa-sticky-note absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                                </i>
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $compra->observaciones ?: 'Sin observaciones' }}"
                                    class="w-full pl-10 pr-3.5 py-3 text-sm font-medium rounded-xl border opacity-70 cursor-not-allowed"
                                    style="
                                        background-color: rgba(0,0,0,0.02);
                                        border-color: var(--border-color);
                                        color: var(--text-main);
                                    ">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border shadow-sm overflow-hidden mb-6"
                style="
                    background-color: var(--bg-card);
                    border-color: var(--border-color);
                ">

                <div
                    class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b"
                    style="border-color: var(--border-color);">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/30 text-red-800 dark:text-red-400 flex items-center justify-center">
                            <i class="fas fa-boxes text-xs"></i>
                        </div>

                        <div>
                            <h3
                                class="text-sm font-extrabold tracking-tight"
                                style="color: var(--text-main);">
                                Paso 2 · Productos
                            </h3>
                            <p class="text-[11px] text-gray-400 font-medium">
                                Agrega y revisa los productos de la requisición
                            </p>
                        </div>
                    </div>
                    @if ($compra->detalleCompras->count() > 0)
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-bold">

                            <i class="fas fa-box text-[9px]"></i>

                            {{ $compra->detalleCompras->count() }}
                            {{ $compra->detalleCompras->count() == 1 ? 'producto' : 'productos' }}
                        </span>
                    @endif
                </div>
                <div class="p-5 sm:p-6">
                    <livewire:admin.movimientos.compras.items-compra
                        :compra="$compra" />

                </div>

            </div>

            @if ($compra->estado == 'Enviado al proveedor')
                <div
                    class="rounded-2xl border shadow-sm overflow-hidden"
                    style="
                        background-color: var(--bg-card);
                        border-color: var(--border-color);
                    ">
                    <div
                        class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b"
                        style="border-color: var(--border-color);">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/30 text-red-800 dark:text-red-400 flex items-center justify-center">
                                <i class="fas fa-calendar-check text-xs"></i>
                            </div>
                            <div>
                                <h3
                                    class="text-sm font-extrabold tracking-tight"
                                    style="color: var(--text-main);">
                                    Paso 3 · Vencimientos
                                </h3>

                                <p class="text-[11px] text-gray-400 font-medium">
                                    Registra las fechas de vencimiento de los productos
                                </p>
                            </div>
                        </div>

                        <span
                            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold">
                            <i class="fas fa-check-circle text-[9px]"></i>
                            Pedido enviado
                        </span>
                    </div>

                    <div class="p-5 sm:p-6">
                        <livewire:admin.movimientos.compras.fechas-compra
                            :compra="$compra" />
                        <form
                            action="{{ route('admin.movimientos.compras.finalizarCompra', $compra) }}"
                            method="POST"
                            class="rd-prevent-double-submit mt-6">
                            @csrf
                            <div
                                class="pt-5 border-t flex flex-col sm:flex-row items-center justify-between gap-4"
                                style="border-color: var(--border-color);">

                                <div class="flex items-center gap-2 text-xs text-gray-400">
                                    <i class="fas fa-info-circle"></i>
                                    <span>
                                        Verifica las fechas antes de finalizar la requisición.
                                    </span>
                                </div>
                                <button
                                    type="submit"
                                    class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md shadow-red-800/20 active:scale-95 transition-all">

                                    <i class="fas fa-check text-xs"></i>

                                    Finalizar requisición
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        if (typeof confirmDelete === 'undefined') {
            function confirmDelete(event, button) {
                event.preventDefault();

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Se perderán todos los productos agregados.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#9f1239',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Volver',
                    customClass: {
                        popup: 'rounded-2xl dark:bg-gray-800 dark:text-gray-100 dark:border dark:border-gray-700'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.closest('form').submit();
                    }
                });
            }
        }
    </script>

    @livewireStyles

    <style>
        .titulos {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .5rem;
            color: var(--text-main);
        }
    </style>

    @livewireScripts

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;

                if (payload.toast) {
                    const Toast = window.Toast || Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: payload.timer || 3000,
                        timerProgressBar: true
                    });

                    Toast.fire({
                        icon: payload.icon || 'success',
                        title: payload.title || payload.text
                    });
                } else {
                    Swal.fire({
                        icon: payload.icon || 'info',
                        title: payload.title || '',
                        text: payload.text || '',
                        confirmButtonColor: payload.confirmButtonColor || '#9f1239',
                        confirmButtonText: payload.confirmButtonText || 'Aceptar',
                        timer: payload.timer || 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'rounded-2xl dark:bg-gray-800 dark:text-gray-100 dark:border dark:border-gray-700'
                        }
                    });
                }
            });
        });
    </script>
</x-app-layout>