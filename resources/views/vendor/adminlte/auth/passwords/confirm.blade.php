@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl">
        <div class="mb-6 text-center">
            <div class="mb-3 flex justify-center">
                <i class="fas fa-shield-alt text-4xl text-slate-500"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">Confirmar contraseña</h2>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
                <div class="flex items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20">
                    <span class="px-3 text-slate-500"><i class="fas fa-lock"></i></span>
                    <input id="password" type="password" name="password" class="w-full border-0 bg-transparent px-0 py-2.5 text-sm text-slate-700 outline-none @error('password') border-red-300 @enderror" placeholder="Tu contraseña" required autofocus>
                    <button type="submit" class="border-l border-slate-200 bg-white px-3 py-2.5 text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
                @error('password')
                    <div class="mt-2 text-sm font-medium text-red-600" role="alert"><b>{{ $message }}</b></div>
                @enderror
            </div>

            <p class="text-center text-sm text-slate-600">Confirma tu contraseña para continuar.</p>

            <div class="text-center">
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-red-600 hover:text-red-700">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>
@stop
