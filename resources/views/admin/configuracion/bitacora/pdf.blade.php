<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Bitácora de auditoría</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
        h1 { font-size: 14px; margin: 0 0 4px; color: #7f1d1d; }
        .meta { font-size: 8px; color: #444; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #7f1d1d; color: #fff; padding: 4px; text-align: left; }
        td { border-bottom: 1px solid #ddd; padding: 3px 4px; vertical-align: top; word-wrap: break-word; }
        .critico { color: #b91c1c; font-weight: bold; }
        .advertencia { color: #b45309; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Bitácora de auditoría — Bienestar Estudiantil UPTP</h1>
    <div class="meta">
        Generado: {{ $generado->format('d/m/Y H:i:s') }} por {{ $usuario?->username ?? 'Sistema' }} ·
        Registros: {{ $registros->count() }} de {{ $total }}{{ $total > $limite ? ' (limitado a ' . $limite . ')' : '' }} ·
        Filtros: {{ collect($filtros)->filter()->map(fn ($v, $k) => "$k=$v")->implode(', ') ?: 'ninguno' }}
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha y hora</th>
                <th>Usuario</th>
                <th>Módulo</th>
                <th>Acción</th>
                <th>Nivel</th>
                <th>Tabla / Registro</th>
                <th>Ruta</th>
                <th>Cód.</th>
                <th>IP</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registros as $registro)
                <tr>
                    <td>{{ $registro->id_bis }}</td>
                    <td>{{ $registro->created_at?->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $registro->nombre_usuario_bis ?? 'Sistema' }}</td>
                    <td>{{ $registro->modulo_bis }}</td>
                    <td>{{ $registro->tipo_evento_bis }}</td>
                    <td class="{{ $registro->nivel_bis }}">{{ $registro->nivel_bis }}</td>
                    <td>{{ $registro->tabla_afectada_bis }}{{ $registro->registro_id_bis ? ' #' . $registro->registro_id_bis : '' }}</td>
                    <td>{{ $registro->metodo_http_bis }} {{ $registro->ruta_bis }}</td>
                    <td>{{ $registro->codigo_respuesta_bis }}</td>
                    <td>{{ $registro->direccion_ip_bis }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($registro->observacion_bis, 120) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
