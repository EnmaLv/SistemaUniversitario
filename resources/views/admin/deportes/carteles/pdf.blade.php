@php
    $vigencia = $horario->vigencia();
    $sede = $horario->calendario?->sede?->nombre_sed;
    $cupoTotal = $horario->cupo_maximo_hde;
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { size: letter portrait; margin: 14mm; }
        html, body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; color: #111827; }
        .marco { border: 6px solid #7f1d1d; border-radius: 18px; padding: 26px 30px; height: 238mm; position: relative; }
        .institucion { text-align: center; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; color: #7f1d1d; font-weight: bold; }
        .subtitulo { text-align: center; font-size: 20px; margin-top: 18px; text-transform: uppercase; letter-spacing: 2px; color: #374151; }
        .titulo { text-align: center; font-size: 50px; line-height: 1.1; font-weight: bold; margin: 12px 0 8px; color: #7f1d1d; text-transform: uppercase; }
        .modalidad { text-align: center; font-size: 20px; font-weight: bold; color: #111827; margin-bottom: 6px; }
        .descripcion { text-align: center; font-size: 15px; color: #374151; margin: 6px 30px 0; }
        .datos { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-top: 22px; }
        .datos td { padding: 12px 16px; vertical-align: middle; }
        .etiqueta { width: 32%; background: #7f1d1d; color: #ffffff; font-size: 17px; font-weight: bold; text-transform: uppercase; border-radius: 10px 0 0 10px; }
        .valor { background: #fef2f2; font-size: 24px; font-weight: bold; border-radius: 0 10px 10px 0; }
        .inscripcion { margin-top: 22px; border: 3px dashed #7f1d1d; border-radius: 14px; padding: 16px 20px; text-align: center; }
        .inscripcion h3 { margin: 0 0 8px; font-size: 22px; text-transform: uppercase; color: #7f1d1d; }
        .inscripcion p { margin: 4px 0; font-size: 17px; }
        .pie { position: absolute; bottom: 18px; left: 30px; right: 30px; text-align: center; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="marco">
        <div class="institucion">Coordinación de Deportes · Bienestar Estudiantil</div>
        <div class="subtitulo">{{ $subtitulo }}</div>
        <div class="titulo">{{ $titulo }}</div>
        @if (!empty($modalidad))
            <div class="modalidad">Modalidad {{ $modalidad }}</div>
        @endif
        @if (!empty($descripcion))
            <div class="descripcion">{{ \Illuminate\Support\Str::limit($descripcion, 260) }}</div>
        @endif

        <table class="datos">
            <tr>
                <td class="etiqueta">Lugar</td>
                <td class="valor">{{ $horario->espacio?->nombre_epd ?? 'Por definir' }}@if ($horario->espacio?->ubicacion_epd)<br><span style="font-size:15px;font-weight:normal;">{{ $horario->espacio->ubicacion_epd }}</span>@endif</td>
            </tr>
            <tr>
                <td class="etiqueta">Día</td>
                <td class="valor">
                    @if ($horario->esFechaEspecifica())
                        {{ $horario->diaLegible() }} {{ $horario->fecha_especifica_hde->format('d/m/Y') }}
                    @else
                        Todos los {{ mb_strtolower($horario->diaLegible()) }}
                        @if ($vigencia)
                            <br><span style="font-size:15px;font-weight:normal;">Del {{ $vigencia[0]->format('d/m/Y') }} al {{ $vigencia[1]->format('d/m/Y') }}</span>
                        @endif
                    @endif
                </td>
            </tr>
            <tr>
                <td class="etiqueta">Hora</td>
                <td class="valor">{{ $horario->horaInicio('g:i A') }} a {{ $horario->horaFin('g:i A') }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Cupos</td>
                <td class="valor">
                    @if ($cupoTotal === null)
                        Sin límite de cupos
                    @else
                        {{ $cupoTotal }} cupos · {{ $cuposLibres }} disponibles
                    @endif
                </td>
            </tr>
        </table>

        <div class="inscripcion">
            <h3>¿Cómo inscribirte?</h3>
            <p>Acércate a la Coordinación de Deportes{{ $sede ? ' de la sede ' . $sede : '' }} con tu cédula de identidad.</p>
            <p>Pueden participar estudiantes, docentes, administrativos y obreros de la universidad.</p>
            <p><strong>Inscripción sujeta a la disponibilidad de cupos.</strong></p>
        </div>

        <div class="pie">Generado el {{ now()->format('d/m/Y h:i A') }}{{ $sede ? ' · ' . $sede : '' }}</div>
    </div>
</body>
</html>
