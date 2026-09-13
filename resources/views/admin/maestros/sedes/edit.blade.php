@extends('layouts.app')

@section('title', 'Editar Sede')

@section('content_header')
    <div class="mb-6"><h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">Editar Sede o Anexo</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Actualiza la informacion de la sede.</p></div>
@stop

@section('content')
    <div class="rounded-2xl border p-6 shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="mb-6 flex items-center justify-between border-b pb-4" style="border-color: var(--border-color);"><div><h2 class="text-lg font-bold" style="color: var(--text-main);">Datos de la sede</h2><p class="text-sm text-gray-500 dark:text-gray-400">Modifique los datos necesarios.</p></div><a href="{{ route('admin.maestros.sedes.index') }}" class="rounded-xl border px-4 py-2 text-sm font-bold" style="border-color: var(--border-color); color: var(--text-main);"><i class="fas fa-arrow-left mr-1"></i> Volver</a></div>
        <form action="{{ route('admin.maestros.sedes.update', $sede->id) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ([['nombre','Nombre de la sede','Ingrese el nombre','fa-tag',$sede->nombre],['direccion','Direccion','Ingrese la direccion','fa-map-marker-alt',$sede->direccion],['telefono','Telefono','(123) 456-7890','fa-phone',$sede->telefono]] as [$name,$label,$placeholder,$icon,$value])
                    <div><label for="{{ $name }}" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">{{ $label }}</label><div class="flex items-center rounded-xl border" style="background-color: var(--input-bg); border-color: var(--border-color);"><span class="px-3 text-gray-400"><i class="fas {{ $icon }}"></i></span><input id="{{ $name }}" type="text" name="{{ $name }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" @if($name === 'telefono') data-inputmask="'mask': '(999) 999-9999'" data-mask @endif class="w-full rounded-xl border-0 bg-transparent px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-red-500/30" style="color: var(--text-main);"></div>@error($name)<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
                @endforeach
            </div>
            <div class="flex justify-end gap-3 border-t pt-5" style="border-color: var(--border-color);"><a href="{{ route('admin.maestros.sedes.index') }}" class="rounded-xl border px-5 py-2.5 text-sm font-bold" style="border-color: var(--border-color); color: var(--text-main);">Cancelar</a><button type="submit" class="rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900"><i class="fas fa-save mr-1"></i> Guardar</button></div>
        </form>
    </div>
@stop

@section('js')
    <script>document.querySelectorAll('[data-mask]').forEach((el) => window.jQuery && $(el).inputmask());</script>
@endsection
