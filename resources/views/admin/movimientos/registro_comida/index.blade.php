@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="
            background: var(--bg-card);
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
         ">

        <!-- Texto principal -->
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:var(--text-main); font-weight:700;">
                Registro De Comida
            </h1>

            <p class="mt-1 mb-0" style="font-size:0.95rem; color:var(--text-main); opacity:.8;">
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

@section('content')
    <livewire:registro-comida />

    <div style="padding: 18px 12px;">

        <div class="rd-card rd-card-list">
            <div class="rd-card-header rd-header-space">
                <div>
                    <h3 class="rd-title-sm">Registros De Sobrantes</h3>
                    <p class="rd-sub-sm">Ãšltimos movimientos del dÃ­a</p>
                </div>
            </div>
            <div class="rd-card-body rd-list-body">
                <div class="rd-list">
                    <table class="rd-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha del Registro</th>
                                <th>Cantidad Sobrante</th>
                                <th>Motivo</th>
                                <th>Accion Tomada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sobrantes as $sobrante)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $sobrante->fecha }}</td>
                                    <td>{{ $sobrante->cantidad_sobrante }}</td>
                                    <td>{{ $sobrante->motivo }}</td>
                                    <td>{{ $sobrante->accion_tomada }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No hay registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PaginaciÃ³n (si aplica) -->
                <div class="rd-pagination">
                    {{ $sobrantes->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/registro-comida.css') }}">
@endsection

