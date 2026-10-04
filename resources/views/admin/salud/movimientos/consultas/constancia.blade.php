{{--
    Constancia de asistencia a consulta médica (PDF, DomPDF).

    Variables:
    - $consulta  Consulta con paciente, medico y consultorio cargados (la hora sale de created_at)
    - $numero    Número de la constancia (ej.: CS-2026-000123)
    - $programa  PNF del estudiante, o null si no aplica
    - $emision   Fecha y hora de emisión (Carbon)
    - $servicio  (opcional) Nombre del servicio que emite
    - $ciudad    (opcional) Lugar de expedición
--}}
@php
    $servicio = $servicio ?? 'Servicio Médico';
    $ciudad = $ciudad ?? 'Acarigua, estado Portuguesa';

    $paciente = $consulta->paciente;
    $medico = $consulta->medico;

    $pacienteNombre = trim(($paciente->nombre_persona ?? '') . ' ' . ($paciente->apellido_persona ?? ''));
    $medicoNombre = trim(($medico->nombre_persona ?? '') . ' ' . ($medico->apellido_persona ?? ''));
    $cedula = $paciente->cedula_persona ?? 'No registrada';
    $consultorio = $consulta->consultorio->nombre ?? null;
    $fechaConsulta = $consulta->fecha ? $consulta->fecha->translatedFormat('j \d\e F \d\e Y') : 'No registrada';

    // Hora: se toma del registro de la consulta (created_at), solo si se registró el mismo día de la atención
    $registro = $consulta->created_at;
    $horaConsulta = $registro && $consulta->fecha && $registro->format('Y-m-d') === $consulta->fecha->format('Y-m-d')
        ? $registro->format('g:i A')
        : null;
    $momentoConsulta = $fechaConsulta . ($horaConsulta ? ' a las ' . $horaConsulta : '');

    $diaEmision = (int) $emision->format('j');
    $expedicion = ($diaEmision === 1 ? 'al primer día' : "a los {$diaEmision} días")
        . ' del mes de ' . $emision->translatedFormat('F') . ' de ' . $emision->format('Y');

    $encabezado = public_path('img/encabezado.png');
    $pie = public_path('img/pie.png');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de asistencia {{ $numero }} - {{ $pacienteNombre }}</title>
    <style>
        @page { margin: 100px 60px 65px 60px; }

        header { position: fixed; top: -60px; left: 0; right: 0; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 50px; text-align: center; }
        header img, footer img { width: 100%; max-height: 50px; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 10px 20px;
            font-size: 12px;
            line-height: 1.5;
        }

        table { border-collapse: collapse; }

        /* ── Membrete ───────────────────────────── */
        .membrete { width: 100%; border-bottom: 2px solid #72110f; }
        .membrete td { vertical-align: bottom; padding: 0 0 12px 0; }

        .cruz { width: 34px; height: 34px; background-color: #720f0f; position: relative; }
        .cruz div { position: absolute; background-color: #ffffff; }
        .cruz .v { left: 14px; top: 7px; width: 6px; height: 20px; }
        .cruz .h { left: 7px; top: 14px; width: 20px; height: 6px; }

        .servicio { font-size: 19px; font-weight: bold; color: #111827; line-height: 1.15; }
        .servicio-detalle { font-size: 10px; color: #6b7280; margin-top: 2px; }

        .documento { text-align: right; }
        .documento-titulo { font-size: 14px; font-weight: bold; color: #000000; text-transform: uppercase; letter-spacing: 0.6px; }
        .documento-numero { font-size: 10px; color: #6b7280; margin-top: 3px; }
        .documento-numero strong { color: #111827; }

        /* ── Cuerpo ─────────────────────────────── */
        .cuerpo {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14.5px;
            line-height: 1.75;
            color: #1f2937;
            text-align: justify;
        }

        .saludo {
            font-family: Arial, Times, serif;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.4px;
            color: #111827;
            margin: 42px 0 22px 0;
        }

        .parrafo { text-indent: 32px; margin: 0 0 16px 0; }

        /* ── Datos de la atención ───────────────── */
        .datos { width: 100%; border-top: 2px solid #720f0f; border-bottom: 1px solid #d1d5db; }
        .datos td { padding: 7px 10px; border-top: 1px solid #e5e7eb; vertical-align: top; }
        .datos tr.primera td { border-top: 0; }
        .datos .etiqueta { width: 27%; font-size: 10.5px; color: #6b7280; background-color: #f3f7f7; }
        .datos .valor { font-size: 12px; font-weight: bold; color: #111827; }

        /* ── Nota ───────────────────────────────── */
        .nota { font-size: 10px; line-height: 1.5; color: #4b5563; text-align: justify; margin-top: 22px; }
        .nota strong { color: #1f2937; }

        /* ── Firma ──────────────────────────────── */
        .firma { margin-top: 96px; text-align: center; page-break-inside: avoid; }
        .firma-linea { width: 250px; border-top: 1px solid #374151; margin: 0 auto 8px auto; }
        .firma-nombre { font-size: 12px; font-weight: bold; color: #111827; }
        .firma-rol { font-size: 10px; color: #6b7280; margin-top: 2px; }

        .control { margin-top: 34px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <header>
        @if (file_exists($encabezado))
            <img src="{{ $encabezado }}" alt="Encabezado">
        @endif
    </header>
    <footer>
        @if (file_exists($pie))
            <img src="{{ $pie }}" alt="Pie de página">
        @endif
    </footer>

    <main>
        {{-- Membrete --}}
        <table class="membrete">
            <tr>
                <td>
                    <div class="servicio">{{ $servicio }}</div>
                    @if ($consultorio)
                        <div class="servicio-detalle">{{ $consultorio }}</div>
                    @endif
                </td>
                <td class="documento">
                    <div class="documento-titulo">Constancia de asistencia</div>
                    <div class="documento-numero">N.º <strong>{{ $numero }}</strong></div>
                </td>
            </tr>
        </table>

        {{-- Texto --}}
        <div class="cuerpo">
            <div class="saludo">A QUIEN PUEDA INTERESAR:</div>

            <p class="parrafo">
                Por medio de la presente se hace constar que el/la ciudadano(a)
                <strong>{{ $pacienteNombre }}</strong>, titular de la cédula de identidad
                <strong>{{ $cedula }}</strong>@if ($programa), estudiante del
                <strong>{{ $programa }}</strong>@endif, asistió a consulta en este servicio el día
                <strong>{{ $momentoConsulta }}</strong>@if ($medicoNombre), donde recibió atención de
                <strong>{{ $medicoNombre }}</strong>@endif.
            </p>

            <p class="parrafo">
                Constancia que se expide a petición de la parte interesada, en la ciudad de
                {{ $ciudad }}, {{ $expedicion }}.
            </p>
        </div>



        <div class="nota">
            <strong>Nota de confidencialidad:</strong> conforme al artículo 46 de la Ley del Ejercicio de la
            Medicina, lo conocido durante la atención constituye secreto médico. Esta constancia certifica
            únicamente la asistencia a la consulta y no incluye diagnósticos, tratamientos ni otros datos clínicos.
        </div>

        {{-- Firma --}}
        <div class="firma">
            <div class="firma-linea"></div>
            <div class="firma-nombre">{{ $medicoNombre ?: $servicio }}</div>
            <div class="firma-rol">Firma y sello</div>
        </div>

        <div class="control">
            Constancia N.º {{ $numero }}, generada el {{ $emision->format('d/m/Y') }} a las {{ $emision->format('g:i A') }}.
            Válida solo con firma y sello del servicio.
        </div>
    </main>
</body>
</html>