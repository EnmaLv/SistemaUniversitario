<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - UPTP</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .wrap {
            display: flex;
            min-height: 100vh;
        }

        .logo {
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .logo img {
            width: 48px;
            height: 48px;
            display: block;
        }

        .left {
            flex: 1;
            padding: 40px 24px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .right {
            flex: 1;
            background: linear-gradient(135deg, #000 0%, #000 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            position: relative;
            overflow: hidden;
        }

        .right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("img/unnamed.webp");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            opacity: 0.5;
        }

        .card {
            width: 100%;
            max-width: 560px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(183, 28, 28, 0.25);
        }

        .header-icon svg {
            width: 44px;
            height: 44px;
            fill: #fff;
        }

        .title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1a1a1a;
            text-align: center;
        }

        .subtitle {
            font-size: 15px;
            color: #666;
            margin-bottom: 28px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-row {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .input,
        .select {
            width: 100%;
            padding: 14px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .input:focus,
        .select:focus {
            outline: none;
            border-color: #b71c1c;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(183, 28, 28, 0.1);
        }

        .select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23666' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c62828;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 24px 0 16px;
            text-align: center;
            position: relative;
        }

        .btn {
            width: 100%;
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            color: #fff;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(183, 28, 28, 0.3);
        }

        .boton {
            width: 100%;
            background: #fff;
            color: #1a1a1a;
            padding: 14px 24px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 12px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        }

        .corner-text {
            text-align: center;
        }

        .corner-text h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .wrap {
                flex-direction: column;
            }

            .left {
                order: 2;
            }

            .right {
                order: 1;
                min-height: 240px;
            }

            .form-row {
                flex-direction: column;
                gap: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="img/Logo.webp" alt="Logo UPTP">
    </div>

    <div class="wrap">
        <div class="left">
            <div class="card">
                <div class="header-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>

                <h2 class="title">Registro de Empleado</h2>
                <p class="subtitle">Completa el formulario para registrar al nuevo empleado</p>

                @if ($errors->any())
                    <div class="error-message">
                        <strong>Por favor corrige los siguientes errores:</strong>
                        <ul class="mt-1 ml-4 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" onsubmit="return validateForm(event)">
                    @csrf

                    @if (isset($roles) && $roles->isNotEmpty())
                        <div class="form-group">
                            <select name="id_rol" id="id_rol" class="select" required
                                onchange="toggleMasterKeyField()">
                                <option value="" disabled selected>Seleccione el rol correspondiente...</option>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id_rol }}" data-nombre="{{ strtolower($rol->nombre_rol) }}"
                                        {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>
                                        {{ $rol->nombre_rol }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-row">
                        <div class="form-group">
                            <input class="input" name="first_name" placeholder="Primer nombre" required
                                value="{{ old('first_name') }}">
                        </div>
                        <div class="form-group">
                            <input class="input" name="first_lastname" placeholder="Primer apellido" required
                                value="{{ old('first_lastname') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <input class="input" name="cedula" placeholder="Cédula de identidad" required maxlength="8"
                            inputmode="numeric" pattern="\d{1,8}"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,8)" value="{{ old('cedula') }}">
                    </div>

                    <div class="form-group">
                        <input id="telefono" class="input" name="telefono"
                            placeholder="Número telefónico (0000-0000000)" maxlength="12" inputmode="numeric"
                            pattern="\d{4}-\d{7}" oninput="formatTelefono(this)" value="{{ old('telefono') }}">
                    </div>

                    <div class="form-group">
                        <input class="input" type="email" name="email" placeholder="Correo electrónico" required
                            value="{{ old('email') }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <input class="input" type="password" name="password" placeholder="Contraseña" required>
                        </div>
                        <div class="form-group">
                            <input class="input" type="password" name="password_confirmation"
                                placeholder="Confirmar contraseña" required>
                        </div>
                    </div>

                    <h3 class="section-title">Preguntas de seguridad</h3>

                    @for ($i = 0; $i < 2; $i++)
                        <div class="form-row">
                            <div class="form-group">
                                <select name="security_questions_use[{{ $i }}][question]"
                                    id="q{{ $i }}_type" class="select" required>
                                    <option value="" disabled selected>Selecciona pregunta {{ $i + 1 }}
                                    </option>
                                    @php
                                        $questions = [
                                            '¿Cuál es el nombre de tu primera mascota?',
                                            '¿Cuál es el nombre de tu madre?',
                                            '¿En qué ciudad naciste?',
                                            '¿Cuál es tu comida favorita?',
                                            '¿Cuál fue tu primer colegio?',
                                            '¿Cuál es el segundo nombre de tu padre?',
                                        ];
                                    @endphp
                                    @foreach ($questions as $q)
                                        <option value="{{ $q }}"
                                            {{ old("security_questions_use.$i.question") == $q ? 'selected' : '' }}>
                                            {{ $q }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <input class="input" name="security_questions_use[{{ $i }}][answer]"
                                    placeholder="Respuesta {{ $i + 1 }}" required
                                    value="{{ old('security_questions_use.' . $i . '.answer') }}">
                            </div>
                        </div>
                    @endfor

                    @php
                        try {
                            $adminRol = \App\Models\Rol::where('nombre', 'Administrador')->first();
                            $systemHasAdmin = $adminRol ? $adminRol->usuarios()->count() > 0 : false;
                        } catch (\Throwable $e) {
                            $systemHasAdmin =
                                \App\Models\Usuario::join('perfil', 'user.id_perfil', '=', 'perfil.id_perfil')
                                    ->where('perfil.nombre_perfil', 'Administrador')
                                    ->count() > 0;
                        }
                    @endphp

                    <div class="form-group" id="master-key-group"
                        style="{{ $systemHasAdmin ? 'display: none;' : '' }}">
                        <input class="input" type="password" id="master_key_use" name="master_key_use"
                            placeholder="Llave Maestra (requerida para administrador)">
                    </div>

                    <button class="btn" type="submit">REGISTRARSE</button>
                    <button class="boton" type="button"
                        onclick="window.location='{{ route('admin.configuracion.empleados.index') }}'">
                        VOLVER AL SISTEMA
                    </button>
                </form>
            </div>
        </div>

        <div class="right">
            <div class="corner-text">
                <h1>Únete al Departamento</h1>
                <p>Crea tu cuenta y accede a todos los servicios y recursos de la universidad</p>
            </div>
        </div>
    </div>

    <script>
        function formatTelefono(el) {
            var v = el.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 4) {
                el.value = v.slice(0, 4) + '-' + v.slice(4);
            } else {
                el.value = v;
            }
        }

        function toggleMasterKeyField() {
            const selectRol = document.getElementById('id_rol');
            const masterKeyGroup = document.getElementById('master-key-group');
            const masterKeyInput = document.getElementById('master_key_use');

            if (!selectRol) return;

            const selectedOption = selectRol.options[selectRol.selectedIndex];
            const nombreRol = selectedOption.getAttribute('data-nombre') || '';

            if (nombreRol === 'administrador') {
                masterKeyGroup.style.display = 'block';
                masterKeyInput.setAttribute('required', 'required');
            } else {
                @if ($systemHasAdmin)
                    masterKeyGroup.style.display = 'none';
                    masterKeyInput.removeAttribute('required');
                    masterKeyInput.value = '';
                @endif
            }
        }

        function validateForm(e) {
            var q0 = document.getElementById('q0_type');
            var q1 = document.getElementById('q1_type');

            if (q0.value === q1.value) {
                e.preventDefault();
                Swal.fire({
                    title: 'Preguntas repetidas',
                    text: 'Por favor, seleccione dos preguntas de seguridad diferentes.',
                    icon: 'warning',
                    confirmButtonColor: '#b71c1c',
                    confirmButtonText: 'Entendido'
                });
                return false;
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            var t = document.getElementById('telefono');
            if (t && t.value) formatTelefono(t);

            toggleMasterKeyField();
        });
    </script>
</body>

</html>
