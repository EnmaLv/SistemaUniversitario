<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- ─── Encabezado ─── --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Editar perfil de empleado
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-user-edit mr-1 text-red-600"></i>
                        ID de usuario: <strong>{{ $usuario->id_usuario }}</strong>
                        · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.configuracion.empleados.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver
                </a>
            </div>

            {{-- ─── Card principal ─── --}}
            <div class="rounded-2xl border shadow-sm overflow-hidden"
                style="background-color: var(--bg-card); border-color: var(--border-color);">

                <form action="{{ route('admin.configuracion.empleados.update', $usuario->id_usuario) }}"
                    method="POST" class="rd-prevent-double-submit">
                    @csrf
                    @method('PUT')

                    {{-- ═══ SECCIÓN 1: Información de Cuenta ═══ --}}
                    <div class="px-6 sm:px-8 py-4 border-b"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                        <h3 class="text-xs font-black uppercase tracking-wider flex items-center gap-2 m-0"
                            style="color: var(--text-main);">
                            <i class="fas fa-id-badge text-red-600"></i>
                            Información de Cuenta
                        </h3>
                    </div>

                    <div class="px-6 sm:px-8 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Correo de usuario --}}
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                    Correo de Usuario (Login / Gmail)
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('username') border-rose-400 @enderror"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                        <i class="fas fa-envelope text-sm"></i>
                                    </span>
                                    <input type="email" name="username"
                                        value="{{ old('username', $usuario->username) }}"
                                        required placeholder="ejemplo@gmail.com"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                                @error('username')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Rol --}}
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                    Asignación de Rol
                                </label>
                                @php
                                    $isAdminUsuario = $usuario->roles->contains('nombre', 'Administrador');
                                    $authUser       = auth()->user();
                                    $isSelfAdmin    = $authUser
                                        && $authUser->id_usuario == $usuario->id_usuario
                                        && $authUser->roles->contains('nombre', 'Administrador');
                                    $hideRoleSelectForSelfAdmin = $isSelfAdmin;
                                    $currentRole    = $usuario->roles->first();
                                @endphp

                                @if (!$hideRoleSelectForSelfAdmin)
                                    <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('role') border-rose-400 @enderror"
                                        style="border-color: var(--border-color);">
                                        <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                            style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                            <i class="fas fa-shield-alt text-sm"></i>
                                        </span>
                                        <select name="role" id="roleSelect"
                                            style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                            class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                            @foreach ($roles as $r)
                                                <option value="{{ $r->id_rol }}"
                                                    {{ old('role', $currentRole ? $currentRole->id_rol : '') == $r->id_rol ? 'selected' : '' }}>
                                                    {{ $r->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="flex items-stretch rounded-xl border border-dashed overflow-hidden"
                                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02);">
                                        <span class="flex items-center justify-center px-3.5 border-r text-emerald-600"
                                            style="background-color: rgba(16,185,129,0.08); border-color: var(--border-color);">
                                            <i class="fas fa-user-check text-sm"></i>
                                        </span>
                                        <div class="w-full px-3 py-2.5 text-sm font-medium"
                                            style="color: var(--text-main);">
                                            {{ $currentRole ? $currentRole->nombre : '—' }}
                                        </div>
                                    </div>
                                    <input type="hidden" name="role" id="roleSelectHidden"
                                        data-text="{{ $currentRole ? $currentRole->nombre : '' }}"
                                        value="{{ $currentRole ? $currentRole->id_rol : '' }}">
                                    <small class="block mt-1.5 text-xs font-semibold text-rose-500">
                                        No puedes cambiar tu propio rol de Administrador.
                                    </small>
                                @endif

                                @error('role')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror

                                @if (!($otherAdminExists ?? true) && !$hideRoleSelectForSelfAdmin && $isAdminUsuario)
                                    <div class="mt-3 p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-2">
                                        <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 mt-0.5 text-sm"></i>
                                        <div class="text-xs text-amber-800 dark:text-amber-300">
                                            No puedes cambiar el rol: Este es el único <strong>Administrador</strong> del sistema.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECCIÓN 2: Seguridad y Credenciales ═══ --}}
                    <div class="px-6 sm:px-8 py-4 border-t border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                        <h3 class="text-xs font-black uppercase tracking-wider flex items-center gap-2 m-0"
                            style="color: var(--text-main);">
                            <i class="fas fa-lock text-red-600"></i>
                            Seguridad y Credenciales
                        </h3>

                        <label for="toggleSecurity"
                            class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="modificar_seguridad" id="toggleSecurity" value="1"
                                {{ old('modificar_seguridad') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 accent-red-600 focus:ring-2 focus:ring-red-500/30 cursor-pointer">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                Modificar credenciales
                            </span>
                        </label>
                    </div>

                    <div id="securityFieldsWrapper" class="px-6 sm:px-8 py-6 border-b"
                        style="display: none; border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Nueva contraseña --}}
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                    Nueva Contraseña
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('password') border-rose-400 @enderror"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                        <i class="fas fa-key text-sm"></i>
                                    </span>
                                    <input type="password" name="password"
                                        placeholder="Escriba la nueva contraseña"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                                @error('password')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Confirmar contraseña --}}
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                    Confirmar Nueva Contraseña
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                        <i class="fas fa-check-double text-sm"></i>
                                    </span>
                                    <input type="password" name="password_confirmation"
                                        placeholder="Repita la nueva contraseña"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                            </div>

                            {{-- Master key --}}
                            <div id="newAdminKeyWrap"
                                class="md:col-span-2 p-4 rounded-xl border border-dashed border-amber-300 dark:border-amber-700/60 bg-amber-50/60 dark:bg-amber-950/20"
                                style="display:none;">
                                <label class="block text-[11px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-2">
                                    <i class="fas fa-star mr-1"></i>
                                    Llave Maestra de Autorización (Solo Administradores)
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden bg-white dark:bg-gray-900 transition-all focus-within:ring-2 focus-within:ring-amber-500/30 focus-within:border-amber-500"
                                    style="border-color: #fbbf24;">
                                    <span class="flex items-center justify-center px-3.5 border-r border-amber-300 text-amber-600"
                                        style="background-color: rgba(251,191,36,0.08);">
                                        <i class="fas fa-shield-alt text-sm"></i>
                                    </span>
                                    <input type="password" name="master_key" id="newAdminKey"
                                        placeholder="{{ $isAdminUsuario ? 'Escriba una nueva llave maestra o deje en blanco para mantener la actual' : 'Defina la llave maestra para el nuevo Administrador...' }}"
                                        style="color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none bg-transparent focus:ring-0 focus:outline-none">
                                </div>
                                @error('master_key')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Preguntas de seguridad --}}
                        @php
                            $sq = $usuario->security_questions;
                            if (is_string($sq)) $sq = json_decode($sq, true);
                            $q1 = $sq['pregunta_1'] ?? '';
                            $q2 = $sq['pregunta_2'] ?? '';

                            $questionsList = [
                                '¿Cuál es el nombre de tu primera mascota?',
                                '¿Cuál es el nombre de tu madre?',
                                '¿En qué ciudad naciste?',
                                '¿Cuál es tu comida favorita?',
                                '¿Cuál fue tu primer colegio?',
                                '¿Cuál es el segundo nombre de tu padre?',
                            ];
                        @endphp

                        <div class="mt-5 p-4 rounded-xl border"
                            style="border-color: var(--border-color); background-color: var(--bg-card);">

                            <h5 class="text-xs font-black uppercase tracking-wider mb-4 flex items-center gap-2"
                                style="color: var(--text-main);">
                                <i class="fas fa-question-circle text-red-600"></i>
                                Preguntas de Recuperación (Opcionales)
                            </h5>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- Pregunta 1 --}}
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 dark:text-gray-500 mb-1.5">
                                        Pregunta #1
                                    </label>
                                    <div class="flex items-stretch rounded-xl border overflow-hidden bg-white dark:bg-gray-900 focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500"
                                        style="border-color: var(--border-color);">
                                        <span class="flex items-center justify-center px-3 border-r text-gray-400"
                                            style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                            <i class="fas fa-list text-xs"></i>
                                        </span>
                                        <select name="security_questions[pregunta_1]"
                                            style="color: var(--text-main);"
                                            class="w-full px-3 py-2.5 text-sm font-medium border-none bg-transparent focus:ring-0 focus:outline-none">
                                            <option value="" {{ empty(old('security_questions.pregunta_1', $q1)) ? 'selected' : '' }}>
                                                -- Selecciona pregunta 1 --
                                            </option>
                                            @foreach ($questionsList as $q)
                                                <option value="{{ $q }}"
                                                    {{ old('security_questions.pregunta_1', $q1) == $q ? 'selected' : '' }}>
                                                    {{ $q }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Respuesta 1 --}}
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 dark:text-gray-500 mb-1.5">
                                        Respuesta #1
                                    </label>
                                    <div class="flex items-stretch rounded-xl border overflow-hidden bg-white dark:bg-gray-900 focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500"
                                        style="border-color: var(--border-color);">
                                        <span class="flex items-center justify-center px-3 border-r text-gray-400"
                                            style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                            <i class="fas fa-comment-dots text-xs"></i>
                                        </span>
                                        <input type="password" name="security_questions[respuesta_1]"
                                            placeholder="Escriba la nueva respuesta..."
                                            style="color: var(--text-main);"
                                            class="w-full px-3 py-2.5 text-sm font-medium border-none bg-transparent focus:ring-0 focus:outline-none">
                                    </div>
                                </div>

                                {{-- Pregunta 2 --}}
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 dark:text-gray-500 mb-1.5">
                                        Pregunta #2
                                    </label>
                                    <div class="flex items-stretch rounded-xl border overflow-hidden bg-white dark:bg-gray-900 focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500"
                                        style="border-color: var(--border-color);">
                                        <span class="flex items-center justify-center px-3 border-r text-gray-400"
                                            style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                            <i class="fas fa-list text-xs"></i>
                                        </span>
                                        <select name="security_questions[pregunta_2]"
                                            style="color: var(--text-main);"
                                            class="w-full px-3 py-2.5 text-sm font-medium border-none bg-transparent focus:ring-0 focus:outline-none">
                                            <option value="" {{ empty(old('security_questions.pregunta_2', $q2)) ? 'selected' : '' }}>
                                                -- Selecciona pregunta 2 --
                                            </option>
                                            @foreach ($questionsList as $q)
                                                <option value="{{ $q }}"
                                                    {{ old('security_questions.pregunta_2', $q2) == $q ? 'selected' : '' }}>
                                                    {{ $q }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Respuesta 2 --}}
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 dark:text-gray-500 mb-1.5">
                                        Respuesta #2
                                    </label>
                                    <div class="flex items-stretch rounded-xl border overflow-hidden bg-white dark:bg-gray-900 focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500"
                                        style="border-color: var(--border-color);">
                                        <span class="flex items-center justify-center px-3 border-r text-gray-400"
                                            style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                            <i class="fas fa-comment-dots text-xs"></i>
                                        </span>
                                        <input type="password" name="security_questions[respuesta_2]"
                                            placeholder="Escriba la nueva respuesta..."
                                            style="color: var(--text-main);"
                                            class="w-full px-3 py-2.5 text-sm font-medium border-none bg-transparent focus:ring-0 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECCIÓN 3: Información Personal ═══ --}}
                    <div class="px-6 sm:px-8 py-4 border-b"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                        <h3 class="text-xs font-black uppercase tracking-wider flex items-center gap-2 m-0"
                            style="color: var(--text-main);">
                            <i class="fas fa-info-circle text-red-600"></i>
                            Información Personal
                        </h3>
                    </div>

                    <div class="px-6 sm:px-8 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Cédula --}}
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                    Cédula de Identidad
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('cedula_persona') border-rose-400 @enderror"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                        <i class="fas fa-id-card text-sm"></i>
                                    </span>
                                    <input type="text" name="cedula_persona"
                                        value="{{ old('cedula_persona', optional($usuario->persona)->cedula_persona) }}"
                                        placeholder="Ej: V-12345678"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                                @error('cedula_persona')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Teléfono --}}
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                    Teléfono de Contacto
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('telefono_persona') border-rose-400 @enderror"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                        <i class="fas fa-phone text-sm"></i>
                                    </span>
                                    <input type="text" name="telefono_persona"
                                        value="{{ old('telefono_persona', optional($usuario->persona)->telefono_persona) }}"
                                        placeholder="Ej: 0412-1234567"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                                @error('telefono_persona')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nombres --}}
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                    Nombres
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('nombre_persona') border-rose-400 @enderror"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                        <i class="fas fa-user text-sm"></i>
                                    </span>
                                    <input type="text" name="nombre_persona"
                                        value="{{ old('nombre_persona', optional($usuario->persona)->nombre_persona) }}"
                                        placeholder="Nombres del empleado"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                                @error('nombre_persona')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Apellidos --}}
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                    Apellidos
                                </label>
                                <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('apellido_persona') border-rose-400 @enderror"
                                    style="border-color: var(--border-color);">
                                    <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                        <i class="fas fa-user text-sm"></i>
                                    </span>
                                    <input type="text" name="apellido_persona"
                                        value="{{ old('apellido_persona', optional($usuario->persona)->apellido_persona) }}"
                                        placeholder="Apellidos del empleado"
                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                        class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                </div>
                                @error('apellido_persona')
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ═══ ACCIONES ═══ --}}
                    <div class="px-6 sm:px-8 py-5 border-t flex items-center justify-end gap-3"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">

                        <a href="{{ route('admin.configuracion.empleados.index') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            <i class="fas fa-arrow-left text-xs"></i>
                            Cancelar
                        </a>

                        <button type="submit"
                            class="rd-submit-btn inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold shadow-md active:scale-95 transition-all">
                            <i class="fas fa-save text-xs"></i>
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                /* ─── Switch: Modificar Credenciales ─── */
                var toggleSecurity  = document.getElementById('toggleSecurity');
                var securityWrapper = document.getElementById('securityFieldsWrapper');

                function toggleSecurityFields() {
                    if (!toggleSecurity || !securityWrapper) return;
                    if (toggleSecurity.checked) {
                        securityWrapper.style.display = 'block';
                        securityWrapper.querySelectorAll('input, select').forEach(el => el.disabled = false);
                    } else {
                        securityWrapper.style.display = 'none';
                        securityWrapper.querySelectorAll('input, select').forEach(el => el.disabled = true);
                    }
                }

                if (toggleSecurity && securityWrapper) {
                    toggleSecurity.addEventListener('change', toggleSecurityFields);
                    toggleSecurityFields();
                }

                /* ─── Mostrar master key solo si el rol es Administrador ─── */
                var select       = document.getElementById('roleSelect');
                var selectHidden = document.getElementById('roleSelectHidden');
                var wrap         = document.getElementById('newAdminKeyWrap');

                function checkRole() {
                    if (!wrap) return;
                    let selText = '';
                    if (select) {
                        selText = select.options[select.selectedIndex].text || '';
                    } else if (selectHidden) {
                        selText = selectHidden.getAttribute('data-text') || '';
                    }
                    if (selText.trim().toLowerCase() === 'administrador') {
                        wrap.style.display = 'block';
                        wrap.classList.add('fade-in');
                    } else {
                        wrap.style.display = 'none';
                    }
                }

                if (select) select.addEventListener('change', checkRole);
                checkRole();
            });
        </script>
    @endpush
</x-app-layout>