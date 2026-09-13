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
                Lotes
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
    @include('components.alert')
    @if ($hayLotesVencidosSinMerma)
        <div class="rd-card rd-card-full mb-4" style="border-left:6px solid #dc2626;">
            <div class="rd-card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>
                        <h3 class="rd-title-sm text-danger mb-1">
                            <i class="fas fa-exclamation-triangle"></i>
                            Productos vencidos detectados
                        </h3>
                        <p class="mb-0 text-muted" style="font-size:0.95rem;">
                            Existen lotes vencidos que aÃºn se encuentran en el inventario.
                            Se recomienda realizar la <strong>merma</strong> para mantener el stock correcto.
                        </p>
                    </div>

                    <form action="{{ route('admin.movimientos.lotes.mermar') }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 danger px-4" onclick="confirmarMerma(event, this)">
                            <i class="fas fa-trash-alt mr-1"></i>
                            Mermar productos vencidos
                        </button>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <div class="rd-card rd-card-full">
        <div class="rd-card-body">
            <div class="rd-card-header rd-header-space">
                <div>
                    <h3 class="rd-title-sm">Lotes Registrados</h3>
                </div>

                <div class="rd-actions">
                    <form action="{{ route('admin.movimientos.lotes.index') }}" method="GET" class="rd-search-inline"
                        role="search">
                        <input type="text" name="buscar" value="{{ $buscar ?? '' }}" class="rd-search-input"
                            placeholder="Escriba el lote" />
                        <button class="rd-icon-btn" type="submit" title="Buscar"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>

            <div class="collapse" id="filters">
                <div class="rd-filters">
                    <form action="{{ route('admin.movimientos.lotes.index') }}" method="GET" class="rd-filters-form">
                        <div class="rd-filter-row" style="display: inline-block;">
                            <label>Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" />
                        </div>
                        <div class="rd-filter-row" style="display: inline-block;">
                            <label>Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                max="{{ now()->format('Y-m-d') }}" />
                        </div>
                        <div class="rd-filter-row" style="display: inline-block;">
                            <select name="estado" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                <option value="">Seleccione una opciÃ³n</option>
                                <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Merma</option>
                            </select>
                        </div>

                        <div class="rd-filter-row rd-filter-actions" style="display: inline-block; vertical-align: bottom;">
                            <button class="rd-btn rd-btn-primary" type="submit">Aplicar</button>
                            <button type="button" class="rd-btn rd-btn-default"
                                onclick="document.getElementById('fecha_desde').value=''; document.getElementById('fecha_hasta').value='';">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabla --}}
            <div id="printArea">
                <table class="rd-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>CÃ³digo Lote</th>
                            <th>Producto</th>
                            <th>Proveedor</th>
                            <th>Fecha Entrada</th>
                            <th>Fecha Vencimiento</th>
                            <th>DÃ­as Restantes</th>
                            <th>Cantidad (U)</th>
                            <th>Cantidad (g)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotes as $lote)
                            <tr class="{{ $lote->is_expired ? 'table-danger' : '' }}">
                                <td class="text-center">
                                    {{ ($lotes->currentPage() - 1) * $lotes->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ $lote->codigo_lote }}</td>
                                <td>{{ $lote->producto->nombre }}</td>
                                <td>{{ $lote->proveedor->nombre }}</td>
                                <td>{{ $lote->fecha_entrada }}</td>
                                <td>{{ $lote->fecha_vencimiento }}</td>
                                <td>
                                    {{ round($lote->days_to_expire) }} dÃ­as
                                </td>
                                <td>{{ round($lote->cantidad_sede) }}</td>
                                <td>{{ round($lote->cantidad_gramos_sede) }}g</td>
                                <td>
                                    @if ($lote->cantidad_sede && $lote->cantidad_sede <= 0)
                                        <span class="rd-badge rd-badge-secondary">Agotado</span>
                                    @elseif ($lote->is_expired)
                                        <span class="rd-badge rd-badge-danger">Vencido</span>
                                    @elseif ($lote->days_to_expire <= 7)
                                        <span class="rd-badge rd-badge-warning">Cerca de Vencer</span>
                                    @else
                                        <span class="rd-badge rd-badge-success">Vigente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">No hay Lotes</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PaginaciÃ³n del servidor --}}
            <div class="mt-3 d-flex justify-content-center">
                {{ $lotes->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
@stop
