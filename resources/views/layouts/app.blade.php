@php
    $moduloActivo = session('modulo_activo', 'general');
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'salud']);
    $primaryColorHex = $esPsicologia ? '#2563eb' : '#dc2626';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-modulo="{{ $moduloActivo }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bienestar Estudiantil') }}</title>

    <script>
        (function () {
            const getStoredTheme = () => localStorage.getItem('theme');
            const getSystemTheme = () => window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

            let theme = getStoredTheme() || 'auto';
            const appliedTheme = theme === 'auto' ? getSystemTheme() : theme;

            if (appliedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            }
            document.documentElement.setAttribute('data-user-theme', theme);

            let sidebarOpen = localStorage.getItem('sidebarOpen');
            if (sidebarOpen === null) sidebarOpen = 'true';
            document.documentElement.classList.add(sidebarOpen === 'true' ? 'sidebar-expanded' : 'sidebar-collapsed');
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireScriptConfig
    @stack('styles')
    @stack('css')

    <style>
        :root {
            --color-primary: #dc2626;
            --color-primary-alpha: rgba(220, 38, 38, 0.25);
            --header-sidebar-bg: #352728;
            --header-sidebar-border: #5c2028;
            --header-sidebar-text: #ffffff;
            --sidebar-active-bg: #623739;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --input-bg: #ffffff;
            --input-border: #cbd5e1;

            --trans-default: all 0.2s ease;
        }

        /* Variables de CKEditor para el Modo Oscuro */
        .dark {
            /* Color de fondo del área de texto y paneles */
            --ck-color-base-background: #1f2937;
            /* Color del texto */
            --ck-color-base-text: #f3f4f6;
            /* Color de los bordes generales */
            --ck-color-base-border: #374151;

            /* Barra de herramientas (Toolbar) */
            --ck-color-toolbar-background: #111827;
            --ck-color-toolbar-border: #374151;

            /* Botones de la barra de herramientas */
            --ck-color-button-default-background: transparent;
            --ck-color-button-default-hover-background: #374151;
            --ck-color-button-default-active-background: #4b5563;
            --ck-color-button-on-background: #374151;
            --ck-color-button-on-color: #ffffff;

            /* Menús desplegables (Listas) */
            --ck-color-list-background: #1f2937;
            --ck-color-list-button-hover-background: #374151;

            /* Paneles emergentes (como el de insertar enlace) */
            --ck-color-panel-background: #1f2937;
            --ck-color-panel-border: #374151;
        }

        .dark .ck-placeholder::before {
            color: #9ca3af !important;
        }

        html.dark {
            --header-sidebar-bg: #352728;
            --header-sidebar-border: #5c2028;
            --sidebar-active-bg: #623739;

            --bg-body: #0d0708;
            --bg-card: #160c0e;
            --border-color: #271418;
            --text-main: #f8fafc;
            --input-bg: #160c0e;
            --input-border: #271418;
        }

        html[data-modulo="psicologia"],
        html[data-modulo="salud"] {
            --color-primary: #2563eb;
            --color-primary-alpha: rgba(37, 99, 235, 0.25);
            --header-sidebar-bg: #0f172a;
            --header-sidebar-border: #1e293b;
            --sidebar-active-bg: #1e3a8a;
        }

        html.dark[data-modulo="psicologia"],
        html.dark[data-modulo="salud"] {
            --header-sidebar-bg: #0b0f19;
            --header-sidebar-border: #1e293b;
            --sidebar-active-bg: #1e3a8a;

            --bg-body: #090d16;
            --bg-card: #0f172a;
            --border-color: #1e293b;
            --input-bg: #0f172a;
            --input-border: #1e293b;
        }

        body {
            background-color: var(--bg-body) !important;
            color: var(--text-main) !important;
        }

        #top-navbar,
        #main-sidebar,
        nav.top-navbar {
            background-color: var(--header-sidebar-bg) !important;
            border-color: var(--header-sidebar-border) !important;
            color: var(--header-sidebar-text) !important;
        }

        #main-sidebar nav a,
        #main-sidebar nav button {
            background-color: transparent !important;
            color: rgba(255, 255, 255, 0.9) !important;
            border-radius: 0.5rem !important;
            margin-bottom: 0.15rem;
            padding: 0.5rem 0.75rem !important;
        }

        #main-sidebar nav a:hover,
        #main-sidebar nav button:hover,
        #main-sidebar nav a.active,
        #main-sidebar nav button.active,
        #main-sidebar .sidebar-item-active {
            background-color: var(--sidebar-active-bg) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        #main-sidebar nav a svg,
        #main-sidebar nav button svg {
            color: #ffffff !important;
        }

        * {
            transition: var(--trans-default);
        }

        .no-transition,
        .preload,
        .preload * {
            transition: none !important;
        }

        .invisible-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .invisible-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        input:not([type="radio"]):not([type="checkbox"]):not([type="file"]),
        select,
        textarea {
            background-color: var(--input-bg) !important;
            border-color: var(--input-border) !important;
            color: var(--text-main) !important;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--color-primary) !important;
            outline: none;
            box-shadow: 0 0 0 2px var(--color-primary-alpha) !important;
        }

        /* Inputs Deshabilitados (Disabled) */
        input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):disabled,
        select:disabled,
        textarea:disabled {
            background-color: #f1f5f9 !important;
            /* gray-100 */
            border-color: #e2e8f0 !important;
            /* gray-200 */
            color: #94a3b8 !important;
            /* gray-400 */
            cursor: not-allowed !important;
            opacity: 0.75 !important;
        }

        html.dark input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):disabled,
        html.dark select:disabled,
        html.dark textarea:disabled {
            background-color: #12090b !important;
            /* fondo oscuro opaco */
            border-color: #271418 !important;
            /* borde oscuro */
            color: #4b5563 !important;
            /* gris oscuro / slate-500 */
            cursor: not-allowed !important;
            opacity: 0.75 !important;
        }

        /* Sidebar Colapsable */
        html.sidebar-collapsed #main-sidebar {
            width: 4rem !important;
        }

        html.sidebar-expanded #main-sidebar {
            width: 16rem !important;
        }

        .swal2-toast-custom {
            background: var(--bg-card) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 1rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            padding: 0.85rem 1.25rem !important;
        }

        .swal2-toast-custom .swal2-title {
            color: var(--text-main) !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
        }

        .swal2-toast-custom .swal2-html-container {
            color: var(--text-main) !important;
            opacity: 0.85;
            font-size: 0.85rem !important;
        }

        .swal2-popup-custom {
            background: var(--bg-card) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem !important;
        }

        .swal2-popup-custom .swal2-title {
            color: var(--text-main) !important;
            font-weight: 800 !important;
        }

        .swal2-popup-custom .swal2-html-container {
            color: var(--text-main) !important;
            opacity: 0.9;
        }
    </style>
