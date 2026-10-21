<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preguntas de seguridad - UPTP</title>
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
            overflow-x: hidden;
            padding: 20px;
        }

        /* Patrón de fondo */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("{{ asset('img/unnamed.webp') }}");
            opacity: 0.5;
            pointer-events: none;
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
            max-width: 720px;
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

        /* Header con icono */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }

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
            animation: fadeIn 0.8s ease-out 0.4s both;
        }

        .subtitle {
            font-size: 15px;
            color: #64748b;
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
            margin-bottom: 24px;
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

        /* Preguntas */
        .questions-container {
            animation: fadeIn 0.8s ease-out 0.8s both;
        }

        .question-item {
            margin-bottom: 24px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .question-item:hover {
            border-color: #cbd5e1;
            background: #fff;
        }

        .question-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(183, 28, 28, 0.2);
        }

        .question-label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
            font-size: 15px;
            line-height: 1.5;
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
            background: white;
        }

        .input:focus {
            outline: none;
            border-color: #b71c1c;
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
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(183, 28, 28, 0.3);
            letter-spacing: 0.5px;
            margin-top: 16px;
            animation: fadeIn 0.8s ease-out 1s both;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(183, 28, 28, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Indicador de progreso */
        .progress-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 32px;
            animation: fadeIn 0.8s ease-out 0.6s both;
        }

        .progress-step {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e1;
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            width: 32px;
            border-radius: 6px;
        }

        /* Link de regreso */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
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

            .question-item {
                padding: 16px;
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

            .question-item {
                padding: 14px;
                margin-bottom: 16px;
            }

            .question-label {
                font-size: 14px;
            }

            .input {
                padding: 12px 12px 12px 40px;
                font-size: 14px;
            }

            .btn {
                padding: 14px 20px;
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
        <!-- Header con icono -->
        <div class="header">
            <div class="icon-header">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z" />
                </svg>
            </div>

            <h2 class="title">Preguntas de seguridad</h2>
            <p class="subtitle">
                Para recuperar tu acceso, necesitamos que respondas correctamente tus preguntas de seguridad
            </p>
        </div>

        <!-- Indicador de progreso -->
        <div class="progress-indicator">
            <div class="progress-step"></div>
            <div class="progress-step active"></div>
            <div class="progress-step"></div>
        </div>

        <!-- Error message (visible solo si hay error) -->
        <div class="error-message" style="display: none;">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
            <span>Una o más respuestas son incorrectas. Verifica e intenta nuevamente.</span>
        </div>

        @if ($errors->any())
            <div role="alert" style="color:#b71c1c;margin-bottom:12px;font-weight:600">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.recover.verify') }}">
            @csrf
            <div class="questions-container">
                @foreach ($questions as $i => $q)
                    <div class="question-item">
                        <div class="question-number">{{ $i + 1 }}</div>
                        <label class="question-label">{{ $q['question'] }}</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24">
                                <!-- Icono contextual según pregunta -->
                            </svg>
                            <input class="input" type="text" name="answers[{{ $i }}]"
                                placeholder="Escribe tu respuesta aquí" required
                                @if ($i === 0) autofocus @endif>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="btn" type="submit">Verificar respuestas</button>
        </form>

        <a href="{{ route('password.recover.email') }}" class="back-link">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
            </svg>
            Volver atrás
        </a>
    </div>
</body>

</html>
