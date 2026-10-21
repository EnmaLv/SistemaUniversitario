<?php

namespace App\Exports\Agenda;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EstadisticasWordExport
{
    public static function generate($citas, $resumen, $fechaInicio, $fechaFin, $estado, $avanceNombre, $estadoAnimoNombre, $prioridad, $psicologo, $periodo)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        
        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        $header = $section->addHeader();
        $header->addImage(public_path('img/encabezado.png'), ['width' => 450, 'alignment' => Jc::CENTER]);
        $footer = $section->addFooter();
        $footer->addImage(public_path('img/pie.png'), ['width' => 450, 'alignment' => Jc::CENTER]);

        // Encabezado del reporte
        $titleStyle = ['bold' => true, 'size' => 18, 'color' => '0F172A'];
        $subtitleStyle = ['bold' => true, 'size' => 12, 'color' => '64748B'];
        $section->addText('REPORTE COMPLETO DE ESTADÍSTICAS', $titleStyle, ['alignment' => Jc::CENTER]);
        
        $psicologoNombre = $psicologo->nombres ?? $psicologo->name ?? '';
        if (isset($psicologo->apellidos)) $psicologoNombre .= ' ' . $psicologo->apellidos;
        $section->addText('Psicólogo: ' . trim($psicologoNombre), $subtitleStyle, ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        // Filtros
        $filterStyle = ['bold' => true, 'size' => 11, 'color' => '1B1B1B'];
        $filterValueStyle = ['size' => 11, 'color' => '334155'];
        
        $run = $section->addTextRun();
        $run->addText('Período (' . ucfirst($periodo ?? 'Mensual') . '): ', $filterStyle);
        $run->addText(Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFin)->format('d/m/Y'), $filterValueStyle);
        
        $run = $section->addTextRun();
        $run->addText('Estado filtrado: ', $filterStyle);
        $estadoTexto = $estado ? ucfirst(str_replace('_', ' ', $estado)) : 'Todos los estados';
        $run->addText($estadoTexto, $filterValueStyle);

        if ($avanceNombre) {
            $run = $section->addTextRun();
            $run->addText('Avance de Sesión: ', $filterStyle);
            $run->addText($avanceNombre, $filterValueStyle);
        }

        if ($estadoAnimoNombre) {
            $run = $section->addTextRun();
            $run->addText('Estado de Ánimo: ', $filterStyle);
            $run->addText($estadoAnimoNombre, $filterValueStyle);
        }

        if ($prioridad) {
            $run = $section->addTextRun();
            $run->addText('Prioridad: ', $filterStyle);
            $run->addText(ucfirst($prioridad), $filterValueStyle);
        }
        $section->addTextBreak(1);

        // Tabla de Citas
        $tableStyle = ['borderSize' => 6, 'borderColor' => 'E2E8F0', 'cellMargin' => 50];
        $phpWord->addTableStyle('Citas Table', $tableStyle);
        $table = $section->addTable('Citas Table');

        $headerRowStyle = ['bgColor' => 'F1F5F9'];
        $headerFontStyle = ['bold' => true, 'color' => '334155', 'size' => 10];
        $cellFontStyle = ['color' => '475569', 'size' => 10];
        
        $table->addRow();
        $table->addCell(500, $headerRowStyle)->addText('ID', $headerFontStyle);
        $table->addCell(2500, $headerRowStyle)->addText('Paciente', $headerFontStyle);
        $table->addCell(1500, $headerRowStyle)->addText('F. Solicitud', $headerFontStyle);
        $table->addCell(1500, $headerRowStyle)->addText('H. Solicitud', $headerFontStyle);
        $table->addCell(1500, $headerRowStyle)->addText('F. Cita', $headerFontStyle);
        $table->addCell(1500, $headerRowStyle)->addText('H. Cita', $headerFontStyle);
        $table->addCell(1500, $headerRowStyle)->addText('Estado', $headerFontStyle);

