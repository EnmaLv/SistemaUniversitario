<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Datos del empleado
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Perfil de: <span class="font-bold">{{ $usuario->username_use }}</span>
                        · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    @canRoute('admin.configuracion.empleados.edit')
                        <a href="{{ route('admin.configuracion.empleados.edit', $usuario->id_use) }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                            <i class="fas fa-edit text-xs"></i>
                            <span>Editar</span>
                        </a>
                    @endcanRoute
                    <a href="{{ route('admin.configuracion.empleados.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-arrow-left text-xs"></i> Volver
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4">
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border shadow-sm p-6 text-center">
                        <div class="flex items-center justify-center mb-4">
                            <div class="w-24 h-24 rounded-full flex items-center justify-center"
                                style="border: 3px solid var(--color-secondary);">
                                <i class="fas fa-user text-4xl" style="color: var(--color-secondary);"></i>
                            </div>
                        </div>
                        <h4 class="text-lg font-extrabold mb-1" style="color: var(--text-main);">
                            {{ optional($usuario->persona)->nombres_per }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $usuario->username_use }}
                        </p>

                        <div class="border-t pt-4" style="border-color: var(--border-color);">
                            <label class="block text-[13px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 text-left mb-2">
                                Rol asignado
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden text-left"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-shield-alt text-sm"></i>
                                </span>
                                <div class="w-full px-3 py-2.5 text-sm font-medium"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);">
                                    {{ $usuario->roles->pluck('nombre_rol')->join(', ') ?: 'Sin rol asignado' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border shadow-sm overflow-hidden">
                        <div class="border-b px-6 py-4" style="border-color: var(--border-color);">
                            <h3 class="text-sm font-black uppercase tracking-wider flex items-center gap-2"
                                style="color: var(--text-main);">
                                <i class="fas fa-info-circle" style="color: var(--color-secondary);"></i>
                                Información personal
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
                            <div>
                                <label class="block text-[13px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                    Cédula de identidad
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden opacity-70"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-id-card text-sm"></i>
                                    </span>
                                    <input type="text" value="{{ optional($usuario->persona)->cedula_per ?? '—' }}" disabled
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[13px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                    Teléfono de contacto
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden opacity-70"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-phone text-sm"></i>
                                    </span>
                                    <input type="text" value="{{ optional($usuario->persona)->telefono_per ?? '—' }}" disabled
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[13px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                    Nombres
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden opacity-70"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-user text-sm"></i>
                                    </span>
                                    <input type="text" value="{{ optional($usuario->persona)->nombres_per ?? '—' }}" disabled
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[13px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                    Apellidos
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden opacity-70"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                        style="border-color: var(--border-color);">
                                        <i class="fas fa-user text-sm"></i>
                                    </span>
                                    <input type="text" value="{{ optional($usuario->persona)->apellidos_per ?? '—' }}" disabled
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="border-t p-6"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                            <h3 class="text-sm font-black uppercase tracking-wider mb-4"
                                style="color: var(--text-main);">
                                Datos de acceso
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                        Nombre de usuario (login)
                                    </label>
                                    <div class="flex items-stretch rounded-xl border overflow-hidden opacity-70"
                                        style="border-color: var(--border-color);">
                                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                            style="border-color: var(--border-color);">
                                            <i class="fas fa-at text-sm"></i>
                                        </span>
                                        <input type="text" value="{{ $usuario->username_use }}" disabled
                                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[13px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                        Fecha de registro
                                    </label>
                                    <div class="flex items-stretch rounded-xl border overflow-hidden opacity-70"
                                        style="border-color: var(--border-color);">
                                        <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                            style="border-color: var(--border-color);">
                                            <i class="fas fa-calendar-alt text-sm"></i>
                                        </span>
                                        <input type="text"
                                            value="{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : '—' }}"
                                            disabled
                                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>