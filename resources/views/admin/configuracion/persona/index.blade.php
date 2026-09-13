@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
         ">
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">
                Estudiantes
            </h1>

            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona ?? auth()->user()->username }}</strong>.
            </p>
        </div>
        <div>
            <a href="{{ url('admin/persona/create') }}" class="rd-btn rd-btn-primary">
                <i class="fas fa-plus"></i>Registra un nuevo estudiante
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')
    <div class="rd-card rd-card-full">
        <div class="rd-card-body">
            <div class="rd-card-header rd-header-space">
                <div>
                    <h3 class="rd-title-sm">Estudiantes Registrados</h3>
                </div>
                <div class="rd-actions">
                    <form action="{{ route('admin.configuracion.persona.index') }}" method="GET" class="rd-search-inline" role="search">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="rd-search-input" placeholder="Buscar nombre o cedula" />
                        <button class="rd-icon-btn" type="submit" title="Buscar"><i class="fas fa-search"></i></button>
                    </form>

                </div>
            </div>
            <div id="printArea">
                <table class="rd-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th class="text-center">Cedula</th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Apellido</th>
                            <th class="text-center">Email</th>
                            <th style="width:150px" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($personas as $persona)
                            <tr>
                                <td class="text-center">
                                    {{ ($personas->currentPage() - 1) * $personas->perPage() + $loop->iteration }}</td>
                                    <td class="text-center ">
                                        {{ $persona->cedula_persona }}
                                    </td>
                                <td class="text-center">{{ $persona->nombre_persona }}</td>
                                <td class="text-center">{{ $persona->apellido_persona }}</td>
                                <td class="text-center">{{ $persona->email_persona }}</td>
                                @php
                                    $perfil = [1=>'Estudiante'];
                                @endphp
                                <td class="text-center">
                                    <div class="rd-action-group">
                                        <a href="{{ route('admin.configuracion.persona.edit', ['id' => $persona->id_persona]) }}"
                                            class="rd-action" title="Editar"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('admin.configuracion.persona.show', ['id' => $persona->id_persona]) }}" class="rd-action" title="Ver"><i class="fas fa-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No hay Estudiantes registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $personas->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>
@stop



