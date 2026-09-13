@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <h2 class="mb-6 text-center text-2xl font-bold text-slate-900 dark:text-slate-100">Restablecer contraseña</h2>

        @if(session('status'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="post" class="space-y-5">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Correo</label>
                <input type="email" name="email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" value="{{ old('email') }}" placeholder="correo@ejemplo.com" autofocus>
                @error('email')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                Enviar enlace de recuperación
            </button>
        </form>
    </div>
@stop
