@extends('layouts.app')

@php
    $sedeId = request()->segment(count(request()->segments()));
    $sedeNombre = $sedes->firstWhere('id', $sedeId)?->nombre ?? 'Sede';
@endphp

@section('title', 'Inventario de sede')

@section('content_header')
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                <a href="{{ route('admin.movimientos.sedes_lotes') }}" class="transition hover:text-red-600">Existencia por sedes</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span>{{ $sedeNombre }}</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">
                Inventario de {{ $sedeNombre }}
            </h1>
            <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                Lotes y productos disponibles en esta sede · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('admin.movimientos.sedes_lotes') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-600 hover:text-red-600"
            style="border-color: var(--border-color); color: var(--text-main);">
            <i class="fas fa-arrow-left"></i>
            Volver a sedes
        </a>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="overflow-hidden rounded-2xl border shadow-sm"
        style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="flex flex-col gap-4 border-b p-5 lg:flex-row lg:items-center lg:justify-between"
            style="border-color: var(--border-color);">
            <div>
                <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Inventario registrado</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Consulta los lotes asignados a esta sede.</p>
            </div>
            <form action="{{ route('admin.movimientos.sedes_lotes.show', $sedeId) }}" method="GET"
                class="flex w-full gap-2 sm:w-auto" role="search">
                <input type="text" name="buscar" value="{{ $buscar ?? '' }}"
                    placeholder="Buscar lote o producto"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/20 sm:w-72"
                    style="background-color: var(--input-bg); border-color: var(--input-border); color: var(--text-main);">
                <button type="submit" title="Buscar"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-700 text-white shadow-sm transition hover:bg-red-800">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left">
                <thead>
                    <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
                        <th class="px-5 py-4 text-center">#</th>
                        <th class="px-5 py-4">Código de lote</th>
                        <th class="px-5 py-4">Producto</th>
                        <th class="px-5 py-4 text-right">Cantidad (U)</th>
                        <th class="px-5 py-4 text-right">Cantidad (g)</th>
                        <th class="px-5 py-4">Entrada</th>
                        <th class="px-5 py-4">Vencimiento</th>
                        <th class="px-5 py-4">Proveedor</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm" style="divide-color: var(--border-color);">
                    @forelse($sede as $loteSede)
                        <tr class="transition hover:bg-red-50/40 dark:hover:bg-red-950/20">
                            <td class="px-5 py-4 text-center font-bold text-gray-400">
                                {{ ($sede->currentPage() - 1) * $sede->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-5 py-4 font-bold" style="color: var(--text-main);">{{ $loteSede->lote->codigo_lote }}</td>
                            <td class="px-5 py-4 font-medium" style="color: var(--text-main);">{{ $loteSede->lote->producto->nombre }}</td>
                            <td class="px-5 py-4 text-right font-bold" style="color: var(--text-main);">{{ $loteSede->cantidad }}</td>
                            <td class="px-5 py-4 text-right" style="color: var(--text-main);">{{ $loteSede->cantidad_convertida }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $loteSede->lote->fecha_entrada }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $loteSede->lote->fecha_vencimiento }}</td>
                            <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $loteSede->lote->proveedor->nombre }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center text-sm font-bold text-gray-400">
                                No hay registros de inventario para esta sede.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sede->hasPages())
            <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
                {{ $sede->onEachSide(1)->appends(request()->query())->links('components.pagination') }}
            </div>
        @endif
    </div>
@stop
