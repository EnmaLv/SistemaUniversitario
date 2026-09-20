@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-xl rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <h2 class="mb-4 text-2xl font-bold text-slate-900 dark:text-slate-100">Verifica tu correo</h2>

        @if(session('resent'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                Se ha enviado un nuevo enlace de verificación.
            </div>
        @endif

        <p class="text-sm text-slate-600 dark:text-slate-300">
            Antes de continuar, revisa tu correo electrónico para obtener el enlace de verificación.
        </p>

        <div class="mt-5 text-sm text-slate-600 dark:text-slate-300">
            Si no recibiste el correo, puedes solicitar otro.
        </div>

        <form method="POST" action="{{ route('verification.resend') }}" class="mt-4">
            @csrf
            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">
                Solicitar otro enlace
            </button>
        </form>
    </div>
@stop