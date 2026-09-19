@extends('layouts.app')

@section('content_header')
    @include('components.alert')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="text-slate-500 hover:text-slate-700" >&times;</button>
        </div>
    @endif

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
    @include('components.alert')
    <div class="row">
        <div class="col-md-12 m-auto">
            <div class="card rd-card">
                <div class="card-header">
                    <h3 class="card-title"><b>Paso 1 | Requisición creada</b></h3>

                    <div class="card-tools">
                        <form action="{{ route('admin.movimientos.compras.cancelar', $compra) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @if ($compra->estado == 'Pendiente')
                                <button type="submit" class="rd-btn rd-btn-alter" onclick="confirmDelete(event, this)">
                                    <i class="fas fa-arrow-left"></i>
                                    <b>Cancelar y volver</b>
                                </button>
                            @elseif ($compra->estado == 'Enviado al proveedor')
                                <a href="{{ url('admin/movimientos/compras') }}" class="rd-btn rd-btn-default">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            @endif
                            <script>
                                function confirmDelete(event, button) {
                                    event.preventDefault();
                                    Swal.fire({
                                        title: '¿Estás seguro?',
                                        text: "Se perderán todos los productos agregados.",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#dc2626',
                                        cancelButtonColor: '#6b7280',
                                        confirmButtonText: 'Sí',
                                        cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            button.closest('form').submit();
                                        }
                                    });
                                }
                            </script>
                        </form>

                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3 display: inline-block;">
                                    <label for="nombre" class="rd-label">Proveedor</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                        <span><i class="fas fa-user-tie"></i></span>
                                        <select class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rd-input" id="proveedor_id" name="proveedor_id"
                                            disabled>
                                            <option value="">Seleccione un proveedor</option>
                                            @foreach ($proveedores as $proveedor)
                                                <option value="{{ $proveedor->id }}"
                                                    {{ old('proveedor_id', $compra->proveedor_id) == $proveedor->id ? 'selected' : '' }}>
                                                    {{ $proveedor->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('proveedor_id')
                                        <div class="alert text-danger p-0 m-0">
                                            <b>{{ 'Este campo es obligatorio.' }}</b>
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-3" style="display: inline-block;">
                                    <label for="codigo">Fecha de la requisición</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                        <span><i class="fas fa-calendar-alt"></i></span>
                                        <input type="datetime-local"
                                            value="{{ \Carbon\Carbon::now('America/Caracas')->format('Y-m-d\TH:i') }}"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="fecha" name="fecha"
                                            value="{{ old('fecha', $compra->fecha) }}" disabled>
                                    </div>
                                    @error('fecha')
                                        <div class="alert text-danger p-0 m-0">
                                            <b>{{ 'Este campo es obligatorio.' }}</b>
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-4" style="display: inline-block;">
                                    <label for="codigo">Observaciones</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                        <span><i class="fas fa-sticky-note"></i></span>
                                        @if ($compra->observaciones == !null)
                                            <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="observaciones"
                                                name="observaciones" placeholder="Ingrese observaciones"
                                                value="{{ old('observaciones', $compra->observaciones) }}" disabled>
                                        @else
                                            <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="observaciones"
                                                name="observaciones" placeholder="Ingrese observaciones"
                                                value="Sin observaciones" disabled>
                                        @endif
                                    </div>
                                    @error('observaciones')
                                        <div class="alert text-danger p-0 m-0">
                                            <b>{{ 'Este campo es obligatorio.' }}</b>
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-2" style="display: inline-block;">
                                    <label for="codigo">Requisición</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                        <span><i class="fas fa-sticky-note"></i></span>
                                        <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" id="estado" name="estado"
                                            placeholder="Ingrese estado" value="{{ old('estado', $compra->estado) }}"
                                            disabled>
                                    </div>
                                    @error('estado')
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
                    <h3 class="card-title"><b>Paso 2 | Agregar productos</b></h3>
                </div>
                <div class="card-body" style="display: block;">
                    <livewire:admin.movimientos.compras.items-compra :compra="$compra" />

                </div>
            </div>
        </div>
    </div>

    @if ($compra->estado == 'Enviado al proveedor')
        <div class="row">
            <div class="col-md-12 m-auto">
                <div class="card rd-card">
                    <div class="card-header">
                        <h3 class="card-title"><b>Paso 3 | Registrar Fechas de Vencimiento</b></h3>
                    </div>
                    <div class="card-body">
                        <livewire:admin.movimientos.compras.fechas-compra :compra="$compra" />
                        <form action="{{ route('admin.movimientos.compras.finalizarCompra', $compra) }}" method="POST"
                            class="rd-prevent-double-submit">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group" style="text-align: right;">
                                        <button type="submit" class="rd-btn rd-btn-primary rd-submit-btn"><i
                                                class="fas fa-check"></i>
                                            Finalizar
                                            Requisición
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <script>
                            document.addEventListener('livewire:init', () => {
                                Livewire.on('swal', data => {
                                    Swal.fire({
                                        icon: data.icon,
                                        title: data.title,
                                        text: data.text,
                                        confirmButtonColor: '#dc2626',
                                        timer: 3000,
                                        timerProgressBar: true
                                    });
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@push('css')
    <style>
        .comedor-swal-popup {
            background: var(--bg-card, #ffffff) !important;
            border: 1px solid var(--border-color, #e2e8f0) !important;
            color: var(--text-main, #0f172a) !important;
        }

        .comedor-swal-title,
        .comedor-swal-text {
            color: var(--text-main, #0f172a) !important;
        }

        html.dark .comedor-swal-popup {
            background: #160c0e !important;
            border-color: #5c2028 !important;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45) !important;
        }

        html.dark .comedor-swal-title,
        html.dark .comedor-swal-text {
            color: #f8fafc !important;
        }
    </style>
@endpush

@section('css')
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/diseño.css') }}">
@stop
@section('js')
    @livewireScripts
@stop
