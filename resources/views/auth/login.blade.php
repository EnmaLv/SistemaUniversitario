<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - UPTP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            position: relative;
            min-height: 100vh;
            background: #f5f5f5;
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

        .left {
            flex: 1;
            min-width: 0;
            padding: 40px 24px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .right {
            flex: 1;
            min-width: 0;
            background: linear-gradient(135deg, #000000 0%, #000000 100%);
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
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("img/unnamed.webp");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            opacity: 0.5;
        }

        .card {
            width: 100%;
            max-width: 420px;
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


        .user-icon {
            width: 96px;
            height: 96px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(183, 28, 28, 0.25);
        }

        .user-icon svg {
            width: 52px;
            height: 52px;
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
            margin-bottom: 32px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input {
            width: 100%;
            padding: 14px 20px;
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
            color: #999;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c62828;
            animation: shake 0.4s ease;
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

        .forgot {
            display: block;
            color: #b71c1c;
            margin: 12px 0 24px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-align: right;
            transition: color 0.2s ease;
        }

        .forgot:hover {
            color: #8b0000;
            text-decoration: underline;
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
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(183, 28, 28, 0.3);
            letter-spacing: 0.5px;
        }

        .boton {
            width: 100%;
            background: #ffffff;
            color: #1a1a1a;
            padding: 14px 24px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            letter-spacing: 0.5px;
            margin-top: 24px;
        }

        .boton:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            border-color: rgba(0, 0, 0, 0.12);
        }


        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(183, 28, 28, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .corner-text {
            position: relative;
            z-index: 1;
            max-width: 500px;
            animation: fadeIn 1s ease-out 0.3s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .corner-text h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
            line-height: 1.2;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .corner-text p {
            font-size: 18px;
            line-height: 1.6;
            opacity: 0.95;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .right {
                flex: 1;
            }

            .corner-text h1 {
                font-size: 32px;
            }

            .corner-text p {
                font-size: 16px;
            }
        }

        @media (max-width: 768px) {
            .wrap {
                flex-direction: column;
            }

            .logo {
                top: 16px;
                left: 16px;
                padding: 10px;
            }

            .logo img {
                width: 40px;
                height: 40px;
            }

            .left {
                order: 2;
                min-height: auto;
                padding: 32px 20px 40px;
            }

            .right {
                order: 1;
                min-height: 280px;
                padding: 80px 24px 32px;
                align-items: center;
                justify-content: center;
            }

            .corner-text {
                text-align: center;
                max-width: 100%;
            }

            .corner-text h1 {
                font-size: 28px;
            }

            .corner-text p {
                font-size: 15px;
            }

            .card {
                max-width: 100%;
            }

            .title {
                font-size: 28px;
            }

            .user-icon {
                width: 80px;
                height: 80px;
                margin-bottom: 20px;
            }

            .user-icon svg {
                width: 44px;
                height: 44px;
            }
        }

        @media (max-width: 480px) {
            .left {
                padding: 24px 16px 32px;
            }

            .right {
                min-height: 240px;
                padding: 70px 20px 24px;
            }

            .corner-text h1 {
                font-size: 24px;
            }

            .corner-text p {
                font-size: 14px;
            }

            .title {
                font-size: 24px;
            }

            .input {
                padding: 12px 16px;
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
        <img src="img/Logo.webp" alt="Logo UPTP">
    </div>

    <div class="wrap">
        <div class="left">
            <div class="card">
                <div class="user-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8V22h19.2v-2.8c0-3.2-6.4-4.8-9.6-4.8z" />
                    </svg>
                </div>

                <h2 class="title">Bienvenido</h2>
                <p class="subtitle">Ingresa tus credenciales para continuar</p>

                @if ($errors->any())
                    <div role="alert" style="color:#b71c1c;margin-bottom:12px">{{ $errors->first() }}</div>
                @elseif (session('status'))
                    <div role="status" style="color:#166534;margin-bottom:12px">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <input class="input" type="email" name="email" placeholder="Correo electrónico" required
                            autofocus>
                    </div>

                    <div class="form-group">
                        <input class="input" type="password" name="password" placeholder="Contraseña" required>
                    </div>

                    <a class="forgot" href="{{ route('password.recover.email') }}">¿Olvidaste tu contraseña?</a>

                    <button class="btn" type="submit">INICIAR SESIÓN</button>
                    <button class="boton" type="button" onclick="window.location='{{ url('/') }}'">VOLVER AL
                        INICIO</button>
                </form>
            </div>
        </div>

        <div class="right">
            <div class="corner-text">
                <h1 style="text-align: center">Sistema de Bienestar Estudiantil</h1>
                <p style="text-align: center">Accede a todas las herramientas y recursos de tu departamento en un solo
                    lugar</p>
            </div>
        </div>
    </div>
</body>

</html>
