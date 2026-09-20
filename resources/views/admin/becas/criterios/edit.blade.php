<x-app-layout>
    @include('admin.becas.criterios._form', [
        'titulo'    => 'Editar Criterio',
        'subtitulo' => 'Actualiza la regla. Los cambios aplican a futuras convocatorias.',
        'action'    => route('admin.becas.criterios.update', $criterio->id),
        'method'    => 'PUT',
        'modelo'    => $criterio,
    ])
</x-app-layout>