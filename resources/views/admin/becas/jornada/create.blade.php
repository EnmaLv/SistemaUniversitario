<x-app-layout>
    @include('admin.becas.jornada._form', [
        'titulo'    => 'Nueva Jornada',
        'subtitulo' => 'Crea una convocatoria para un beneficio. Los criterios se heredan del beneficio.',
        'action'    => route('admin.becas.jornada.store'),
        'method'    => 'POST',
        'modelo'    => null,
        'criteriosActuales' => [],
    ])
</x-app-layout>