<?php

namespace Database\Seeders;

use App\Models\TipoProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'categoria'                  => 'ANTIPIRÉTICOS',
                'codigo_pdt'                 => 'ANT-ACE-001',
                'nombre_pdt'                 => 'ACETAMINOFEN 500 MG',
                'descripcion_pdt'            => 'ANALGÉSICO Y ANTIPIRÉTICO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 15,
                'stock_maximo_pdt'           => 60,
                'peso_contenido_pdt'         => 10,
                'unidad'                     => 'Unidad',
                'unidades_presentacion_pdt'  => 10,
                'envase'                     => 'BLISTER',
                'status_id'                  => 1,
            ],
            [
                'categoria'                  => 'ANTIHISTAMÍNICOS',
                'codigo_pdt'                 => 'ANT-LOR-006',
                'nombre_pdt'                 => 'LORATADINA 10 MG',
                'descripcion_pdt'            => 'ANTIHISTAMÍNICO UTILIZADO PARA ALIVIAR LOS SÍNTOMAS ALÉRGICOS.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 10,
                'stock_maximo_pdt'           => 40,
                'peso_contenido_pdt'         => 8,
                'unidad'                     => 'Unidad',
                'unidades_presentacion_pdt'  => 10,
                'envase'                     => 'BLISTER',
                'status_id'                  => 1,
            ],
            [
                'categoria'                  => 'ANTIINFLAMATORIOS',
                'codigo_pdt'                 => 'ANT-IBU-008',
                'nombre_pdt'                 => 'IBUPROFENO 800 MG',
                'descripcion_pdt'            => 'ANTIINFLAMATORIO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 10,
                'stock_maximo_pdt'           => 50,
                'peso_contenido_pdt'         => 15,
                'unidad'                     => 'Unidad',
                'unidades_presentacion_pdt'  => 10,
                'envase'                     => 'BLISTER',
                'status_id'                  => 1,
            ],
            [
                'categoria'                  => 'ANALGÉSICOS',
                'codigo_pdt'                 => 'ANA-ATA-001',
                'nombre_pdt'                 => 'ATAMEL 500 MG',
                'descripcion_pdt'            => 'ANALGÉSICO Y ANTIPIRÉTICO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 10,
                'stock_maximo_pdt'           => 50,
                'peso_contenido_pdt'         => 10,
                'unidad'                     => 'Unidad',
                'unidades_presentacion_pdt'  => 10,
                'envase'                     => 'BLISTER',
                'status_id'                  => 1,
            ],
            [
                'categoria'                  => 'ANTIINFLAMATORIOS',
                'codigo_pdt'                 => 'ANT-BRU-002',
                'nombre_pdt'                 => 'BRUGESIC 400 MG',
                'descripcion_pdt'            => 'ANTIINFLAMATORIO UTILIZADO PARA ALIVIAR EL DOLOR Y REDUCIR LA FIEBRE.',
                'imagen_pdt'                 => null,
                'precio_compra_pdt'          => 800,
                'stock_minimo_pdt'           => 10,
                'stock_maximo_pdt'           => 50,
                'peso_contenido_pdt'         => 12,
                'unidad'                     => 'Unidad',
                'unidades_presentacion_pdt'  => 10,
                'envase'                     => 'BLISTER',
                'status_id'                  => 1,
            ],
        ];

        $costos = [
            'ANT-ACE-001' => 2.00,
            'ANT-LOR-006' => 1.80,
            'ANT-IBU-008' => 2.50,
            'ANA-ATA-001' => 1.90,
            'ANT-BRU-002' => 2.20,
        ];

        foreach ($productos as $p) {
            if (DB::table('producto')->where('codigo_pdt', $p['codigo_pdt'])->exists()) {
                continue;
            }

            $p['cap_id'] = ProductoSeeder::categoria(TipoProducto::MEDICAMENTO, $p['categoria']);
            $p['unm_id'] = ProductoSeeder::unidad($p['unidad']);
            $p['enp_id'] = DB::table('envase_primario')->where('nombre_enp', $p['envase'])->value('id_enp');
            $p['created_at'] = now();
            $p['updated_at'] = now();
            unset($p['categoria'], $p['unidad'], $p['envase']);

            $id = DB::table('producto')->insertGetId($p, 'id_pdt');

            DB::table('precio_producto')->insert([
                'pdt_id'        => $id,
                'costo_usd_ppr' => $costos[$p['codigo_pdt']],
                'margen_ppr'    => 20,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}