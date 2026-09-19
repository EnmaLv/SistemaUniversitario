@extends('layouts.app')

@section('title', 'Editar Proveedor')

@section('content_header')
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">Editar proveedor</h1>
            <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('admin.maestros.proveedores.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition hover:border-red-600 hover:text-red-600"
            style="border-color: var(--border-color); color: var(--text-main);">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @include('components.alert')
    <div class="rounded-2xl border p-6 shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="mb-6 border-b pb-4" style="border-color: var(--border-color);">
            <h2 class="text-lg font-bold" style="color: var(--text-main);">Datos del proveedor</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Modifica los datos necesarios.</p>
        </div>
        <form action="{{ route('admin.maestros.proveedores.update', $proveedor->id) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ([['empresa','Empresa','Nombre de la empresa','fa-building',$proveedor->empresa],['direccion','Direccion','Direccion completa','fa-map-marker-alt',$proveedor->direccion],['nombre','Nombre del proveedor','Nombre de contacto','fa-user',$proveedor->nombre],['telefono','Telefono','(123) 456-7890','fa-phone',$proveedor->telefono],['email','Email','correo@empresa.com','fa-envelope',$proveedor->email]] as [$name,$label,$placeholder,$icon,$value])
                    <div><label for="{{ $name }}" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">{{ $label }}</label><div class="flex items-center rounded-xl border" style="background-color: var(--input-bg); border-color: var(--border-color);"><span class="px-3 text-gray-400"><i class="fas {{ $icon }}"></i></span><input id="{{ $name }}" type="{{ $name === 'email' ? 'email' : 'text' }}" name="{{ $name }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" @if($name === 'telefono') data-inputmask="'mask': '(999) 999-9999'" data-mask @endif class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-red-500/30" style="color: var(--text-main);"></div>@error($name)<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
                @endforeach
            </div>
            <div class="flex justify-end gap-3 border-t pt-5" style="border-color: var(--border-color);"><a href="{{ route('admin.maestros.proveedores.index') }}" class="rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color: var(--border-color); color: var(--text-main);">Cancelar</a><button type="submit" class="rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900"><i class="fas fa-save mr-1"></i> Guardar</button></div>
        </form>
    </div>
@stop

@section('js')
    <script>document.querySelectorAll('[data-mask]').forEach((el) => window.jQuery && $(el).inputmask());</script>
@endsection
