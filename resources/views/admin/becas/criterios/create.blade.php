<x-app-layout>
    @include('admin.becas.criterios._form', [
        'titulo'    => 'Nuevo Criterio',
        'subtitulo' => 'Define una regla base para las preguntas de un beneficio.',
        'action'    => route('admin.becas.criterios.store'),
        'method'    => 'POST',
        'modelo'    => null,
    ])
</x-app-layout>