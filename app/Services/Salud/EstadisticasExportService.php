<?php

namespace App\Services\Salud;

use App\Exports\Salud\SaludEstadisticasWordExport;
use App\Models\Persona;
use App\Models\salud\Consultorio;
use App\Models\salud\Enfermedad;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exportación de estadísticas de salud (PDF / Word).
 */
class EstadisticasExportService
{
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
        $pdf->setOptions([
            'isRemoteEnabled'      => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont'          => 'DejaVu Sans',
        ]);

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
