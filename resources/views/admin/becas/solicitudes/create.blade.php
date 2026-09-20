<x-app-layout>
    @include('admin.becas.solicitudes._form', [
        'action' => route('admin.becas.solicitudes.store'),
        'modo'   => 'admin',
    ])
</x-app-layout>