@php $themeColor = 'green'; @endphp
<x-app-layout>
    <div class="pt-4 pb-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="text-sm">← Volver</a>
            <h1 class="text-2xl font-bold my-2">Crear Horario - Deportes</h1>

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
