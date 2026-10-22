<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Bitacora as BitacoraModelo;
use App\Support\Auditoria\Bitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BitacoraController extends Controller
{
    private const FILTROS = ['usuario', 'modulo', 'accion', 'nivel', 'ip', 'desde', 'hasta'];

    private const COLUMNAS_CSV = [
        'id_bis', 'created_at', 'nivel_bis', 'modulo_bis', 'tipo_evento_bis', 'use_id', 'nombre_usuario_bis', 'tabla_afectada_bis',
        'registro_id_bis', 'metodo_http_bis', 'ruta_bis', 'url_bis', 'codigo_respuesta_bis', 'direccion_ip_bis', 'navegador_bis',
        'sistema_operativo_bis', 'session_id_bis', 'observacion_bis', 'datos_anteriores_bis', 'datos_nuevos_bis',
    ];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\RequireMasterKey::class);
    }

    public function index(Request $request)
    {
        $filtros = $this->filtros($request);

        $registros = BitacoraModelo::query()
            ->filtrar($filtros)
            ->orderByDesc('id_bis')
            ->paginate(20)
            ->withQueryString();

        $modulos  = BitacoraModelo::query()->distinct()->orderBy('modulo_bis')->pluck('modulo_bis');
        $acciones = BitacoraModelo::query()->distinct()->orderBy('tipo_evento_bis')->pluck('tipo_evento_bis');
        $niveles  = [BitacoraModelo::NIVEL_INFO, BitacoraModelo::NIVEL_ADVERTENCIA, BitacoraModelo::NIVEL_CRITICO];

        return view('admin.configuracion.bitacora.index', compact('registros', 'filtros', 'modulos', 'acciones', 'niveles'));
    }

    public function show($id)
    {
        $registro = BitacoraModelo::with('usuario.persona')->findOrFail($id);

        $antes   = $registro->datos_anteriores_bis ?? [];
        $despues = $registro->datos_nuevos_bis ?? [];
        $campos  = array_values(array_unique(array_merge(array_keys($antes), array_keys($despues))));

        $diferencias = array_map(fn ($campo) => [
            'campo'   => $campo,
            'antes'   => array_key_exists($campo, $antes) ? $antes[$campo] : null,
            'despues' => array_key_exists($campo, $despues) ? $despues[$campo] : null,
            'cambio'  => ($antes[$campo] ?? null) !== ($despues[$campo] ?? null),
        ], $campos);

        return view('admin.configuracion.bitacora.show', compact('registro', 'diferencias'));
    }

    public function exportarCsv(Request $request): StreamedResponse
    {
        $filtros = $this->filtros($request);

        Bitacora::registrar(
            accion: 'bitacora_exportada',
            modulo: 'administracion',
            tablaAfectada: 'bitacora_sistema',
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: 'Formato CSV | Filtros: ' . $this->describirFiltros($filtros),
        );

        $consulta = BitacoraModelo::query()->filtrar($filtros);
        $nombre = 'bitacora_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($consulta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, self::COLUMNAS_CSV, ';');

            foreach ($consulta->lazyByIdDesc(500, 'id_bis') as $fila) {
                fputcsv($salida, array_map(function ($columna) use ($fila) {
                    $valor = $fila->{$columna};
                    if (is_array($valor)) {
                        return json_encode($valor, JSON_UNESCAPED_UNICODE);
                    }
                    if ($valor instanceof \DateTimeInterface) {
                        return $valor->format('Y-m-d H:i:s');
                    }
                    $valor = (string) $valor;

                    return preg_match('/^[=+\-@\t\r]/', $valor) ? "'" . $valor : $valor;
                }, self::COLUMNAS_CSV), ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportarPdf(Request $request)
    {
        $filtros = $this->filtros($request);
        $limite = (int) config('seguridad.bitacora.max_filas_pdf', 1000);

        Bitacora::registrar(
            accion: 'bitacora_exportada',
            modulo: 'administracion',
            tablaAfectada: 'bitacora_sistema',
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: "Formato PDF (máx. {$limite} filas) | Filtros: " . $this->describirFiltros($filtros),
        );

        $registros = BitacoraModelo::query()->filtrar($filtros)->orderByDesc('id_bis')->limit($limite)->get();
        $total = BitacoraModelo::query()->filtrar($filtros)->count();

        $pdf = Pdf::loadView('admin.configuracion.bitacora.pdf', [
            'registros' => $registros,
            'filtros'   => $filtros,
            'total'     => $total,
            'limite'    => $limite,
            'generado'  => now(),
            'usuario'   => auth()->user(),
        ])->setPaper('letter', 'landscape');

        return $pdf->download('bitacora_' . now()->format('Ymd_His') . '.pdf');
    }

    private function filtros(Request $request): array
    {
        $datos = $request->validate([
            'usuario' => 'nullable|string|max:255',
            'modulo'  => 'nullable|string|max:100',
            'accion'  => 'nullable|string|max:100',
            'nivel'   => 'nullable|in:info,advertencia,critico',
            'ip'      => 'nullable|string|max:45',
            'desde'   => 'nullable|date_format:Y-m-d',
            'hasta'   => 'nullable|date_format:Y-m-d|after_or_equal:desde',
        ]);

        return array_intersect_key($datos, array_flip(self::FILTROS));
    }

    private function describirFiltros(array $filtros): string
    {
        $activos = array_filter($filtros, fn ($valor) => $valor !== null && $valor !== '');

        if (! $activos) {
            return 'ninguno';
        }

        return collect($activos)->map(fn ($valor, $clave) => "{$clave}={$valor}")->implode(', ');
    }
}