        foreach ($citas as $cita) {
            $fechaSolicitada = $cita->created_at_carbon ? $cita->created_at_carbon->format('d/m/Y') : 'N/A';
            $horaSolicitada = $cita->created_at_carbon ? $cita->created_at_carbon->format('h:i A') : 'N/A';
            $fechaProgramada = ($cita->fecha_carbon && !in_array($cita->estado, ['pendiente', 'rechazada'])) ? $cita->fecha_carbon->format('d/m/Y') : 'N/A';
            $horaProgramada = ($cita->hora && !in_array($cita->estado, ['pendiente', 'rechazada'])) ? Carbon::parse($cita->hora)->format('h:i A') : 'N/A';

            $table->addRow();
            $table->addCell()->addText('#' . $cita->id, $cellFontStyle);
            $table->addCell()->addText($cita->paciente_nombre, $cellFontStyle);
            $table->addCell()->addText($fechaSolicitada, $cellFontStyle);
            $table->addCell()->addText($horaSolicitada, $cellFontStyle);
            $table->addCell()->addText($fechaProgramada, $cellFontStyle);
            $table->addCell()->addText($horaProgramada, $cellFontStyle);
            $table->addCell()->addText(ucfirst(str_replace('_', ' ', $cita->estado)), $cellFontStyle);
        }
        
        $section->addTextBreak(2);

        // Resumen
        $totales = [];
        foreach($citas as $cita) {
            $est = $cita->estado;
            if ($est === 'cancelada') {
                $est = $cita->cancelado_por === 'psicologo' ? 'cancelada_por_psicólogo' : 'cancelada_por_paciente';
            }
            if(!isset($totales[$est])) $totales[$est] = 0;
            $totales[$est]++;
        }

