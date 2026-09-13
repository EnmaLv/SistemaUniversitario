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
                Inventario de la Sede:
                {{ $sedes->firstWhere('id', request()->segment(count(request()->segments())))?->nombre ?? '' }}
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

@section('content')
    <div class="rd-card rd-card-full">

        <div class="rd-card-body">
            <div class="rd-card-header rd-header-space">
                <div>
                    <h3 class="rd-title-sm">Inventario Registrado</h3>
                </div>

                <div class="rd-actions">
                    <form
                        action="{{ route('admin.movimientos.sedes_lotes.show', request()->segment(count(request()->segments()))) }}"
                        method="GET" class="rd-search-inline" role="search">
                        <input type="text" name="buscar" value="{{ $buscar ?? '' }}" class="rd-search-input"
                            placeholder="Escriba el lote o producto" />
                        <button class="rd-icon-btn" type="submit" title="Buscar"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>

            {{-- Tabla --}}
            <div id="printArea">
                <table class="rd-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>CÃ³digo de Lote</th>
                            <th>Producto</th>
                            <th>Cantidad (U)</th>
                            <th>Cantidad (g)</th>
                            <th>Fecha Entrada</th>
                            <th>Fecha Vencimiento</th>
                            <th>Proveedor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sede as $loteSede)
                            <tr>
                                <td class="text-center">
                                    {{ ($sede->currentPage() - 1) * $sede->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ $loteSede->lote->codigo_lote }}</td>
                                <td>{{ $loteSede->lote->producto->nombre }}</td>
                                <td>{{ $loteSede->cantidad }}</td>
                                <td>{{ $loteSede->cantidad_convertida }}</td>
                                <td>{{ $loteSede->lote->fecha_entrada }}</td>
                                <td>{{ $loteSede->lote->fecha_vencimiento }}</td>
                                <td>{{ $loteSede->lote->proveedor->nombre }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">No hay registros</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PaginaciÃ³n del servidor --}}
            <div class="mt-3 d-flex justify-content-center">
                {{ $sede->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
@stop
