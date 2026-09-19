<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página no encontrada - UPTP J.J. Montilla</title>
    <meta name="description" content="Página no encontrada - Bienestar Estudiantil UPTP Juan de Jesús Montilla">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            position: relative;
            padding: 20px;
        }

        /* Patrón de fondo animado */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle, rgba(183, 28, 28, 0.1) 1px, transparent 1px),
                radial-gradient(circle, rgba(183, 28, 28, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            background-position: 0 0, 25px 25px;
            animation: moveBackground 20s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes moveBackground {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(50px, 50px);
            }
        }

        /* Partículas flotantes */
        .particle {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .particle:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 10%;
            left: 10%;
            background: rgba(183, 28, 28, 0.15);
            animation: float1 15s infinite ease-in-out;
        }

        .particle:nth-child(2) {
            width: 40px;
            height: 40px;
            top: 60%;
            right: 10%;
            background: rgba(255, 255, 255, 0.1);
            animation: float2 18s infinite ease-in-out;
        }

        .particle:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 15%;
            left: 15%;
            background: rgba(183, 28, 28, 0.1);
            animation: float3 20s infinite ease-in-out;
        }

        .particle:nth-child(4) {
            width: 50px;
            height: 50px;
            top: 20%;
            right: 20%;
            background: rgba(255, 255, 255, 0.08);
            animation: float4 17s infinite ease-in-out;
        }

        @keyframes float1 {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(-20px, -30px) scale(1.1);
            }
        }

        @keyframes float2 {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(20px, 30px) scale(0.9);
            }
        }

        @keyframes float3 {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(30px, -20px) scale(1.15);
            }
        }

        @keyframes float4 {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(-25px, 25px) scale(0.95);
            }
        }

        .error-container {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 100%;
            max-width: 600px;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 60px 40px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(183, 28, 28, 0.1),
                inset 0 0 0 1px rgba(255, 255, 255, 0.8);
            animation: fadeInUp 0.8s ease-out;
            position: relative;
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

        /* Logo animado */
        .logo-container {
            margin-bottom: 30px;
            animation: bounceIn 1s ease-out 0.3s both;
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

        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            position: absolute;
        }

        .logo::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 28px;
            z-index: -1;
            opacity: 0.5;
            filter: blur(10px);
        }

        .logo img {
            width: 100%;

            display: block;
        }

        .logo-text {
            font-size: 48px;
            font-weight: 900;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Número 404 */
        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
            animation: glitch 3s infinite;
            position: relative;
        }

        @keyframes glitch {

            0%,
            100% {
                transform: translate(0);
            }

            20% {
                transform: translate(-1px, 1px);
            }

            40% {
                transform: translate(-1px, -1px);
            }

            60% {
                transform: translate(1px, 1px);
            }

            80% {
                transform: translate(1px, -1px);
            }
        }

        .error-title {
            font-size: 32px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 16px;
            animation: fadeIn 1s ease-out 0.5s both;
        }

        .error-message {
            font-size: 18px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 40px;
            animation: fadeIn 1s ease-out 0.7s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Botones */
        .button-group {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.9s both;
        }

        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(183, 28, 28, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #1a1a1a;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .btn i {
            font-size: 18px;
        }

        /* Ilustración decorativa */
        .illustration {
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .illustration svg {
            width: 200px;
            height: 200px;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.1));
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .error-card {
                padding: 40px 24px;
                border-radius: 24px;
            }

            .logo {
                width: 80px;
                height: 80px;
            }

            .logo-text {
                font-size: 38px;
            }

            .error-code {
                font-size: 80px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-message {
                font-size: 16px;
            }

            .button-group {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .illustration svg {
                width: 150px;
                height: 150px;
            }
        }

        @media (max-width: 480px) {
            .error-card {
                padding: 32px 20px;
            }

            .error-code {
                font-size: 64px;
            }

            .error-title {
                font-size: 20px;
            }

            .error-message {
                font-size: 14px;
            }

            .btn {
                padding: 12px 24px;
                font-size: 14px;
            }
        }
    </style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Partículas decorativas -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <div class="error-container">
        <div class="error-card">
            <!-- Logo UPTP -->
            <div class="logo-container">
                <div class="logo">
                    <span class="logo-text"><img src="{{ asset('img/Logo.webp') }}" alt="Logo UPTP"></span>
                </div>
            </div>

            <!-- Ilustración -->
            <div class="illustration">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="80" fill="#f1f5f9" opacity="0.5" />
                    <path d="M70 90 Q75 85 80 90 T90 90" stroke="#b71c1c" stroke-width="4" fill="none"
                        stroke-linecap="round" />
                    <path d="M110 90 Q115 85 120 90 T130 90" stroke="#b71c1c" stroke-width="4" fill="none"
                        stroke-linecap="round" />
                    <path d="M70 130 Q100 145 130 130" stroke="#b71c1c" stroke-width="4" fill="none"
                        stroke-linecap="round" />
                    <circle cx="75" cy="90" r="3" fill="#b71c1c" />
                    <circle cx="125" cy="90" r="3" fill="#b71c1c" />
                </svg>
            </div>

            <!-- Código de error -->
            <div class="error-code">403</div>

            <!-- Título y mensaje -->
            <h1 class="error-title">¡Oops! Página restringida</h1>
            <p class="error-message">
                Para la página que buscas no tienes los permisos suficientes para ingresar.
                No te preocupes, puedes volver al inicio o regresar a la página anterior.
            </p>

            <!-- Botones de acción -->
            <div class="button-group">
                <a href="#" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 primary" onclick="goHome(event)">
                    <i class="fas fa-home"></i>
                    Ir al inicio
                </a>
                <a href="#" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 secondary" onclick="goBack(event)">
                    <i class="fas fa-arrow-left"></i>
                    Volver atrás
                </a>
            </div>
        </div>
    </div>

    <script>
        function goHome(e) {
            e.preventDefault();
            window.location.href = "{{ route('home') }}";
        }

        function goBack(e) {
            e.preventDefault();
            window.history.back();
        }
    </script>
</body>

</html>