        $section->addText('RESUMEN DETALLADO DE ESTADÍSTICAS', ['bold' => true, 'size' => 14, 'color' => '334155'], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        $summaryTableStyle = ['borderSize' => 6, 'borderColor' => 'E2E8F0', 'cellMargin' => 80];
        $phpWord->addTableStyle('Summary Table', $summaryTableStyle);
        $summaryTable = $section->addTable('Summary Table');

        foreach ($totales as $est => $cantidad) {
            $summaryTable->addRow();
            $summaryTable->addCell(7000)->addText('Citas ' . ucfirst(str_replace('_', ' ', $est)) . ':', ['bold' => true, 'color' => '334155']);
            $summaryTable->addCell(3000)->addText($cantidad, ['bold' => true, 'color' => '1B1B1B'], ['alignment' => Jc::CENTER]);
        }
        $summaryTable->addRow();
        $summaryTable->addCell(7000, ['bgColor' => 'F1F5F9'])->addText('TOTAL DE CITAS:', ['bold' => true, 'color' => '334155']);
        $summaryTable->addCell(3000, ['bgColor' => 'F1F5F9'])->addText($resumen['total_citas'], ['bold' => true, 'color' => '1B1B1B'], ['alignment' => Jc::CENTER]);

        if (isset($resumen['total_pacientes'])) {
            $summaryTable->addRow();
            $summaryTable->addCell(7000)->addText('Total de Pacientes Únicos atendidos:', ['bold' => true, 'color' => '334155']);
            $summaryTable->addCell(3000)->addText($resumen['total_pacientes'], ['bold' => true, 'color' => '1B1B1B'], ['alignment' => Jc::CENTER]);

            self::addSummarySection($summaryTable, 'Distribución por Género');
            self::addSummaryRow($summaryTable, '- Hombres:', $resumen['genero']['masculino']);
            self::addSummaryRow($summaryTable, '- Mujeres:', $resumen['genero']['femenino']);

            self::addSummarySection($summaryTable, 'Rangos de Edad');
            foreach ($resumen['edades']['rangos'] as $rango => $cantidad) {
                self::addSummaryRow($summaryTable, '- ' . $rango . ' años:', $cantidad);
            }
            self::addSummaryRow($summaryTable, 'Promedio de Edad:', $resumen['edades']['promedio'] . ' años', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Mediana de Edad:', $resumen['edades']['mediana'] . ' años', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Moda de Edad:', $resumen['edades']['moda'] . ' años', 'F8FAFC');

            self::addSummarySection($summaryTable, 'Perfil Institucional / Académico');
            foreach ($resumen['perfil_academico'] as $rol => $cantidad) {
                self::addSummaryRow($summaryTable, '- ' . $rol . ':', $cantidad);
            }

            self::addSummarySection($summaryTable, 'Pacientes de acuerdo al PNF');
            $pnfLabels = [
                'Administracion' => 'Administración', 'Mecanica' => 'Mecánica', 'Mantenimiento' => 'Mantenimiento',
                'Electricidad' => 'Electricidad', 'Veterinaria' => 'Veterinaria', 'Informatica' => 'Informática',
                'PDA' => 'PDA', 'Distribucion_Logistica' => 'Distribución y Logística', 'Agroalimentacion' => 'Agroalimentación',
                'Seguridad_Alimentaria_Nutricional' => 'Seguridad alimentaria y Cultura Nutricional', 'No especificado' => 'No especificado', 'No aplica' => 'No aplica'
            ];
            foreach ($resumen['pnf'] as $pnfKey => $cantidad) {
                $label = $pnfLabels[$pnfKey] ?? $pnfKey;
                self::addSummaryRow($summaryTable, '- ' . $label . ':', $cantidad);
            }

            self::addSummarySection($summaryTable, 'Métricas Avanzadas');
            self::addSummaryRow($summaryTable, 'Hora Pico (Moda):', $resumen['hora_pico'], 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Volumen Promedio Semanal:', $resumen['promedio_semanal'] . ' citas/semana', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Tasa de Asistencia:', $resumen['tasa_asistencia'] . '%', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Tiempo de Espera Promedio:', $resumen['tiempo_espera_promedio'] . ' días', 'F8FAFC');
            self::addSummaryRow($summaryTable, 'Comparativa Mensual (Pacientes):', ($resumen['comparativa_pacientes'] > 0 ? '+' : '') . $resumen['comparativa_pacientes'] . '%', 'F8FAFC');

            if (!empty($resumen['distribucion_horas'])) {
                self::addSummarySection($summaryTable, 'Distribución por Horas de Atención');
                foreach ($resumen['distribucion_horas'] as $bloque => $cantidad) {
                    self::addSummaryRow($summaryTable, '- ' . $bloque . ':', $cantidad);
                }
            }

            if (!empty($resumen['flujo_semanal'])) {
                self::addSummarySection($summaryTable, 'Flujo de Pacientes por Semana');
                foreach ($resumen['flujo_semanal'] as $semana => $cantidad) {
                    self::addSummaryRow($summaryTable, '- Semana ' . explode('-', $semana)[0] . ':', $cantidad);
                }
            }

            self::addSummarySection($summaryTable, 'Avances Clínicos');
            foreach ($resumen['avances'] as $avance => $cantidad) {
                self::addSummaryRow($summaryTable, '- ' . $avance . ':', $cantidad);
            }

            self::addSummarySection($summaryTable, 'Pacientes por Prioridad');
            foreach ($resumen['prioridades'] as $prior => $cantidad) {
                self::addSummaryRow($summaryTable, '- ' . $prior . ':', $cantidad);
            }

            self::addSummarySection($summaryTable, 'Pacientes por Estado de Ánimo');
            foreach ($resumen['estados_animo'] as $animo => $cantidad) {
                self::addSummaryRow($summaryTable, '- ' . $animo . ':', $cantidad);
            }
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        
        $fileName = 'Estadisticas_Citas_' . date('Y_m_d_His') . '.docx';
        $tempPath = \App\Support\Archivos\Temporales::ruta($fileName);
        $objWriter->save($tempPath);

        return $tempPath;
    }

    private static function addSummarySection($table, $title)
    {
        $table->addRow();
        $table->addCell(10000, ['gridSpan' => 2, 'bgColor' => 'E2E8F0'])->addText($title, ['bold' => true, 'color' => '334155']);
    }

    private static function addSummaryRow($table, $label, $value, $bgColor = null)
    {
        $cellProps = [];
        if ($bgColor) $cellProps['bgColor'] = $bgColor;
        $table->addRow();
        $table->addCell(7000, $cellProps)->addText($label, ['color' => '334155']);
        $table->addCell(3000, $cellProps)->addText($value, ['bold' => true, 'color' => '1B1B1B'], ['alignment' => Jc::CENTER]);
    }
}
