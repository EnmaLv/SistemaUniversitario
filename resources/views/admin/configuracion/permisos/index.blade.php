@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="background: #ffffff; border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">Permisos Especiales</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                <i class="fas fa-user-shield mr-1" style="color: var(--color-secondary)"></i> 
                AsignaciÃ³n de permisos granulares por usuario.
            </p>
        </div>
        <div>
            <i class="fas fa-info-circle text-muted" title="Los permisos asignados aquÃ­ se suman a los del rol base."></i>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="rd-card rd-card-full shadow-sm border-0 overflow-hidden">
        <div class="rd-card-body border-bottom bg-white">
            <form action="{{ route('admin.configuracion.permisos.index') }}" method="GET">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="rd-title-sm">Usuarios del Sistema</h3>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                            <span><i class="fas fa-search"></i></span>
                            <input type="text" name="q" value="{{ request('q') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" placeholder="Buscar por nombre o usuario...">
                        </div>
                        <button type="submit" class="rd-btn rd-btn-primary ml-2" style="padding: 5px 15px;">
                            Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rd-table-container">
            <table class="rd-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px">#</th>
                        <th>IdentificaciÃ³n</th>
                        <th>Nombre y Apellido</th>
                        <th>Rol Asignado</th>
                        <th class="text-center" style="width:180px">GestiÃ³n</th>
                    </tr>
                </thead>
                <tbody class="fade-in">
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td class="text-center text-muted font-weight-bold">
                                {{ $loop->iteration + ($usuarios->currentPage()-1)* $usuarios->perPage() }}
                            </td>
                            <td>
                                <span class="rd-badge rd-badge-success" style="font-family: monospace; font-size: 0.85rem;">
                                    {{ $usuario->username }}
                                </span>
                            </td>
                            <td class="font-weight-bold" style="color: #1e293b;">
                                {{$usuario->persona->nombre_persona . ' ' . $usuario->persona->apellido_persona ?? "â€”" }}
                            </td>
                            <td>
                                @php
                                    $rolNombre = $usuario->roles->pluck('nombre')->first() ?: ($usuario->perfil->nombre_perfil ?? 'Usuario Base');
                                @endphp
                                <span class="badge badge-light border text-muted px-2 py-1" style="border-radius: 6px; font-weight: 500;">
                                    <i class="fas fa-briefcase "></i> {{ $rolNombre }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $auth = auth()->user();
                                    $isSelfAdmin = $auth && $auth->id_usuario == $usuario->id_usuario && $auth->roles->contains('nombre', 'Administrador');
                                @endphp
                                
                                @if(!$isSelfAdmin)
                                    <a href="{{ route('admin.configuracion.permisos.edit', $usuario->id_usuario) }}" 
                                       class="rd-btn rd-btn-alter d-inline-flex justify-content-center" 
                                       style="width: 120px; font-size: 0.85rem;">
                                        <i class="fas fa-cog"></i> Gestionar
                                    </a>
                                @else
                                    <span class="text-muted small" title="El administrador principal no puede editar sus propios permisos granulares">
                                        <i class="fas fa-lock mr-1"></i> Protegido
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3" style="opacity: 0.1"></i>
                                    <p>No se encontraron usuarios para la bÃºsqueda.</p>
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
    /* Estilo de fila al pasar el mouse */
    .rd-table tbody tr:hover {
        background-color: #f8fafc;
        transition: var(--trans-default);
    }

    /* Quitar bordes de foco azules/morados solicitados */
    .rd-input:focus, .rd-btn:focus {
        outline: none !important;
        box-shadow: none !important;
    }
</style>
@stop
