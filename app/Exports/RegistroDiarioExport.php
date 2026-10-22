<?php

namespace App\Exports;

use App\Models\Registro_diario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;




class RegistroDiarioExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithCustomStartCell
{

    private $filtro;

    public function __construct(array $filtro)
    {
        $this->filtro = $filtro;
    }

    public function map($registro): array
    {
        return [
            $registro->id_rgc,
            $registro->cedula_per,
            $registro->nombres_per,
            $registro->apellidos_per,
            $registro->nombre_prg,
            $registro->fecha_comedor_rgc,
            $registro->sector_dir ?? 'N/A',
        ]; 
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cedula',
            'Nombre',
            'Apellido',
            'PNF',
            'Fecha Registro',
            'Direccion',
        ];
    }

    public function collection()
    {
        return Registro_diario::showData($this->filtro, true);
    }
}
