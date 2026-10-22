<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    public const ALIMENTOS = [
        'Alimento' => [
            'Embutidos', 'Frutas', 'Verduras', 'Legumbres', 'Carnes', 'Lácteos', 'Cereales',
            'Aceite', 'Carbohidrato', 'Grasas', 'Bebidas', 'Enlatados', 'Condimentos',
        ],
    ];

    public const COMPLEMENTARIAS = [
        'Implemento deportivo' => ['Balones', 'Raquetas y mallas'],
        'Material de oficina'  => ['Papelería', 'Escritura'],
    ];

    public function run(): void
    {
        $this->sembrar(self::ALIMENTOS);

        $medicamentosSembrados = DB::table('categoria_producto')
            ->join('tipo_producto', 'tipo_producto.id_tpr', '=', 'categoria_producto.tpr_id')
            ->where('tipo_producto.nombre_tpr', 'Medicamento')
            ->exists();

        if ($medicamentosSembrados) {
            $this->sembrarComplementarias();
        }
    }

    public function sembrarComplementarias(): void
    {
        $this->sembrar(self::COMPLEMENTARIAS);
    }

    private function sembrar(array $porTipo): void
    {
        foreach ($porTipo as $tipo => $categorias) {
            $tipoId = DB::table('tipo_producto')->where('nombre_tpr', $tipo)->value('id_tpr');

            if (! $tipoId) {
                continue;
            }

            foreach ($categorias as $nombre) {
                $existe = DB::table('categoria_producto')
                    ->where('nombre_cap', $nombre)
                    ->where('tpr_id', $tipoId)
                    ->exists();

                if ($existe) {
                    continue;
                }

                DB::table('categoria_producto')->insert([
                    'nombre_cap'      => $nombre,
                    'descripcion_cap' => null,
                    'tpr_id'          => $tipoId,
                    'status_id'       => 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }
}
