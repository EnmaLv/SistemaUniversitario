@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 rd-title-sm" style="font-size:1.4rem;">Lotes por Sedes</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem;color:#475569;">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center" style="gap:14px;">
            <div class="text-right d-none d-sm-block">
                <small class="text-muted d-block" style="font-size:0.75rem;">Hoy</small>
                <span style="font-weight:600;font-size:0.95rem;">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
            </div>
            <div
                style="width:46px;height:46px;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(15,23,42,0.08);">
                <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                    style="width:100%;height:100%;object-fit:cover;">
            </div>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="row">
        @forelse ($sedes as $sede)
            <div class="col-md-3 col-sm-6 col-12 mb-3">
                <div class="info-box shadow-sm" style="border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                    <a href="{{ url('admin/movimientos/sedes_lotes/show/' . $sede->id) }}"
                        class="info-box-icon bg-info d-flex align-items-center justify-content-center">
                        <img src="{{ asset('img/restaurante.webp') }}" alt="Sede"
                            style="width:32px;height:32px;object-fit:contain;">
                    </a>
                    <div class="info-box-content">
                        <a href="{{ url('admin/movimientos/sedes_lotes/show/' . $sede->id) }}"
                            class="text-dark font-weight-bold" style="text-decoration:none;">
                            <span class="info-box-text" style="font-size:1rem;">
                                {{ $sede->nombre_sede ?? $sede->nombre }}
                            </span>
                        </a>
                        <span class="info-box-number text-muted mt-1" style="font-size:0.9rem;">
                            {{ number_format($sede->totalInventarioSedeLotes ?? 0, 0, ',', '.') }} Unidades
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center" style="border-radius:12px;">
                    <i class="fas fa-info-circle mr-2"></i> No se encontraron sedes registradas.
                </div>
            </div>
        @endforelse
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
@stop

