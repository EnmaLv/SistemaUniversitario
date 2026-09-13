<!-- Empleados index (migrated from empleos) -->
@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="background: #ffffff; border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">GestiÃ³n de Empleados</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                <i class="fas fa-user-circle mr-1" style="color: var(--color-secondary)"></i> 
                Usuario: <strong>{{ auth()->user()->username }}</strong>
            </p>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="rd-card rd-card-full shadow-sm border-0 overflow-hidden">
        <div class="rd-card-body border-bottom bg-white">
            <form action="{{ route('admin.configuracion.empleados.index') }}" method="GET">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h3 class="rd-title-sm">
                            <i class="fas fa-users-cog mr-2" style="color: var(--color-secondary)"></i>Directorio de Personal
                        </h3>
                    </div>
                    
                    <div class="col-md-8 d-flex justify-content-end gap-2">
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group" style="min-width: 250px;">
                            <span><i class="fas fa-filter"></i></span>
                            <select name="rol" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100">
                                <option value="">Todos los Roles</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id_rol }}" {{ request('rol') == $r->id_rol ? 'selected' : '' }}>
                                        {{ $r->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <button type="submit" class="rd-btn rd-btn-primary">
                            Buscar
                        </button>

                        @if(request()->filled('rol') || request()->filled('search'))
                            <a href="{{ route('admin.configuracion.empleados.index') }}" class="rd-btn rd-btn-default" title="Limpiar Filtros">
                                <i class="fas fa-times text-danger"></i>
                            </a>
                        @endif

                        @php
                            $user = auth()->user();
                            $isAdmin = false;
                            if ($user) {
                                $roleField = strtolower($user->role ?? '');
                                $isAdmin = $roleField === 'administrador' || ($user->roles && $user->roles->pluck('nombre')->contains('Administrador'));
                            }
                        @endphp

                        @if($isAdmin)
                            <a href="/register" class="rd-btn rd-btn-primary" title="Registrar empleados">
                                <i class="fas fa-user-plus me-1"></i> Registrar empleados
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="rd-table-container p-0">
            <table class="rd-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:70px">ID</th>
                        <th>InformaciÃ³n del Empleado</th>
                        <th>Usuario de Sistema</th>
                        <th>Rol / Perfil</th>
                        <th class="text-center" style="width:180px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td class="text-center text-muted font-weight-bold">
                                #{{ ($usuarios->currentPage() - 1) * $usuarios->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span style="font-weight: 700; color: #1e293b;">{{ optional($usuario->persona)->nombre_persona ?? 'N/A' }} {{ optional($usuario->persona)->apellido_persona ?? 'N/A' }} </span>
                                </div>
                            </td>
                            <td>
                                <span class="rd-badge rd-badge-success" style="font-family: monospace;">
                                    {{ $usuario->username }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px 10px;">
                                        {{ $usuario->roles->pluck('nombre')->first() ?: ($usuario->perfil->nombre_perfil ?? 'â€”') }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.configuracion.empleados.show', $usuario->id_usuario) }}" 
                                       class="rd-action" title="Ver Perfil">
                                        <i class="fas fa-eye "></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.configuracion.empleados.edit', $usuario->id_usuario) }}" 
                                       class="rd-action" title="Modificar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @php
                                        $isSelf = auth()->id() == $usuario->id_usuario;
                                    @endphp

                                    @if(!$isSelf)
                                        <form action="{{ route('admin.configuracion.empleados.destroy', $usuario->id_usuario) }}" method="POST" onsubmit="return confirm('Â¿Eliminar empleado?');" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rd-action rd-btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="rd-action disabled" style="opacity: 0.4; cursor: not-allowed;" title="AcciÃ³n no permitida">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3" style="opacity: 0.2"></i>
                                    <p class="mb-0">No se encontraron empleados con los criterios de bÃºsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rd-card-body border-top bg-light d-flex justify-content-center">
            {{ $usuarios->appends(request()->query())->onEachSide(1)->links('components.pagination') }}
        </div>
    </div>
@stop

@section('css')
<style>
    /* Efecto hover en las filas de la tabla usando tus variables */
    .rd-table tbody tr:hover {
        background-color: #f8fafc;
        transition: var(--trans-default);
    }
    
    /* Estilo adicional para los iconos de acciÃ³n */
    .rd-action {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: var(--trans-default);
    }

    .rd-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
</style>
@stop
