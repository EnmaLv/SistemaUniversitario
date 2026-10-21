<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="rounded-2xl border shadow-sm p-6 sm:p-8">

    <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit" id="permisosForm">
        @csrf
        @if (($metodo ?? 'POST') !== 'POST')
            @method($metodo)
        @endif

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
                    {{ $usuario->username }}
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

        @if ($esSuperAdmin)
            <div class="mb-8 p-4 rounded-xl border border-sky-200 dark:border-sky-800/60 bg-sky-50 dark:bg-sky-950/30 flex items-start gap-3">
                <i class="fas fa-info-circle text-sky-600 dark:text-sky-400 mt-0.5"></i>
                <div class="text-sm text-sky-800 dark:text-sky-300">
                    Este usuario tiene un rol con <strong>acceso total</strong>. Las excepciones que guardes aquí no lo limitan mientras conserve ese rol.
                </div>
            </div>
        @endif

        <div class="mb-8">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <i class="fas fa-cubes text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold" style="color: var(--text-main);">
                        Módulos especiales
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Los módulos <span class="font-bold text-emerald-600 dark:text-emerald-400">heredados del rol</span> están bloqueados. Marca otros para que el usuario pueda entrar a ellos.
                    </p>
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
                                    @checked($checked)
                                    @disabled($fromRole)>
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

        <div class="mb-8">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold" style="color: var(--text-main);">
                        Vigencia de las excepciones
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Opcional. Se aplica a todos los permisos extra y bloqueados de este usuario. Vacío = permanente.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl">
                <div>
                    <label for="fecha_inicio" class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Desde</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio"
                        value="{{ old('fecha_inicio', $vigencia['fecha_inicio'] ?? '') }}"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30"
                        style="background-color: var(--input-bg); border-color: var(--input-border); color: var(--text-main);">
                    @error('fecha_inicio')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="fecha_fin" class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Hasta</label>
                    <input type="date" id="fecha_fin" name="fecha_fin"
                        value="{{ old('fecha_fin', $vigencia['fecha_fin'] ?? '') }}"
                        class="w-full rounded-xl border px-3 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30"
                        style="background-color: var(--input-bg); border-color: var(--input-border); color: var(--text-main);">
                    @error('fecha_fin')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
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
                        Los cambios aquí <span class="font-bold">sobrescriben</span> al rol. Marcar algo que el rol no tiene = <span class="font-bold text-blue-600 dark:text-blue-400">Extra</span>. Desmarcar algo del rol = <span class="font-bold text-red-600 dark:text-red-400">Bloqueado</span>.
                    </p>
                </div>
            </div>

            @include('admin.configuracion.partials.matriz-permisos', [
                'grupos'  => $grupos,
                'modo'    => 'usuario',
                'deRol'   => $deRol,
                'grants'  => $grants,
                'revokes' => $revokes,
            ])

            <div id="permisosOcultos"></div>

            @error('grant')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
            @error('revoke')
                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
        </div>

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
        const contenedor = document.getElementById('permisosOcultos');
        if (!form || !contenedor) return;

        function escapar(texto) {
            const div = document.createElement('div');
            div.textContent = texto;
            return div.innerHTML;
        }

        function agregarOculto(nombre, valor) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = nombre;
            input.value = valor;
            contenedor.appendChild(input);
        }

        function lista(titulo, clase, elementos) {
            if (!elementos.length) return '';
            const items = elementos.slice(0, 15).map(t => '<li>' + escapar(t) + '</li>').join('');
            const resto = elementos.length > 15 ? '<li>… y ' + (elementos.length - 15) + ' más</li>' : '';
            return '<p class="font-bold ' + clase + '">' + titulo + ' (' + elementos.length + ')</p>'
                + '<ul style="list-style:disc;padding-left:1.25rem;margin-bottom:.75rem;">' + items + resto + '</ul>';
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            contenedor.innerHTML = '';

            const extra = [];
            const bloqueado = [];

            document.querySelectorAll('#matrizPermisos .perm-cell').forEach(celda => {
                const deRol = celda.dataset.role === '1';
                if (deRol && !celda.checked) {
                    agregarOculto('revoke[]', celda.value);
                    bloqueado.push(celda.dataset.etiqueta);
                } else if (!deRol && celda.checked) {
                    agregarOculto('grant[]', celda.value);
                    extra.push(celda.dataset.etiqueta);
                }
            });

            if (!extra.length && !bloqueado.length) {
                form.submit();
                return;
            }

            const html = '<div style="text-align:left;font-size:.85rem;">'
                + lista('Permisos extra', 'text-blue-600', extra)
                + lista('Permisos bloqueados', 'text-red-600', bloqueado)
                + '</div>';

            if (typeof Swal === 'undefined') {
                if (confirm('Se guardarán ' + extra.length + ' permisos extra y ' + bloqueado.length + ' bloqueados. ¿Continuar?')) {
                    form.submit();
                }
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
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    })();
</script>
