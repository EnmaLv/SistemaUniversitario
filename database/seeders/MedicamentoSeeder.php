<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'categoria_id'               => 22,
                'codigo'                     => 'ANT-ACE-001',
                'nombre'                     => 'ACETAMINOFEN 500 MG',
                'descripcion'                => 'ANALGÉSICO Y ANTIPIRÉTICO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen'                     => null,
                'precio_compra'              => 800,
                'stock_minimo'               => 15,
                'stock_maximo'               => 60,
                'peso_contenido'             => 10, 
                'unidad_id'                  => 1,
                'unidades_por_presentacion'  => 10,
                'presentacion_id'            => 1,
                'estado'                     => 1,
            ],
            [
                'categoria_id'               => 21,
                'codigo'                     => 'ANT-LOR-006',
                'nombre'                     => 'LORATADINA 10 MG',
                'descripcion'                => 'ANTIHISTAMÍNICO UTILIZADO PARA ALIVIAR LOS SÍNTOMAS ALÉRGICOS.',
                'imagen'                     => null,
                'precio_compra'              => 800,
                'stock_minimo'               => 10,
                'stock_maximo'               => 40,
                'peso_contenido'             => 8,
                'unidad_id'                  => 1,
                'unidades_por_presentacion'  => 10,
                'presentacion_id'            => 1,
                'estado'                     => 1,
            ],
            [
                'categoria_id'               => 15,
                'codigo'                     => 'ANT-IBU-008',
                'nombre'                     => 'IBUPROFENO 800 MG',
                'descripcion'                => 'ANTIINFLAMATORIO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen'                     => null,
                'precio_compra'              => 800,
                'stock_minimo'               => 10,
                'stock_maximo'               => 50,
                'peso_contenido'             => 15,
                'unidad_id'                  => 1,
                'unidades_por_presentacion'  => 10,
                'presentacion_id'            => 1,
                'estado'                     => 1,
            ],
            [
                'categoria_id'               => 14,
                'codigo'                     => 'ANA-ATA-001',
                'nombre'                     => 'ATAMEL 500 MG',
                'descripcion'                => 'ANALGÉSICO Y ANTIPIRÉTICO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen'                     => null,
                'precio_compra'              => 800,
                'stock_minimo'               => 10,
                'stock_maximo'               => 50,
                'peso_contenido'             => 10,
                'unidad_id'                  => 1,
                'unidades_por_presentacion'  => 10,
                'presentacion_id'            => 1,
                'estado'                     => 1,
            ],
            [
                'categoria_id'               => 15,
                'codigo'                     => 'ANT-BRU-002',
                'nombre'                     => 'BRUGESIC 400 MG',
                'descripcion'                => 'ANTIINFLAMATORIO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen'                     => null,
                'precio_compra'              => 800,
                'stock_minimo'               => 10,
                'stock_maximo'               => 50,
                'peso_contenido'             => 12,
                'unidad_id'                  => 1,
                'unidades_por_presentacion'  => 10,
                'presentacion_id'            => 1,
                'estado'                     => 1,
            ],
        ];

        foreach ($productos as $p) {
            $p['created_at'] = now();
            $p['updated_at'] = now();

            DB::table('productos')->insert($p);
        }
    }
}