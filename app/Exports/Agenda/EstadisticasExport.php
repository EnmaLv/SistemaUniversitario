<?php

namespace App\Exports\Agenda;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class EstadisticasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithCustomStartCell, WithEvents, WithDrawings
{
    protected $citas;
    protected $fechaInicio;
    protected $fechaFin;
    protected $estado;
    protected $psicologo;
    protected $avanceNombre;
    protected $estadoAnimoNombre;
    protected $resumen;
    protected $prioridad;

    public function __construct($citas, $fechaInicio, $fechaFin, $estado, $psicologo, $avanceNombre = null, $resumen = null, $estadoAnimoNombre = null, $prioridad = null)
    {
        $this->citas = $citas;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->estado = $estado;
        $this->psicologo = $psicologo;
        $this->avanceNombre = $avanceNombre;
        $this->estadoAnimoNombre = $estadoAnimoNombre;
        $this->resumen = $resumen;
        $this->prioridad = $prioridad;
    }

    public function collection()
    {
        return $this->citas;
    }

    public function startCell(): string
    {
        $offset = 0;
        if ($this->avanceNombre) $offset++;
        if ($this->estadoAnimoNombre) $offset++;
        return 'A' . (11 + $offset);
    }

    public function headings(): array
    {
        return [
            'ID Cita',
            'Paciente',
            'F. Solicitud',
            'H. Solicitud',
            'F. Cita',
            'H. Cita',
            'Estado',
            'Prioridad',
        ];
    }

