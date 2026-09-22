<x-app-layout>
    @include('admin.becas.beneficios._form', [
        'titulo'    => 'Nuevo Beneficio',
        'subtitulo' => 'Registra un beneficio para que pueda ser usado en las convocatorias.',
        'action'    => route('admin.becas.beneficios.store'),
        'method'    => 'POST',
        'modelo'    => null,
    ])
</x-app-layout>