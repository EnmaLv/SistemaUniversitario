@extends('layouts.app')

@section('title', 'Beneficios de Becas')

@section('content_header')
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">
                Beneficios de Becas
            </h1>
            <p class="mt-1 text-sm text-gray-500">Beneficios disponibles para asociar a becas.</p>
        </div>
        <a href="{{ route('admin.becas.beneficios.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white transition-colors hover:bg-red-900">
            <i class="fas fa-plus"></i>
            Nuevo Beneficio
        </a>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="mb-3 flex flex-col gap-4 rounded-2xl border p-2.5 shadow-sm lg:flex-row lg:items-center"
        style="background-color: var(--bg-card); border-color: var(--border-color);">
        <form action="{{ route('admin.becas.beneficios.index') }}" method="GET" class="relative w-full">
            <input type="hidden" name="activo" value="{{ request('activo', 1) }}">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                <i class="fas fa-search"></i>
            </div>
            <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar beneficio..."
                class="w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
        </form>
        <div class="flex items-center gap-2 rounded-xl border px-3 py-2" style="border-color: var(--border-color);">
            <span class="text-[11px] font-black uppercase tracking-wider text-gray-500">Activos</span>
            <label class="relative inline-flex cursor-pointer items-center">
                <input id="estadoToggle" type="checkbox" class="peer sr-only"
                    {{ request('activo', 1) == 1 ? 'checked' : '' }}>
                <span class="h-6 w-10 rounded-full bg-gray-300 peer-checked:bg-red-700 dark:bg-gray-700"></span>
                <span
                    class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-4"></span>
            </label>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border shadow-sm"
        style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b text-[13px] font-black uppercase tracking-wider"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <th class="px-6 py-4 text-center" style="width: 80px;">#</th>
                        <th class="px-6 py-4">Nombre</th>
                        <th class="px-6 py-4">Descripción</th>
                        <th class="px-6 py-4 text-center" style="width: 140px;">Estado</th>
                        <th class="px-6 py-4 text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-xs font-medium">
                    @forelse($beneficios as $beneficio)
                        <x-table-row :id="$beneficio->id">
                            <td class="px-6 py-4 text-center" style="color: var(--text-muted);">
                                {{ ($beneficios->currentPage() - 1) * $beneficios->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 font-bold" style="color: var(--text-main);">
                                {{ $beneficio->nombre_beneficio }}
                            </td>
                            <td class="px-6 py-4" style="color: var(--text-muted);">
                                {{ $beneficio->descripcion ?: 'Sin descripción' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                @if ($beneficio->status)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        <i class="fas fa-check-circle"></i>
                                        Activo
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400">
                                        <i class="fas fa-times-circle"></i>
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="acciones-wrap relative flex h-8 items-center justify-center">
                                    <div
                                        class="acciones-trigger flex h-8 w-8 items-center justify-center rounded-xl border text-gray-500 shadow-sm transition-all hover:bg-rose-50 hover:text-rose-600 dark:border-gray-600/50 dark:text-gray-400 dark:hover:bg-rose-950/50">
                                        <i class="fas fa-ellipsis-vertical text-xs"></i>
                                    </div>
                                    <div class="acciones-panel">
                                        <a href="{{ route('admin.becas.beneficios.edit', $beneficio) }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-amber-100 hover:text-amber-500 dark:hover:bg-amber-950/50"
                                            title="Editar">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <form action="{{ route('admin.becas.beneficios.toggle', $beneficio) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            @if ($beneficio->status)
                                                <button type="submit"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50"
                                                    title="Inactivar">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            @else
                                                <button type="submit"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-emerald-100 hover:text-emerald-500 dark:hover:bg-emerald-950/50"
                                                    title="Activar">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                <i class="fas fa-gift mb-3 block text-3xl text-gray-300 dark:text-gray-700"></i>
                                No hay beneficios registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
            {{ $beneficios->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
        </div>
    </div>
@stop

@push('js')
    <script>
        document.getElementById('estadoToggle')?.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('activo', this.checked ? '1' : '0');
            window.location.href = url.toString();
        });
    </script>
@endpush
