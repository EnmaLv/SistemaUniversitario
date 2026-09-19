@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <h2 class="mb-6 text-center text-2xl font-bold text-slate-900 dark:text-slate-100">Iniciar Sesión</h2>

        <form action="{{ route('login') }}" method="post" class="space-y-5">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Correo</label>
                <input type="email" name="email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" value="{{ old('email') }}" placeholder="correo@ejemplo.com" autofocus>
                @error('email')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Contraseña</label>
                <input type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" placeholder="Contraseña">
                @error('password')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                    Recordarme
                </label>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                    Ingresar
                </button>
            </div>
        </form>

        <div class="mt-6 space-y-2 text-center text-sm">
            <p><a href="{{ route('password.request') }}" class="text-red-600 hover:text-red-700">Olvidé mi contraseña</a></p>
            <p><a href="{{ route('register') }}" class="text-red-600 hover:text-red-700">Registrar un nuevo usuario</a></p>
        </div>
    </div>
@stop
