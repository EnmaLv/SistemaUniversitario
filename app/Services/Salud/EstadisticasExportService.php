<?php

namespace App\Services\Salud;

use App\Exports\Salud\SaludEstadisticasWordExport;
use App\Exports\Salud\SaludListadoExcelExport;
use App\Models\Persona;
use App\Models\salud\Consultorio;
use App\Models\salud\Enfermedad;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exportación del panel estadístico de Salud:
 * reporte estadístico (PDF / Word) y listados (PDF / Excel).
 */
class EstadisticasExportService
{
    private const ESTADOS_RECETA = [
        'con_receta'       => 'Con receta',
        'sin_receta'       => 'Sin receta',
        'completado'       => 'Completadas',
        'sin_dispensacion' => 'Sin dispensación',
        'con_pendientes'   => 'Con pendientes',
    ];

    public function __construct(private readonly ListadoSaludService $listados) {}

    /* ══════════════════════════════════════════════════════════════
     |  Filtros legibles
     ══════════════════════════════════════════════════════════════ */

    /**
     * Traduce los IDs de los filtros a etiquetas legibles para el reporte.
     */
    public function etiquetasFiltros(array $filtros): array
    {
        return [
            'estado_receta'        => $filtros['estado_receta'] ?? null,
            'consultorio_nombre'   => $this->nombreConsultorio($filtros['consultorio_id'] ?? null),
            'medico_nombre'        => $this->nombreMedico($filtros['medico_id'] ?? null),
            'enfermedades_nombres' => $this->nombresEnfermedades($filtros['enfermedad_ids'] ?? []),
            'perfil_academico'     => $filtros['perfil_academico'] ?? null,
            'pnf'                  => $filtros['pnf'] ?? null,
        ];
    }

    /**
     * Filtros aplicados como pares [etiqueta, valor], listos para mostrar.
     */
    public function filtrosAplicados(array $filtros): array
    {
        $e = $this->etiquetasFiltros($filtros);

        $pares = [
            ['Estado', self::ESTADOS_RECETA[$e['estado_receta']] ?? $e['estado_receta']],
            ['Consultorio', $e['consultorio_nombre']],
            ['Médico', $e['medico_nombre']],
            ['Enfermedades', implode(', ', $e['enfermedades_nombres']) ?: null],
            ['Rol', $e['perfil_academico']],
            ['PNF', $e['pnf'] ? str_replace('_', ' ', $e['pnf']) : null],
        ];

        return array_values(array_filter($pares, fn($par) => !empty($par[1])));
    }

    /* ══════════════════════════════════════════════════════════════
     |  Reporte estadístico
     ══════════════════════════════════════════════════════════════ */

    public function pdf(array $data, string $periodo, string $tipoReporte, array $filtros): Response
    {
        ini_set('memory_limit', '512M');

        $pdf = Pdf::loadView('pdf.salud.pdf', array_merge([
            'consultas'   => $data['consultas'],
            'resumen'     => $data['resumen'],
            'fechaInicio' => $data['fechaInicio'],
            'fechaFin'    => $data['fechaFin'],
            'periodo'     => $periodo,
            'reportType'  => $tipoReporte,
        ], $this->etiquetasFiltros($filtros)));

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions($this->opcionesPdf());

        return $pdf->stream('reporte-salud-' . $tipoReporte . '-' . now()->format('Ymd_His') . '.pdf');
    }

    public function word(array $data, string $periodo, string $tipoReporte, array $filtros): BinaryFileResponse
    {
        $tempFile = SaludEstadisticasWordExport::generate(
            $data['consultas'],
            $data['resumen'],
            $data['fechaInicio'],
            $data['fechaFin'],
            $periodo,
            $tipoReporte,
            $this->etiquetasFiltros($filtros)
        );

        return response()
            ->download($tempFile, 'Estadisticas_Salud_' . now()->format('Ymd_His') . '.docx')
            ->deleteFileAfterSend(true);
    }

    /* ══════════════════════════════════════════════════════════════
     |  Listados (consultas / medicamentos dispensados)
     ══════════════════════════════════════════════════════════════ */

    /**
     * @param string $tipo    ListadoSaludService::CONSULTAS | DISPENSACIONES
     * @param string $formato pdf | excel
     */
    public function listado(string $tipo, string $formato, array $data, string $periodo, array $filtros): Response
    {
        $consultas = $data['consultas'];
        $consultas->loadMissing(ListadoSaludService::RELACIONES[$tipo]);

        $reporte = $this->listados->construir($tipo, $consultas);

        $meta = [
            'periodo'     => Carbon::parse($data['fechaInicio'])->format('d/m/Y')
                . ' al ' . Carbon::parse($data['fechaFin'])->format('d/m/Y'),
            'tipoPeriodo' => ucfirst($periodo),
            'generado'    => now()->format('d/m/Y H:i'),
            'filtros'     => $this->filtrosAplicados($filtros),
        ];

        return $formato === 'excel'
            ? $this->listadoExcel($reporte, $meta)
            : $this->listadoPdf($reporte, $meta);
    }

    private function listadoPdf(array $reporte, array $meta): Response
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $pdf = Pdf::loadView('pdf.salud.listado', compact('reporte', 'meta'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions($this->opcionesPdf());

        return $pdf->stream($reporte['archivo'] . '-' . now()->format('Ymd_His') . '.pdf');
    }

    private function listadoExcel(array $reporte, array $meta): BinaryFileResponse
    {
        $tempFile = SaludListadoExcelExport::generate($reporte, $meta);

        return response()
            ->download($tempFile, $reporte['archivo'] . '-' . now()->format('Ymd_His') . '.xlsx')
            ->deleteFileAfterSend(true);
    }

    /* ══════════════════════════════════════════════════════════════
     |  Utilidades
     ══════════════════════════════════════════════════════════════ */

    private function opcionesPdf(): array
    {
        return [
            'isRemoteEnabled'      => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont'          => 'DejaVu Sans',
        ];
    }

    private function nombreConsultorio($consultorioId): ?string
    {
        return $consultorioId ? Consultorio::find($consultorioId)?->nombre : null;
    }

    private function nombreMedico($medicoId): ?string
    {
        if (!$medicoId) {
            return null;
        }

        $medico = Persona::find($medicoId);

        return $medico
            ? trim(($medico->nombre_persona ?? '') . ' ' . ($medico->apellido_persona ?? ''))
            : null;
    }

    private function nombresEnfermedades($ids): array
    {
        $ids = array_filter(array_map('intval', (array) $ids));

        return $ids
            ? Enfermedad::whereIn('id', $ids)->pluck('nombre')->toArray()
            : [];
    }
}
