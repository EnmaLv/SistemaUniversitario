@php
    /* ──────────────── Datos base ──────────────── */
    $isEdicion    = isset($rol) && $rol !== null;
    $isProtected  = $isProtected ?? false;
    $isAdminRole  = $isEdicion && (strtolower($rol->nombre ?? '') === 'administrador');
    $isDisabled   = $isAdminRole || $isProtected;

    $nombreValue      = old('nombre', $rol->nombre ?? '');
    $descripcionValue = old('descripcion', $rol->descripcion ?? '');

    $permisosValue = old('menu_permissions', $rol->menu_permissions ?? []);
    if (!is_array($permisosValue)) $permisosValue = [];

    $modulosValue = old('modulos', $isEdicion ? $rol->modulos->pluck('id')->toArray() : []);
    if (!is_array($modulosValue)) $modulosValue = [];

    /* Grupos de permisos desde el config */
    $grupos = $menu ?? config('menu_permissions', []);
@endphp

<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="rounded-2xl border shadow-sm p-6 sm:p-8">

    <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit">
        @csrf
        @if (($metodo ?? 'POST') !== 'POST')
            @method($metodo)
        @endif

        {{-- ═══════════════════ SECCIÓN 1: Datos básicos ═══════════════════ --}}
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
                {{-- Nombre --}}
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

                {{-- Descripción --}}
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

        {{-- ═══════════════════ SECCIÓN 2: Módulos del sistema ═══════════════════ --}}
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
                            Selecciona los módulos a los que este rol puede ingresar.
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
                                $checked  = in_array($modulo->id, $modulosValue) || $isAdminRole;
                                $disabled = $isDisabled ? 'disabled' : '';
                                $id       = 'modulo_' . $modulo->id;
                            @endphp
                            <label for="{{ $id }}"
                                class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all group
                                    {{ $isDisabled
                                        ? 'opacity-60 cursor-not-allowed'
                                        : 'hover:border-red-300 hover:bg-red-50/40 dark:hover:bg-red-950/20' }}"
                                style="border-color: var(--border-color);">
                                <input type="checkbox" name="modulos[]" value="{{ $modulo->id }}"
                                    id="{{ $id }}"
                                    class="modulo-check w-4 h-4 rounded border-gray-300 dark:border-gray-600 accent-red-600 focus:ring-2 focus:ring-red-500/30 cursor-pointer"
                                    {{ $checked ? 'checked' : '' }} {{ $disabled }}>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                        {{ $modulo->nombre }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-mono truncate">
                                        {{ $modulo->key }}
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

        {{-- ═══════════════════ SECCIÓN 3: Permisos de menú (por módulo del sistema) ═══════════════════ --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                        <i class="fas fa-list-check text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold" style="color: var(--text-main);">
                            Permisos de menú por módulo
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Marca las secciones que este rol podrá ver en el menú lateral.
                        </p>
                    </div>
                </div>

                @if (!$isDisabled)
                    <button type="button" id="selectAllPermissions"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:border-red-400 hover:text-red-600 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-check-double text-[10px]"></i>
                        <span>Seleccionar todos</span>
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 {{ $isDisabled ? 'opacity-70' : '' }}">
                @foreach ($grupos as $grupoKey => $grupo)
                    @php
                        $grupoLabel = $grupo['label'] ?? ucfirst($grupoKey);
                        $grupoIcon  = $grupo['icon'] ?? 'fas fa-folder';
                        $grupoDesc  = $grupo['description'] ?? null;
                        $grupoItems = $grupo['items'] ?? [];
                    @endphp

                    <div class="rounded-xl border p-4 flex flex-col"
                        style="background-color: var(--bg-card); border-color: var(--border-color);">

                        {{-- Cabecera del grupo --}}
                        <div class="flex items-center gap-2.5 mb-3 pb-2 border-b"
                            style="border-color: var(--border-color);">
                            <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center flex-shrink-0">
                                <i class="{{ $grupoIcon }} text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-black uppercase tracking-wider"
                                    style="color: var(--text-main);">
                                    {{ $grupoLabel }}
                                </p>
                                @if ($grupoDesc)
                                    <p class="text-[10px] text-gray-400 leading-tight mt-0.5">
                                        {{ $grupoDesc }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Items del grupo --}}
                        <div class="flex flex-col gap-0.5">
                            @foreach ($grupoItems as $key => $label)
                                @php
                                    $checked  = in_array($key, $permisosValue) || $isAdminRole;
                                    $disabled = $isDisabled ? 'disabled' : '';
                                    $id       = 'perm_' . md5($key);
                                    $opacity  = $isDisabled
                                        ? 'opacity-60 cursor-not-allowed'
                                        : 'hover:bg-red-50 dark:hover:bg-red-950/20';
                                @endphp
                                <label for="{{ $id }}"
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-lg cursor-pointer transition-colors group {{ $opacity }}">
                                    <input type="checkbox" name="menu_permissions[]" value="{{ $key }}"
                                        id="{{ $id }}"
                                        class="perm-check w-4 h-4 rounded border-gray-300 dark:border-gray-600 accent-red-600 focus:ring-2 focus:ring-red-500/30 cursor-pointer"
                                        {{ $checked ? 'checked' : '' }} {{ $disabled }}>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @error('menu_permissions')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- ═══════════════════ Alertas informativas ═══════════════════ --}}
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
                    El rol <strong>{{ $rol->nombre }}</strong> es un rol de sistema protegido y no puede ser alterado desde la interfaz web.
                </div>
            </div>
        @endif

        {{-- ═══════════════════ Acciones ═══════════════════ --}}
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

        toggleGroup('selectAllPermissions', '.perm-check', {
            select:   'Seleccionar todos',
            deselect: 'Desmarcar todos',
        });
    })();
</script>
@endpush