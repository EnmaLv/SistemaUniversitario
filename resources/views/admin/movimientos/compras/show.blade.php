@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="background: var(--bg-card); border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid var(--border-color);">

        <!-- Texto principal -->
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:var(--text-main); font-weight:700;">
                Requisición N.º {{ $compra->id }}
            </h1>

            <p class="mt-1 mb-0" style="font-size:0.95rem; color:var(--text-main); opacity:.72;">
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
    <div class="row">
        <div class="col-md-12 m-auto">
            <div class="card rd-card">
                <div class="card-header">
                    <h3 class="card-title"><b>Compra Creada</b></h3>

                    <div class="card-tools">
                        <a href="{{ route('admin.movimientos.compras.index') }}" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 tool">
                            <i class="fas fa-arrow-left"></i>
                            <b>Volver</b>
                        </a>
                    </div>
                </div>
                <div class="card-body" style="display: block;">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3" style="display: inline-block;">
                                    <div class="form-group">
                                        <label for="proveedor_id">Proveedor</label>
                                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mb-3">
                                            <div class="">
                                                <span class="px-3 text-slate-500 inline-block"><i
                                                        class="fas fa-tags"></i></span>
                                            </div>
                                            <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="proveedor_id" name="proveedor_id"
                                                placeholder="Seleccione proveedor"
                                                value="{{ old('proveedor_id', $compra->proveedor_nombre) }}" readonly>
                                        </div>
                                        @error('proveedor_id')
                                            <div class="alert text-danger p-0 m-0">
                                                <b>{{ 'Este campo es obligatorio.' }}</b>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-md-2" style="display: inline-block;">
                                    <label for="fecha">Fecha de Compra</label>
                                    <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mb-3">
                                        <div class="">
                                            <span class="px-3 text-slate-500 inline-block"><i
                                                    class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="datetime-local"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="fecha" name="fecha"
                                            value="{{ old('fecha', $compra->fecha ? \Carbon\Carbon::parse($compra->fecha)->format('Y-m-d\TH:i') : '') }}" disabled>
                                    </div>
                                    @error('fecha')
                                        <div class="alert text-danger p-0 m-0">
                                            <b>{{ 'Este campo es obligatorio.' }}</b>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3" style="display: inline-block;">
                                    <label for="observaciones">Observaciones</label>
                                    <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mb-3">
                                        <div class="">
                                            <span class="px-3 text-slate-500 inline-block"><i
                                                    class="fas fa-sticky-note"></i></span>
                                        </div>
                                        <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="observaciones"
                                            name="observaciones" placeholder="Ingrese observaciones"
                                            value="{{ old('observaciones', $compra->observaciones ?? 'Sin observaciones') }}" readonly>
                                    </div>
                                    @error('observaciones')
                                        <div class="alert text-danger p-0 m-0">
                                            <b>{{ 'Este campo es obligatorio.' }}</b>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-2" style="display: inline-block;">
                                    <label for="estado">Estado Compra</label>
                                    <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mb-3">
                                        <div class="">
                                            <span class="px-3 text-slate-500 inline-block"><i
                                                    class="fas fa-sticky-note"></i></span>
                                        </div>
                                        <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="estado" name="estado"
                                            placeholder="Ingrese estado" value="{{ old('estado', $compra->estado) }}"
                                            readonly>
                                    </div>
                                    @error('estado')
                                        <div class="alert text-danger p-0 m-0">
                                            <b>{{ 'Este campo es obligatorio.' }}</b>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-2" style="display: inline-block;">
                                    <label for="sede_destino">Sede de Destino</label>
                                    <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 mb-3">
                                        <div class="">
                                            <span class="px-3 text-slate-500 inline-block"><i
                                                    class="fas fa-building"></i></span>
                                        </div>
                                        <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="sede_destino"
                                            name="sede_destino" placeholder="Sede de destino"
                                            value="{{ $sede_destino ? $sede_destino->nombre : 'Sin concluir' }}" readonly>
                                    </div>
                                    @error('sede_destino')
                                        <div class="alert text-danger p-0 m-0">
                                            <b>{{ 'Este campo es obligatorio.' }}</b>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 m-auto">
            <div class="card rd-card">
                <div class="card-header">
                    <h3 class="card-title"><b>Productos Agregados</b></h3>
                </div>
                <div class="card-body" style="display: block;">

                    <div class="row">
                        <div class="col-md-12">

                            @if ($detalles->count() > 0)
                                <h2 class="my-4">Detalles de la Orden de Compra</h2>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Código de Lote</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unitario</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($detalles as $detalle)
                                            <tr>
                                                <td>{{ $detalle->producto_nombre }}</td>
                                                <td>{{ $detalle->codigo_lote }}</td>
                                                <td>{{ $detalle->cantidad }} {{ $detalle->unidad_abreviatura }}</td>
                                                <td>{{ number_format($detalle->precio_unitario, 2, ',', '.') }}.BS</td>
                                                <td>{{ number_format($detalle->subtotal, 2, ',', '.') }}.BS</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <hr>
                            @else
                                <h4>No hay productos agregados a la compra.</h4>
                            @endif
                            <h3 align="right"><b>Total de la Compra:
                                </b>{{ number_format($compra->total, 2, ',', '.') }}.BS</h3>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .rd-card {
            background: var(--bg-card);
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-main);
            font-size: 0.875rem;
        }

        .flex.items-center.rounded-xl.border.border-slate-200.bg-slate-50 {
            border: 1px solid #d8dee9;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .px-3.text-slate-500 {
            background: transparent;
            border: none;
            color: #64748b;
            padding: 0.5rem 0.75rem;
        }

        input[readonly],
        input:disabled,
        select:disabled,
        textarea:disabled,
        textarea[readonly] {
            background-color: var(--input-bg);
            color: var(--text-main);
            cursor: not-allowed;
        }

        .table {
            width: 100%;
            margin-bottom: 1.5rem;
            background-color: var(--bg-card);
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background-color: var(--input-bg);
            color: var(--text-main);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: #f1f5f9;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .card-tools {
            margin-left: auto;
            display: flex;
            align-items: center;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            background-color: var(--color-primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--color-btn-hover, #b91c1c);
        }

        .btn-tool {
            background: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-tool:hover {
            background-color: var(--input-bg);
        }

        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        .alert.alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .content-header {
            padding: 1.5rem 1.5rem 0;
        }
    </style>
@endpush
