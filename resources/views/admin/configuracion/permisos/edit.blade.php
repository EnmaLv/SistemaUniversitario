@extends('layouts.app')

@section('content_header')
    <div class="rounded-2xl border p-5 mb-6 shadow-sm d-flex justify-content-between align-items-center"
        style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div>
            <h1 class="m-0 text-2xl sm:text-3xl font-extrabold" style="color:var(--text-main);">Gestionar permisos especiales</h1>
            <p class="mt-1 mb-0 text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-user-shield mr-1" style="color:var(--color-primary)"></i>
                Usuario: <strong>{{ $usuario->username }}</strong> · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('admin.configuracion.permisos.index') }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold hover:border-red-600 hover:text-red-600" style="border-color:var(--border-color);color:var(--text-main);">
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
                    <form action="{{ route('admin.configuracion.permisos.update', $usuario->id_usuario) }}" method="POST"
                        class="rd-prevent-double-submit">
                        @csrf
                        @method('PUT')
                        <div class="mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="rd-label m-0">
                                    <i class="fas fa-cubes mr-2" style="color:var(--color-primary);"></i> Módulos especiales para este usuario
                                </label>
                                <span class="inline-flex items-center gap-1 rounded-lg border px-3 py-1 text-xs font-bold text-gray-500 dark:text-gray-400"
                                    style="border-color:var(--border-color);">
                                    <i class="fas fa-info-circle mr-1"></i> Los módulos marcados en verde vienen heredados
                                    de su Rol base
                                </span>
                            </div>

                            <div class="modules-container p-4"
                                style="border:1px solid var(--border-color);border-radius:12px;background-color:var(--input-bg);">
                                <div class="row">
                                    @forelse($modulos as $modulo)
                                        @php
                                            $loTienePorRol = in_array($modulo->id, $roleModules);
                                            $loTieneAsignadoExtra = in_array($modulo->id, $modulosExtra);
                                        @endphp
                                        <div class="col-md-4 mb-2">
                                            <div class="item-modulo rounded-lg border p-2"
                                                style="background-color:var(--input-bg);border-color:{{ $loTienePorRol ? 'var(--color-primary)' : 'var(--border-color)' }};">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="modulos[]" value="{{ $modulo->id }}"
                                                        class="custom-control-input modulo-check"
                                                        id="modulo_{{ $modulo->id }}"
                                                        {{ $loTienePorRol || $loTieneAsignadoExtra ? 'checked' : '' }}
                                                        {{ $loTienePorRol ? 'disabled data-from-role="1"' : '' }}>
                                                    <label class="custom-control-label font-weight-normal mb-0"
                                                        style="cursor:pointer; font-size:0.95rem; color:var(--text-main);"
                                                        for="modulo_{{ $modulo->id }}">
                                                        <strong>{{ $modulo->nombre }}</strong>
                                                        @if ($loTienePorRol)
                                                            <span class="ml-1 rounded-md border px-2 py-0.5 text-[10px] font-bold"
                                                                style="border-color:var(--color-primary);color:var(--color-primary);">Heredado del rol</span>
                                                        @endif
                                                    </label>
                                                </div>
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
                        </div>

                        <hr class="my-4" style="opacity:0.3;">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="rd-label m-0">
                                    <i class="fas fa-list-check mr-2" style="color:var(--color-primary);"></i> Matriz de permisos individuales
                                </label>
                                <span class="inline-flex items-center gap-1 rounded-lg border px-3 py-1 text-xs font-bold text-gray-500 dark:text-gray-400"
                                    style="border-color:var(--border-color);">
                                    <i class="fas fa-info-circle mr-1"></i> Los cambios aquí sobrescriben el rol base
                                </span>
                            </div>

                            <div class="permissions-grid p-4"
                                style="border:1px solid var(--border-color);border-radius:12px;background-color:var(--input-bg);">
                                @include('admin.configuracion.permisos._matrix', [
                                    'items' => $menu ?? [],
                                    'rolePerms' => $rolePerms ?? [],
                                    'rolePatterns' => $rolePatterns ?? [],
                                    'effective' => $effective ?? ($allow ?? []),
                                    'keyToPatterns' => $keyToPatterns ?? [],
                                    'depth' => 0,
                                ])
                            </div>
                        </div>

                        <hr class="my-4" style="opacity:0.5;">

                        <div class="d-flex gap-3 justify-content-end" style="gap:10px">
                            <a href="{{ route('admin.configuracion.permisos.index') }}"
                                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold hover:border-red-600 hover:text-red-600"
                                style="border-color:var(--border-color);color:var(--text-main);">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" id="save-perms" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900"
                                style="height: 48px; justify-content: center;">
                                <i class="fas fa-save"></i> Aplicar Ajustes
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
            border-color: var(--color-primary);
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
    </style>
@stop

@section('js')
    <script>
        (function() {
            const form = document.querySelector('form');

            const moduloChecks = document.querySelectorAll('.modulo-check');
            const selectorPermChk = document.querySelector('.perm-chk[value="admin/modulos/seleccionar"]');

            if (moduloChecks.length && selectorPermChk) {
                function actualizarPermisoSelector() {
                    const totalModulosChecked = document.querySelectorAll('.modulo-check:checked').length;

                    if (totalModulosChecked > 1) {
                        selectorPermChk.checked = true;
                    } else {
                        selectorPermChk.checked = false;
                    }
                }

                moduloChecks.forEach(chk => {
                    chk.addEventListener('change', actualizarPermisoSelector);
                });
            }

            document.querySelectorAll('.perm-chk[data-role="1"]').forEach(chk => {
                chk.addEventListener('click', function(e) {
                    if (!this.checked) {
                        e.preventDefault();
                        Swal.fire({
                            title: '¿Deshabilitar permiso de rol?',
                            text: 'Este permiso es heredado del rol del usuario. Al desmarcarlo, estarás restringiendo explícitamente esta función.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: 'var(--color-primary)',
                            confirmButtonText: 'Sí, restringir',
                            cancelButtonText: 'Mantener'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.checked = false;
                            }
                        });
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                document.querySelectorAll('.perm-chk').forEach(i => i.removeAttribute('name'));

                const allow = [];
                const deny = [];

                document.querySelectorAll('.perm-chk').forEach(i => {
                    const val = i.value;
                    const isRole = i.dataset.role === '1';
                    if (isRole) {
                        if (!i.checked) {
                            deny.push(val);
                            i.name = 'deny[]';
                        }
                    } else {
                        if (i.checked) {
                            allow.push(val);
                            i.name = 'allow[]';
                        }
                    }
                });

                if (allow.length === 0 && deny.length === 0) {
                    form.submit();
                    return;
                }

                let htmlContent = '<div class="text-left small">';
                if (allow.length) htmlContent += '<strong>Permisos adicionales:</strong><ul class="mb-2">' +
                    allow.map(a => '<li>' + a + '</li>').join('') + '</ul>';
                if (deny.length) htmlContent += '<strong>Restricciones sobre el rol:</strong><ul>' + deny.map(
                    a => '<li>' + a + '</li>').join('') + '</ul>';
                htmlContent += '</div>';

                Swal.fire({
                    title: 'Confirmar cambios',
                    html: htmlContent,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--color-secondary)',
                    confirmButtonText: 'Aplicar',
                    cancelButtonText: 'Revisar'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        })();
    </script>
@stop
