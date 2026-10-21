@php
    $modo          = $modo ?? 'rol';
    $deshabilitado = $deshabilitado ?? false;
    $todoMarcado   = $todoMarcado ?? false;
    $seleccionados = array_map('intval', $seleccionados ?? []);
    $deRol         = array_map('intval', $deRol ?? []);
    $grants        = array_map('intval', $grants ?? []);
    $revokes       = array_map('intval', $revokes ?? []);
    $acciones      = config('permisos.acciones', []);
@endphp

<div id="matrizPermisos" data-modo="{{ $modo }}">
    <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4">
        <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5"
            style="background-color: var(--input-bg); border-color: var(--input-border);">
            <i class="fas fa-search text-gray-400 text-sm"></i>
            <input type="text" id="matrizBuscar" autocomplete="off"
                class="w-full border-0 bg-transparent text-sm outline-none" style="color: var(--text-main);"
                placeholder="Filtrar vistas por nombre o clave...">
        </div>

        @if (!$deshabilitado)
            <button type="button" id="selectAllPermissions"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-bold hover:border-red-400 hover:text-red-600 transition-all shrink-0"
                style="border-color: var(--border-color); color: var(--text-main);">
                <i class="fas fa-check-double text-[10px]"></i>
                <span>Marcar / desmarcar todo</span>
            </button>
        @endif
    </div>

    @if ($modo === 'usuario')
        <div class="flex flex-wrap items-center gap-3 mb-4 text-[11px] font-bold">
            <span class="inline-flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400">
                <span class="w-3 h-3 rounded ring-2 ring-emerald-400/70 bg-emerald-50 dark:bg-emerald-950/30"></span> Heredado del rol
            </span>
            <span class="inline-flex items-center gap-1.5 text-blue-700 dark:text-blue-400">
                <span class="w-3 h-3 rounded ring-2 ring-blue-400/70 bg-blue-50 dark:bg-blue-950/30"></span> Extra (solo este usuario)
            </span>
            <span class="inline-flex items-center gap-1.5 text-red-700 dark:text-red-400">
                <span class="w-3 h-3 rounded ring-2 ring-red-400/70 bg-red-50 dark:bg-red-950/30"></span> Bloqueado (quitado al rol)
            </span>
        </div>
    @endif

    <div class="flex flex-col gap-4 {{ $deshabilitado ? 'opacity-70' : '' }}">
        @forelse ($grupos as $nombreGrupo => $vistas)
            <div class="rounded-xl border overflow-hidden" data-grupo
                style="background-color: var(--bg-card); border-color: var(--border-color);">

                <div class="flex items-center justify-between gap-3 px-4 py-3 border-b"
                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                            <i class="fas fa-layer-group text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-wider truncate" style="color: var(--text-main);">
                                {{ $nombreGrupo }}
                            </p>
                            <p class="text-[10px] text-gray-400">{{ $vistas->count() }} vistas</p>
                        </div>
                    </div>
                    @if (!$deshabilitado)
                        <button type="button"
                            class="grupo-toggle inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-[11px] font-bold hover:border-red-400 hover:text-red-600 transition-all shrink-0"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            <i class="fas fa-check-double text-[9px]"></i> Grupo
                        </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                style="border-color: var(--border-color);">
                                <th class="px-4 py-2.5">Vista</th>
                                @foreach ($acciones as $accion => $def)
                                    <th class="px-2 py-2.5 text-center w-20">
                                        @if (!$deshabilitado)
                                            <button type="button" data-accion="{{ $accion }}"
                                                class="col-toggle inline-flex flex-col items-center gap-1 rounded-md px-1.5 py-1 hover:text-red-600 transition-colors"
                                                title="Marcar / desmarcar la columna {{ $def['label'] }} en este grupo">
                                                <i class="{{ $def['icon'] }} text-[11px]"></i>
                                                <span>{{ $def['label'] }}</span>
                                            </button>
                                        @else
                                            <span class="inline-flex flex-col items-center gap-1">
                                                <i class="{{ $def['icon'] }} text-[11px]"></i>
                                                <span>{{ $def['label'] }}</span>
                                            </span>
                                        @endif
                                    </th>
                                @endforeach
                                <th class="px-2 py-2.5 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--border-color);">
                            @foreach ($vistas as $vista)
                                @php $porAccion = $vista->permisos->keyBy('accion'); @endphp
                                <tr data-fila="{{ $vista->id_vis }}"
                                    data-buscar="{{ mb_strtolower($vista->nombre_vis . ' ' . $vista->key_vis) }}"
                                    class="hover:bg-red-50/30 dark:hover:bg-red-950/10 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <p class="text-xs font-bold" style="color: var(--text-main);">{{ $vista->nombre_vis }}</p>
                                        <p class="text-[10px] text-gray-400 font-mono">{{ $vista->key_vis }}</p>
                                    </td>
                                    @foreach ($acciones as $accion => $def)
                                        <td class="px-2 py-2.5 text-center">
                                            @if ($permiso = $porAccion->get($accion))
                                                @php
                                                    $pid = (int) $permiso->id_pmi;
                                                    if ($modo === 'usuario') {
                                                        $esDeRol = in_array($pid, $deRol, true);
                                                        $marcado = $esDeRol ? !in_array($pid, $revokes, true) : in_array($pid, $grants, true);
                                                    } else {
                                                        $esDeRol = false;
                                                        $marcado = $todoMarcado || in_array($pid, $seleccionados, true);
                                                    }
                                                @endphp
                                                <label class="perm-caja inline-flex items-center justify-center w-8 h-8 rounded-lg transition-all {{ $deshabilitado ? 'cursor-not-allowed' : 'cursor-pointer' }}"
                                                    title="{{ $permiso->nombre_pmi }}">
                                                    <input type="checkbox"
                                                        class="perm-cell w-4 h-4 rounded border-gray-300 dark:border-gray-600 accent-red-600 focus:ring-2 focus:ring-red-500/30 {{ $deshabilitado ? 'cursor-not-allowed' : 'cursor-pointer' }}"
                                                        value="{{ $pid }}"
                                                        data-fila="{{ $vista->id_vis }}"
                                                        data-accion="{{ $accion }}"
                                                        data-role="{{ $esDeRol ? '1' : '0' }}"
                                                        data-etiqueta="{{ $def['label'] }} · {{ $vista->nombre_vis }}"
                                                        @if ($modo === 'rol') name="permisos[]" @endif
                                                        @checked($marcado)
                                                        @disabled($deshabilitado)>
                                                </label>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-700 text-xs select-none" title="No aplica">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-2 py-2.5 text-center">
                                        @if (!$deshabilitado)
                                            <button type="button" data-fila="{{ $vista->id_vis }}"
                                                class="fila-toggle w-7 h-7 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                                title="Marcar / desmarcar toda la fila">
                                                <i class="fas fa-arrows-left-right text-[10px]"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="py-12 text-center text-sm text-gray-400 rounded-xl border" style="border-color: var(--border-color);">
                <i class="fas fa-exclamation-triangle text-amber-500 mr-2"></i>
                No hay vistas registradas. Ejecuta <code class="font-mono">php artisan permisos:sincronizar</code>.
            </div>
        @endforelse
    </div>
