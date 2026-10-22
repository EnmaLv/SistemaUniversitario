@php
    $isEdicion    = isset($rol) && $rol !== null;
    $isProtected  = $isProtected ?? false;
    $isAdminRole  = $isEdicion && (strtolower($rol->nombre_rol ?? '') === 'administrador');
    $isDisabled   = $isAdminRole || $isProtected;

    $nombreValue      = old('nombre', $rol->nombre_rol ?? '');
    $descripcionValue = old('descripcion', $rol->descripcion_rol ?? '');

    $permisosValue = old('permisos', $isEdicion ? $rol->permisos->pluck('id_pmi')->all() : []);
    if (!is_array($permisosValue)) $permisosValue = [];

    $modulosValue = old('modulos', $isEdicion ? $rol->modulos->pluck('id_mod')->toArray() : []);
    if (!is_array($modulosValue)) $modulosValue = [];

@endphp

<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="rounded-2xl border shadow-sm p-6 sm:p-8">

    <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit">
        @csrf
        @if (($metodo ?? 'POST') !== 'POST')
            @method($metodo)
        @endif

        <div class="mb-8">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold" style="color: var(--text-main);">
                        Información del rol
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Datos básicos que identifican al perfil.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                        Nombre del rol <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 {{ $isDisabled ? 'opacity-60' : '' }}"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                            style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                            <i class="fas fa-tag text-sm"></i>
                        </span>
                        <input type="text" name="nombre" value="{{ $nombreValue }}"
                            placeholder="Ej: Supervisor de Comedor"
                            {{ $isDisabled ? 'readonly' : 'required' }}
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                    </div>
                    @error('nombre')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                        Descripción corta
                    </label>
                    <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 {{ $isDisabled ? 'opacity-60' : '' }}"
                        style="border-color: var(--border-color);">
                        <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                            style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                            <i class="fas fa-align-left text-sm"></i>
                        </span>
                        <input type="text" name="descripcion" value="{{ $descripcionValue }}"
                            placeholder="Propósito del rol"
                            {{ $isDisabled ? 'readonly' : '' }}
                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                    </div>
                    @error('descripcion')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                        <i class="fas fa-cubes text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold" style="color: var(--text-main);">
                            Acceso a módulos del sistema
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Con un solo módulo el usuario entra directo a él; con dos o más verá el selector de módulos al iniciar sesión.
                        </p>
                    </div>
                </div>

                @if (!$isDisabled)
                    <button type="button" id="selectAllModules"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:border-red-400 hover:text-red-600 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-check-double text-[10px]"></i>
                        <span>Seleccionar todos</span>
                    </button>
                @endif
            </div>

            <div class="rounded-xl border p-4 sm:p-5 {{ $isDisabled ? 'opacity-70' : '' }}"
                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">

                @if ($modulos->isEmpty())
                    <div class="py-8 text-center text-sm text-gray-400">
                        <i class="fas fa-exclamation-triangle text-amber-500 mr-2"></i>
                        No hay módulos activos registrados en el sistema.
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach ($modulos as $modulo)
                            @php
                                $checked  = in_array($modulo->id_mod, $modulosValue) || $isAdminRole;
                                $disabled = $isDisabled ? 'disabled' : '';
                                $id       = 'modulo_' . $modulo->id_mod;
                            @endphp
                            <label for="{{ $id }}"
                                class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all group
                                    {{ $isDisabled
                                        ? 'opacity-60 cursor-not-allowed'
                                        : 'hover:border-red-300 hover:bg-red-50/40 dark:hover:bg-red-950/20' }}"
                                style="border-color: var(--border-color);">
                                <input type="checkbox" name="modulos[]" value="{{ $modulo->id_mod }}"
                                    id="{{ $id }}"
                                    class="modulo-check w-4 h-4 rounded border-gray-300 dark:border-gray-600 accent-red-600 focus:ring-2 focus:ring-red-500/30 cursor-pointer"
                                    {{ $checked ? 'checked' : '' }} {{ $disabled }}>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                        {{ $modulo->nombre_mod }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-mono truncate">
                                        {{ $modulo->key_mod }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            @error('modulos')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-8">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <i class="fas fa-list-check text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold" style="color: var(--text-main);">
                        Permisos por vista y acción
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-bold">Ver</span> muestra la vista en el menú. Las demás acciones habilitan los botones y rutas de crear, editar, eliminar, activar y exportar dentro de esa vista.
                    </p>
                </div>
            </div>

            @include('admin.configuracion.partials.matriz-permisos', [
                'grupos'        => $grupos,
                'modo'          => 'rol',
                'seleccionados' => $permisosValue,
                'todoMarcado'   => $isAdminRole,
                'deshabilitado' => $isDisabled,
            ])

            @error('permisos')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
            @error('permisos.*')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        @if ($isAdminRole)
            <div class="mb-6 p-4 rounded-xl border border-sky-200 dark:border-sky-800/60 bg-sky-50 dark:bg-sky-950/30 flex items-start gap-3">
                <i class="fas fa-info-circle text-sky-600 dark:text-sky-400 mt-0.5"></i>
                <div class="text-sm text-sky-800 dark:text-sky-300">
                    El rol <strong>Administrador</strong> tiene acceso total por diseño del sistema.
                    Sus módulos y permisos no pueden modificarse.
                </div>
            </div>
        @endif

        @if ($isProtected && !$isAdminRole)
            <div class="mb-6 p-4 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-3">
                <i class="fas fa-lock text-amber-600 dark:text-amber-400 mt-0.5"></i>
                <div class="text-sm text-amber-800 dark:text-amber-300">
                    El rol <strong>{{ $rol->nombre_rol }}</strong> es un rol de sistema protegido y no puede ser alterado desde la interfaz web.
                </div>
            </div>
        @endif

        <div class="pt-6 border-t flex items-center justify-end gap-3"
            style="border-color: var(--border-color);">

            <a href="{{ $rutaVolver ?? route('admin.configuracion.roles.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                style="border-color: var(--border-color); color: var(--text-main);">
                <i class="fas fa-arrow-left text-xs"></i>
                Cancelar
            </a>

            @if (!$isDisabled)
                <button type="submit"
                    class="rd-submit-btn inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold shadow-md active:scale-95 transition-all">
                    <i class="fas fa-save text-xs"></i>
                    {{ $isEdicion ? 'Actualizar rol' : 'Guardar rol' }}
                </button>
            @endif
        </div>
    </form>
</div>

@push('scripts')
<script>
    (function () {
        'use strict';

        function toggleGroup(buttonId, checkSelector, labels) {
            const btn = document.getElementById(buttonId);
            if (!btn) return;

            btn.addEventListener('click', () => {
                const checks = document.querySelectorAll(checkSelector + ':not(:disabled)');
                if (!checks.length) return;

                const allChecked = Array.from(checks).every(c => c.checked);
                checks.forEach(c => c.checked = !allChecked);

                const span = btn.querySelector('span');
                if (span) {
                    span.textContent = allChecked ? labels.select : labels.deselect;
                }
            });
        }

        toggleGroup('selectAllModules', '.modulo-check', {
            select:   'Seleccionar todos',
            deselect: 'Desmarcar todos',
        });
    })();
</script>
@endpush