</head>

<body
    class="preload font-sans antialiased overflow-hidden bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="w-full flex flex-col overflow-hidden" style="height: 100dvh;"
        @toggle-chat.window="isChatOpen = !isChatOpen" x-data="{
            isChatOpen: false,
            open: false,
            sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false'
        }" x-init="$watch('sidebarOpen', val => {
            localStorage.setItem('sidebarOpen', val);
            if (val) {
                document.documentElement.classList.remove('sidebar-collapsed');
                document.documentElement.classList.add('sidebar-expanded');
            } else {
                document.documentElement.classList.remove('sidebar-expanded');
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })">

        <header
            class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm z-40 flex-shrink-0 relative">
            @include('layouts.navigation')
        </header>

        <div class="flex flex-1 overflow-hidden relative" style="min-height: 0;">
            @include('layouts.sidebar')

            <main class="flex-1 overflow-y-auto invisible-scrollbar p-6 scroll-smooth">
                @isset($header)
                    <div class="max-w-7xl mx-auto mb-6">
                        {{ $header }}
                    </div>
                @endisset

                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>

            @if (View::exists('components.chat-window'))
                <x-chat-window />
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const primaryColor = '{{ $primaryColorHex }}';

            window.Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal2-toast-custom'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            window.AppModal = {
                show: function (title, text, options = {}) {
                    const isDark = document.documentElement.classList.contains('dark');
                    return Swal.fire({
                        title: title || 'Aviso',
                        html: text || '',
                        icon: options.icon || 'info',
                        showCancelButton: options.type !== 'alert',
                        confirmButtonText: options.confirmText || 'Aceptar',
                        cancelButtonText: options.cancelText || 'Cancelar',
                        confirmButtonColor: primaryColor,
                        cancelButtonColor: isDark ? '#374151' : '#e5e7eb',
                        customClass: {
                            popup: 'swal2-popup-custom',
                            cancelButton: 'text-slate-700 dark:text-gray-200 font-bold rounded-xl px-5 py-2.5',
                            confirmButton: 'text-white font-bold rounded-xl px-5 py-2.5'
                        },
                        buttonsStyling: true
                    }).then((result) => result.isConfirmed);
                },
                confirm: function (title, text) {
                    return this.show(title, text, {
                        type: 'confirm',
                        icon: 'warning'
                    });
                },
                alert: function (title, text) {
                    return this.show(title, text, {
                        type: 'alert',
                        icon: 'info'
                    });
                }
            };

            @if (session('success'))
                window.Toast.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    html: {!! json_encode(session('success')) !!}
                });
            @endif

            @if (session('error'))
                window.Toast.fire({
                    icon: 'error',
                    title: 'Atención',
                    html: {!! json_encode(session('error')) !!}
                });
            @endif

            @if (session('info'))
                window.Toast.fire({
                    icon: 'info',
                    title: 'Información',
                    html: {!! json_encode(session('info')) !!}
                });
            @endif

            @if ($errors->any())
                window.Toast.fire({
                    icon: 'error',
                    title: 'Error de Validación',
                    html: {!! json_encode($errors->first()) !!}
                });
            @endif
        });

        window.addEventListener('load', () => setTimeout(() => document.body.classList.remove('preload'), 150));
    </script>

    @stack('scripts')

    <form id="form-actualizar-tasa" action="{{ route('productos.actualizar.tasa') }}" method="POST"
        style="display:none;">
        @csrf
    </form>

    @if (session()->has('tasa_pendiente') || session()->has('tasa_obligatoria'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const isDark = document.documentElement.classList.contains('dark');
                const primaryColor = '{{ $primaryColorHex }}';

                @if (session()->has('tasa_obligatoria'))
                    Swal.fire({
                        title: '🚨 Tasa requerida',
                        html: `<p class="text-sm opacity-90">Debe registrar la <b>tasa del dólar</b> para poder navegar en el sistema.</p>`,
                        icon: 'error',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonText: 'Registrar tasa',
                        confirmButtonColor: primaryColor,
                        customClass: {
                            popup: 'swal2-popup-custom'
                        }
                    }).then(() => {
                        document.getElementById('form-actualizar-tasa').submit();
                    });
                @elseif (session()->has('tasa_pendiente'))
                    Swal.fire({
                        title: '🔄 Tasa no actualizada',
                        html: `<p class="text-sm opacity-90">Hay una tasa registrada, pero no corresponde al día de hoy.<br>¿Desea actualizarla ahora?</p>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Actualizar',
                        cancelButtonText: 'Más tarde',
                        confirmButtonColor: '#16a34a',
                        cancelButtonColor: isDark ? '#374151' : '#6b7280',
                        customClass: {
                            popup: 'swal2-popup-custom'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('form-actualizar-tasa').submit();
                        } else {
                            fetch('{{ route('tasa.ignorar') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            });
                        }
                    });
                @endif
                    });
        </script>
    @endif
</body>

</html>