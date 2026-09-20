@extends('layouts.app')

@section('content_header')
    <div class="rounded-2xl border p-5 shadow-sm mb-6 d-flex justify-content-between align-items-center"
        style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div>
            <h1 class="m-0 text-2xl font-extrabold" style="color:var(--text-main);">Editar perfil de empleado</h1>
            <p class="mt-1 mb-0" style="font-size:0.95rem; color:#475569;">
                <i class="fas fa-user-edit mr-1" style="color: var(--color-secondary)"></i> 
                ID de usuario: <strong>{{ $usuario->id_usuario }}</strong> · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('admin.configuracion.empleados.index') }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold hover:border-red-600 hover:text-red-600" style="border-color:var(--border-color);color:var(--text-main);">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @include('components.alert')

    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="rd-card shadow-sm border-0 overflow-hidden fade-in">
                
                <form action="{{ route('admin.configuracion.empleados.update', $usuario->id_usuario) }}" method="POST" class="rd-prevent-double-submit">
                    @csrf
                    @method('PUT')

                    <div class="rd-card-body border-bottom bg-light py-3">
                        <h3 class="rd-title-sm">
                            <i class="fas fa-id-badge mr-2" style="color: var(--color-secondary)"></i> Información de Cuenta
                        </h3>
                    </div>

                    <div class="rd-card-body p-4">
                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label class="rd-label mb-2">Correo de Usuario (Login / Gmail)</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group @error('username') border-danger @enderror">
                                    <span><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="username" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" value="{{ old('username', $usuario->username) }}" required placeholder="ejemplo@gmail.com">
                                </div>
                                @error('username') <div class="rd-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label class="rd-label mb-2">Asignación de Rol</label>
                                @php
                                    $isAdminUsuario = $usuario->roles->contains('nombre', 'Administrador');
                                    $authUser = auth()->user();
                                    $isSelfAdmin = $authUser && $authUser->id_usuario == $usuario->id_usuario && $authUser->roles->contains('nombre', 'Administrador');
                                    $hideRoleSelectForSelfAdmin = $isSelfAdmin;
                                    $currentRole = $usuario->roles->first();
                                @endphp

                                @if(!$hideRoleSelectForSelfAdmin)
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group @error('role') border-danger @enderror">
                                        <span><i class="fas fa-shield-alt"></i></span>
                                        <select name="role" id="roleSelect" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100">
                                            @foreach($roles as $r)
                                                <option value="{{ $r->id_rol }}" {{ old('role', $currentRole ? $currentRole->id_rol : '') == $r->id_rol ? 'selected' : '' }}>
                                                    {{ $r->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group" style="background: #f8fafc; border-style: dashed;">
                                        <span><i class="fas fa-user-check text-success"></i></span>
                                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100 py-2">{{ $currentRole ? $currentRole->nombre : '”' }}</div>
                                    </div>
                                    <input type="hidden" name="role" id="roleSelectHidden" data-text="{{ $currentRole ? $currentRole->nombre : '' }}" value="{{ $currentRole ? $currentRole->id_rol : '' }}">
                                    <small class="text-danger mt-1 d-block">No puedes cambiar tu propio rol de Administrador.</small>
                                @endif

                                @error('role') <div class="rd-error">{{ $message }}</div> @enderror

                                @if(!($otherAdminExists ?? true) && !$hideRoleSelectForSelfAdmin && $isAdminUsuario)
                                    <div class="alert alert-warning mt-3 border-0 shadow-sm" style="border-radius: 10px; font-size: 0.9rem;">
                                        <i class="fas fa-exclamation-triangle mr-2"></i> No puedes cambiar el rol: Este es el único <strong>Administrador</strong> del sistema.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="rd-card-body border-bottom bg-light py-3 border-top d-flex justify-content-between align-items-center">
                        <h3 class="rd-title-sm m-0">
                            <i class="fas fa-lock mr-2" style="color: var(--color-secondary)"></i> Seguridad y Credenciales
                        </h3>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="modificar_seguridad" class="custom-control-input" id="toggleSecurity" value="1" {{ old('modificar_seguridad') ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-secondary" for="toggleSecurity" style="cursor: pointer;">Modificar Credenciales</label>
                        </div>
                    </div>

                    <div id="securityFieldsWrapper" class="rd-card-body p-4 border-bottom" style="display: none; background-color: #fafbfc;">
                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label class="rd-label mb-2">Nueva Contraseña</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group @error('password') border-danger @enderror">
                                    <span><i class="fas fa-key"></i></span>
                                    <input type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" placeholder="Escriba la nueva contraseña">
                                </div>
                                @error('password') <div class="rd-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label class="rd-label mb-2">Confirmar Nueva Contraseña</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group">
                                    <span><i class="fas fa-check-double"></i></span>
                                    <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" placeholder="Repita la nueva contraseña">
                                </div>
                            </div>

                            <div id="newAdminKeyWrap" class="col-12 form-group mb-4 p-3" style="display:none; background: #fffcf0; border: 1px dashed #fcd34d; border-radius: 12px;">
                                <label class="rd-label text-warning mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-star mr-1"></i> Llave Maestra de Autorización (Solo Administradores)
                                </label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group bg-white" style="border-color: #fbbf24;">
                                    <span><i class="fas fa-shield-alt text-warning"></i></span>
                                    <input type="password" name="master_key" id="newAdminKey" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" 
                                           placeholder="{{ $isAdminUsuario ? 'Escriba una nueva llave maestra o deje en blanco para mantener la actual' : 'Defina la llave maestra para el nuevo Administrador...' }}">
                                </div>
                                @error('master_key') <div class="rd-error">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @php
                            $sq = $usuario->security_questions;
                            if(is_string($sq)) $sq = json_decode($sq, true);
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
                        
                        <div class="p-3 rounded mt-2" style="background: #ffffff; border: 1px solid #e2e8f0;">
                            <h5 class="rd-title-sm mb-3" style="font-size: 0.95rem; color: #475569;">
                                <i class="fas fa-question-circle mr-1"></i> Preguntas de Recuperación (Opcionales)
                            </h5>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="rd-label small mb-1">Pregunta de Seguridad #1</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group bg-white">
                                        <span><i class="fas fa-list text-muted"></i></span>
                                        <select name="security_questions[pregunta_1]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100">
                                            <option value="" {{ empty(old('security_questions.pregunta_1', $q1)) ? 'selected' : '' }}>-- Selecciona pregunta 1 --</option>
                                            @foreach ($questionsList as $q)
                                                <option value="{{ $q }}" {{ old('security_questions.pregunta_1', $q1) == $q ? 'selected' : '' }}>
                                                    {{ $q }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="rd-label small mb-1">Respuesta #1</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group bg-white">
                                        <span><i class="fas fa-comment-dots text-muted"></i></span>
                                        <input type="password" name="security_questions[respuesta_1]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" placeholder="Escriba la nueva respuesta...">
                                    </div>
                                </div>

                                <div class="col-md-6 form-group mb-3 mb-md-0">
                                    <label class="rd-label small mb-1">Pregunta de Seguridad #2</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group bg-white">
                                        <span><i class="fas fa-list text-muted"></i></span>
                                        <select name="security_questions[pregunta_2]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100">
                                            <option value="" {{ empty(old('security_questions.pregunta_2', $q2)) ? 'selected' : '' }}>-- Selecciona pregunta 2 --</option>
                                            @foreach ($questionsList as $q)
                                                <option value="{{ $q }}" {{ old('security_questions.pregunta_2', $q2) == $q ? 'selected' : '' }}>
                                                    {{ $q }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-0">
                                    <label class="rd-label small mb-1">Respuesta #2</label>
                                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group bg-white">
                                        <span><i class="fas fa-comment-dots text-muted"></i></span>
                                        <input type="password" name="security_questions[respuesta_2]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" placeholder="Escriba la nueva respuesta...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rd-card-body border-bottom bg-light py-3 border-top">
                        <h3 class="rd-title-sm">
                            <i class="fas fa-info-circle mr-2" style="color: var(--color-secondary)"></i> Información Personal
                        </h3>
                    </div>

                    <div class="rd-card-body p-4">
                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label class="rd-label mb-2">Cédula de Identidad</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group @error('cedula_persona') border-danger @enderror">
                                    <span><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="cedula_persona" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" 
                                           value="{{ old('cedula_persona', optional($usuario->persona)->cedula_persona) }}" placeholder="Ej: V-12345678">
                                </div>
                                @error('cedula_persona') <div class="rd-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label class="rd-label mb-2">Teléfono de Contacto</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group @error('telefono_persona') border-danger @enderror">
                                    <span><i class="fas fa-phone"></i></span>
                                    <input type="text" name="telefono_persona" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" 
                                           value="{{ old('telefono_persona', optional($usuario->persona)->telefono_persona) }}" placeholder="Ej: 0412-1234567">
                                </div>
                                @error('telefono_persona') <div class="rd-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label class="rd-label mb-2">Nombres</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group @error('nombre_persona') border-danger @enderror">
                                    <span><i class="fas fa-user"></i></span>
                                    <input type="text" name="nombre_persona" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" 
                                           value="{{ old('nombre_persona', optional($usuario->persona)->nombre_persona) }}" placeholder="Nombres del empleado">
                                </div>
                                @error('nombre_persona') <div class="rd-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label class="rd-label mb-2">Apellidos</label>
                                <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20-group @error('apellido_persona') border-danger @enderror">
                                    <span><i class="fas fa-user"></i></span>
                                    <input type="text" name="apellido_persona" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 w-100" 
                                           value="{{ old('apellido_persona', optional($usuario->persona)->apellido_persona) }}" placeholder="Apellidos del empleado">
                                </div>
                                @error('apellido_persona') <div class="rd-error">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.configuracion.empleados.index') }}" class="rd-btn rd-btn-alter px-4" style="height: 45px; justify-content: center; align-items: center;">
                                Cancelar
                            </a>
                            <button type="submit" class="rd-btn rd-btn-primary rd-submit-btn px-4" style="height: 45px; justify-content: center; align-items: center;">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        // --- CONTROL DEL SWITCH DE SEGURIDAD ---
        var toggleSecurity = document.getElementById('toggleSecurity');
        var securityWrapper = document.getElementById('securityFieldsWrapper');

        function toggleSecurityFields() {
            if (toggleSecurity.checked) {
                securityWrapper.style.display = 'block';
                // Habilitar los inputs internos para que viajen en el request si se rellenan
                securityWrapper.querySelectorAll('input, select').forEach(el => el.disabled = false);
            } else {
                securityWrapper.style.display = 'none';
                // Deshabilitar inputs internos para evitar envíos incidentales
                securityWrapper.querySelectorAll('input, select').forEach(el => el.disabled = true);
            }
        }

        if (toggleSecurity && securityWrapper) {
            toggleSecurity.addEventListener('change', toggleSecurityFields);
            toggleSecurityFields(); // Ejecución inicial (por si hay errores de validación redireccionados)
        }

        // --- CONTROL DE ROL ADMINISTRADOR (MASTER KEY) ---
        var select = document.getElementById('roleSelect');
        var selectHidden = document.getElementById('roleSelectHidden');
        var wrap = document.getElementById('newAdminKeyWrap');

        function checkRole(){
            if (!wrap) return;
            let selText = '';
            if (select) {
                selText = select.options[select.selectedIndex].text || '';
            } else if (selectHidden) {
                selText = selectHidden.getAttribute('data-text') || '';
            }

            if(selText.trim().toLowerCase() === 'administrador'){
                wrap.style.display = 'block';
                wrap.classList.add('fade-in');
            } else { 
                wrap.style.display = 'none'; 
            }
        }

        if (select) {
            select.addEventListener('change', checkRole);
        }
        checkRole();
    });
</script>
@stop
