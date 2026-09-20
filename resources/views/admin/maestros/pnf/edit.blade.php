@extends('layouts.app')

@section('title', 'Editar Programa de Formacion')

@section('content_header')
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">Editar programa de formación</h1>
            <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('admin.maestros.pnf.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-600 hover:text-red-600"
            style="border-color: var(--border-color); color: var(--text-main);">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @include('components.alert')
    <div class="rounded-2xl border p-6 shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="mb-6 border-b pb-4" style="border-color: var(--border-color);">
            <h2 class="text-lg font-bold" style="color: var(--text-main);">Datos del programa</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Modifica los datos necesarios.</p>
        </div>
        <form action="{{ route('admin.maestros.pnf.update', ['id' => $pnf->id_pnf]) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><label for="nombre" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Nombre del programa</label><div class="flex items-center rounded-xl border" style="background-color: var(--input-bg); border-color: var(--border-color);"><span class="px-3 text-gray-400"><i class="fas fa-graduation-cap"></i></span><input id="nombre" type="text" name="nombre" value="{{ old('nombre', $pnf->nombre_pnf) }}" class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-red-500/30" style="color: var(--text-main);" required></div>@error('nombre')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
                <div><label for="id_estatus" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Estado</label><div class="flex items-center rounded-xl border" style="background-color: var(--input-bg); border-color: var(--border-color);"><span class="px-3 text-gray-400"><i class="fas fa-toggle-on"></i></span><select id="id_estatus" name="id_estatus" class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none" style="color: var(--text-main);" required><option value="1" {{ $pnf->id_estatus == 1 ? 'selected' : '' }}>Activo</option><option value="2" {{ $pnf->id_estatus == 2 ? 'selected' : '' }}>Inactivo</option></select></div>@error('id_estatus')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
            </div>
            <div class="flex justify-end gap-3 border-t pt-5" style="border-color: var(--border-color);"><a href="{{ route('admin.maestros.pnf.index') }}" class="rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color: var(--border-color); color: var(--text-main);">Cancelar</a><button type="submit" class="rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900"><i class="fas fa-save mr-1"></i> Guardar cambios</button></div>
        </form>
    </div>
@stop
