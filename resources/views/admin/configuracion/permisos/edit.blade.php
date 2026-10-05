<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- Encabezado --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Gestionar permisos especiales
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-user-shield mr-1 text-red-600"></i>
                        Usuario: <strong>{{ $usuario->username }}</strong>
                        · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.configuracion.permisos.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver
                </a>
            </div>

            {{-- Formulario compartido --}}
            @include('admin.configuracion.permisos._form', [
                'action'       => route('admin.configuracion.permisos.update', $usuario->id_usuario),
                'metodo'       => 'PUT',
                'usuario'      => $usuario,
                'grupos'       => $grupos,
                'allow'        => $allow,
                'deny'         => $deny,
                'rolePerms'    => $rolePerms,
                'modulos'      => $modulos,
                'roleModules'  => $roleModules,
                'modulosExtra' => $modulosExtra,
            ])

        </div>
    </div>
</x-app-layout>