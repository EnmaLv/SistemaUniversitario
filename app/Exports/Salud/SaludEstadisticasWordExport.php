<?php

namespace App\Exports\Salud;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use Carbon\Carbon;

class SaludEstadisticasWordExport
{
    public static function generate(
        $consultas,
        $resumen,
        $fechaInicio,
        $fechaFin,
        $periodo = 'mensual',
        $reportType = 'completo',
        $filtros = []
    ) {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop'    => 1440,
            'marginBottom' => 1440,
            'marginLeft'   => 1440,
            'marginRight'  => 1440,
        ]);

        // =====================================================
        // HEADER / FOOTER INSTITUCIONAL
        // =====================================================
        $header = $section->addHeader();
        $header->addImage(public_path('img/encabezado.png'), ['width' => 450, 'alignment' => Jc::CENTER]);

        $footer = $section->addFooter();
        $footer->addImage(public_path('img/pie.png'), ['width' => 450, 'alignment' => Jc::CENTER]);

        // =====================================================
        // TÍTULO
        // =====================================================
        $titleStyle = ['bold' => true, 'size' => 18, 'color' => '0F172A'];
        $subtitleStyle = ['bold' => true, 'size' => 12, 'color' => '64748B'];

        $section->addText('REPORTE ESTADÍSTICO DE SALUD', $titleStyle, ['alignment' => Jc::CENTER]);
        $section->addText('Módulo Salud — Coordinación de Bienestar Estudiantil', $subtitleStyle, ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        // =====================================================
        // FILTROS
        // =====================================================
        $filterStyle = ['bold' => true, 'size' => 11, 'color' => '1B1B1B'];
        $filterValueStyle = ['size' => 11, 'color' => '334155'];

        $run = $section->addTextRun();
        $run->addText('Período (' . ucfirst($periodo ?? 'Mensual') . '): ', $filterStyle);
        $run->addText(
            Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFin)->format('d/m/Y'),
            $filterValueStyle
        );

        $run = $section->addTextRun();
        $run->addText('Alcance: ', $filterStyle);
        $run->addText(ucfirst($reportType), $filterValueStyle);

        // Filtros opcionales
        if (!empty($filtros['estado_receta'])) {
            $estadoLabels = [
                'con_receta'       => 'Con receta',
                'sin_receta'       => 'Sin receta',
                'completado'       => 'Completadas',
                'sin_dispensacion' => 'Sin dispensación',
                'con_pendientes'   => 'Con pendientes',
            ];
            $run = $section->addTextRun();
            $run->addText('Estado de receta: ', $filterStyle);
            $run->addText($estadoLabels[$filtros['estado_receta']] ?? $filtros['estado_receta'], $filterValueStyle);
        }

        if (!empty($filtros['consultorio_nombre'])) {
            $run = $section->addTextRun();
            $run->addText('Consultorio: ', $filterStyle);
            $run->addText($filtros['consultorio_nombre'], $filterValueStyle);
        }

        if (!empty($filtros['medico_nombre'])) {
            $run = $section->addTextRun();
            $run->addText('Médico: ', $filterStyle);
            $run->addText($filtros['medico_nombre'], $filterValueStyle);
        }

        if (!empty($filtros['enfermedades_nombres']) && is_array($filtros['enfermedades_nombres'])) {
            $run = $section->addTextRun();
            $run->addText('Enfermedades: ', $filterStyle);
            $run->addText(implode(', ', $filtros['enfermedades_nombres']), $filterValueStyle);
        }

        if (!empty($filtros['perfil_academico'])) {
            $run = $section->addTextRun();
            $run->addText('Rol institucional: ', $filterStyle);
            $run->addText($filtros['perfil_academico'], $filterValueStyle);
        }

        if (!empty($filtros['pnf'])) {
            $run = $section->addTextRun();
            $run->addText('PNF: ', $filterStyle);
            $run->addText(str_replace('_', ' ', $filtros['pnf']), $filterValueStyle);
        }

        $section->addTextBreak(1);

        // =====================================================
        // TABLA DE CONSULTAS (solo si el reportType lo requiere)
        // =====================================================
        if (in_array($reportType, ['consultas', 'completo']) && !empty($consultas)) {
            $tableStyle = ['borderSize' => 6, 'borderColor' => 'E2E8F0', 'cellMargin' => 50];
            $phpWord->addTableStyle('Consultas Table', $tableStyle);
            $table = $section->addTable('Consultas Table');

            $headerRowStyle = ['bgColor' => 'F1F5F9'];
            $headerFontStyle = ['bold' => true, 'color' => '334155', 'size' => 10];
            $cellFontStyle = ['color' => '475569', 'size' => 10];

            $table->addRow();
            $table->addCell(700, $headerRowStyle)->addText('ID', $headerFontStyle);
            $table->addCell(2000, $headerRowStyle)->addText('Paciente', $headerFontStyle);
            $table->addCell(1200, $headerRowStyle)->addText('Cédula', $headerFontStyle);
            $table->addCell(1500, $headerRowStyle)->addText('F. Consulta', $headerFontStyle);
            $table->addCell(2200, $headerRowStyle)->addText('Médico', $headerFontStyle);
            $table->addCell(1500, $headerRowStyle)->addText('Consultorio', $headerFontStyle);
            $table->addCell(1400, $headerRowStyle)->addText('Estado', $headerFontStyle);

            foreach ($consultas as $c) {
                $estado = $c->receta
                    ? ($c->receta->estado == 2 ? 'Completada' : 'En proceso')
                    : 'Sin receta';

                $table->addRow();
                $table->addCell()->addText('#' . $c->id, $cellFontStyle);
                $table->addCell()->addText(
                    trim(($c->paciente->nombre_persona ?? '') . ' ' . ($c->paciente->apellido_persona ?? '')) ?: '—',
                    $cellFontStyle
                );
                $table->addCell()->addText($c->paciente->cedula_persona ?? '—', $cellFontStyle);
                $table->addCell()->addText(
                    $c->fecha ? Carbon::parse($c->fecha)->format('d/m/Y') : '—',
                    $cellFontStyle
                );
                $table->addCell()->addText(
                    trim(($c->medico->nombre_persona ?? '') . ' ' . ($c->medico->apellido_persona ?? '')) ?: '—',
                    $cellFontStyle
                );
                $table->addCell()->addText($c->consultorio->nombre ?? '—', $cellFontStyle);
                $table->addCell()->addText($estado, $cellFontStyle);
            }

            $section->addTextBreak(2);
        }

        // =====================================================
        // TABLA DE RECETAS
        // =====================================================
        if ($reportType === 'recetas' && !empty($consultas)) {
            $tableStyle = ['borderSize' => 6, 'borderColor' => 'E2E8F0', 'cellMargin' => 50];
            $phpWord->addTableStyle('Recetas Table', $tableStyle);
            $table = $section->addTable('Recetas Table');

            $headerRowStyle = ['bgColor' => 'F1F5F9'];
            $headerFontStyle = ['bold' => true, 'color' => '334155', 'size' => 10];
            $cellFontStyle = ['color' => '475569', 'size' => 10];

            $table->addRow();
            $table->addCell(900, $headerRowStyle)->addText('N°', $headerFontStyle);
            $table->addCell(1500, $headerRowStyle)->addText('Fecha', $headerFontStyle);
            $table->addCell(2500, $headerRowStyle)->addText('Paciente', $headerFontStyle);
            $table->addCell(2500, $headerRowStyle)->addText('Médico', $headerFontStyle);
            $table->addCell(900, $headerRowStyle)->addText('Ítems', $headerFontStyle);
            $table->addCell(1500, $headerRowStyle)->addText('Estado', $headerFontStyle);

            foreach ($consultas as $c) {
                if (!$c->receta) continue;

                $table->addRow();
                $table->addCell()->addText('#' . str_pad($c->receta->id, 5, '0', STR_PAD_LEFT), $cellFontStyle);
                $table->addCell()->addText(
                    $c->receta->fecha ? Carbon::parse($c->receta->fecha)->format('d/m/Y') : '—',
                    $cellFontStyle
                );
                $table->addCell()->addText(
                    trim(($c->paciente->nombre_persona ?? '') . ' ' . ($c->paciente->apellido_persona ?? '')),
                    $cellFontStyle
                );
                $table->addCell()->addText(
                    trim(($c->medico->nombre_persona ?? '') . ' ' . ($c->medico->apellido_persona ?? '')),
                    $cellFontStyle
                );
                $table->addCell()->addText((string) $c->receta->detalles->count(), $cellFontStyle);
                $table->addCell()->addText($c->receta->estado == 2 ? 'Completada' : 'En proceso', $cellFontStyle);
            }

            $section->addTextBreak(2);
        }

        // =====================================================
        // TABLA DE DISPENSACIONES
        // =====================================================
        if ($reportType === 'dispensaciones' && !empty($consultas)) {
            $tableStyle = ['borderSize' => 6, 'borderColor' => 'E2E8F0', 'cellMargin' => 50];
            $phpWord->addTableStyle('Dispensaciones Table', $tableStyle);
            $table = $section->addTable('Dispensaciones Table');

            $headerRowStyle = ['bgColor' => 'F1F5F9'];
            $headerFontStyle = ['bold' => true, 'color' => '334155', 'size' => 10];
            $cellFontStyle = ['color' => '475569', 'size' => 10];

            $table->addRow();
            $table->addCell(1200, $headerRowStyle)->addText('Fecha', $headerFontStyle);
            $table->addCell(2500, $headerRowStyle)->addText('Paciente', $headerFontStyle);
            $table->addCell(2500, $headerRowStyle)->addText('Producto', $headerFontStyle);
            $table->addCell(1000, $headerRowStyle)->addText('Cant.', $headerFontStyle);
            $table->addCell(1300, $headerRowStyle)->addText('Lote', $headerFontStyle);
            $table->addCell(1500, $headerRowStyle)->addText('Dispensado por', $headerFontStyle);

            $totalItems = 0;
            foreach ($consultas as $c) {
                if (!$c->receta) continue;
                foreach ($c->receta->detalles as $det) {
                    foreach ($det->dispensaciones as $disp) {
                        $totalItems++;
                        $table->addRow();
                        $table->addCell()->addText(
                            $disp->fecha ? Carbon::parse($disp->fecha)->format('d/m/Y') : '—',
                            $cellFontStyle
                        );
                        $table->addCell()->addText(
                            trim(($c->paciente->nombre_persona ?? '') . ' ' . ($c->paciente->apellido_persona ?? '')),
                            $cellFontStyle
                        );
                        $table->addCell()->addText($det->producto->nombre ?? '—', $cellFontStyle);
                        $table->addCell()->addText(number_format($disp->cantidad, 2), $cellFontStyle);
                        $table->addCell()->addText($disp->lote->codigo ?? '—', $cellFontStyle);
                        $table->addCell()->addText(
                            trim(($disp->usuario->persona->nombre_persona ?? '') . ' ' . ($disp->usuario->persona->apellido_persona ?? '')) ?: '—',
                            $cellFontStyle
                        );
                    }
                }
            }

            // Fila total
            $table->addRow();
            $table->addCell(7200, ['gridSpan' => 3, 'bgColor' => 'F1F5F9'])
                ->addText('TOTAL DE DISPENSACIONES:', ['bold' => true, 'color' => '334155'], ['alignment' => Jc::RIGHT]);
            $table->addCell(1000, ['bgColor' => 'F1F5F9'])
                ->addText((string) $totalItems, ['bold' => true, 'color' => '1B1B1B'], ['alignment' => Jc::CENTER]);
            $table->addCell(1300, ['bgColor' => 'F1F5F9'])->addText('');
            $table->addCell(1500, ['bgColor' => 'F1F5F9'])->addText('');

            $section->addTextBreak(2);
        }

        // =====================================================
        // RESUMEN DETALLADO
        // =====================================================
        if ($reportType === 'completo') {
            $section->addText(
                'RESUMEN DETALLADO DE ESTADÍSTICAS',
                ['bold' => true, 'size' => 14, 'color' => '334155'],
                ['alignment' => Jc::CENTER]
            );
            $section->addTextBreak(1);

            $summaryTableStyle = ['borderSize' => 6, 'borderColor' => 'E2E8F0', 'cellMargin' => 80];
            $phpWord->addTableStyle('Summary Table', $summaryTableStyle);
            $summaryTable = $section->addTable('Summary Table');

            // Totales generales
            self::addSummaryRow($summaryTable, 'Total de Consultas:', (string) $resumen['total_consultas'], 'F1F5F9');
            self::addSummaryRow($summaryTable, 'Total de Pacientes Únicos:', (string) $resumen['total_pacientes'], 'F1F5F9');
            self::addSummaryRow($summaryTable, 'Total de Recetas Emitidas:', (string) $resumen['total_recetas'], 'F1F5F9');
            self::addSummaryRow($summaryTable, 'Total de Medicamentos Dispensados:', number_format($resumen['total_dispensados'], 1) . ' und', 'F1F5F9');

            // Género
            self::addSummarySection($summaryTable, 'Distribución por Género');
            self::addSummaryRow($summaryTable, '- Hombres:', (string) $resumen['genero']['masculino']);
            self::addSummaryRow($summaryTable, '- Mujeres:', (string) $resumen['genero']['femenino']);
            self::addSummaryRow($summaryTable, '- Otro:', (string) $resumen['genero']['otro']);

            // Edades
            self::addSummarySection($summaryTable, 'Rangos de Edad');
            foreach ($resumen['edades']['rangos'] as $rango => $cantidad) {
                self::addSummaryRow($summaryTable, '- ' . $rango . ' años:', (string) $cantidad);
            }
            self::addSummaryRow($summaryTable, 'Promedio de Edad:', $resumen['edades']['promedio'] . ' años', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Mediana de Edad:', $resumen['edades']['mediana'] . ' años', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Moda de Edad:', $resumen['edades']['moda'] . ' años', 'F8FAFC');

            // Perfil académico
            self::addSummarySection($summaryTable, 'Perfil Institucional / Académico');
            foreach ($resumen['perfil_academico'] as $rol => $cantidad) {
                self::addSummaryRow($summaryTable, '- ' . $rol . ':', (string) $cantidad);
            }

            // PNF
            self::addSummarySection($summaryTable, 'Pacientes de acuerdo al PNF');
            foreach ($resumen['pnf'] as $pnfKey => $cantidad) {
                self::addSummaryRow($summaryTable, '- ' . str_replace('_', ' ', $pnfKey) . ':', (string) $cantidad);
            }

            // Métricas avanzadas
            self::addSummarySection($summaryTable, 'Métricas Avanzadas');
            self::addSummaryRow($summaryTable, 'Hora Pico (Moda):', $resumen['hora_pico'], 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Volumen Promedio Semanal:', $resumen['promedio_semanal'] . ' consultas/semana', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Tasa de Consultas con Receta:', $resumen['tasa_con_receta'] . '%', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Tasa de Recetas Dispensadas:', $resumen['tasa_dispensada'] . '%', 'F8FAFC');
            $comp = $resumen['comparativa_consultas'] ?? 0;
            self::addSummaryRow($summaryTable, 'Comparativa con Período Anterior:', ($comp > 0 ? '+' : '') . $comp . '%', 'F8FAFC');

            // Estado de recetas
            if (!empty($resumen['estado_recetas'])) {
                self::addSummarySection($summaryTable, 'Estado de Recetas');
                foreach ($resumen['estado_recetas'] as $estado => $cantidad) {
                    self::addSummaryRow($summaryTable, '- ' . $estado . ':', (string) $cantidad);
                }
            }

            // Horas
            if (!empty($resumen['distribucion_horas'])) {
                self::addSummarySection($summaryTable, 'Distribución por Horas de Atención');
                foreach ($resumen['distribucion_horas'] as $bloque => $cantidad) {
                    self::addSummaryRow($summaryTable, '- ' . $bloque . ':', (string) $cantidad);
                }
            }

            // Flujo semanal
            if (!empty($resumen['flujo_semanal'])) {
                self::addSummarySection($summaryTable, 'Flujo de Consultas por Semana');
                foreach ($resumen['flujo_semanal'] as $semana => $cantidad) {
                    self::addSummaryRow($summaryTable, '- Semana ' . explode('-', $semana)[0] . ':', (string) $cantidad);
                }
            }

            // Enfermedades
            if (!empty($resumen['enfermedades'])) {
                self::addSummarySection($summaryTable, 'Enfermedades Más Frecuentes');
                foreach (array_slice($resumen['enfermedades'], 0, 15, true) as $nombre => $cantidad) {
                    self::addSummaryRow($summaryTable, '- ' . $nombre . ':', (string) $cantidad);
                }
            }

            // Productos
            if (!empty($resumen['productos_top'])) {
                self::addSummarySection($summaryTable, 'Productos Más Dispensados');
                foreach (array_slice($resumen['productos_top'], 0, 15, true) as $nombre => $cantidad) {
                    self::addSummaryRow($summaryTable, '- ' . $nombre . ':', number_format($cantidad, 1) . ' und');
                }
            }

            // Médicos
            if (!empty($resumen['medicos'])) {
                self::addSummarySection($summaryTable, 'Consultas por Médico');
                foreach ($resumen['medicos'] as $nombre => $cantidad) {
                    self::addSummaryRow($summaryTable, '- ' . $nombre . ':', (string) $cantidad);
                }
            }

            // Consultorios
            if (!empty($resumen['consultorios'])) {
                self::addSummarySection($summaryTable, 'Consultas por Consultorio');
                foreach ($resumen['consultorios'] as $nombre => $cantidad) {
                    self::addSummaryRow($summaryTable, '- ' . $nombre . ':', (string) $cantidad);
                }
            }
        }

        // =====================================================
        // GUARDAR
        // =====================================================
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        $fileName = 'Estadisticas_Salud_' . date('Y_m_d_His') . '.docx';
        $tempPath = storage_path('app/public/' . $fileName);
        $objWriter->save($tempPath);

        return $tempPath;
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private static function addSummarySection($table, $title)
    {
        $table->addRow();
        $table->addCell(10000, ['gridSpan' => 2, 'bgColor' => 'E2E8F0'])
            ->addText($title, ['bold' => true, 'color' => '334155']);
    }

    private static function addSummaryRow($table, $label, $value, $bgColor = null)
    {
        $cellProps = [];
        if ($bgColor) $cellProps['bgColor'] = $bgColor;
        $table->addRow();
        $table->addCell(7000, $cellProps)->addText($label, ['color' => '334155']);
        $table->addCell(3000, $cellProps)
            ->addText((string) $value, ['bold' => true, 'color' => '1B1B1B'], ['alignment' => Jc::CENTER]);
    }
}