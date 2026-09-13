@extends('layouts.app')

@section('content_header')
    <div class="rd-card p-4 mb-4 d-flex justify-content-between align-items-center"
        style="background: #ffffff; border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
        <div>
            <h1 class="m-0" style="font-size:1.45rem; color:#0f172a; font-weight:700;">Crear Nuevo Rol</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                <i class="fas fa-shield-alt mr-1" style="color: var(--color-secondary)"></i> 
                Define un nuevo perfil de acceso al sistema.
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
                    <form action="{{ route('admin.configuracion.roles.store') }}" method="POST" class="rd-prevent-double-submit">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="rd-label mb-2">Nombre del Rol</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                    <span><i class="fas fa-tag"></i></span>
                                    <input type="text" name="nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" 
                                           placeholder="Ej: Supervisor" required value="{{ old('nombre') }}">
                                </div>
                                @error('nombre')
                                    <div class="col-md-12 mt-2">
                                        <small class="text-danger">{{ $message }}</small>
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="rd-label mb-2">DescripciÃ³n Corta</label>
                                <input type="text" name="descripcion" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20" 
                                       placeholder="PropÃ³sito del rol" value="{{ old('descripcion') }}"
                                       style="border: 1px solid #d8dee9; border-radius: 10px; padding: 8px 12px; height: 45px;">
                            </div>
                        </div>

                        <div class="form-group mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="rd-label m-0">
                                    <i class="fas fa-cubes mr-2 text-success"></i> Acceso a MÃ³dulos Globales del Sistema
                                </label>
                                <button type="button" id="selectAllModules" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 xs btn-outline-secondary" style="border-radius: 6px;">
                                    Seleccionar Todos los MÃ³dulos
                                </button>
                            </div>
                            
                            <div class="modules-container p-4" 
                                 style="border: 1px solid #eef2f6; border-radius: 12px; background: #fafbfc;">
                                <div class="row">
                                    @forelse($modulos as $modulo)
                                        <div class="col-md-4 mb-2">
                                            <div class="custom-control custom-checkbox item-modulo">
                                                <input type="checkbox" name="modulos[]" value="{{ $modulo->id }}" 
                                                       class="custom-control-input modulo-check" id="modulo_{{ $modulo->id }}"
                                                       {{ is_array(old('modulos')) && in_array($modulo->id, old('modulos')) ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-normal" style="cursor:pointer; font-size:0.95rem; color:#1e293b;" for="modulo_{{ $modulo->id }}">
                                                    <strong>{{ $modulo->nombre }}</strong> <span class="text-muted small">({{ $modulo->key }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center text-muted py-2">
                                            <i class="fas fa-exclamation-triangle mr-1 text-warning"></i> No hay mÃ³dulos activos registrados en la base de datos.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('modulos')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="rd-label m-0">
                                    <i class="fas fa-list-check mr-2 text-primary"></i> Visibilidad de Ãtems del MenÃº Lateral
                                </label>
                                <button type="button" id="selectAll" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 xs btn-outline-secondary" style="border-radius: 6px;">
                                    Seleccionar Todos los MenÃºs
                                </button>
                            </div>
                            
                            <div class="permissions-grid">
                                @php
                                    // 1. Separamos los elementos de la raÃ­z en submenÃºs y links directos
                                    $submenus = [];
                                    $directLinks = [];
                                    
                                    foreach ($menu as $item) {
                                        if (isset($item['submenu']) && is_array($item['submenu'])) {
                                            $submenus[] = $item;
                                        } else {
                                            $directLinks[] = $item;
                                        }
                                    }

                                    // 2. FunciÃ³n interna recursiva para renderizar los niveles internos (> 0)
                                    if (!function_exists('renderChildrenItems')) {
                                        function renderChildrenItems($items, $depth = 1) {
                                            $margin = $depth * 15;
                                            foreach ($items as $it) {
                                                if (isset($it['submenu']) && is_array($it['submenu'])) {
                                                    echo '<div class="permission-group-title mt-2 mb-2" style="margin-left:'.$margin.'px;">
                                                            <strong class="text-uppercase small text-muted" style="letter-spacing:0.5px; color:var(--color-secondary) !important;">
                                                                <i class="fas fa-folder-open mr-1" style="color: #64748b;"></i> '.e($it['text']).'
                                                            </strong>
                                                          </div>';
                                                    renderChildrenItems($it['submenu'], $depth + 1);
                                                } else {
                                                    $val = $it['key'] ?? ($it['url'] ?? ($it['route'] ?? null));
                                                    if (!$val) continue;
                                                    
                                                    $checked = is_array(old('menu_permissions')) && in_array($val, old('menu_permissions')) ? 'checked' : '';

                                                    echo '<div class="custom-control custom-checkbox mb-2 permission-item" style="margin-left:'.$margin.'px;">
                                                            <input type="checkbox" name="menu_permissions[]" value="'.e($val).'" '.$checked.' class="custom-control-input perm-check" id="check_'.e($val).'">
                                                            <label class="custom-control-label font-weight-normal" style="cursor:pointer; font-size:0.9rem; color: #334155;" for="check_'.e($val).'">
                                                                '.e($it['text']).'
                                                            </label>
                                                          </div>';
                                                }
                                            }
                                        }
                                    }

                                    // 3. Renderizar las Carpetas Principales (Cada una en su bloque Card)
                                    foreach ($submenus as $folder) {
                                        echo '<div class="permission-group-block">';
                                        echo '<div class="permission-group-title mt-1 mb-2">
                                                <strong class="text-uppercase small text-muted" style="letter-spacing:0.5px; color:var(--color-secondary) !important;">
                                                    <i class="fas fa-folder-open mr-1" style="color: #64748b;"></i> '.e($folder['text']).'
                                                </strong>
                                              </div>';
                                        renderChildrenItems($folder['submenu'], 1);
                                        echo '</div>';
                                    }

                                    // 4. Renderizar Links HuÃ©rfanos Agrupados en una sola tarjeta al final
                                    if (count($directLinks) > 0) {
                                        echo '<div class="permission-group-block">';
                                        echo '<div class="permission-group-title mt-1 mb-3">
                                                <strong class="text-uppercase small text-muted" style="letter-spacing:0.5px; color:var(--color-secondary) !important;">
                                                    <i class="fas fa-link mr-1" style="color: #64748b;"></i> Accesos del Sistema
                                                </strong>
                                              </div>';
                                              
                                        foreach ($directLinks as $link) {
                                            $val = $link['key'] ?? ($link['url'] ?? ($link['route'] ?? null));
                                            if (!$val) continue;
                                            
                                            $checked = is_array(old('menu_permissions')) && in_array($val, old('menu_permissions')) ? 'checked' : '';

                                            echo '<div class="custom-control custom-checkbox mb-2 permission-item">
                                                    <input type="checkbox" name="menu_permissions[]" value="'.e($val).'" '.$checked.' class="custom-control-input perm-check" id="check_'.e($val).'">
                                                    <label class="custom-control-label font-weight-normal mb-0" style="cursor:pointer; font-size:0.9rem; color: #334155;" for="check_'.e($val).'">
                                                        '.e($link['text']).'
                                                    </label>
                                                  </div>';
                                        }
                                        echo '</div>';
                                    }
                                @endphp
                            </div>
                        </div>

                        {{-- Botones de AcciÃ³n --}}
                        <div class="d-flex mt-5 justify-content-end" style="gap: 10px">
                            <a href="{{ route('admin.configuracion.roles.index') }}" class="rd-btn rd-btn-default px-4" style="height: 48px; justify-content: center;">
                                Cancelar
                            </a>
                            <button type="submit" class="rd-btn rd-btn-primary rd-submit-btn px-5" style="height: 48px;justify-content: center;">
                                <i class="fas fa-save"></i> Guardar Nuevo Rol
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

        /* PequeÃ±o efecto hover para los mÃ³dulos superiores */
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
    document.getElementById('selectAll').addEventListener('click', function() {
        const checks = document.querySelectorAll('.perm-check');
        const allChecked = Array.from(checks).every(c => c.checked);
        checks.forEach(c => c.checked = !allChecked);
        this.textContent = allChecked ? 'Seleccionar Todos los MenÃºs' : 'Desmarcar Todos los MenÃºs';
    });

    // Manejo de SelecciÃ³n para MÃ³dulos Globales
    document.getElementById('selectAllModules').addEventListener('click', function() {
        const checks = document.querySelectorAll('.modulo-check');
        const allChecked = Array.from(checks).every(c => c.checked);
        checks.forEach(c => c.checked = !allChecked);
        this.textContent = allChecked ? 'Seleccionar Todos los MÃ³dulos' : 'Desmarcar Todos los MÃ³dulos';
    });
</script>
@stop
