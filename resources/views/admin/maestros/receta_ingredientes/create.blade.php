@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="background: #ffffff; border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">Nueva ComposiciÃ³n de Receta</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                <i class="fas fa-mortar-pestle mr-1" style="color: var(--color-secondary)"></i>
                Bienvenido: <strong>{{ auth()->user()->persona->nombre_persona }}</strong>
            </p>
        </div>
        <div class="d-flex align-items-center" style="gap:14px;">
            <div class="text-right d-none d-sm-block">
                <small class="text-muted d-block" style="font-size:0.75rem;">Fecha Actual</small>
                <span style="font-weight:600; font-size:0.95rem;">
                    {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                </span>
            </div>
            <div
                style="width:46px;height:46px;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(15,23,42,0.08); border: 2px solid #fff;">
                <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                    style="width:100%; height:100%; object-fit:cover;">
            </div>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')
    <div class="row justify-content-center fade-in">
        <div class="col-md-11">
            <div class="rd-card shadow-sm border-0 overflow-hidden">
                <div class="rd-card-body border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="rd-title-sm m-0">
                            <i class="fas fa-plus-circle mr-2" style="color: var(--color-secondary)"></i>Registrar
                            Ingredientes
                        </h3>
                        <a href="{{ url('admin/maestros/receta_ingredientes') }}" class="rd-btn rd-btn-default">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="rd-card-body p-4">
                    <form action="{{ route('admin.maestros.receta_ingredientes.store') }}" method="POST"
                        class="rd-prevent-double-submit">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 border-right pr-md-4">
                                <div class="form-group">
                                    <label class="rd-label mb-2">Seleccionar Receta</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                        <span><i class="fas fa-concierge-bell"></i></span>
                                        <select class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" name="recetas_id" required>
                                            <option value="">Seleccione una receta...</option>
                                            @foreach ($recetas as $receta)
                                                <option value="{{ $receta->id }}"
                                                    {{ old('recetas_id', request('recetas_id')) == $receta->id ? 'selected' : '' }}>
                                                    {{ $receta->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-2 pt-2" style="border-top: 1px solid #e5e7eb; padding-top: 12px;">
                                        <small style="color: #64748b; font-size: 0.85rem;">
                                            Â¿No encuentras lo que buscas?
                                            <a style="color: #a84348; text-decoration: none; font-weight: 600; transition: color 0.2s;"
                                                href="{{ route('admin.maestros.recetas.create', [
                                                    'from' => url()->current(),
                                                ]) }}">
                                                CrÃ©ala aquÃ­
                                            </a>
                                        </small>
                                    </div>
                                    <div class="mt-4 p-3 rounded"
                                        style="background: #f8fafc; border-left: 4px solid var(--color-secondary);">
                                        <p class="small text-muted mb-0">
                                            <i class="fas fa-info-circle mr-1"></i> Seleccione primero la receta base para
                                            luego asignar sus ingredientes y porciones.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8 pl-md-4">
                                <label class="rd-label mb-2">Agregar Insumos</label>
                                <div class="d-flex gap-2 align-items-start mb-4">
                                    <div style="flex-grow:1">
                                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                            <span><i class="fas fa-search"></i></span>
                                            <select class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" id="producto_select">
                                                <option value="">Buscar producto...</option>
                                                @foreach ($productos as $producto)
                                                    <option value="{{ $producto->id }}"
                                                        data-nombre="{{ $producto->nombre }}">
                                                        {{ $producto->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div style="width:120px">
                                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                            <span><i class="fas fa-hashtag"></i></span>
                                            <input type="number" step="any" min="0" id="cantidad_input"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div style="width:150px">
                                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                            <span><i class="fas fa-balance-scale"></i></span>
                                            <select class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" id="unidad_select">
                                                <option value="">Unidad</option>
                                                @foreach ($unidades as $unidad)
                                                    <option value="{{ $unidad->id }}"
                                                        data-nombre="{{ $unidad->nombre }}">
                                                        {{ $unidad->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" class="rd-btn rd-btn-primary shadow-sm" id="agregarProducto"
                                        style="height: 45px; width: 50px; justify-content: center;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <h5 class="rd-title-sm mb-3" style="font-size: 0.9rem; color: #64748b;">Lista de PreparaciÃ³n
                                </h5>
                                <div class="border rounded-lg overflow-hidden"
                                    style="background: #fbfdff; border-color: #eef2f6 !important;">
                                    <ul id="listaProductos" class="list-group list-group-flush"
                                        style="max-height: 350px; overflow-y: auto;">
                                        <li class="list-group-item text-center py-5 text-muted empty-msg">
                                            <i class="fas fa-layer-group fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">Agregue ingredientes para comenzar la receta.</p>
                                        </li>
                                    </ul>
                                </div>
                                @error('producto_id.*')
                                    <div class="rd-error mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <hr class="my-4" style="opacity: 0.4;">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ url('admin/maestros/receta_ingredientes') }}"
                                class="rd-btn rd-btn-default px-4">Cancelar</a>
                            <button type="submit" class="rd-btn rd-btn-primary rd-submit-btn px-5">
                                <i class="fas fa-save mr-1"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* EstilizaciÃ³n del Scroll */
        #listaProductos::-webkit-scrollbar {
            width: 5px;
        }

        #listaProductos::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Efectos de InteracciÃ³n */
        .list-group-item {
            border-color: #f1f5f9 !important;
        }

        .list-group-item:hover {
            background-color: #f8fafc !important;
            transition: var(--trans-default);
        }

        /* EliminaciÃ³n de bordes de foco solicitados */
        .rd-input:focus,
        .rd-btn:focus,
        select:focus {
            outline: none !important;
            box-shadow: none !important;
            border-color: var(--color-tertiary) !important;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .rounded-lg {
            border-radius: 12px !important;
        }

        .badge-soft-secondary {
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectProd = document.getElementById('producto_select');
            const cantidadInput = document.getElementById('cantidad_input');
            const unidadSelect = document.getElementById('unidad_select');
            const btnAgregar = document.getElementById('agregarProducto');
            const lista = document.getElementById('listaProductos');

            function crearItem(prodId, prodNombre, cantidad, unidadId, unidadNombre) {
                const empty = lista.querySelector('.empty-msg');
                if (empty) empty.remove();

                const li = document.createElement('li');
                li.className =
                    'list-group-item d-flex justify-content-between align-items-center bg-transparent py-3 border-bottom fade-in';
                li.id = 'prod_' + prodId;

                li.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="mr-3 d-flex align-items-center justify-content-center" style="width:38px; height:38px; border-radius:10px; background: #fff; border: 1px solid #eef2f6;">
                            <i class="fas fa-plus text-success"></i>
                        </div>
                        <div>
                            <span class="font-weight-bold d-block" style="color: #1e293b; font-size: 0.95rem;">${prodNombre}</span>
                            <small class="badge badge-soft-secondary" style="background: #f1f5f9; color: #475569; padding: 2px 8px;">
                                ${cantidad} ${unidadNombre}
                            </small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="hidden" name="producto_id[]" value="${prodId}">
                        <input type="hidden" name="cantidad_porcion[]" value="${cantidad}">
                        <input type="hidden" name="unidad_id[]" value="${unidadId}">
                        <button type="button" class="rd-action rd-action-danger">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;

                li.querySelector('.rd-action-danger').addEventListener('click', () => {
                    li.remove();
                    if (lista.children.length === 0) {
                        lista.innerHTML = `<li class="list-group-item text-center py-5 text-muted empty-msg">
                                            <i class="fas fa-layer-group fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">Agregue ingredientes para comenzar la receta.</p>
                                          </li>`;
                    }
                });
                return li;
            }

            btnAgregar.addEventListener('click', function() {
                const prodId = selectProd.value;
                const prodNombre = selectProd.options[selectProd.selectedIndex]?.text || '';
                const cantidad = cantidadInput.value;
                const unidadId = unidadSelect.value;
                const unidadNombre = unidadSelect.options[unidadSelect.selectedIndex]?.text || '';

                if (!prodId) return Swal.fire('Error', 'Seleccione un producto.', 'error');
                if (!cantidad || Number(cantidad) <= 0) return Swal.fire('Error', 'Ingrese una cantidad.',
                    'error');
                if (!unidadId) return Swal.fire('Error', 'Seleccione unidad.', 'error');

                if (document.getElementById('prod_' + prodId)) {
                    return Swal.fire('Aviso', 'Ya estÃ¡ en la lista.', 'info');
                }

                const item = crearItem(prodId, prodNombre, cantidad, unidadId, unidadNombre);
                lista.appendChild(item);

                selectProd.selectedIndex = 0;
                cantidadInput.value = '';
                unidadSelect.selectedIndex = 0;
            });
        });
    </script>
@endsection

