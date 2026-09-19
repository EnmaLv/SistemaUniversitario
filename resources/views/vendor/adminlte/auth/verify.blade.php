@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-lg rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <h2 class="mb-4 text-center text-2xl font-bold text-slate-900 dark:text-slate-100">Verifica tu correo</h2>

        @if(session('resent'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                Se ha enviado un nuevo enlace de verificación a tu correo.
            </div>
        @endif

        <p class="text-sm text-slate-600 dark:text-slate-300">
            Revisa tu correo electrónico para completar la verificación de tu cuenta. Si no recibiste el correo, puedes solicitar otro enlace.
        </p>

        <form method="POST" action="{{ route('verification.resend') }}" class="mt-5">
            @csrf
            <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                Solicitar otro enlace
            </button>
        </form>
    </div>
@stop
