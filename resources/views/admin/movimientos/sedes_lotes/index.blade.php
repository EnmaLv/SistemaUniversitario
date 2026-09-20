@extends('layouts.app')

@section('title', 'Existencia por sedes')

@section('content_header')
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">
                Existencia por sedes
            </h1>
            <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                Consulta el inventario disponible en cada sede ·
                {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
                <small class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Usuario</small>
                <span class="text-sm font-bold" style="color: var(--text-main);">
                    {{ auth()->user()->persona->nombre_persona }}
                </span>
            </div>
            <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                class="h-11 w-11 rounded-xl object-cover shadow-sm">
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @forelse ($sedes as $sede)
            <a href="{{ route('admin.movimientos.sedes_lotes.show', $sede->id) }}"
                class="group rounded-2xl border p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300">
                        <i class="fas fa-store text-xl" aria-hidden="true"></i>
                    </div>
                    <i class="fas fa-arrow-up-right-from-square text-sm text-gray-400 transition group-hover:text-red-600"></i>
                </div>
                <h2 class="mt-5 truncate text-base font-extrabold" style="color: var(--text-main);">
                    {{ $sede->nombre_sede ?? $sede->nombre }}
                </h2>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Inventario disponible</span>
                    <span class="text-lg font-black text-red-700 dark:text-red-400">
                        {{ number_format($sede->totalInventarioSedeLotes ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-1 text-right text-[11px] font-bold uppercase tracking-wider text-gray-400">unidades</div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border px-6 py-14 text-center shadow-sm"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <i class="fas fa-building-circle-exclamation mb-3 text-3xl text-gray-400"></i>
                <p class="text-sm font-bold text-gray-500 dark:text-gray-400">No se encontraron sedes registradas.</p>
            </div>
        @endforelse
    </div>
@stop
