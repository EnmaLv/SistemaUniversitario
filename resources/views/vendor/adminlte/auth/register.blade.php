@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <h2 class="mb-6 text-center text-2xl font-bold text-slate-900 dark:text-slate-100">Crear cuenta</h2>

        <form action="{{ url('register') }}" method="post" class="space-y-5">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre completo</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" placeholder="Tu nombre" autofocus>
                @error('name')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" placeholder="correo@ejemplo.com">
                @error('email')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Contraseña</label>
                <input type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" placeholder="••••••••">
                @error('password')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" placeholder="Repite tu contraseña">
            </div>

            <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                Registrarse
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600 dark:text-slate-300">
            Ya tengo cuenta <a href="{{ route('login') }}" class="font-semibold text-red-600 hover:text-red-700">Iniciar sesión</a>
        </p>
    </div>
@stop
