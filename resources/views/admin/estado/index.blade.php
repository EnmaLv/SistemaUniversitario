@extends('layouts.app')

@section('title', 'Gestión de Estados')

@section('content_header')
    <div class="mb-6 flex items-center justify-between rounded-2xl border p-5 shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text-main);">Estados</h1>
            <p class="mt-1 text-sm" style="color: var(--text-muted);">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>
        <button type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40"
                onclick="document.getElementById('modalCrearEstado').classList.remove('hidden'); document.getElementById('modalCrearEstado').classList.add('flex');">
            <i class="fas fa-plus"></i>
            <span>Nuevo Estado</span>
        </button>
    </div>
@stop

@section('content')
    @livewire('admin.estado-index')
@endsection

@section('scripts')
    <script src="{{ asset('js/validations/estado.js') }}"></script>
@stop