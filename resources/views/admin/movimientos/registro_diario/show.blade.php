<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Vista detallada del registro
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span>
                        · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.movimientos.registro_diario.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-arrow-left text-[10px]"></i> Volver
                    </a>
                </div>
            </div>

            @php
                $edad = \Carbon\Carbon::parse($registro->fecha_nacimiento_persona)->age;
            @endphp

            <div class="space-y-5">

                <div class="rounded-2xl border shadow-sm p-6 sm:p-8"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">

                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-wider text-red-700 dark:text-red-500">
                                Registro diario · {{ $registro->nombre_pnf ?? 'Sin PNF' }}
                            </p>
                            <h2 class="mt-2 text-xl sm:text-2xl font-extrabold" style="color: var(--text-main);">
                                Ficha del estudiante
                            </h2>
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 leading-relaxed max-w-2xl">
                                <strong style="color: var(--text-main);">
                                    {{ trim($registro->nombre_persona . ' ' . $registro->segundo_nombre_persona . ' ' . $registro->apellido_persona . ' ' . $registro->segundo_apellido_persona) }}
                                </strong>
                                fue registrado en el sistema con la información detallada a continuación.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    <div class="rounded-2xl border shadow-sm overflow-hidden"
                        style="background-color: var(--bg-card); border-color: var(--border-color);">

                        <div class="px-6 py-4 border-b" style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                            <h3 class="text-sm font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                                <i class="fas fa-user-circle text-red-700 dark:text-red-500"></i>
                                Datos personales
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                Identidad del estudiante
                            </p>
                        </div>

                        <div class="p-6 space-y-4">

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                    Nombre completo
                                </p>
                                <p class="text-sm font-bold" style="color: var(--text-main);">
                                    {{ trim($registro->nombre_persona . ' ' . $registro->segundo_nombre_persona . ' ' . $registro->apellido_persona . ' ' . $registro->segundo_apellido_persona) }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                        Cédula
                                    </p>
                                    <p class="text-sm font-bold" style="color: var(--text-main);">
                                        {{ $registro->cedula_persona ?? '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                        Género
                                    </p>
                                    <p class="text-sm font-bold" style="color: var(--text-main);">
                                        {{ $registro->genero_persona ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                        Fecha de nacimiento
                                    </p>
                                    <p class="text-sm font-bold" style="color: var(--text-main);">
                                        {{ \Carbon\Carbon::parse($registro->fecha_nacimiento_persona)->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                        Edad
                                    </p>
                                    <p class="text-sm font-bold" style="color: var(--text-main);">
                                        {{ $edad }} años
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border shadow-sm overflow-hidden"
                        style="background-color: var(--bg-card); border-color: var(--border-color);">

                        <div class="px-6 py-4 border-b" style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                            <h3 class="text-sm font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                                <i class="fas fa-address-book text-red-700 dark:text-red-500"></i>
                                Contacto y PNF
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                Medios para ubicar al estudiante
                            </p>
                        </div>

                        <div class="p-6 space-y-4">

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                    Teléfono
                                </p>
                                <p class="text-sm font-bold" style="color: var(--text-main);">
                                    {{ $registro->telefono_persona ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                    Correo electrónico
                                </p>
                                <p class="text-sm font-bold break-all" style="color: var(--text-main);">
                                    {{ $registro->email_persona ?? '—' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                        PNF asociado
                                    </p>
                                    <p class="text-sm font-bold" style="color: var(--text-main);">
                                        {{ $registro->nombre_pnf ?? '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                        Semestre
                                    </p>
                                    <p class="text-sm font-bold" style="color: var(--text-main);">
                                        {{ $registro->semestre_persona ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 rounded-2xl border shadow-sm overflow-hidden"
                        style="background-color: var(--bg-card); border-color: var(--border-color);">

                        <div class="px-6 py-4 border-b" style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                            <h3 class="text-sm font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                                <i class="fas fa-clock text-red-700 dark:text-red-500"></i>
                                Registro del sistema
                            </h3>
                            <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                Fecha y hora oficial
                            </p>
                        </div>

                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                    Fecha de registro
                                </p>
                                <p class="text-sm font-bold" style="color: var(--text-main);">
                                    {{ \Carbon\Carbon::parse($registro->fecha_regis_diario_c)->format('d/m/Y') }}
                                </p>
                                <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                                    Información tomada del formulario enviado.
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                                    Hora registrada
                                </p>
                                <p class="text-sm font-bold" style="color: var(--text-main);">
                                    {{ $registro->hora ?? '—' }}
                                </p>
                                <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                                    Corresponde a la hora exacta de creación.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>