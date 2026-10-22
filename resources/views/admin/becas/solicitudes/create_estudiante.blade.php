<x-app-layout>
    @if (! empty($soloEstudiantes))
        <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm p-8 text-center">
                    <span class="mx-auto mb-4 w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-950/30 flex items-center justify-center text-red-700 dark:text-red-400">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Postulación no disponible
                    </h1>
                    <p class="mt-2 text-sm" style="color: var(--text-muted);">
                        Las solicitudes de beca solo están disponibles para personas con perfil Estudiante.
                        Si crees que tu perfil es incorrecto, comunícate con la Secretaría de Bienestar.
                    </p>
                    <a href="{{ route('home') }}"
                        class="mt-6 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-home text-xs"></i> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    @else
        @include('admin.becas.solicitudes._form', [
            'action' => route('admin.becas.solicitudes.store'),
            'modo'   => 'estudiante',
        ])
    @endif
</x-app-layout>
