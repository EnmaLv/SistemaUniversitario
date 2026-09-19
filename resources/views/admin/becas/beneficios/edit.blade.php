@extends('layouts.app')

@section('content_header')
    <div class="mb-6 flex items-center justify-between rounded-2xl border p-5 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div>
            <h1 class="text-2xl font-extrabold" style="color:var(--text-main);">Editar Beneficio</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                {{ $beneficio->nombre_beneficio }}
            </p>
        </div>
        <a href="{{ route('admin.becas.beneficios.index') }}" class="rounded-xl border px-4 py-2 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="rounded-2xl border p-6 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div class="mb-5 border-b pb-4" style="border-color:var(--border-color);">
            <h3 class="text-lg font-bold" style="color:var(--text-main);">Datos del beneficio</h3>
        </div>
        <form action="{{ route('admin.becas.beneficios.update', $beneficio) }}" method="POST" class="rd-prevent-double-submit">
            @csrf
            @method('PUT')
            @include('admin.becas.beneficios._form')
            <div class="mt-6 flex justify-end gap-3 border-t pt-5" style="border-color:var(--border-color);">
                <a href="{{ route('admin.becas.beneficios.index') }}" class="rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">Cancelar</a>
                <button type="submit" class="rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white hover:bg-red-900">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
@stop
