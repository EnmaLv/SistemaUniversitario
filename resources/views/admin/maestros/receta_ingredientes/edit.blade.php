@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="background: #ffffff; border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">Editar Ingredientes de Receta</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                <i class="fas fa-utensils mr-1" style="color: var(--color-secondary)"></i>
                Usuario: <strong>{{ auth()->user()->persona->nombre_persona }}</strong>
            </p>
        </div>
        <div class="d-flex align-items-center" style="gap:14px;">
            <div class="text-right d-none d-sm-block">
                <small class="text-muted d-block" style="font-size:0.75rem;">Fecha de ediciÃ³n</small>
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
                            <i class="fas fa-edit mr-2" style="color: var(--color-secondary)"></i>ConfiguraciÃ³n de la Receta
                        </h3>
                        <a href="{{ url('admin/maestros/receta_ingredientes') }}" class="rd-btn rd-btn-default">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="rd-card-body p-4">
                    <form action="{{ route('admin.maestros.receta_ingredientes.update', $receta->id) }}" method="POST"
                        class="rd-prevent-double-submit">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4 border-right">
                                <div class="form-group">
                                    <label class="rd-label mb-2">Nombre de la Receta</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group" style="background: #f8fafc; cursor: not-allowed;">
                                        <span><i class="fas fa-book-open"></i></span>
                                        <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" value="{{ $receta->nombre }}" disabled>
                                        <input type="hidden" name="recetas_id" value="{{ $receta->id }}">
                                    </div>
                                    <small class="text-muted mt-2 d-block">EstÃ¡s editando la composiciÃ³n tÃ©cnica de esta
                                        preparaciÃ³n.</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="rd-label mb-2">AÃ±adir nuevos ingredientes</label>
                                <div class="d-flex gap-2 align-items-start mb-4">
                                    <div class="flex-grow-1">
                                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                            <span><i class="fas fa-box-open"></i></span>
                                            <select class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" id="producto_select">
                                                <option value="">Producto...</option>
                                                @foreach ($productos as $producto)
                                                    <option value="{{ $producto->id }}"
                                                        data-nombre="{{ $producto->nombre }}">
                                                        {{ $producto->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div style="width:130px">
                                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                            <span><i class="fas fa-weight-hanging"></i></span>
                                            <input type="number" step="any" min="0" id="cantidad_input"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" placeholder="Cant.">
                                        </div>
                                    </div>
                                    <div style="width:160px">
                                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                            <span><i class="fas fa-ruler-combined"></i></span>
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
                                    <button type="button"
                                        class="rd-btn rd-btn-primary rd-submit-btn shadow-sm d-flex justify-content-center align-items-center"
                                        id="agregarProducto" style="height: 45px; width: 45px;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <h5 class="rd-title-sm mb-3" style="font-size: 0.95rem;">Ingredientes Actuales</h5>
                                <div class="border rounded-lg" style="background: #fbfdff;">
                                    <ul id="listaProductos" class="list-group list-group-flush"
                                        style="max-height: 400px; overflow-y: auto;">
                                        @forelse ($receta->recetaIngredientes as $ing)
                                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-3 border-bottom"
                                                id="prod_{{ $ing->producto_id }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center"
                                                        style="width:35px; height:35px; background: #f1f5f9;">
                                                        <i class="fas fa-carrot text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <span class="font-weight-bold"
                                                            style="color: #1e293b;">{{ $ing->producto->nombre }}</span>
                                                        <div class="small text-muted">{{ $ing->cantidad_porcion }}
                                                            {{ $ing->unidad->nombre }}</div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="hidden" name="producto_id[]"
                                                        value="{{ $ing->producto_id }}">
                                                    <input type="hidden" name="cantidad_porcion[]"
                                                        value="{{ $ing->cantidad_porcion }}">
                                                    <input type="hidden" name="unidad_id[]"
                                                        value="{{ $ing->unidad_id }}">

                                                    <button type="button" class="rd-action rd-action-danger"
                                                        onclick="this.closest('li').remove();" title="Eliminar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-center py-5 text-muted empty-msg">
                                                <i class="fas fa-layer-group fa-3x mb-3 opacity-25"></i>
                                                <p>No hay ingredientes en esta receta.</p>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                                @error('producto_id.*')
                                    <div class="rd-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <hr class="my-4" style="opacity: 0.5;">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ url('admin/maestros/receta_ingredientes') }}"
                                class="rd-btn rd-btn-default px-4">Cancelar</a>
                            <button type="submit" class="rd-btn rd-btn-primary rd-submit-btn px-5 shadow-sm">
                                <i class="fas fa-check-circle mr-1"></i> Guardar
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
        /* Estilo para el scroll de la lista */
        #listaProductos::-webkit-scrollbar {
            width: 6px;
        }

        #listaProductos::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        /* Efecto hover en los items */
        .list-group-item:hover {
            background-color: #f8fafc !important;
            transition: var(--trans-default);
        }

        /* Quitar bordes de foco azules solicitados */
        .rd-input:focus,
        .rd-btn:focus,
        select:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .gap-3 {
            gap: 1rem;
        }

        .rounded-lg {
            border-radius: 12px !important;
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
                // Remover mensaje de lista vacÃ­a si existe
                const empty = lista.querySelector('.empty-msg');
                if (empty) empty.remove();

                const li = document.createElement('li');
                li.className =
                    'list-group-item d-flex justify-content-between align-items-center bg-transparent py-3 border-bottom fade-in';
                li.id = 'prod_' + prodId;

                li.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width:35px; height:35px; background: #f1f5f9;">
                            <i class="fas fa-plus text-success small"></i>
                        </div>
                        <div>
                            <span class="font-weight-bold" style="color: #1e293b;">${prodNombre}</span>
                            <div class="small text-muted">${cantidad} ${unidadNombre}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="hidden" name="producto_id[]" value="${prodId}">
                        <input type="hidden" name="cantidad_porcion[]" value="${cantidad}">
                        <input type="hidden" name="unidad_id[]" value="${unidadId}">
                        <button type="button" class="rd-action rd-action-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                li.querySelector('.rd-action-danger').addEventListener('click', () => li.remove());
                return li;
            }

            btnAgregar.addEventListener('click', function() {
                const prodId = selectProd.value;
                const prodNombre = selectProd.options[selectProd.selectedIndex]?.text || '';
                const cantidad = cantidadInput.value;
                const unidadId = unidadSelect.value;
                const unidadNombre = unidadSelect.options[unidadSelect.selectedIndex]?.text || '';

                if (!prodId) return Swal.fire('Error', 'Seleccione un producto.', 'error');
                if (!cantidad || Number(cantidad) <= 0) return Swal.fire('Error',
                    'Ingrese una cantidad vÃ¡lida.', 'error');
                if (!unidadId) return Swal.fire('Error', 'Seleccione una unidad.', 'error');

                // Verificar si ya existe para no duplicar en la lista visual
                if (document.getElementById('prod_' + prodId)) {
                    return Swal.fire('Aviso', 'Este producto ya estÃ¡ en la lista.', 'info');
                }

                const item = crearItem(prodId, prodNombre, cantidad, unidadId, unidadNombre);
                lista.appendChild(item);

                // Reset
                selectProd.selectedIndex = 0;
                cantidadInput.value = '';
                unidadSelect.selectedIndex = 0;
            });
        });
    </script>
@endsection

