<!doctype html>
<html lang="es" data-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Verificar llave maestra</title>
    <style>
        :root {
            --bg-page: #0d0708;
            --bg-card: #160c0e;
            --bg-input: #12090b;
            --border: #5c2028;
            --border-soft: #271418;
            --text: #f8fafc;
            --muted: #d1b8bc;
            --primary: #991b1b;
            --primary-hover: #b91c1c;
            --danger-bg: #3b1116;
            --danger-text: #fecaca;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 15%, rgba(153, 27, 27, .18), transparent 32rem),
                radial-gradient(circle at 85% 85%, rgba(92, 32, 40, .18), transparent 30rem),
                var(--bg-page);
            font-family: "Figtree", "Segoe UI", Arial, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 460px;
            padding: 32px;
            background: var(--bg-card);
            border: 1px solid var(--border-soft);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .42);
        }

        .icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            color: #fee2e2;
            background: linear-gradient(135deg, #7f1d1d, var(--primary));
            border: 1px solid #b91c1c;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(127, 29, 29, .3);
        }

        .icon svg {
            width: 32px;
            height: 32px;
            fill: currentColor;
        }

        h1 {
            margin: 0;
            color: var(--text);
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .description {
            margin: 10px 0 26px;
            color: var(--muted);
            font-size: .95rem;
            line-height: 1.55;
        }

        .error {
            margin-bottom: 18px;
            padding: 12px 14px;
            color: var(--danger-text);
            background: var(--danger-bg);
            border: 1px solid #7f1d1d;
            border-radius: 12px;
            font-size: .875rem;
            line-height: 1.4;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text);
            font-size: .875rem;
            font-weight: 700;
        }

        .input-group {
            display: flex;
            align-items: center;
            overflow: hidden;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 12px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .input-group:focus-within {
            border-color: var(--primary-hover);
            box-shadow: 0 0 0 3px rgba(185, 28, 28, .22);
        }

        .input-group > span {
            display: inline-flex;
            padding: 0 14px;
            color: #fca5a5;
        }

        input {
            width: 100%;
            min-width: 0;
            padding: 13px 12px 13px 0;
            color: var(--text);
            background: transparent;
            border: 0;
            outline: 0;
            font-size: .95rem;
        }

        input::placeholder {
            color: #98777c;
        }

        .toggle {
            padding: 10px 14px;
            color: #fca5a5;
            background: transparent;
            border: 0;
            cursor: pointer;
        }

        .submit {
            width: 100%;
            margin-top: 22px;
            padding: 13px 18px;
            color: #fff;
            background: var(--primary);
            border: 1px solid #b91c1c;
            border-radius: 12px;
            box-shadow: 0 8px 18px rgba(127, 29, 29, .25);
            cursor: pointer;
            font-size: .95rem;
            font-weight: 800;
            transition: background-color .2s ease, transform .2s ease;
        }

        .submit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .secure-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 22px;
            color: #a9878c;
            font-size: .78rem;
        }

        .secure-note svg {
            width: 14px;
            height: 14px;
            fill: #c2414a;
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .card {
                padding: 24px 20px;
                border-radius: 20px;
            }
        }
    </style>
</head>

<body>
    <main class="card">
        <div class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <path d="M17 8h-1V6a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2Zm-7-2a2 2 0 0 1 4 0v2h-4V6Zm5 9h-2v2h-2v-2H9v-2h2v-2h2v2h2v2Z"/>
            </svg>
        </div>

        <h1>Verificar llave maestra</h1>
        <p class="description">Introduce la llave maestra para completar el acceso de administrador.</p>

        @if ($errors->any())
            <div class="error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.master_key.verify') }}">
            @csrf
            <label for="master_key">Llave maestra</label>
            <div class="input-group">
                <span aria-hidden="true">
                    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;">
                        <path d="M14.5 3a5.5 5.5 0 0 0-4.8 8.18L4 16.88V21h4.12l1.5-1.5H12V17h2.5l1.72-1.72A5.5 5.5 0 1 0 14.5 3Zm0 3a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Z"/>
                    </svg>
                </span>
                <input id="master_key" name="master_key" type="password" placeholder="Introduce tu llave" required autofocus>
                <button class="toggle" type="button" id="toggleMasterKey" aria-label="Mostrar llave">
                    <span id="toggleIcon">◉</span>
                </button>
            </div>
            <button class="submit" type="submit">Verificar acceso</button>
        </form>

        <div class="secure-note">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.25 3.4 9.65 8 11 4.6-1.35 8-5.75 8-11V5l-8-3Zm0 4 4 1.5V11c0 3.54-2.08 6.7-4 7.83C10.08 17.7 8 14.54 8 11V7.5L12 6Z"/></svg>
            Acceso protegido por seguridad del sistema
        </div>
    </main>

    <script>
        document.getElementById('toggleMasterKey').addEventListener('click', function () {
            const input = document.getElementById('master_key');
            const icon = document.getElementById('toggleIcon');
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            icon.textContent = visible ? '◌' : '◉';
            this.setAttribute('aria-label', visible ? 'Ocultar llave' : 'Mostrar llave');
        });
    </script>
</body>

</html>
