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
                Historial de Movimientos de Inventario
            </h1>

            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>
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
                        action="{{ route('admin.movimientos.historial_movimientos.index', request()->segment(count(request()->segments()))) }}"
                        method="GET" class="rd-search-inline" role="search">
                        <input type="text" name="buscar" value="{{ $buscar ?? '' }}" class="rd-search-input"
                            placeholder="Escriba el lote" />
                        <button class="rd-icon-btn" type="submit" title="Buscar"><i class="fas fa-search"></i></button>
                    </form>

                    <button class="rd-icon-btn"   aria-expanded="false"
                        aria-controls="filters" title="Filtros">
                        <i class="fas fa-filter"></i>
                    </button>

                    <div class="rd-export-group">
                        <button class="rd-btn rd-btn-danger" title="Exportar PDF" id="pdfBtn"><i
                                class="fas fa-file-pdf"></i>
                            PDF</button>
                    </div>
                </div>
            </div>

            <div class="collapse @if (request()->all()) show @endif" id="filters">
                <div class="rd-filters">
                    <form action="{{ route('admin.movimientos.historial_movimientos.index') }}" method="GET"
                        class="rd-filters-form">
                        <div class="rd-filter-row">
                            <label for="fecha_desde">Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                value="{{ request('fecha_desde') }}" />
                        </div>
                        <div class="rd-filter-row">
                            <label for="fecha_hasta">Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                value="{{ request('fecha_hasta') }}" />
                        </div>
                        <div class="rd-filter-row">
                            <label for="tipo_movimiento">Tipo de Movimiento</label>
                            <select name="tipo_movimiento" id="tipo_movimiento" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                style="width:100px; background-color: white;">
                                <option value="">Todos</option>
                                <option value="ENTRADA" {{ request('tipo_movimiento') === 'ENTRADA' ? 'selected' : '' }}>
                                    Entrada</option>
                                <option value="SALIDA" {{ request('tipo_movimiento') === 'SALIDA' ? 'selected' : '' }}>
                                    Salida</option>
                            </select>
                        </div>
                        <div class="rd-filter-row rd-filter-actions">
                            <button class="rd-btn rd-btn-primary" type="submit">Aplicar</button>
                            <button type="button" class="rd-btn rd-btn-default"
                                onclick="document.forms[0].reset(); 
                 document.querySelectorAll('select').forEach(s => s.value = '');">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="printArea">
                <table class="rd-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Tipo de Movimiento</th>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Cantidad (g)</th>
                            <th>Unidad</th>
                            <th>Sede</th>
                            <th>Fecha</th>
                            <th>ObservaciÃ³n</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimiento as $movimientos)
                            <tr>
                                <td class="text-center">
                                    {{ ($movimiento->currentPage() - 1) * $movimiento->perPage() + $loop->iteration }}
                                </td>
                                @php
                                    $tipoEntrada = $movimientos->tipo_movimiento == 'ENTRADA' ? true : false;
                                @endphp
                                <td class="text-center">
                                    @if ($tipoEntrada)
                                        <span
                                            class="text-success rd-badge rd-badge-success d-inline-flex align-items-center">
                                            <svg class="me-1 text-success" width="16" height="16" fill="currentColor"
                                                viewBox="0 2 16 16">
                                                <path
                                                    d="M8 14a.75.75 0 0 1-.53-.22l-4-4a.75.75 0 0 1 1.06-1.06L8 11.69l3.47-3.47a.75.75 0 1 1 1.06 1.06l-4 4A.75.75 0 0 1 8 14z" />
                                            </svg>
                                            {{ $movimientos->tipo_movimiento }}
                                        </span>
                                    @else
                                        <span class="text-danger rd-badge rd-badge-danger d-inline-flex align-items-center">
                                            <svg class="me-1 text-danger" width="16" height="16" fill="currentColor"
                                                viewBox="0 -3 16 16">
                                                <path
                                                    d="M8 2a.75.75 0 0 1 .53.22l4 4a.75.75 0 0 1-1.06 1.06L8 4.31 4.53 7.78a.75.75 0 0 1-1.06-1.06l4-4A.75.75 0 0 1 8 2z" />
                                            </svg>
                                            {{ $movimientos->tipo_movimiento }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $movimientos->producto->nombre }}</td>
                                <td>{{ $movimientos->lote->codigo_lote }}</td>
                                <td>{{ $movimientos->cantidad_convertida }}</td>
                                <td>{{ $movimientos->unidad->nombre }}</td>
                                <td>{{ $movimientos->sede->nombre }}</td>
                                <td>{{ $movimientos->fecha }}</td>
                                @if ($movimientos->observacion)
                                    <td>{{ $movimientos->observacion }}</td>
                                @else
                                    <td>Sin observaciÃ³n</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">No hay movimientos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $movimiento->onEachSide(1)->appends(request()->query())->links('components.pagination') }}
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pdfBtn = document.getElementById('pdfBtn');
            if (pdfBtn) {
                pdfBtn.addEventListener('click', () => {
                    const params = new URLSearchParams(window.location.search);
                    const fechaDesde = params.get('fecha_desde') ?? "";
                    const fechaHasta = params.get('fecha_hasta') ?? "";
                    const tipoMovimiento = params.get('tipo_movimiento') ?? "";
                    const url =
                        `{{ route('admin.movimientos.historial_movimientos.export_pdf') }}?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}&tipo_movimiento=${tipoMovimiento}`;
                    window.open(url, '_blank');
                });
            }
        });
    </script>
@endpush
