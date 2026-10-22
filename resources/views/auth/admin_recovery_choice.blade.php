<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperación Administrador - UPTP</title>
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
            max-width: 680px;
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);
            animation: bounceIn 0.8s ease-out 0.2s both;
        }

        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
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
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Badge de verificación */
        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ecfdf5;
            color: #065f46;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            margin: 16px 0 32px;
            animation: fadeIn 0.8s ease-out 0.7s both;
        }

        .verified-badge svg {
            width: 18px;
            height: 18px;
            fill: #10b981;
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
        }

        .progress-step.completed {
            background: #10b981;
        }

        .progress-step.active {
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            width: 32px;
            border-radius: 6px;
        }

        /* Opciones de recuperación */
        .options-container {
            animation: fadeIn 0.8s ease-out 0.9s both;
        }

        .option-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .option-card:hover {
            border-color: #cbd5e1;
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        }

        .option-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .option-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(183, 28, 28, 0.2);
        }

        .option-icon svg {
            width: 24px;
            height: 24px;
            fill: white;
        }

        .option-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .option-description {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        /* Form groups */
        .form-group {
            margin-bottom: 14px;
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
            padding: 12px 12px 12px 44px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .input:focus {
            outline: none;
            border-color: #b71c1c;
            box-shadow: 0 0 0 3px rgba(183, 28, 28, 0.1);
        }

        .input::placeholder {
            color: #94a3b8;
        }

        /* Botones */
        .btn {
            width: 100%;
            background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(183, 28, 28, 0.25);
            letter-spacing: 0.3px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(183, 28, 28, 0.35);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Separador */
        .divider {
            position: relative;
            text-align: center;
            margin: 28px 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
        }

        .divider span {
            position: relative;
            background: rgba(255, 255, 255, 0.98);
            padding: 0 16px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 600;
        }

        /* Link de cancelar */
        .cancel-link {
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

        .cancel-link:hover {
            color: #b71c1c;
        }

        .cancel-link svg {
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

            .option-card {
                padding: 20px;
            }

            .option-title {
                font-size: 16px;
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

            .option-card {
                padding: 16px;
            }

            .option-icon {
                width: 40px;
                height: 40px;
            }

            .option-icon svg {
                width: 20px;
                height: 20px;
            }

            .option-title {
                font-size: 15px;
            }

            .option-description {
                font-size: 13px;
            }

            .input {
                padding: 10px 10px 10px 40px;
                font-size: 14px;
            }

            .btn {
                padding: 12px 16px;
                font-size: 14px;
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
                    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                </svg>
            </div>

            <h2 class="title">Verificación exitosa</h2>
            <p class="subtitle">
                Has validado correctamente tus preguntas de seguridad. Ahora puedes elegir qué deseas restablecer
            </p>

            <div style="text-align: center;">
                <div class="verified-badge">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    Identidad verificada
                </div>
            </div>
        </div>

        <!-- Indicador de progreso -->
        <div class="progress-indicator">
            <div class="progress-step completed"></div>
            <div class="progress-step completed"></div>
            <div class="progress-step active"></div>
        </div>

        <!-- Opciones de recuperación -->
        <div class="options-container">
            <!-- Opción 1: Restablecer Contraseña -->
            <div class="option-card">
                <div class="option-header">
                    <div class="option-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                        </svg>
                    </div>
                    <h3 class="option-title">Restablecer Contraseña</h3>
                </div>
                <p class="option-description">
                    Crea una nueva contraseña para acceder a tu cuenta
                </p>

                <form method="POST" action="{{ route('password.recover.reset_password') }}">
                    @csrf
                    <div class="form-group">
                        <div class="input-wrapper">
                            <svg class="input-icon">...</svg>
                            <input 
                                class="input" 
                                type="password"
                                name="password" 
                                placeholder="Nueva contraseña" 
                                required
                            >
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-wrapper">
                            <svg class="input-icon">...</svg>
                            <input 
                                class="input" 
                                type="password"
                                name="password_confirmation" 
                                placeholder="Confirmar contraseña" 
                                required
                            >
                        </div>
                    </div>
                    <button class="btn" type="submit">
                        Restablecer Contraseña
                    </button>
                </form>
            </div>

            <div class="divider">
                <span>O TAMBIÉN</span>
            </div>

            <!-- Opción 2: Restablecer Llave Maestra -->
            <div class="option-card">
                <div class="option-header">
                    <div class="option-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.65 10C11.7 7.31 8.9 5.5 5.77 6.12c-2.29.46-4.15 2.29-4.63 4.58C.32 14.57 3.26 18 7 18c2.61 0 4.83-1.67 5.65-4H17v2c0 1.1.9 2 2 2s2-.9 2-2v-2c1.1 0 2-.9 2-2s-.9-2-2-2h-8.35zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="option-title">Restablecer Llave Maestra</h3>
                </div>
                <p class="option-description">
                    Define una nueva llave maestra para recuperación administrativa
                </p>

                <form method="POST" action="{{ route('password.recover.reset_masterkey') }}">
                    @csrf
                    <div class="form-group">
                        <div class="input-wrapper">
                            <svg class="input-icon">...</svg>
                            <input 
                                class="input" 
                                type="password"
                                name="master_key_use" 
                                placeholder="Nueva llave maestra" 
                                required
                            >
                        </div>
                    </div>
                    <button class="btn" type="submit">
                        Restablecer Llave Maestra
                    </button>
                </form>
            </div>
        </div>

        <a href="{{ route('login') }}" class="cancel-link">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
            </svg>
            Cancelar proceso
        </a>
    </div>
</body>
</html>