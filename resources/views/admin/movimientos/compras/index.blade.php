@extends('layouts.app')

@section('content_header')
    @include('components.alert')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
         ">

        <!-- Texto principal -->
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">
                Requisicion
            </h1>

            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>

        <!-- Imagen + Fecha -->
        <div>
            <a href="{{ url('admin/movimientos/compras/create') }}" class="rd-btn rd-btn-primary">
                <i class="fas fa-plus"></i> Crear Requisicion
            </a>
        </div>

    </div>
@stop

@section('content')
    @livewire('compra-index')
@stop


@section('css')
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
@stop

@section('js')
    <script>
        const pdfBtn = document.querySelector('#pdfBtn');
        const pdfRoute = `{{ route('admin.movimientos.compras.export_pdf') }}`;
        if (pdfBtn) {
            pdfBtn.addEventListener('click', function() {

                const url = `${pdfRoute}`;
                window.open(url, '_blank');
            });
        }
    </script>
@stop

