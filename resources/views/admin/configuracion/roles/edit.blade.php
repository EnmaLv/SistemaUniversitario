@extends('layouts.app')

@section('content_header')
    <div class="rounded-2xl border p-5 mb-6 shadow-sm d-flex justify-content-between align-items-center"
        style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div>
            <h1 class="m-0 text-2xl sm:text-3xl font-extrabold" style="color:var(--text-main);">Editar rol: {{ $rol->nombre }}</h1>
            <p class="mt-1 mb-0 text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-user-shield mr-1" style="color:var(--color-primary)"></i>
                Modifica los accesos y descripción del perfil.
            </p>
        </div>
        <a href="{{ route('admin.configuracion.roles.index') }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold hover:border-red-600 hover:text-red-600" style="border-color:var(--border-color);color:var(--text-main);">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="row justify-content-center fade-in">
        <div class="col-md-11">
            <div class="rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-5">
                    <form action="{{ route('admin.configuracion.roles.update', $rol->id_rol) }}" method="POST"
                        class="rd-prevent-double-submit">
                        @csrf
                        @method('PUT')

                        {{-- 1. Datos Básicos del Rol --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="rd-label mb-2" style="color:var(--text-main);">Nombre del rol</label>
                                <div class="flex items-center gap-2 rounded-xl border px-3 py-2.5 text-sm outline-none transition {{ $isProtected ?? false ? 'opacity-70' : '' }}" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <span><i class="fas fa-tag"></i></span>
                                    <input type="text" name="nombre" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);"
                                        value="{{ old('nombre', $rol->nombre) }}"
                                        {{ $isProtected ?? false ? 'readonly' : 'required' }}
                                        placeholder="Nombre del rol">
                                </div>
                                @error('nombre')
                                    <div class="col-md-12 mt-2">
                                        <small class="text-danger">{{ $message }}</small>
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="rd-label mb-2" style="color:var(--text-main);">Descripción</label>
                                <input type="text" name="descripcion" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none transition" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);"
                                    style="border: 1px solid #d8dee9; border-radius: 10px; height: 45px; padding: 0 12px;"
                                    value="{{ old('descripcion', $rol->descripcion) }}" placeholder="Descripcion del rol"
                                    {{ $isProtected ?? false ? 'readonly' : '' }}>
                            </div>
                        </div>

                        @php $isAdminRole = (strtolower($rol->nombre ?? '') === 'administrador'); @endphp

                        {{-- 2. Asignación de Módulos de la Base de Datos --}}
                        <div class="form-group mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="rd-label m-0">
                                    <i class="fas fa-cubes mr-2" style="color:var(--color-primary);"></i> Acceso a módulos globales del sistema
                                </label>
                                @if (!$isAdminRole && !($isProtected ?? false))
                                    <button type="button" id="selectAllModules" class="rounded-xl border px-4 py-2 text-sm font-bold hover:border-red-600 hover:text-red-600"
                                        style="border-color:var(--border-color);color:var(--text-main);">
                                        Alternar Módulos
                                    </button>
                                @endif
                            </div>

                            <div class="modules-container p-4 {{ $isAdminRole ? 'bg-light opacity-75' : '' }}"
                                style="border:1px solid var(--border-color);border-radius:12px;background-color:var(--input-bg);">
                                <div class="row">
                                    @forelse($modulos as $modulo)
                                        @php
                                            $moduloChecked =
                                                $isAdminRole ||
                                                (is_array(old('modulos')) && in_array($modulo->id, old('modulos'))) ||
                                                $rol->modulos->contains('id', $modulo->id);

                                            $moduloDisabled = $isAdminRole || ($isProtected ?? false) ? 'disabled' : '';
                                        @endphp
                                        <div class="col-md-4 mb-2">
                                            <div class="custom-control custom-checkbox item-modulo">
                                                <input type="checkbox" name="modulos[]" value="{{ $modulo->id }}"
                                                    class="custom-control-input modulo-check"
                                                    id="modulo_{{ $modulo->id }}" {{ $moduloChecked ? 'checked' : '' }}
                                                    {{ $moduloDisabled }}>
                                                <label class="custom-control-label font-weight-normal"
                                                    style="cursor:pointer; font-size:0.95rem; color:var(--text-main);"
                                                    for="modulo_{{ $modulo->id }}">
                                                    <strong>{{ $modulo->nombre }}</strong> <span
                                                        class="text-muted small">({{ $modulo->key }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center text-muted py-2">
                                            <i class="fas fa-exclamation-triangle mr-1 text-warning"></i> No hay módulos
                                            activos registrados en la base de datos.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('modulos')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- 3. Permisos de Menú y Navegación con Grilla Tipo Masonry --}}
                        <div class="form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="rd-label m-0">
                                    <i class="fas fa-list-check mr-2" style="color:var(--color-primary);"></i> Permisos de menú y navegación
                                </label>
                                @if (!$isAdminRole && !($isProtected ?? false))
                                    <button type="button" id="selectAll" class="rounded-xl border px-4 py-2 text-sm font-bold hover:border-red-600 hover:text-red-600"
                                        style="border-color:var(--border-color);color:var(--text-main);">
                                        Alternar Todos
                                    </button>
                                @endif
                            </div>

                            <div class="permissions-grid {{ $isAdminRole ? 'bg-light opacity-75 p-3 rounded' : '' }}">
                                @php
                                    // 1. Clasificación en la raíz
                                    $submenus = [];
                                    $directLinks = [];

                                    foreach ($menu as $item) {
                                        if (isset($item['submenu']) && is_array($item['submenu'])) {
                                            $submenus[] = $item;
                                        } else {
                                            $directLinks[] = $item;
                                        }
                                    }

                                    // 2. Función interna recursiva para submenús anidados (> 0)
                                    if (!function_exists('renderEditChildrenItems')) {
                                        function renderEditChildrenItems($items, $rol, $isAdminRole, $depth = 1)
                                        {
                                            $margin = $depth * 15;
                                            foreach ($items as $it) {
                                                if (isset($it['submenu']) && is_array($it['submenu'])) {
                                                    echo '<div class="permission-group-title mt-2 mb-2" style="margin-left:' .
                                                        $margin .
                                                        'px;">
                                                            <strong class="text-uppercase small text-muted" style="letter-spacing:0.5px; color:var(--color-secondary) !important;">
                                                                <i class="fas fa-folder-open mr-1" style="color: #64748b;"></i> ' .
                                                        e($it['text']) .
                                                        '
                                                            </strong>
                                                          </div>';
                                                    renderEditChildrenItems(
                                                        $it['submenu'],
                                                        $rol,
                                                        $isAdminRole,
                                                        $depth + 1,
                                                    );
                                                } else {
                                                    $val = $it['key'] ?? ($it['url'] ?? ($it['route'] ?? null));
                                                    if (!$val) {
                                                        continue;
                                                    }

                                                    $checked =
                                                        $isAdminRole ||
                                                        (is_array(old('menu_permissions')) &&
                                                            in_array($val, old('menu_permissions'))) ||
                                                        in_array($val, (array) ($rol->menu_permissions ?? []))
                                                            ? 'checked'
                                                            : '';

                                                    $disabled = $isAdminRole ? 'disabled' : '';
                                                    $id = 'check_' . Str::slug($val);

                                                    echo '<div class="custom-control custom-checkbox mb-2 permission-item" style="margin-left:' .
                                                        $margin .
                                                        'px;">
                                                            <input type="checkbox" name="menu_permissions[]" value="' .
                                                        e($val) .
                                                        '" 
                                                                   class="custom-control-input perm-check" id="' .
                                                        $id .
                                                        '" ' .
                                                        $checked .
                                                        ' ' .
                                                        $disabled .
                                                        '>
                                                            <label class="custom-control-label font-weight-normal" style="cursor:pointer; font-size:0.9rem; color:var(--text-main);" for="' .
                                                        $id .
                                                        '">
                                                                ' .
                                                        e($it['text']) .
                                                        '
                                                            </label>
                                                          </div>';
                                                }
                                            }
                                        }
                                    }

                                    // 3. Renderizado de las Carpetas Principales en Cards
                                    foreach ($submenus as $folder) {
                                        echo '<div class="permission-group-block">';
                                        echo '<div class="permission-group-title mt-1 mb-2">
                                                <strong class="text-uppercase small text-muted" style="letter-spacing:0.5px; color:var(--color-secondary) !important;">
                                                    <i class="fas fa-folder-open mr-1" style="color: #64748b;"></i> ' .
                                            e($folder['text']) .
                                            '
                                                </strong>
                                              </div>';
                                        renderEditChildrenItems($folder['submenu'], $rol, $isAdminRole, 1);
                                        echo '</div>';
                                    }

                                    // 4. Renderizado Unificado de Enlaces Huérfanos Raíz
                                    if (count($directLinks) > 0) {
                                        echo '<div class="permission-group-block">';
                                        echo '<div class="permission-group-title mt-1 mb-3">
                                                <strong class="text-uppercase small text-muted" style="letter-spacing:0.5px; color:var(--color-secondary) !important;">
                                                    <i class="fas fa-link mr-1" style="color: #64748b;"></i> Accesos del Sistema
                                                </strong>
                                              </div>';

                                        foreach ($directLinks as $link) {
                                            $val = $link['key'] ?? ($link['url'] ?? ($link['route'] ?? null));
                                            if (!$val) {
                                                continue;
                                            }

                                            $checked =
                                                $isAdminRole ||
                                                (is_array(old('menu_permissions')) &&
                                                    in_array($val, old('menu_permissions'))) ||
                                                in_array($val, (array) ($rol->menu_permissions ?? []))
                                                    ? 'checked'
                                                    : '';

                                            $disabled = $isAdminRole ? 'disabled' : '';
                                            $id = 'check_' . Str::slug($val);

                                            echo '<div class="custom-control custom-checkbox mb-2 permission-item">
                                                    <input type="checkbox" name="menu_permissions[]" value="' .
                                                e($val) .
                                                '" 
                                                           class="custom-control-input perm-check" id="' .
                                                $id .
                                                '" ' .
                                                $checked .
                                                ' ' .
                                                $disabled .
                                                '>
                                                    <label class="custom-control-label font-weight-normal mb-0" style="cursor:pointer; font-size:0.9rem; color:var(--text-main);" for="' .
                                                $id .
                                                '">
                                                        ' .
                                                e($link['text']) .
                                                '
                                                    </label>
                                                  </div>';
                                        }
                                        echo '</div>';
                                    }
                                @endphp
                            </div>

                            @if ($isAdminRole)
                                <div class="alert alert-info mt-3 border-0 shadow-sm" style="border-radius: 10px;">
                                    <i class="fas fa-info-circle mr-2"></i> Los módulos y permisos del rol
                                    <strong>Administrador</strong> son totales por diseño del sistema y no requieren
                                    modificarse.
                                </div>
                            @endif
                        </div>

                        <hr class="my-4" style="opacity:0.5;">

                        {{-- Panel de Acciones --}}
                        <div class="d-flex gap-3 justify-content-end" style="gap: 10px">
                            <a href="{{ route('admin.configuracion.roles.index') }}"
                                class="inline-flex items-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-bold hover:border-red-600 hover:text-red-600"
                                style="border-color:var(--border-color);color:var(--text-main);">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            @if (!($isProtected ?? false))
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900"
                                    style="height: 48px; justify-content: center;">
                                    <i class="fas fa-save"></i> Actualizar rol
                                </button>
                            @else
                                <div class="alert alert-warning w-100 mb-0 shadow-sm border-0 d-flex align-items-center"
                                    style="border-radius: 10px;">
                                    <i class="fas fa-lock mr-3 fa-lg"></i>
                                    <div>El rol <strong>{{ $rol->nombre }}</strong> es un rol de sistema protegido y no
                                        puede ser alterado desde la interfaz web.</div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .permissions-grid {
            column-count: 3;
            column-gap: 24px;
            width: 100%;
        }

        .permission-group-block {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: inline-block;
            width: 100%;
            break-inside: avoid;
            margin-bottom: 24px;
        }

        .permission-group-block:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .custom-control-input:checked~.custom-control-label::before {
            background-color: var(--color-secondary) !important;
            border-color: var(--color-secondary) !important;
        }

        .rd-input:focus,
        .perm-check:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        @media (max-width: 1200px) {
            .permissions-grid {
                column-count: 2;
            }
        }

        @media (max-width: 768px) {
            .permissions-grid {
                column-count: 1;
            }
        }

        .item-modulo {
            padding: 6px;
            border-radius: 6px;
            transition: background 0.2s ease;
        }

        .item-modulo:hover {
            background-color: var(--input-bg);
        }
    </style>
@stop

@section('js')
    <script>
        // Manejo de Selección para Permisos de Menú
        const selectBtn = document.getElementById('selectAll');
        if (selectBtn) {
            selectBtn.addEventListener('click', function() {
                const checks = document.querySelectorAll('.perm-check:not(:disabled)');
                const allChecked = Array.from(checks).every(c => c.checked);
                checks.forEach(c => c.checked = !allChecked);
                this.textContent = allChecked ? 'Seleccionar Todos' : 'Desmarcar Todos';
            });
        }

        // Manejo de Selección para Módulos Globales
        const selectModulesBtn = document.getElementById('selectAllModules');
        if (selectModulesBtn) {
            selectModulesBtn.addEventListener('click', function() {
                const checks = document.querySelectorAll('.modulo-check:not(:disabled)');
                const allChecked = Array.from(checks).every(c => c.checked);
                checks.forEach(c => c.checked = !allChecked);
                this.textContent = allChecked ? 'Seleccionar Todos' : 'Desmarcar Todos';
            });
        }
    </script>
@stop
