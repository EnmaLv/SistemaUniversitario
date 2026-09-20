<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Permisos especiales</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-user-shield mr-1" style="color:var(--color-primary)"></i>
                        Asignación granular por usuario · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold text-gray-500 dark:text-gray-400" style="border-color:var(--border-color);">
                    <i class="fas fa-info-circle" style="color:var(--color-primary);"></i>
                    Los permisos especiales se suman al rol base
                </span>
            </div>

    <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div class="p-3 border-bottom" style="border-color:var(--border-color);">
            <form action="{{ route('admin.configuracion.permisos.index') }}" method="GET">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="flex-1"><h3 class="text-lg font-extrabold" style="color:var(--text-main);">Usuarios del sistema</h3></div>
                    <div class="flex w-full gap-2 lg:w-auto">
                        <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-96" style="background-color:var(--input-bg);border-color:var(--input-border);">
                            <i class="fas fa-search text-gray-400"></i>
                            <input type="text" name="q" value="{{ request('q') }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Buscar por nombre o usuario...">
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                            <i class="fas fa-search text-xs"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-black/10" style="border-bottom:1px solid var(--border-color);">
                        <th class="px-6 py-4 text-center text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:60px">#</th>
                        <th class="px-6 py-4 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400">Identificación</th>
                        <th class="px-6 py-4 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400">Nombre y apellido</th>
                        <th class="px-6 py-4 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400">Rol asignado</th>
                        <th class="px-6 py-4 text-center text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:180px">Gestión</th>
                    </tr>
                </thead>
                <tbody class="fade-in">
                    @forelse($usuarios as $usuario)
                        <x-table-row :id="$usuario->id_usuario" class="border-b" style="border-color:var(--border-color);">
                            <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 font-bold">
                                {{ $loop->iteration + ($usuarios->currentPage()-1)* $usuarios->perPage() }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg border px-3 py-1 font-mono text-xs font-bold" style="border-color:var(--border-color);color:var(--color-primary);">
                                    {{ $usuario->username }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold" style="color:var(--text-main);">
                                {{$usuario->persona->nombre_persona . ' ' . $usuario->persona->apellido_persona ?? "”" }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $rolNombre = $usuario->roles->pluck('nombre')->first() ?: ($usuario->perfil->nombre_perfil ?? 'Usuario Base');
                                @endphp
                                <span class="inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400" style="border-color:var(--border-color);">
                                    <i class="fas fa-briefcase "></i> {{ $rolNombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $auth = auth()->user();
                                    $isSelfAdmin = $auth && $auth->id_usuario == $usuario->id_usuario && $auth->roles->contains('nombre', 'Administrador');
                                @endphp
                                
                                @if(!$isSelfAdmin)
                                    <x-table-actions :id="$usuario->id_usuario" baseUrl="admin/configuracion/permisos" :show="false" :toggle="false" />
                                @else
                                    <span class="text-gray-500 dark:text-gray-400 text-xs" title="El administrador principal no puede editar sus propios permisos granulares">
                                        <i class="fas fa-lock mr-1"></i> Protegido
                                    </span>
                                @endif
                            </td>
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                <div>
                                    <i class="fas fa-users-slash fa-3x mb-3" style="opacity: 0.1"></i>
                                    <p>No se encontraron usuarios para la búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-top flex justify-content-center" style="border-color:var(--border-color);">
            {{ $usuarios->appends(request()->query())->onEachSide(1)->links('components.pagination') }}
        </div>
    </div>
        </div>
    </div>
<style>
    /* Estilo de fila al pasar el mouse */
    table tbody tr:hover {
        background-color: color-mix(in srgb, var(--color-primary) 6%, transparent);
        transition: var(--trans-default);
    }

    /* Quitar bordes de foco azules/morados solicitados */
    .rd-input:focus, .rd-btn:focus {
        outline: none !important;
        box-shadow: none !important;
    }
</style>
</x-app-layout>
