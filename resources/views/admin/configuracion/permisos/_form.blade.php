@php
    $grupos = $grupos ?? config('menu_permissions', []);
@endphp

<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="rounded-2xl border shadow-sm p-6 sm:p-8">

    <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit" id="permisosForm">
        @csrf
        @if (($metodo ?? 'POST') !== 'POST')
            @method($metodo)
        @endif

        {{-- ═══════════════════ CABECERA USUARIO ═══════════════════ --}}
        <div class="flex items-center gap-4 mb-8 p-4 rounded-2xl border"
            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">

            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-lg shadow-md shrink-0">
                {{ strtoupper(mb_substr($usuario->nombre_completo ?? $usuario->username, 0, 2)) }}
            </div>

            <div class="min-w-0 flex-1">
                <h3 class="text-lg font-extrabold truncate leading-tight" style="color: var(--text-main);">
                    {{ $usuario->nombre_completo ?? 'Sin nombre' }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                    <span class="font-mono"></span>{{ $usuario->username }}
                    @if ($usuario->roles->isNotEmpty())
                        ·
                        @foreach ($usuario->roles as $r)
                            <span class="inline-flex items-center rounded-md border px-1.5 py-0.5 text-[10px] font-bold ml-1 align-middle"
                                style="border-color: var(--border-color); color: var(--text-main);">
                                {{ $r->nombre }}
                            </span>
                        @endforeach
                    @endif
                </p>
            </div>
        </div>

        {{-- ═══════════════════ SECCIÓN 1: MÓDULOS ESPECIALES ═══════════════════ --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                        <i class="fas fa-cubes text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold" style="color: var(--text-main);">
                            Módulos especiales
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Los módulos <span class="font-bold text-emerald-600 dark:text-emerald-400">heredados del rol</span> están bloqueados y no se pueden desmarcar.
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border p-4 sm:p-5"
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
                                $fromRole  = in_array($modulo->id, $roleModules);
                                $fromExtra = in_array($modulo->id, $modulosExtra);
                                $checked   = $fromRole || $fromExtra;
                                $id        = 'modulo_' . $modulo->id;
                            @endphp
                            <label for="{{ $id }}"
                                class="flex items-center gap-3 p-3 rounded-xl border transition-all group
                                    {{ $fromRole
                                        ? 'cursor-not-allowed bg-emerald-50/50 dark:bg-emerald-950/10 border-emerald-200 dark:border-emerald-800/40'
                                        : 'cursor-pointer hover:border-red-300 hover:bg-red-50/40 dark:hover:bg-red-950/20' }}"
                                @if (!$fromRole) style="border-color: var(--border-color);" @endif>
                                <input type="checkbox" name="modulos[]" value="{{ $modulo->id }}"
                                    id="{{ $id }}"
                                    class="modulo-check w-4 h-4 rounded border-gray-300 dark:border-gray-600 accent-red-600 focus:ring-2 focus:ring-red-500/30 cursor-pointer"
                                    {{ $checked ? 'checked' : '' }}
                                    {{ $fromRole ? 'disabled data-from-role="1"' : '' }}>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                        {{ $modulo->nombre }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-mono truncate">
                                        {{ $modulo->key }}
                                    </p>
                                </div>
                                @if ($fromRole)
                                    <span class="inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 shrink-0"
                                        style="border-color: rgba(16,185,129,0.3);">
                                        <i class="fas fa-id-badge text-[8px]"></i> Heredado
                                    </span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            @error('modulos')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- ═══════════════════ SECCIÓN 2: PERMISOS DE MENÚ ═══════════════════ --}}
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
                            Los cambios aquí <span class="font-bold">sobrescriben</span> el rol base. Marcar un permiso que el rol no tiene = <span class="font-bold text-blue-600 dark:text-blue-400">Extra</span>. Desmarcar uno del rol = <span class="font-bold text-red-600 dark:text-red-400">Bloqueado</span>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse ($grupos as $grupoKey => $grupo)
                    @php
                        $grupoLabel = $grupo['label'] ?? ucfirst($grupoKey);
                        $grupoIcon  = $grupo['icon']  ?? 'fas fa-folder';
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

                        {{-- Items --}}
                        <div class="flex flex-col gap-0.5">
                            @forelse ($grupoItems as $key => $label)
                                @php
                                    $fromRole  = in_array($key, $rolePerms);
                                    $isDenied  = in_array($key, $deny);
                                    $isAllowed = in_array($key, $allow);

                                    if ($fromRole) {
                                        $checked = !$isDenied;
                                        $badge   = $isDenied
                                            ? ['text' => 'Bloqueado', 'color' => 'text-red-600 dark:text-red-400',     'icon' => 'fas fa-lock']
                                            : ['text' => 'Heredado', 'color' => 'text-emerald-600 dark:text-emerald-400', 'icon' => 'fas fa-id-badge'];
                                    } else {
                                        $checked = $isAllowed;
                                        $badge   = $isAllowed
                                            ? ['text' => 'Extra',   'color' => 'text-blue-600 dark:text-blue-400',    'icon' => 'fas fa-plus']
                                            : null;
                                    }
                                    $id = 'perm_' . md5($key);
                                @endphp

                                <label for="{{ $id }}"
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-lg cursor-pointer transition-colors group
                                        {{ $fromRole
                                            ? 'hover:bg-emerald-50 dark:hover:bg-emerald-950/20'
                                            : 'hover:bg-red-50 dark:hover:bg-red-950/20' }}">
                                    <input type="checkbox"
                                        class="perm-check w-4 h-4 rounded border-gray-300 dark:border-gray-600 accent-red-600 focus:ring-2 focus:ring-red-500/30 cursor-pointer"
                                        id="{{ $id }}"
                                        value="{{ $key }}"
                                        data-role="{{ $fromRole ? '1' : '0' }}"
                                        {{ $checked ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors flex-1">
                                        {{ $label }}
                                    </span>
                                    @if ($badge)
                                        <span class="inline-flex items-center gap-1 rounded text-[9px] font-black uppercase tracking-wider shrink-0 {{ $badge['color'] }}"
                                            title="{{ $badge['text'] }}">
                                            <i class="{{ $badge['icon'] }} text-[8px]"></i>
                                            <span class="hidden sm:inline">{{ $badge['text'] }}</span>
                                        </span>
                                    @endif
                                </label>
                            @empty
                                <p class="text-[11px] text-gray-400 py-2 px-2 italic">
                                    Sin permisos en este grupo.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-gray-400">
                        <i class="fas fa-exclamation-triangle text-amber-500 mr-2"></i>
                        No hay permisos definidos en <code class="font-mono">config/menu_permissions.php</code>.
                    </div>
                @endforelse
            </div>

            @error('allow')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
            @error('deny')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- ═══════════════════ ACCIONES ═══════════════════ --}}
        <div class="pt-6 border-t flex items-center justify-end gap-3"
            style="border-color: var(--border-color);">

            <a href="{{ route('admin.configuracion.permisos.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                style="border-color: var(--border-color); color: var(--text-main);">
                <i class="fas fa-arrow-left text-xs"></i>
                Cancelar
            </a>

            <button type="submit"
                class="rd-submit-btn inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold shadow-md active:scale-95 transition-all">
                <i class="fas fa-save text-xs"></i>
                Aplicar ajustes
            </button>
        </div>
    </form>
</div>

<script>
    (function () {
        'use strict';

        const form = document.getElementById('permisosForm');
        if (!form) return;

        /* ─────── Marcar/desmarcar todos los módulos (excepto los del rol) ─────── */
        const btnModules = document.getElementById('selectAllModules');
        if (btnModules) {
            btnModules.addEventListener('click', () => {
                const checks = document.querySelectorAll('.modulo-check:not(:disabled)');
                if (!checks.length) return;
                const allChecked = Array.from(checks).every(c => c.checked);
                checks.forEach(c => c.checked = !allChecked);
                btnModules.querySelector('span').textContent = allChecked ? 'Seleccionar todos' : 'Desmarcar todos';
            });
        }

        /* ─────── Marcar/desmarcar todos los permisos ─────── */
        const btnPerms = document.getElementById('selectAllPermissions');
        if (btnPerms) {
            btnPerms.addEventListener('click', () => {
                const checks = document.querySelectorAll('.perm-check');
                if (!checks.length) return;
                const allChecked = Array.from(checks).every(c => c.checked);
                checks.forEach(c => c.checked = !allChecked);
                btnPerms.querySelector('span').textContent = allChecked ? 'Seleccionar todos' : 'Desmarcar todos';
            });
        }

        /* ─────── Al enviar: convertir el estado de cada checkbox en allow[] / deny[] ─────── */
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Limpiamos cualquier name previo y lo recalculamos
            document.querySelectorAll('.perm-check').forEach(i => i.removeAttribute('name'));

            const allow = [];
            const deny  = [];

            document.querySelectorAll('.perm-check').forEach(i => {
                const val    = i.value;
                const isRole = i.dataset.role === '1';

                if (isRole) {
                    // Rol + desmarcado → deny[]
                    if (!i.checked) {
                        deny.push(val);
                        i.name = 'deny[]';
                    }
                    // Rol + marcado → no enviamos nada (queda heredado tal cual)
                } else {
                    // No rol + marcado → allow[]
                    if (i.checked) {
                        allow.push(val);
                        i.name = 'allow[]';
                    }
                    // No rol + desmarcado → no enviamos nada
                }
            });

            // Sin cambios → enviar directo
            if (allow.length === 0 && deny.length === 0) {
                form.submit();
                return;
            }

            // Confirmación con resumen
            let html = '<div class="text-left small">';
            if (allow.length) {
                html += '<strong class="text-blue-600">Permisos adicionales (allow):</strong><ul class="mb-3" style="list-style:disc;padding-left:1.25rem;">'
                      + allow.map(a => '<li>' + a + '</li>').join('')
                      + '</ul>';
            }
            if (deny.length) {
                html += '<strong class="text-red-600">Restricciones sobre el rol (deny):</strong><ul style="list-style:disc;padding-left:1.25rem;">'
                      + deny.map(a => '<li>' + a + '</li>').join('')
                      + '</ul>';
            }
            html += '</div>';

            if (typeof Swal === 'undefined') {
                // fallback si por alguna razón no está Swal
                if (confirm('¿Aplicar cambios de permisos?')) form.submit();
                return;
            }

            Swal.fire({
                title: 'Confirmar cambios',
                html: html,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#991b1b',
                confirmButtonText: 'Aplicar',
                cancelButtonText: 'Revisar',
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    })();
</script>