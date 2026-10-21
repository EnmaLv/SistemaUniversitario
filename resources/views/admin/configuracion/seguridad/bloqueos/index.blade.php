<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Bloqueos de seguridad</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-user-lock mr-1" style="color:var(--color-primary)"></i>
                        Cuentas con bloqueo temporal o permanente · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-3 border-b" style="border-color:var(--border-color);">
                    <form action="{{ route('admin.configuracion.seguridad.bloqueos.index') }}" method="GET">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Usuarios bloqueados</h3>
                            </div>
                            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                                <select name="tipo" class="rounded-xl border px-3 py-2.5 text-sm" style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="">Todos los bloqueos</option>
                                    <option value="temporal" @selected($tipo === 'temporal')>Temporales</option>
                                    <option value="permanente" @selected($tipo === 'permanente')>Permanentes</option>
                                </select>
                                <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-80" style="background-color:var(--input-bg);border-color:var(--input-border);">
                                    <i class="fas fa-search text-gray-400"></i>
                                    <input type="text" name="q" value="{{ $busqueda }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Buscar por usuario o nombre...">
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-red-900">
                                    <i class="fas fa-search text-xs"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-4 text-center">#</th>
                                <th class="px-6 py-4">Usuario</th>
                                <th class="px-6 py-4 text-center">Tipo</th>
                                <th class="px-6 py-4">Motivo</th>
                                <th class="px-6 py-4">Bloqueado el</th>
                                <th class="px-6 py-4 text-center">Intentos</th>
                                <th class="px-6 py-4">Último login</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="fade-in">
                            @forelse ($usuarios as $usuario)
                                @php
                                    $permanente = (bool) $usuario->bloqueado;
                                    $esPropio = (int) $usuario->id_usuario === (int) auth()->id();
                                @endphp
                                <x-table-row :id="$usuario->id_usuario" class="border-b text-sm" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 text-center font-bold" style="color:var(--text-muted);">
                                        {{ ($usuarios->currentPage() - 1) * $usuarios->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4" style="color:var(--text-main);">
                                        <span class="block font-bold">{{ $usuario->username }}</span>
                                        <span class="block text-xs" style="color:var(--text-muted);">{{ $usuario->nombre_completo }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($permanente)
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-950/50 dark:text-red-300">
                                                <i class="fas fa-ban"></i> Permanente
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                                                <i class="fas fa-hourglass-half"></i> Temporal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4" style="color:var(--text-main);">
                                        {{ $motivos[$usuario->motivo_bloqueo] ?? ($usuario->motivo_bloqueo ?? '—') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="color:var(--text-main);">
                                        {{ $usuario->bloqueado_en?->format('d/m/Y H:i') ?? '—' }}
                                        @if (! $permanente && $usuario->bloqueado_hasta)
                                            <span class="block text-xs" style="color:var(--text-muted);">hasta {{ $usuario->bloqueado_hasta->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-xs" style="color:var(--text-main);">
                                        <span class="block" title="Contraseñas incorrectas seguidas">Contraseña: <strong>{{ $usuario->intentos_fallidos }}</strong></span>
                                        <span class="block" title="Llaves maestras incorrectas seguidas">Llave: <strong>{{ $usuario->intentos_llave_fallidos }}</strong></span>
                                        <span class="block" title="Bloqueos temporales en la ventana actual">Bloqueos: <strong>{{ $usuario->bloqueos_temporales }}</strong></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs" style="color:var(--text-main);">
                                        {{ $usuario->ultimo_login?->format('d/m/Y H:i') ?? 'Nunca' }}
                                        @if ($usuario->ultimo_login_ip)
                                            <span class="block" style="color:var(--text-muted);">{{ $usuario->ultimo_login_ip }}</span>
                                        @endif
                                    </td>
                                    @if ($esPropio)
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-xs font-bold text-gray-500 dark:text-gray-400" style="border-color:var(--border-color);">
                                                <i class="fas fa-lock"></i> Tu cuenta
                                            </span>
                                        </td>
                                    @else
                                        <x-table-actions :id="$usuario->id_usuario" :show="false" :edit="false" :toggle="false">
                                            @canAction('seguridad_bloqueos', 'activar')
                                                <form action="{{ route('admin.configuracion.seguridad.bloqueos.desbloquear', $usuario->id_usuario) }}" method="POST" class="inline form-desbloquear" data-usuario="{{ $usuario->username }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" onclick="event.stopPropagation()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition-colors" title="Desbloquear">
                                                        <i class="fas fa-unlock text-xs"></i>
                                                    </button>
                                                </form>
                                            @endcanAction
                                        </x-table-actions>
                                    @endif
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-shield-alt fa-3x mb-3" style="opacity: 0.1"></i>
                                        <p>No hay usuarios bloqueados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t flex justify-center" style="border-color:var(--border-color);">
                    {{ $usuarios->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.form-desbloquear').forEach(function (formulario) {
                formulario.addEventListener('submit', function (evento) {
                    evento.preventDefault();
                    const texto = 'Se reiniciarán los contadores de intentos de ' + formulario.dataset.usuario + ' y podrá volver a iniciar sesión.';

                    if (typeof Swal === 'undefined') {
                        if (confirm('¿Desbloquear usuario? ' + texto)) {
                            formulario.submit();
                        }
                        return;
                    }

                    Swal.fire({
                        title: '¿Desbloquear usuario?',
                        text: texto,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: 'var(--color-primary)',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, desbloquear',
                        cancelButtonText: 'Cancelar'
                    }).then(function (resultado) {
                        if (resultado.isConfirmed) {
                            formulario.submit();
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>
