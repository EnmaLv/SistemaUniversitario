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
                Ingredientes para Recetas Registradas
            </h1>

            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                Bienvenido <strong>{{ auth()->user()->persona->nombre_persona }}</strong>.
            </p>
        </div>
        <div>
            <a href="{{ url('admin/maestros/receta_ingredientes/create') }}" class="rd-btn rd-btn-primary">
                <i class="fas fa-plus"></i> Asignar Insumos
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
                    <h3 class="rd-title-sm">Ingredientes para Recetas Registradas</h3>
                </div>
                <div class="rd-actions">
                    <div class="d-flex gap-3 align-items-center">
                        <span class="font-weight-bold" style="margin-right:10px;">Filtrar por estado:</span>

                        <div class="toggle-container">
                            <input type="checkbox" id="estadoToggle" class="toggle-checkbox"
                                {{ request('estado', 1) == 1 ? 'checked' : '' }}>
                            <label for="estadoToggle" class="toggle-label">
                                <span class="toggle-inner"></span>
                                <span class="toggle-switch"></span>
                            </label>
                        </div>
                    </div>
                    <form action="{{ route('admin.maestros.receta_ingredientes.index') }}" method="GET"
                        class="rd-search-inline" role="search">
                        <input type="hidden" name="estado" value="{{ request('estado', 1) }}">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="rd-search-input"
                            placeholder="Escriba la receta" />
                        <button class="rd-icon-btn" type="submit" title="Buscar"><i class="fas fa-search"></i></button>
                    </form>

                </div>
            </div>
            <div id="printArea">
                <table class="rd-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Cantidad por Porcion</th>
                            <th style="width:150px" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recetas as $receta)
                            <tr>
                                <td class="text-center">
                                    {{ ($recetas->currentPage() - 1) * $recetas->perPage() + $loop->iteration }}
                                </td>
                                <td class="text-center">
                                    <strong>{{ $receta->nombre }}</strong>
                                    @if (!empty($receta->descripcion))
                                        <div class="text-muted" style="font-size:.9rem;">
                                            {{ Str::limit($receta->descripcion, 120) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($receta->recetaIngredientes->isEmpty())
                                        <em class="text-muted">No tiene ingredientes</em>
                                    @else
                                        @php
                                            $ingredientes = $receta->recetaIngredientes;
                                            $totalIngredientes = count($ingredientes);
                                            $showMore = $totalIngredientes > 3;
                                        @endphp
                                        <ul id="receta-{{ $receta->id }}" class="ingredientes-list"
                                            style="padding-left: 1rem; margin:0 auto; text-decoration: none; list-style: none;">
                                            @foreach ($ingredientes as $index => $ing)
                                                <li class="ingrediente-item @if ($showMore && $index >= 3) d-none @endif"
                                                    style="margin-bottom:4px;">
                                                    {{ optional($ing->producto)->nombre ?? 'Producto eliminado' }}
                                                    â€”
                                                    <strong>{{ round($ing->cantidad_porcion) }}</strong>
                                                    {{ optional($ing->unidad)->nombre ?? '' }}
                                                </li>
                                            @endforeach
                                        </ul>
                                        @if ($showMore)
                                            <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 link p-0 ver-mas-btn"
                                                data-recipiente-id="{{ $receta->id }}" style="font-size: 0.9rem;">
                                                Ver mÃ¡s...
                                            </button>
                                            <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 link p-0 ver-menos-btn d-none"
                                                data-recipiente-id="{{ $receta->id }}" style="font-size: 0.9rem;">
                                                Ver menos
                                            </button>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    const recipeId = '{{ $receta->id }}';
                                                    const verMasBtn = document.querySelector(`.ver-mas-btn[data-recipiente-id="${recipeId}"]`);
                                                    const verMenosBtn = document.querySelector(`.ver-menos-btn[data-recipiente-id="${recipeId}"]`);
                                                    const items = document.querySelectorAll(`#receta-${recipeId} .ingrediente-item`);
                                                    if (verMasBtn) {
                                                        verMasBtn.addEventListener('click', function() {
                                                            items.forEach((item, index) => {
                                                                if (index >= 3) {
                                                                    item.classList.remove('d-none');
                                                                }
                                                            });
                                                            verMasBtn.classList.add('d-none');
                                                            if (verMenosBtn) verMenosBtn.classList.remove('d-none');
                                                        });
                                                    }
                                                    if (verMenosBtn) {
                                                        verMenosBtn.addEventListener('click', function() {
                                                            items.forEach((item, index) => {
                                                                if (index >= 3) {
                                                                    item.classList.add('d-none');
                                                                }
                                                            });
                                                            if (verMasBtn) verMasBtn.classList.remove('d-none');
                                                            verMenosBtn.classList.add('d-none');
                                                        });
                                                    }
                                                });
                                            </script>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="rd-action-group">
                                        @if ($receta->recetaIngredientes->isNotEmpty())
                                            <a href="{{ route('admin.maestros.receta_ingredientes.edit', $receta->id) }}"
                                                class="rd-action" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form
                                                action="{{ route('admin.maestros.receta_ingredientes.destroy', $receta->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rd-action rd-btn-danger" title="Eliminar"
                                                    onclick="confirmDelete(event, this)"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                        <script>
                                            function confirmDelete(event, button) {
                                                event.preventDefault();
                                                Swal.fire({
                                                    title: 'Â¿EstÃ¡s seguro?',
                                                    text: "Desea eliminar los ingredientes asociados a esta receta?",
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3085d6',
                                                    cancelButtonColor: '#d33',
                                                    confirmButtonText: 'SÃ­, eliminar',
                                                    cancelButtonText: 'Cancelar'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        button.closest('form').submit();
                                                    }
                                                });
                                            }
                                        </script>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No hay Ingredientes de Recetas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $recetas->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.getElementById('estadoToggle').addEventListener('change', function() {
            if (this.checked) {
                window.location.href = "{!! route('admin.maestros.receta_ingredientes.index', array_merge(request()->query(), ['estado' => 1])) !!}";
            } else {
                window.location.href = "{!! route('admin.maestros.receta_ingredientes.index', array_merge(request()->query(), ['estado' => 0])) !!}";
            }
        });
    </script>
@stop