    public function map($cita): array
    {
        $fechaSolicitada = $cita->created_at_carbon ? $cita->created_at_carbon->format('d/m/Y') : 'N/A';
        $horaSolicitada = $cita->created_at_carbon ? $cita->created_at_carbon->format('h:i A') : 'N/A';
        
        $fechaProgramada = ($cita->fecha_carbon && !in_array($cita->estado_cita_cps, ['pendiente', 'rechazada'])) ? $cita->fecha_carbon->format('d/m/Y') : 'Por definir';
        $horaProgramada = ($cita->hora_cps && !in_array($cita->estado_cita_cps, ['pendiente', 'rechazada'])) ? Carbon::parse($cita->hora_cps)->format('h:i A') : 'Por definir';

        return [
            $cita->id_cps,
            $cita->paciente_nombre,
            $fechaSolicitada,
            $horaSolicitada,
            $fechaProgramada,
            $horaProgramada,
            ucfirst(str_replace('_', ' ', $cita->estado_cita_cps)),
            ucfirst($cita->prioridad_texto_cps ?? 'Normal'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $offset = 0;
        if ($this->avanceNombre) $offset++;
        if ($this->estadoAnimoNombre) $offset++;
        $headingRow = 11 + $offset;
        return [
            $headingRow => ['font' => ['bold' => true, 'color' => ['rgb' => '334155']], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F1F5F9']]],
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Encabezado');
        $drawing->setDescription('Encabezado');
        $drawing->setPath(public_path('img/encabezado.png'));
        $drawing->setWidth(900);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function title(): string
    {
        return 'Historial de Citas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set row heights for image space
                $sheet->getRowDimension(1)->setRowHeight(15);
                $sheet->getRowDimension(2)->setRowHeight(15);
                $sheet->getRowDimension(3)->setRowHeight(15);
                $sheet->getRowDimension(4)->setRowHeight(15);

                $sheet->mergeCells('A6:F6');
                $sheet->setCellValue('A6', 'REPORTE DE ESTADÍSTICAS DE CITAS');
                $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A7:F7');
                $sheet->setCellValue('A7', 'Psicólogo: ' . $this->psicologo->nombres . ' ' . $this->psicologo->apellidos);
                
                $sheet->mergeCells('A8:F8');
                $sheet->setCellValue('A8', 'Período: ' . Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($this->fechaFin)->format('d/m/Y'));
                
                $sheet->mergeCells('A9:F9');
                $estadoTexto = $this->estado ? ucfirst(str_replace('_', ' ', $this->estado)) : 'Todos';
                $sheet->setCellValue('A9', 'Estado filtrado: ' . $estadoTexto);

                $currentRowHeader = 10;
                if ($this->avanceNombre) {
                    $sheet->mergeCells('A'.$currentRowHeader.':F'.$currentRowHeader);
                    $sheet->setCellValue('A'.$currentRowHeader, 'Avance de Sesión: ' . $this->avanceNombre);
                    $currentRowHeader++;
                }
                if ($this->estadoAnimoNombre) {
                    $sheet->mergeCells('A'.$currentRowHeader.':F'.$currentRowHeader);
                    $sheet->setCellValue('A'.$currentRowHeader, 'Estado de Ánimo: ' . $this->estadoAnimoNombre);
                    $currentRowHeader++;
                }
                if ($this->prioridad) {
                    $sheet->mergeCells('A'.$currentRowHeader.':F'.$currentRowHeader);
                    $sheet->setCellValue('A'.$currentRowHeader, 'Prioridad: ' . ucfirst($this->prioridad));
                    $currentRowHeader++;
                }

                $offset = 0;
                if ($this->avanceNombre) $offset++;
                if ($this->estadoAnimoNombre) $offset++;
                if ($this->prioridad) $offset++;
                $headingRow = 11 + $offset;
                $lastRow = $headingRow + count($this->citas);
                
                // Add Summary
                $summaryStartRow = $lastRow + 2;
                
                $sheet->mergeCells('A' . $summaryStartRow . ':H' . $summaryStartRow);
                $sheet->setCellValue('A' . $summaryStartRow, 'Resumen Detallado de Estadísticas');
                $sheet->getStyle('A' . $summaryStartRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                $sheet->getStyle('A' . $summaryStartRow . ':H' . $summaryStartRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                $sheet->getStyle('A' . $summaryStartRow . ':H' . $summaryStartRow)->getBorders()->getBottom()->setBorderStyle('thin');
                
                $totales = [];
                foreach($this->citas as $cita) {
                    $estado = $cita->estado_cita_cps;
                    if(!isset($totales[$estado])) {
                        $totales[$estado] = 0;
                    }
                    $totales[$estado]++;
                }
                
                $currentRow = $summaryStartRow + 1;
                foreach($totales as $estado => $cantidad) {
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Citas ' . ucfirst(str_replace('_', ' ', $estado)) . ':');
                    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal('left');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $cantidad);
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;
                }
                if ($this->resumen) {
                    // Total de pacientes
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Total de Pacientes Únicos:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['total_pacientes']);
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;

                    // Género
                    $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Distribución por Género');
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                    $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                    $currentRow++;
                    
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, '- Hombres:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['genero']['masculino']);
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;
                    
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, '- Mujeres:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['genero']['femenino']);
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;

                    // Edades
                    $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Rangos de Edad');
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                    $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                    $currentRow++;
                    
                    foreach ($this->resumen['edades']['rangos'] as $rango => $cantidad) {
                        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                        $sheet->setCellValue('A' . $currentRow, '- ' . $rango . ' años:');
                        $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                        $sheet->setCellValue('F' . $currentRow, $cantidad);
                        $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                        $currentRow++;
                    }
                    
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Promedio de Edad:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['edades']['promedio'] . ' años');
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;
                    
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Mediana de Edad:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['edades']['mediana'] . ' años');
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;
                    
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Moda de Edad:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['edades']['moda'] . ' años');
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;

                    // Perfil Institucional / Académico
                    if (isset($this->resumen['perfil_academico'])) {
                        $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                        $sheet->setCellValue('A' . $currentRow, 'Perfil Institucional / Académico');
                        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                        $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                        $currentRow++;
                        
                        foreach ($this->resumen['perfil_academico'] as $rol => $cantidad) {
                            $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                            $sheet->setCellValue('A' . $currentRow, '- ' . $rol . ':');
                            $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                            $sheet->setCellValue('F' . $currentRow, $cantidad);
                            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                            $currentRow++;
                        }
                    }

                    // Pacientes de acuerdo al PNF
                    if (isset($this->resumen['pnf'])) {
                        $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                        $sheet->setCellValue('A' . $currentRow, 'Pacientes de acuerdo al PNF');
                        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                        $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                        $currentRow++;
                        
                        $pnfLabels = [
                            'Administracion' => 'Administración',
                            'Mecanica' => 'Mecánica',
                            'Mantenimiento' => 'Mantenimiento',
                            'Electricidad' => 'Electricidad',
                            'Veterinaria' => 'Veterinaria',
                            'Informatica' => 'Informática',
                            'PDA' => 'PDA',
                            'Distribucion_Logistica' => 'Distribución y Logística',
                            'Agroalimentacion' => 'Agroalimentación',
                            'Seguridad_Alimentaria_Nutricional' => 'Seguridad alimentaria y Cultura Nutricional',
                            'No especificado' => 'No especificado',
                            'No aplica' => 'No aplica'
                        ];

                        foreach ($this->resumen['pnf'] as $pnfKey => $cantidad) {
                            $label = $pnfLabels[$pnfKey] ?? $pnfKey;
                            $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                            $sheet->setCellValue('A' . $currentRow, '- ' . $label . ':');
                            $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                            $sheet->setCellValue('F' . $currentRow, $cantidad);
                            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                            $currentRow++;
                        }
                    }

                    // Nuevas Estadisticas
                    $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Métricas Avanzadas');
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                    $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                    $currentRow++;

                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Hora Pico (Moda):');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['hora_pico']);
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;
                    
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Volumen Promedio Semanal:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['promedio_semanal'] . ' citas/semana');
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;
                    
                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Tasa de Asistencia:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['tasa_asistencia'] . '%');
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;

                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Tiempo de Espera Promedio:');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, $this->resumen['tiempo_espera_promedio'] . ' días');
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;

                    $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Comparativa Mensual (Pacientes):');
                    $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('F' . $currentRow, ($this->resumen['comparativa_pacientes'] > 0 ? '+' : '') . $this->resumen['comparativa_pacientes'] . '%');
                    $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                    $currentRow++;

                    // Avances
                    $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Avances Clínicos');
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                    $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                    $currentRow++;
                    
                    foreach ($this->resumen['avances'] as $avance => $cantidad) {
                        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                        $sheet->setCellValue('A' . $currentRow, '- ' . $avance . ':');
                        $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                        $sheet->setCellValue('F' . $currentRow, $cantidad);
                        $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                        $currentRow++;
                    }

                    // Distribución de Horas
                    if (!empty($this->resumen['distribucion_horas'])) {
                        $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                        $sheet->setCellValue('A' . $currentRow, 'Distribución por Horas de Atención');
                        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                        $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                        $currentRow++;
                        
                        foreach ($this->resumen['distribucion_horas'] as $bloque => $cantidad) {
                            $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                            $sheet->setCellValue('A' . $currentRow, '- ' . $bloque . ':');
                            $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                            $sheet->setCellValue('F' . $currentRow, $cantidad);
                            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                            $currentRow++;
                        }
                    }

                    // Flujo Semanal
                    if (!empty($this->resumen['flujo_semanal'])) {
                        $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                        $sheet->setCellValue('A' . $currentRow, 'Flujo de Pacientes por Semana');
                        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                        $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                        $currentRow++;
                        
                        foreach ($this->resumen['flujo_semanal'] as $semana => $cantidad) {
                            $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                            $sheet->setCellValue('A' . $currentRow, '- Semana ' . explode('-', $semana)[0] . ':');
                            $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                            $sheet->setCellValue('F' . $currentRow, $cantidad);
                            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                            $currentRow++;
                        }
                    }

                    // Prioridades
                    $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Pacientes por Prioridad');
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                    $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                    $currentRow++;
                    
                    foreach ($this->resumen['prioridades'] as $prioridad => $cantidad) {
                        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                        $sheet->setCellValue('A' . $currentRow, '- ' . $prioridad . ':');
                        $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                        $sheet->setCellValue('F' . $currentRow, $cantidad);
                        $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                        $currentRow++;
                    }

                    // Estados de Animo
                    $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
                    $sheet->setCellValue('A' . $currentRow, 'Pacientes por Estado de Ánimo');
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('334155');
                    $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType('solid')->getStartColor()->setRGB('F1F5F9');
                    $currentRow++;
                    
                    foreach ($this->resumen['estados_animo'] as $estadoAnimo => $cantidad) {
                        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
                        $sheet->setCellValue('A' . $currentRow, '- ' . $estadoAnimo . ':');
                        $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
                        $sheet->setCellValue('F' . $currentRow, $cantidad);
                        $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal('center');
                        $currentRow++;
                    }
                }
                
                // Fondo blanco para todos los contenidos de la tabla de resumen y alinear a la izquierda
                $sheet->getStyle('A' . ($summaryStartRow + 1) . ':H' . ($currentRow - 1))->getFill()->setFillType('solid')->getStartColor()->setRGB('FFFFFF');
                $sheet->getStyle('A' . $summaryStartRow . ':H' . ($currentRow - 1))->getBorders()->getAllBorders()->setBorderStyle('thin');

                $sheet->setSelectedCell('A1');

            },
        ];
    }
}
