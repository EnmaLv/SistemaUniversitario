<x-app-layout>
    <div class="pt-4 pb-6">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a horarios
            </a>
            <h1 class="text-2xl font-extrabold my-2" style="color:var(--text-main);">Crear horario deportivo</h1>

            @include('components.alert')

            @include('admin.deportes.maestros.horarios.form', [
                'calendarios' => $calendarios,
                'actividades' => $actividades,
                'espacios' => $espacios,
                'horariosActivos' => $horariosActivos ?? [],
            ])
        </div>
    </div>
</x-app-layout>
