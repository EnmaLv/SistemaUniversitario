{{--
    Estilos compartidos del flujo de consulta (Paso 1, 2 y 3).
    Se apoyan en las variables del layout: --bg-card, --border-color, --text-main.
--}}
@once
    <style>
        [x-cloak] { display: none !important; }

        /* ── Superficies ─────────────────────────────────────────── */
        .cx-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
        }

        .cx-divider { border-top: 1px solid var(--border-color); }

        .cx-soft { background-color: rgba(127, 127, 127, 0.06); }

        /* ── Texto ───────────────────────────────────────────────── */
        .cx-title {
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text-main);
        }

        .cx-text { color: var(--text-main); }
        .cx-muted { color: var(--text-main); opacity: .62; }
        .cx-faint { color: var(--text-main); opacity: .45; }

        .cx-label {
            display: block;
            font-size: .8125rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: .375rem;
        }

        .cx-req { color: #e11d48; margin-left: 2px; }
        .cx-opt { font-weight: 500; font-size: .75rem; opacity: .55; margin-left: .25rem; }

        .cx-hint { font-size: .75rem; margin-top: .375rem; color: var(--text-main); opacity: .6; }

        .cx-error {
            display: flex;
            align-items: flex-start;
            gap: .375rem;
            margin-top: .375rem;
            font-size: .75rem;
            font-weight: 600;
            color: #e11d48;
        }

        /* ── Campos ──────────────────────────────────────────────── */
        .cx-input {
            width: 100%;
            font-size: .875rem;
            font-weight: 500;
            color: var(--text-main);
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: .75rem;
            padding: .625rem .875rem;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .cx-input::placeholder { color: var(--text-main); opacity: .4; }

        .cx-input:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, .18);
        }

        .cx-input:disabled { opacity: .5; cursor: not-allowed; }
        .cx-input.is-invalid { border-color: #e11d48; }
        .cx-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(225, 29, 72, .15); }

        textarea.cx-input { resize: vertical; line-height: 1.55; }

        /* ── Botones ─────────────────────────────────────────────── */
        .cx-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .625rem 1.125rem;
            border-radius: .75rem;
            font-size: .875rem;
            font-weight: 600;
            white-space: nowrap;
            transition: background-color .15s ease, opacity .15s ease, transform .1s ease;
        }

        .cx-btn:focus-visible,
        .cx-link:focus-visible {
            outline: 2px solid #0284c7;
            outline-offset: 2px;
        }

        .cx-btn:active { transform: scale(.98); }

        .cx-btn-primary { background-color: #0284c7; color: #fff; }
        .cx-btn-primary:hover { background-color: #0369a1; }

        .cx-btn-ghost {
            color: var(--text-main);
            border: 1px solid var(--border-color);
            background-color: transparent;
        }
        .cx-btn-ghost:hover { background-color: rgba(127, 127, 127, .08); }

        .cx-link {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            font-size: .8125rem;
            font-weight: 600;
            color: #0284c7;
            border-radius: .375rem;
        }
        .cx-link:hover { text-decoration: underline; text-underline-offset: 3px; }

        /* ── Barra de acciones fija ──────────────────────────────── */
        .cx-actionbar {
            position: sticky;
            bottom: 1rem;
            z-index: 30;
            margin-top: 1.5rem;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: 0 12px 32px -12px rgba(15, 23, 42, .28);
        }

        /* ── Listas desplegables (buscadores) ────────────────────── */
        .cx-dropdown {
            position: absolute;
            z-index: 40;
            left: 0;
            right: 0;
            margin-top: .375rem;
            max-height: 16rem;
            overflow-y: auto;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: .75rem;
            box-shadow: 0 16px 40px -16px rgba(15, 23, 42, .35);
            padding: .25rem;
        }

        .cx-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            width: 100%;
            text-align: left;
            padding: .5rem .75rem;
            border-radius: .5rem;
            font-size: .8125rem;
            color: var(--text-main);
            cursor: pointer;
        }

        .cx-option[data-activo="true"],
        .cx-option:hover { background-color: rgba(2, 132, 199, .1); }

        /* ── Ficha del paciente ──────────────────────────────────── */
        .cx-ficha dt {
            font-size: .75rem;
            font-weight: 500;
            color: var(--text-main);
            opacity: .55;
        }

        .cx-ficha dd {
            font-size: .875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-top: .125rem;
            overflow-wrap: anywhere;
        }

        @media (prefers-reduced-motion: reduce) {
            .cx-btn, .cx-input, .cx-btn:active { transition: none; transform: none; }
        }
    </style>
@endonce
