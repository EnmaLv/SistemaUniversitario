@props(['id', 'tipo' => null])

@php
    // Detectar si el contexto actual pertenece a salud o psicología
    $moduloActual = strtolower($tipo ?? (session('modulo_activo') ?? (request()->segment(2) ?? '')));

    $esSaludOPsicologia =
        in_array($moduloActual, ['salud', 'psicologia', 'psicología']) || request()->is('*salud*', '*psicologia*');

    // Configuración de colores (Azul para Salud/Psicología, Rojo para el resto)
    $bgHighlight = $esSaludOPsicologia ? 'rgba(2, 132, 199, 0.08)' : 'rgba(239, 68, 68, 0.02)';
    $borderHighlight = $esSaludOPsicologia ? '#0284c7' : '#ef4444';
    $borderHighlightDark = $esSaludOPsicologia ? '#38bdf8' : '#f87171';
@endphp

<tr {{ $attributes->merge([
    'class' => 'fila-tabla cursor-pointer hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors',
    'style' => "--highlight-bg: {$bgHighlight}; --highlight-border: {$borderHighlight}; --highlight-border-dark: {$borderHighlightDark};",
]) }}
    onclick="toggleAccionesTabla(event, {{ $id }})">
    {{ $slot }}
</tr>

@once
    @push('styles')
        <style>
            .acciones-trigger {
                transition: opacity .15s ease, transform .15s ease;
            }

            .acciones-panel {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .5rem;
                max-width: 0;
                width: 0;
                opacity: 0;
                overflow: hidden;
                pointer-events: none;
                visibility: hidden;
                transition: max-width .25s ease, opacity .2s ease;
            }

            .fila-tabla.is-open .acciones-trigger {
                opacity: 0;
                pointer-events: none;
                position: absolute;
            }

            .fila-tabla.is-open .acciones-panel {
                max-width: 200px;
                width: auto;
                opacity: 1;
                pointer-events: auto;
                visibility: visible;
            }

            /* Resaltado dinámico según el módulo */
            .fila-tabla.is-open {
                background-color: var(--highlight-bg) !important;
            }

            /* Borde lateral izquierdo dinámico */
            .fila-tabla.is-open td:first-child {
                box-shadow: inset 4px 0 0 0 var(--highlight-border);
                transition: box-shadow 0.2s ease;
            }

            .dark .fila-tabla.is-open td:first-child {
                box-shadow: inset 4px 0 0 0 var(--highlight-border-dark);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            if (typeof filaAccionesAbierta === 'undefined') {
                var filaAccionesAbierta = null;

                function toggleAccionesTabla(event, id) {
                    const filaClickeada = event.currentTarget;
                    const yaEstabaAbierta = filaClickeada.classList.contains('is-open');

                    if (filaAccionesAbierta && filaAccionesAbierta !== filaClickeada) {
                        filaAccionesAbierta.classList.remove('is-open');
                    }

                    if (yaEstabaAbierta) {
                        filaClickeada.classList.remove('is-open');
                        filaAccionesAbierta = null;
                    } else {
                        filaClickeada.classList.add('is-open');
                        filaAccionesAbierta = filaClickeada;
                    }
                }

                document.addEventListener('click', function(event) {
                    if (filaAccionesAbierta && !filaAccionesAbierta.contains(event.target)) {
                        filaAccionesAbierta.classList.remove('is-open');
                        filaAccionesAbierta = null;
                    }
                });
            }
        </script>
    @endpush
@endonce
