@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="background: #ffffff; border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">Editar Rol: {{ $rol->nombre }}</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                <i class="fas fa-user-shield mr-1" style="color: var(--color-secondary)"></i>
                Modifica los accesos y descripciÃ³n del perfil.
            </p>
        </div>
        <a href="{{ route('admin.configuracion.roles.index') }}" class="rd-btn rd-btn-default">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="row justify-content-center fade-in">
        <div class="col-md-11">
            <div class="rd-card shadow-sm border-0">
                <div class="rd-card-body p-4">
                    <form action="{{ route('admin.configuracion.roles.update', $rol->id_rol) }}" method="POST"
                        class="rd-prevent-double-submit">
                        @csrf
                        @method('PUT')

                        {{-- 1. Datos BÃ¡sicos del Rol --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="rd-label mb-2">Nombre del Rol</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group {{ $isProtected ?? false ? 'bg-light' : '' }}">
                                    <span><i class="fas fa-tag"></i></span>
                                    <input type="text" name="nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100"
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
                                <label class="rd-label mb-2">DescripciÃ³n</label>
                                <input type="text" name="descripcion" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                    style="border: 1px solid #d8dee9; border-radius: 10px; height: 45px; padding: 0 12px;"
                                    value="{{ old('descripcion', $rol->descripcion) }}" placeholder="Descripcion del rol"
                                    {{ $isProtected ?? false ? 'readonly' : '' }}>
                            </div>
                        </div>

                        @php $isAdminRole = (strtolower($rol->nombre ?? '') === 'administrador'); @endphp

                        {{-- 2. AsignaciÃ³n de MÃ³dulos de la Base de Datos --}}
                        <div class="form-group mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="rd-label m-0">
                                    <i class="fas fa-cubes mr-2 text-success"></i> Acceso a MÃ³dulos Globales del Sistema
                                </label>
                                @if (!$isAdminRole && !($isProtected ?? false))
                                    <button type="button" id="selectAllModules" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 xs btn-outline-secondary"
                                        style="border-radius: 6px;">
                                        Alternar MÃ³dulos
                                    </button>
                                @endif
                            </div>

                            <div class="modules-container p-4 {{ $isAdminRole ? 'bg-light opacity-75' : '' }}"
                                style="border: 1px solid #eef2f6; border-radius: 12px; background: #fafbfc;">
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
                                                    style="cursor:pointer; font-size:0.95rem; color:#1e293b;"
                                                    for="modulo_{{ $modulo->id }}">
                                                    <strong>{{ $modulo->nombre }}</strong> <span
                                                        class="text-muted small">({{ $modulo->key }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center text-muted py-2">
                                            <i class="fas fa-exclamation-triangle mr-1 text-warning"></i> No hay mÃ³dulos
                                            activos registrados en la base de datos.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('modulos')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- 3. Permisos de MenÃº y NavegaciÃ³n con Grilla Tipo Masonry --}}
                        <div class="form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="rd-label m-0">
                                    <i class="fas fa-list-check mr-2 text-primary"></i> Permisos de MenÃº y NavegaciÃ³n
                                </label>
                                @if (!$isAdminRole && !($isProtected ?? false))
                                    <button type="button" id="selectAll" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 xs btn-outline-secondary"
                                        style="border-radius:6px;">
                                        Alternar Todos
                                    </button>
                                @endif
                            </div>

                            <div class="permissions-grid {{ $isAdminRole ? 'bg-light opacity-75 p-3 rounded' : '' }}">
                                @php
                                    // 1. ClasificaciÃ³n en la raÃ­z
                                    $submenus = [];
                                    $directLinks = [];

                                    foreach ($menu as $item) {
                                        if (isset($item['submenu']) && is_array($item['submenu'])) {
                                            $submenus[] = $item;
                                        } else {
                                            $directLinks[] = $item;
                                        }
                                    }

                                    // 2. FunciÃ³n interna recursiva para submenÃºs anidados (> 0)
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
                                                            <label class="custom-control-label font-weight-normal" style="cursor:pointer; font-size:0.9rem; color: #334155;" for="' .
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

                                    // 4. Renderizado Unificado de Enlaces HuÃ©rfanos RaÃ­z
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
                                                    <label class="custom-control-label font-weight-normal mb-0" style="cursor:pointer; font-size:0.9rem; color: #334155;" for="' .
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
                                    <i class="fas fa-info-circle mr-2"></i> Los mÃ³dulos y permisos del rol
                                    <strong>Administrador</strong> son totales por diseÃ±o del sistema y no requieren
                                    modificarse.
                                </div>
                            @endif
                        </div>

                        <hr class="my-4" style="opacity:0.5;">

                        {{-- Panel de Acciones --}}
                        <div class="d-flex gap-3 justify-content-end" style="gap: 10px">
                            <a href="{{ route('admin.configuracion.roles.index') }}"
                                class="rd-btn rd-btn-default px-4 d-flex align-items-center">
                                Cancelar
                            </a>
                            @if (!($isProtected ?? false))
                                <button type="submit" class="rd-btn rd-btn-primary rd-submit-btn px-5"
                                    style="height: 48px; justify-content: center;">
                                    <i class="fas fa-sync-alt"></i> Actualizar Rol
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
            background: #ffffff;
            border: 1px solid #e2e8f0;
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
            background-color: #f1f5f9;
        }
    </style>
@stop

@section('js')
    <script>
        // Manejo de SelecciÃ³n para Permisos de MenÃº
        const selectBtn = document.getElementById('selectAll');
        if (selectBtn) {
            selectBtn.addEventListener('click', function() {
                const checks = document.querySelectorAll('.perm-check:not(:disabled)');
                const allChecked = Array.from(checks).every(c => c.checked);
                checks.forEach(c => c.checked = !allChecked);
                this.textContent = allChecked ? 'Seleccionar Todos' : 'Desmarcar Todos';
            });
        }

        // Manejo de SelecciÃ³n para MÃ³dulos Globales
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

