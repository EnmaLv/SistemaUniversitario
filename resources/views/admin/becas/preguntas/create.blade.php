<x-app-layout>
    @include('admin.becas.preguntas._form', [
        'titulo'    => 'Nueva Pregunta',
        'subtitulo' => 'Define la pregunta y sus reglas para el formulario de un beneficio.',
        'action'    => route('admin.becas.preguntas.store'),
        'method'    => 'POST',
        'modelo'    => null,
    ])
</x-app-layout>