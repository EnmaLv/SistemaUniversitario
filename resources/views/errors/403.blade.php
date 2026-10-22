@php
    $restantes = $intentosRestantes ?? null;
    $mensaje = isset($exception) && $exception->getMessage() !== '' ? $exception->getMessage() : null;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso restringido - UPTP J.J. Montilla</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900 font-sans antialiased">
    <main class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-xl rounded-3xl bg-white p-8 text-center shadow-2xl sm:p-12 dark:bg-neutral-900">
            <img src="{{ asset('img/Logo.webp') }}" alt="Logo UPTP" class="mx-auto mb-6 h-20 w-20 rounded-full object-contain">

            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/50">
                <i class="fas fa-lock text-3xl text-red-800 dark:text-red-400"></i>
            </div>

            <p class="text-7xl font-black tracking-tight text-red-800 sm:text-8xl dark:text-red-500">403</p>
            <h1 class="mt-2 text-2xl font-extrabold text-neutral-900 sm:text-3xl dark:text-white">Página restringida</h1>
            <p class="mt-3 text-base text-neutral-600 dark:text-neutral-300">
                No tienes los permisos suficientes para ingresar a esta sección.
            </p>

            @if ($mensaje)
                <p class="mt-2 text-sm font-semibold text-neutral-500 dark:text-neutral-400">{{ $mensaje }}</p>
            @endif

            @if (! is_null($restantes))
                <div role="alert" class="mt-6 flex items-start gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-left dark:border-amber-700 dark:bg-amber-950/40">
                    <i class="fas fa-triangle-exclamation mt-0.5 text-amber-600 dark:text-amber-400"></i>
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        Este intento quedó registrado.
                        @if ($restantes > 0)
                            Te {{ $restantes === 1 ? 'queda' : 'quedan' }} <strong>{{ $restantes }}</strong>
                            {{ $restantes === 1 ? 'intento' : 'intentos' }} antes de que tu cuenta sea bloqueada por accesos restringidos repetidos.
                        @else
                            Tu cuenta fue bloqueada por accesos restringidos repetidos.
                        @endif
                    </p>
                </div>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-6 py-3 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900">
                    <i class="fas fa-home"></i> Ir al inicio
                </a>
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-neutral-300 px-6 py-3 text-sm font-extrabold text-neutral-800 transition hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-100 dark:hover:bg-neutral-800">
                    <i class="fas fa-arrow-left"></i> Volver atrás
                </a>
            </div>
        </div>
    </main>
</body>

</html>
