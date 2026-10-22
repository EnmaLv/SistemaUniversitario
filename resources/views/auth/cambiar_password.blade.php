<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cambiar contraseña - UPTP J.J. Montilla</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900 font-sans antialiased">
    <main class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl sm:p-10 dark:bg-neutral-900" x-data="{ ver: false }">
            <img src="{{ asset('img/Logo.webp') }}" alt="Logo UPTP" class="mx-auto mb-4 h-16 w-16 rounded-full object-contain">

            <div class="text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/50">
                    <i class="fas fa-key text-xl text-red-800 dark:text-red-400"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-neutral-900 dark:text-white">Cambiar contraseña</h1>
                @if ($obligatorio)
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                        Es tu primer inicio de sesión. Por seguridad debes reemplazar la contraseña inicial (tu cédula) antes de continuar.
                    </p>
                @else
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">Elige una contraseña nueva para tu cuenta.</p>
                @endif
            </div>

            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.cambiar.guardar') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="password_actual" class="mb-1 block text-xs font-bold uppercase tracking-wider text-neutral-500">Contraseña actual</label>
                    <input id="password_actual" name="password_actual" required autocomplete="current-password"
                        type="password" :type="ver ? 'text' : 'password'"
                        class="w-full rounded-xl border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 focus:border-red-800 focus:outline-none focus:ring-2 focus:ring-red-800/30 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                </div>

                <div>
                    <label for="password" class="mb-1 block text-xs font-bold uppercase tracking-wider text-neutral-500">Nueva contraseña</label>
                    <input id="password" name="password" required minlength="8" autocomplete="new-password"
                        type="password" :type="ver ? 'text' : 'password'"
                        class="w-full rounded-xl border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 focus:border-red-800 focus:outline-none focus:ring-2 focus:ring-red-800/30 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                    <p class="mt-1 text-xs text-neutral-500">Mínimo 8 caracteres. No puede ser tu cédula.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-xs font-bold uppercase tracking-wider text-neutral-500">Confirmar nueva contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password"
                        type="password" :type="ver ? 'text' : 'password'"
                        class="w-full rounded-xl border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 focus:border-red-800 focus:outline-none focus:ring-2 focus:ring-red-800/30 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                </div>

                <label class="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-300">
                    <input type="checkbox" x-model="ver" class="rounded border-neutral-300 text-red-800 focus:ring-red-800">
                    Mostrar contraseñas
                </label>

                <button type="submit" class="w-full rounded-xl bg-red-800 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-red-900">
                    <i class="fas fa-check mr-2"></i>Guardar contraseña
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-sm font-semibold text-neutral-500 underline-offset-4 hover:text-red-800 hover:underline">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </main>
</body>

</html>
