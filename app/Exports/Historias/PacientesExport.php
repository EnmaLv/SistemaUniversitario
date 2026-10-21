<?php

namespace App\Exports\Historias;

use App\Models\salud\HistoriaClinica;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PacientesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithDrawings, WithCustomStartCell, WithEvents
{
    protected $psicologoId;
    protected $search;
    protected $filters;
    protected $filterNames;

    public function __construct($psicologoId, $search = null, $filters = [], $filterNames = [])
    {
        $this->psicologoId = $psicologoId;
        $this->search = $search;
        $this->filters = $filters;
        $this->filterNames = $filterNames;
    }

    public function collection()
    {
        return HistoriaClinica::obtenerListado($this->psicologoId, $this->search, $this->filters);
    }

    public function map($historia): array
    {
        $paciente = $historia['paciente'] ?? null;
        $persona = $paciente?->persona;
        $fechaNacimiento = $persona?->fecha_nacimiento_per;
        return [
            $persona->nombres_per ?? '',
            $persona->apellidos_per ?? '',
            $persona->cedula_per ?? '',
            $persona?->personaPnf->first()?->pnf?->nombre_prg ?? 'No asignado',
            $fechaNacimiento ? \Carbon\Carbon::parse($fechaNacimiento)->age : 'N/A',
            $fechaNacimiento ? \Carbon\Carbon::parse($fechaNacimiento)->format('d/m/Y') : 'N/A',
            $persona->telefono_per ?? 'N/A',
            $persona->correo_per ?? '',
            $historia['citas_realizadas'] ?? 0,
        ];
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Encabezado');
        $drawing->setDescription('Encabezado del reporte');
        $drawing->setPath(public_path('img/encabezado.png'));
        $columnCount = count($this->headings());
        $pxPerColumn = 110;
        $estimatedWidth = $columnCount * $pxPerColumn;
        $drawing->setWidth($estimatedWidth);
        $drawing->setResizeProportional(true);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(10);

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->getRowDimension(1)->setRowHeight(15);
                $sheet->getRowDimension(2)->setRowHeight(15);
                $sheet->getRowDimension(3)->setRowHeight(15);
                $sheet->getRowDimension(4)->setRowHeight(15);

                $sheet->mergeCells('A6:I6');
                $sheet->setCellValue('A6', 'REPORTE DE PACIENTES - HISTORIAL CLÍNICO');
                $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');

                $filtros = [];
                if (!empty($this->filterNames['fecha_desde']) && !empty($this->filterNames['fecha_hasta'])) {
                    $filtros[] = 'Fechas: ' . \Carbon\Carbon::parse($this->filterNames['fecha_desde'])->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($this->filterNames['fecha_hasta'])->format('d/m/Y');
                }
                if (!empty($this->filterNames['pnf'])) $filtros[] = 'PNF: ' . $this->filterNames['pnf'];
                if (!empty($this->filterNames['edad'])) $filtros[] = 'Edad: ' . $this->filterNames['edad'];
                if (!empty($this->filterNames['enfermedad'])) $filtros[] = 'Enfermedad: ' . $this->filterNames['enfermedad'];
                if (!empty($this->filterNames['prioridad'])) $filtros[] = 'Prioridad: ' . $this->filterNames['prioridad'];
                if (!empty($this->filterNames['avance'])) $filtros[] = 'Avance: ' . $this->filterNames['avance'];
                if (!empty($this->filterNames['estado_animo'])) $filtros[] = 'Ánimo: ' . $this->filterNames['estado_animo'];

                if (count($filtros) > 0) {
                    $sheet->mergeCells('A8:I8');
                    $sheet->setCellValue('A8', 'Filtros Aplicados: ' . implode(' | ', $filtros));
                    $sheet->getStyle('A8')->getFont()->setItalic(true)->getColor()->setRGB('4B5563');
                    $sheet->getStyle('A8')->getAlignment()->setHorizontal('center');
                }
            }
        ];
    }

    public function headings(): array
    {
        return [
            'Nombres',
            'Apellidos',
            'Cédula',
            'PNF / Carrera',
            'Edad',
            'Fecha Nacimiento',
            'Teléfono',
            'Correo',
            'Sesiones Realizadas',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            10    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']]],
        ];
    }
}
