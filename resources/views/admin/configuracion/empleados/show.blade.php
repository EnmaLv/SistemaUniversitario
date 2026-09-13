@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="background: #ffffff; border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">Datos del Empleado</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                <i class="fas fa-id-card mr-1" style="color: var(--color-secondary)"></i> 
                Visualizando perfil de: <strong>{{ $usuario->username }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.configuracion.empleados.edit', $usuario->id_usuario) }}" class="rd-btn rd-btn-alter">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.configuracion.empleados.index') }}" class="rd-btn rd-btn-primary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="row fade-in">
        <div class="col-md-4 align-self-center">
            <div class="rd-card text-center p-4 mb-4 shadow-sm border-0">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" 
                         style="width: 100px; height: 100px; border: 3px solid var(--color-secondary);">
                        <i class="fas fa-user fa-3x" style="color: var(--color-secondary)"></i>
                    </div>
                </div>
                <h4 class="font-weight-bold mb-1">{{ optional($usuario->persona)->nombre_persona }}</h4>
                <p class="text-muted mb-3">{{ $usuario->username }}</p>
                <hr class="my-4">
                <div class="text-left">
                    <label class="rd-label small text-uppercase">Rol Asignado</label>
                    <div class="p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <i class="fas fa-shield-alt mr-2" style="color: var(--color-secondary)"></i>
                        {{ $usuario->roles->pluck('nombre')->join(', ') ?: 'Sin rol asignado' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="rd-card shadow-sm border-0 overflow-hidden">
                <div class="rd-card-body border-bottom bg-light py-3">
                    <h3 class="rd-title-sm">
                        <i class="fas fa-info-circle mr-2" style="color: var(--color-secondary)"></i>InformaciÃ³n Personal
                    </h3>
                </div>
                
                <div class="rd-card-body p-0">
                    <div class="row p-4">
                        <div class="col-sm-6 mb-4">
                            <label class="rd-label text-muted small">CÃ©dula de Identidad</label>
                            <div class="h6 font-weight-bold">{{ optional($usuario->persona)->cedula_persona ?? 'â€”' }}</div>
                        </div>

                        <div class="col-sm-6 mb-4">
                            <label class="rd-label text-muted small">TelÃ©fono de Contacto</label>
                            <div class="h6 font-weight-bold">
                                <i class="fas fa-phone mr-1 text-success small"></i>
                                {{ optional($usuario->persona)->telefono_persona ?? 'â€”' }}
                            </div>
                        </div>

                        <div class="col-sm-6 mb-4">
                            <label class="rd-label text-muted small">Nombres</label>
                            <div class="h6 font-weight-bold">{{ optional($usuario->persona)->nombre_persona ?? 'â€”' }}</div>
                        </div>

                        <div class="col-sm-6 mb-4">
                            <label class="rd-label text-muted small">Apellidos</label>
                            <div class="h6 font-weight-bold">{{ optional($usuario->persona)->apellido_persona ?? 'â€”' }}</div>
                        </div>
                    </div>

                    <div class="bg-light p-4 border-top">
                        <h3 class="rd-title-sm mb-3" style="font-size: 0.9rem;">Datos de Acceso</h3>
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="rd-label text-muted small">Nombre de Usuario (Login)</label>
                                <div class="p-2 bg-white border rounded" style="font-family: sans-serif; color: var(--color-secondary);">
                                    {{ $usuario->username }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="rd-label text-muted small">Fecha de Registro</label>
                                <div class="p-2 text-muted">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : 'â€”' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
