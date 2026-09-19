@extends('layouts.app')

@section('content_header')
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
                Vista detallada del Registro
            </h1>

            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>

        <!-- Imagen + Fecha -->
        <div class="d-flex align-items-center" style="gap:14px;">
            <div class="text-right d-none d-sm-block">
                <small class="text-muted d-block" style="font-size:0.75rem;">Hoy</small>
                <span style="font-weight:600; font-size:0.95rem;">
                    {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                </span>
            </div>

            <div
                style="
                width:46px;
                height:46px;
                border-radius:12px;
                overflow:hidden;
                box-shadow:0 4px 12px rgba(15,23,42,0.08);
            ">
                <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                    style="width:100%; height:100%; object-fit:cover;">
            </div>
        </div>

    </div>
@stop

@php
    //Formatear la edad para que se actualize dinamicamente
    $edad = \Carbon\Carbon::parse($registro->fecha_nacimiento_persona)->age;
@endphp

@section('content')
    <div class="registro-layout">
        <section class="profile-hero rd-card">
            <div class="profile-hero__content">
                <p class="hero-eyebrow">Registro diario · {{ $registro->nombre_pnf }}</p>
                <h2 class="hero-title">Ficha del estudiante</h2>
                <p class="hero-text">
                    <strong>
                        {{ $registro->nombre_persona . ' ' . $registro->segundo_nombre_persona . ' ' . $registro->apellido_persona . ' ' . $registro->segundo_apellido_persona }}
                    </strong>
                    fue registrado en el sistema con la información detallada a continuación.
                </p>

            </div>

            <div class="profile-hero__actions">
                <a href="{{ url()->previous() }}" class="rd-btn rd-btn-default">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </section>

        <section class="info-grid">
            <article class="info-card rd-card">
                <header>
                    <h3><i class="fas fa-user-circle"></i> Datos personales</h3>
                    <span class="section-hint">Identidad del estudiante</span>
                </header>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Nombre completo</span>
                        <p class="info-value">
                            {{ $registro->nombre_persona . ' ' . $registro->segundo_nombre_persona . ' ' . $registro->apellido_persona . ' ' . $registro->segundo_apellido_persona }}
                        </p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Cédula</span>
                        <p class="info-value">{{ $registro->cedula_persona }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Género</span>
                        <p class="info-value">{{ $registro->genero_persona }}</p>
                    </div>
                    <div class="info-item info-item-inline">
                        <div>
                            <span class="info-label">Fecha de nacimiento</span>
                            <p class="info-value">
                                {{ \Carbon\Carbon::parse($registro->fecha_nacimiento_persona)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <span class="info-label">Edad</span>
                            <p class="info-value">{{ $edad }} años</p>
                        </div>
                    </div>
                </div>
            </article>

            <article class="info-card rd-card">
                <header>
                    <h3><i class="fas fa-address-book"></i> Contacto y PNF</h3>
                    <span class="section-hint">Medios para ubicar al estudiante</span>
                </header>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Teléfono</span>
                        <p class="info-value">{{ $registro->telefono_persona }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Correo electrónico</span>
                        <p class="info-value">{{ $registro->email_persona }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">PNF asociado</span>
                        <p class="info-value">{{ $registro->nombre_pnf }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Semestre</span>
                        <p class="info-value">{{ $registro->semestre_persona }}</p>
                    </div>
                </div>
            </article>

            <article class="info-card rd-card registro-card">
                <header>
                    <h3><i class="fas fa-clock"></i> Registro del sistema</h3>
                    <span class="section-hint">Fecha y hora oficial</span>
                </header>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Fecha de registro</span>
                        <p class="info-value">{{ \Carbon\Carbon::parse($registro->fecha_regis_diario_c)->format('d/m/Y') }}
                        </p>
                        <small class="info-helper">Información tomada del formulario enviado.</small>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Hora registrada</span>
                        <p class="info-value">{{ $registro->hora }}</p>
                        <small class="info-helper">Corresponde a la hora exacta de creación.</small>
                    </div>
                </div>
            </article>
        </section>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/show-noti-register.css') }}">
@endsection

