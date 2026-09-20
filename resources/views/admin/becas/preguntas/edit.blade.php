<x-app-layout>
    @include('admin.becas.preguntas._form', [
        'titulo'    => 'Editar Pregunta',
        'subtitulo' => 'Actualiza la pregunta y sus reglas. Los cambios afectan a las nuevas solicitudes.',
        'action'    => route('admin.becas.preguntas.update', $pregunta->id),
        'method'    => 'PUT',
        'modelo'    => $pregunta,
    ])
</x-app-layout>