</div>

@once
<script>
    (function () {
        'use strict';

        const raiz = document.getElementById('matrizPermisos');
        if (!raiz) return;

        const esUsuario = raiz.dataset.modo === 'usuario';
        const estilos = {
            heredado:  'ring-2 ring-emerald-400/70 bg-emerald-50 dark:bg-emerald-950/30',
            extra:     'ring-2 ring-blue-400/70 bg-blue-50 dark:bg-blue-950/30',
            bloqueado: 'ring-2 ring-red-400/70 bg-red-50 dark:bg-red-950/30',
        };
        const titulos = {
            heredado:  'Heredado del rol',
            extra:     'Extra',
            bloqueado: 'Bloqueado',
            ninguno:   'Sin permiso',
        };
        const todasLasClases = Object.values(estilos).join(' ').split(' ');

        function pintar(input) {
            if (!esUsuario) return;
            const caja = input.closest('.perm-caja');
            if (!caja) return;
            const deRol = input.dataset.role === '1';
            let estado = 'ninguno';
            if (deRol) estado = input.checked ? 'heredado' : 'bloqueado';
            else if (input.checked) estado = 'extra';
            caja.classList.remove(...todasLasClases);
            if (estilos[estado]) caja.classList.add(...estilos[estado].split(' '));
            caja.title = titulos[estado];
        }

        function celdasDeFila(fila) {
            return Array.from(raiz.querySelectorAll('.perm-cell[data-fila="' + fila + '"]:not(:disabled)'));
        }

        function celdaVer(fila) {
            return raiz.querySelector('.perm-cell[data-fila="' + fila + '"][data-accion="ver"]:not(:disabled)');
        }

        function asegurarCoherencia(celda) {
            if (celda.checked && celda.dataset.accion !== 'ver') {
                const ver = celdaVer(celda.dataset.fila);
                if (ver && !ver.checked) {
                    ver.checked = true;
                    pintar(ver);
                }
            }
            if (!celda.checked && celda.dataset.accion === 'ver') {
                celdasDeFila(celda.dataset.fila).forEach(c => {
                    c.checked = false;
                    pintar(c);
                });
            }
            pintar(celda);
        }

        function alternar(celdas) {
            if (!celdas.length) return;
            const marcar = !celdas.every(c => c.checked);
            celdas.forEach(c => { c.checked = marcar; });
            celdas.forEach(asegurarCoherencia);
        }

        raiz.addEventListener('change', e => {
            if (e.target.classList.contains('perm-cell')) asegurarCoherencia(e.target);
        });

        raiz.querySelectorAll('.fila-toggle').forEach(btn => {
            btn.addEventListener('click', () => alternar(celdasDeFila(btn.dataset.fila)));
        });

        raiz.querySelectorAll('.col-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const grupo = btn.closest('[data-grupo]');
                alternar(Array.from(grupo.querySelectorAll('tr:not(.hidden) .perm-cell[data-accion="' + btn.dataset.accion + '"]:not(:disabled)')));
            });
        });

        raiz.querySelectorAll('.grupo-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                alternar(Array.from(btn.closest('[data-grupo]').querySelectorAll('tr:not(.hidden) .perm-cell:not(:disabled)')));
            });
        });

        const btnTodo = document.getElementById('selectAllPermissions');
        if (btnTodo) {
            btnTodo.addEventListener('click', () => {
                alternar(Array.from(raiz.querySelectorAll('tr:not(.hidden) .perm-cell:not(:disabled)')));
            });
        }

        const buscador = document.getElementById('matrizBuscar');
        if (buscador) {
            buscador.addEventListener('input', () => {
                const termino = buscador.value.trim().toLowerCase();
                raiz.querySelectorAll('tr[data-fila]').forEach(tr => {
                    tr.classList.toggle('hidden', termino !== '' && !tr.dataset.buscar.includes(termino));
                });
                raiz.querySelectorAll('[data-grupo]').forEach(grupo => {
                    grupo.classList.toggle('hidden', grupo.querySelectorAll('tr[data-fila]:not(.hidden)').length === 0);
                });
            });
        }

        raiz.querySelectorAll('.perm-cell').forEach(pintar);
    })();
</script>
@endonce
