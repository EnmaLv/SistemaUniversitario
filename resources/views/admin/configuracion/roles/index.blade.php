<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Roles</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-user-tag mr-1" style="color:var(--color-primary)"></i>
                        Definición de permisos y accesos · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.configuracion.roles.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-plus text-xs"></i> Crear rol
                </a>
            </div>

    <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div class="p-3 border-bottom" style="border-color:var(--border-color);">
            <form action="{{ route('admin.configuracion.roles.index') }}" method="GET">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="flex-1">
                        <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Listado de roles</h3>
                    </div>
                    <div class="flex w-full gap-2 lg:w-auto">
                        <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-96" style="background-color:var(--input-bg);border-color:var(--input-border);">
                            <i class="fas fa-search text-gray-400"></i>
                            <input type="text" name="q" value="{{ request('q') }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Buscar por nombre o descripción...">
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
                    <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                        <th class="px-6 py-4 text-center">#</th>
                        <th class="px-6 py-4">Nombre del rol</th>
                        <th class="px-6 py-4">Descripción</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="fade-in">
                    @forelse($roles as $rol)
                        <x-table-row :id="$rol->id_rol" class="border-b" style="border-color:var(--border-color);">
                            <td class="px-6 py-4 text-center font-bold" style="color:var(--text-muted);">
                                {{ ($roles->currentPage()-1)*$roles->perPage()+$loop->iteration }}
                            </td>
                            <td class="px-6 py-4 font-bold" style="color:var(--text-main);">
                                <span class="inline-flex items-center gap-1 rounded-lg border px-3 py-1 text-xs font-extrabold uppercase tracking-wide" style="border-color:var(--border-color);color:var(--color-primary);">
                                    <i class="fas fa-user-tag"></i>
                                    {{ strtoupper($rol->nombre) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span style="color:var(--text-muted);" title="{{ $rol->descripcion }}">
                                    {{ \Illuminate\Support\Str::limit($rol->descripcion, 80) ?? 'Sin descripción' }}
                                </span>
                            </td>
                            @php
                                $protected = ['Empleado', 'Obrero', 'Administrador'];
                                $isProtected = in_array(strtolower($rol->nombre ?? ''), array_map('strtolower', $protected));
                            @endphp
                            @if (!$isProtected)
                                <x-table-actions :id="$rol->id_rol" baseUrl="admin/configuracion/roles" :show="false" :toggle="false">
                                    <form action="{{ route('admin.configuracion.roles.destroy', $rol->id_rol) }}"
                                        method="POST" class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-950/50 transition-colors btn-delete"
                                            title="Eliminar rol">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </x-table-actions>
                            @else
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-xs font-bold text-gray-500 dark:text-gray-400" style="border-color:var(--border-color);">
                                        <i class="fas fa-lock"></i> Protegido
                                    </span>
                                </td>
                            @endif
                        </x-table-row>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                <div>
                                    <i class="fas fa-shield-alt fa-3x mb-3" style="opacity: 0.1"></i>
                                    <p>No se encontraron roles registrados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-top flex justify-content-center" style="border-color:var(--border-color);">
            {{ $roles->appends(request()->query())->links('components.pagination') }}
        </div>
    </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        // Integración de SweetAlert2 para la eliminación
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(){
                Swal.fire({
                    title: '¿Eliminar Rol?',
                    text: "Esto podría afectar el acceso de los usuarios vinculados.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--color-primary)',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.closest('form').submit();
                    }
                })
            });
        });
    });
</script>
</x-app-layout>
