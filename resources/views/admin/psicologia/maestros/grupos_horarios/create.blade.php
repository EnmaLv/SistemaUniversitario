@extends('layouts.app')

@section('content_header')
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Crear Grupo de Horarios</h1>
    </div>
@endsection

@section('content')
    <div class="mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <form method="POST" action="{{ route('admin.psicologia.maestros.grupos_horarios.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="nombre" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Nombre del Grupo') }}</label>
                <input id="nombre" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" type="text" name="nombre" value="{{ old('nombre') }}" required autofocus autocomplete="nombre">
                @error('nombre') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <a href="{{ route('admin.psicologia.maestros.grupos_horarios.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancelar</a>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">{{ __('Crear') }}</button>
            </div>
        </form>
    </div>
@endsection
