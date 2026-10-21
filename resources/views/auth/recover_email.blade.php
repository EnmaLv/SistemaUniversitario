<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar acceso - UPTP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #000000 0%, #000000 100%);
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        /* Patrón de fondo */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("{{ asset('img/unnamed.webp') }}");
            opacity: 0.5;
            background-size: cover;
            background-position: center;
        }

        /* Logo flotante */
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
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo img {
            width: 48px;
            height: 48px;
            display: block;
        }

        /* Contenedor principal */
        .box {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Icono de header */
        .icon-header {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(183, 28, 28, 0.25);
            animation: bounceIn 0.8s ease-out 0.2s both;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .icon-header svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        /* Título */
        .title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1a1a1a;
            text-align: center;
            animation: fadeIn 0.8s ease-out 0.4s both;
        }

        .subtitle {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 32px;
            text-align: center;
            line-height: 1.5;
            animation: fadeIn 0.8s ease-out 0.6s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Mensaje de error */
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c62828;
            animation: shake 0.4s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-8px);
            }

            75% {
                transform: translateX(8px);
            }
        }

        .error-message svg {
            width: 20px;
            height: 20px;
            fill: #c62828;
            flex-shrink: 0;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 20px;
            position: relative;
            animation: fadeIn 0.8s ease-out 0.8s both;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            fill: #94a3b8;
            pointer-events: none;
        }

        .input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .input:focus {
            outline: none;
            border-color: #b71c1c;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(183, 28, 28, 0.1);
        }

        .input::placeholder {
            color: #94a3b8;
        }

        /* Botón */
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
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(183, 28, 28, 0.3);
            letter-spacing: 0.5px;
            margin-top: 8px;
            animation: fadeIn 0.8s ease-out 1s both;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(183, 28, 28, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Link de regreso */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s ease;
            animation: fadeIn 0.8s ease-out 1.2s both;
        }

        .back-link:hover {
            color: #b71c1c;
        }

        .back-link svg {
            width: 14px;
            height: 14px;
            vertical-align: middle;
            margin-right: 6px;
            fill: currentColor;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .logo {
                top: 16px;
                left: 16px;
                padding: 10px;
            }

            .logo img {
                width: 40px;
                height: 40px;
            }

            .box {
                padding: 40px 28px;
                border-radius: 20px;
            }

            .icon-header {
                width: 70px;
                height: 70px;
                margin-bottom: 20px;
            }

            .icon-header svg {
                width: 35px;
                height: 35px;
            }

            .title {
                font-size: 24px;
            }

            .subtitle {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .box {
                padding: 32px 24px;
            }

            .title {
                font-size: 22px;
            }

            .input {
                padding: 12px 12px 12px 40px;
                font-size: 14px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="{{ asset('img/Logo.webp') }}" alt="Logo UPTP">
    </div>

    <div class="box">
        <!-- Icono de header -->
        <div class="icon-header">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm3 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" />
            </svg>
        </div>

        <h2 class="title">Recuperar acceso</h2>
        <p class="subtitle">
            Ingresa tu correo electrónico registrado y te enviaremos las instrucciones para recuperar tu contraseña
        </p>

        <!-- Error message (visible solo si hay error) -->
        <div class="error-message" style="display: none;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
            <span>El correo electrónico no está registrado en el sistema</span>
        </div>

        @if ($errors->any())
            <div role="alert" style="color:#b71c1c;margin-bottom:12px;font-weight:600">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.recover.post_email') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <div class="input-wrapper">
                    <svg class="input-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                    </svg>
                    <input class="input" type="email" name="email" placeholder="ejemplo@correo.com" required
                        autofocus>
                </div>
            </div>

            <button class="btn" type="submit">
                Enviar instrucciones
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
            </svg>
            Volver al login
        </a>
    </div>
</body>

</html>
