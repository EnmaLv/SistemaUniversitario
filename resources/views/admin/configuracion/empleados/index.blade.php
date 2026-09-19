<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] px-4 pb-12 pt-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @include('components.alert')

            @php
                $user = auth()->user();
                $isAdmin = $user && (strtolower($user->role ?? '') === 'administrador' ||
                    ($user->roles && $user->roles->pluck('nombre')->contains('Administrador')));
            @endphp

            <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">
                        Gestión de empleados
                    </h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                        Directorio de personal ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                @if ($isAdmin)
                    <a href="{{ url('/register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                        <i class="fas fa-user-plus text-xs"></i> Registrar empleado
                    </a>
                @endif
            </div>

            <div class="mb-3 flex flex-col gap-4 rounded-2xl border p-2.5 shadow-sm lg:flex-row lg:items-center"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <form action="{{ route('admin.configuracion.empleados.index') }}" method="GET"
                    class="flex w-full flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="relative w-full lg:flex-1">
                        <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar empleado o usuario..."
                            class="w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm font-medium outline-none transition-all focus:border-red-600 focus:ring-2 focus:ring-red-600/20"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <select name="rol"
                            class="rounded-xl border px-4 py-2.5 text-sm font-medium outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600/20"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
                            <option value="">Todos los roles</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->id_rol }}" {{ request('rol') == $r->id_rol ? 'selected' : '' }}>
                                    {{ $r->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-md transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        @if (request()->filled('rol') || request()->filled('search'))
                            <a href="{{ route('admin.configuracion.empleados.index') }}"
                                class="inline-flex items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-600 hover:text-red-600"
                                style="border-color: var(--border-color); color: var(--text-main);" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border shadow-sm"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full min-w-[900px] border-collapse text-left">
                        <thead>
                            <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
                                <th class="px-6 py-4 text-center" style="width:80px;">#</th>
                                <th class="px-6 py-4 text-center">Información del empleado</th>
                                <th class="px-6 py-4 text-center">Usuario del sistema</th>
                                <th class="px-6 py-4 text-center">Rol / perfil</th>
                                <th class="px-6 py-4 text-center" style="width:120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-medium">
                            @forelse ($usuarios as $usuario)
                                @php
                                    $isSelf = auth()->id() == $usuario->id_usuario;
                                @endphp
                                <x-table-row :id="$usuario->id_usuario" class="border-b"
                                    style="border-color: var(--border-color);">
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ ($usuarios->currentPage() - 1) * $usuarios->perPage() + $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300">
                                                <i class="fas fa-user text-sm"></i>
                                            </span>
                                            <span class="font-bold" style="color: var(--text-main);">
                                                {{ optional($usuario->persona)->nombre_persona ?? 'N/A' }}
                                                {{ optional($usuario->persona)->apellido_persona ?? '' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="rounded-lg border px-3 py-1 text-xs font-bold"
                                            style="border-color: var(--border-color); color: var(--text-main);">
                                            {{ $usuario->username }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $usuario->roles->pluck('nombre')->first() ?: ($usuario->perfil->nombre_perfil ?? 'Sin perfil') }}
                                    </td>
                                    <x-table-actions :id="$usuario->id_usuario"
                                        baseUrl="admin/configuracion/empleados" :show="false" :edit="false"
                                        :toggle="false">
                                        <a href="{{ route('admin.configuracion.empleados.show', $usuario->id_usuario) }}"
                                            onclick="event.stopPropagation()"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-sky-100 hover:text-sky-500 dark:hover:bg-sky-950/50"
                                            title="Ver perfil"><i class="fas fa-eye text-xs"></i></a>
                                        <a href="{{ route('admin.configuracion.empleados.edit', $usuario->id_usuario) }}"
                                            onclick="event.stopPropagation()"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-amber-100 hover:text-amber-500 dark:hover:bg-amber-950/50"
                                            title="Editar"><i class="fas fa-edit text-xs"></i></a>
                                            @if (!$isSelf)
                                                <form action="{{ route('admin.configuracion.empleados.destroy', $usuario->id_usuario) }}"
                                                    method="POST" class="inline" onclick="event.stopPropagation()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-950/40"
                                                        onclick="return confirm('¿Eliminar empleado?')" title="Eliminar">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-300 dark:text-gray-700" title="No puedes eliminarte">
                                                    <i class="fas fa-ban text-xs"></i>
                                                </span>
                                            @endif
                                    </x-table-actions>
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-users-slash mb-3 block text-3xl text-gray-300 dark:text-gray-700"></i>
                                        No se encontraron empleados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($usuarios->hasPages())
                    <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
                        {{ $usuarios->onEachSide(1)->appends(request()->query())->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
