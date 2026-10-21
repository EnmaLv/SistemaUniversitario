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
                'cap_id'                     => 22,
                'codigo_pdt'                 => 'ANT-ACE-001',
                'nombre_pdt'                 => 'ACETAMINOFEN 500 MG',
                'descripcion_pdt'            => 'ANALGÉSICO Y ANTIPIRÉTICO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 15,
                'stock_maximo_pdt'           => 60,
                'peso_contenido_pdt'         => 10,
                'unm_id'                     => 1,
                'unidades_presentacion_pdt'  => 10,
                'enp_id'                     => 1,
                'status_id'                  => 1,
            ],
            [
                'cap_id'                     => 21,
                'codigo_pdt'                 => 'ANT-LOR-006',
                'nombre_pdt'                 => 'LORATADINA 10 MG',
                'descripcion_pdt'            => 'ANTIHISTAMÍNICO UTILIZADO PARA ALIVIAR LOS SÍNTOMAS ALÉRGICOS.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 10,
                'stock_maximo_pdt'           => 40,
                'peso_contenido_pdt'         => 8,
                'unm_id'                     => 1,
                'unidades_presentacion_pdt'  => 10,
                'enp_id'                     => 1,
                'status_id'                  => 1,
            ],
            [
                'cap_id'                     => 15,
                'codigo_pdt'                 => 'ANT-IBU-008',
                'nombre_pdt'                 => 'IBUPROFENO 800 MG',
                'descripcion_pdt'            => 'ANTIINFLAMATORIO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 10,
                'stock_maximo_pdt'           => 50,
                'peso_contenido_pdt'         => 15,
                'unm_id'                     => 1,
                'unidades_presentacion_pdt'  => 10,
                'enp_id'                     => 1,
                'status_id'                  => 1,
            ],
            [
                'cap_id'                     => 14,
                'codigo_pdt'                 => 'ANA-ATA-001',
                'nombre_pdt'                 => 'ATAMEL 500 MG',
                'descripcion_pdt'            => 'ANALGÉSICO Y ANTIPIRÉTICO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 10,
                'stock_maximo_pdt'           => 50,
                'peso_contenido_pdt'         => 10,
                'unm_id'                     => 1,
                'unidades_presentacion_pdt'  => 10,
                'enp_id'                     => 1,
                'status_id'                  => 1,
            ],
            [
                'cap_id'                     => 15,
                'codigo_pdt'                 => 'ANT-BRU-002',
                'nombre_pdt'                 => 'BRUGESIC 400 MG',
                'descripcion_pdt'            => 'ANTIINFLAMATORIO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 10,
                'stock_maximo_pdt'           => 50,
                'peso_contenido_pdt'         => 12,
                'unm_id'                     => 1,
                'unidades_presentacion_pdt'  => 10,
                'enp_id'                     => 1,
                'status_id'                  => 1,
            ],
        ];

        foreach ($productos as $p) {
            $p['created_at'] = now();
            $p['updated_at'] = now();

            DB::table('producto')->insert($p);
        }
    }
}