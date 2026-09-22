<x-app-layout>
    @include('admin.becas.beneficios._form', [
        'titulo'    => 'Editar Beneficio',
        'subtitulo' => 'Modifica los datos del beneficio. Los cupones se gestionan automáticamente al aprobar solicitudes.',
        'action'    => route('admin.becas.beneficios.update', $beneficio),
        'method'    => 'PUT',
        'modelo'    => $beneficio,
    ])
</x-app-layout>