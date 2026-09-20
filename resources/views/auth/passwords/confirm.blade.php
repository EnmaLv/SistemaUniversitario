@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <h2 class="mb-6 text-center text-2xl font-bold text-slate-900 dark:text-slate-100">Confirmar contraseña</h2>
        <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">Por favor confirma tu contraseña antes de continuar.</p>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Contraseña</label>
                <input id="password" type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" placeholder="Contraseña" required autofocus>
                @error('password')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                Confirmar
            </button>
        </form>
    </div>
@stop