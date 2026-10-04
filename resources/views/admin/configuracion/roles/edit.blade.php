<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- Encabezado --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Editar rol: {{ $rol->nombre }}
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-user-shield mr-1 text-red-600"></i>
                        Modifica los accesos y la descripción del perfil.
                    </p>
                </div>
                <a href="{{ route('admin.configuracion.roles.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver
                </a>
            </div>

            {{-- Formulario compartido --}}
            @include('admin.configuracion.roles._form', [
                'action'      => route('admin.configuracion.roles.update', $rol->id_rol),
                'metodo'      => 'PUT',
                'rutaVolver'  => route('admin.configuracion.roles.index'),
                'menu'        => $menu,
                'modulos'     => $modulos,
                'rol'         => $rol,
                'isProtected' => $isProtected ?? false,
            ])

        </div>
    </div>
</x-app-layout>