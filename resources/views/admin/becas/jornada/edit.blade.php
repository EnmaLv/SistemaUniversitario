<x-app-layout>
    @include('admin.becas.jornada._form', [
        'titulo'    => 'Editar Jornada',
        'subtitulo' => 'Modifica la convocatoria. Los criterios solo se pueden ajustar antes de que la jornada inicie.',
        'action'    => route('admin.becas.jornada.update', $jornada->id),
        'method'    => 'PUT',
        'modelo'    => $jornada,
        'criteriosActuales' => $criteriosActuales,
    ])
</x-app-layout>