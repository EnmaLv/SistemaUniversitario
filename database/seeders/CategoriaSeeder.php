<?php

namespace Database\Seeders;

use App\Models\TipoProducto;
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
        'Implemento deportivo' => ['Balones', 'Raquetas y mallas', 'Equipos', 'Uniformes y petos', 'Accesorios de entrenamiento'],
        'Material de oficina'  => ['Escritura', 'Papelería', 'Marcadores', 'Carpetas y archivo', 'Adhesivos y corte'],
    ];

    public function run(): void
    {
        $this->sembrar(self::ALIMENTOS);
        $this->sembrarComplementarias();
    }

    public function sembrarComplementarias(): void
    {
        $this->sembrar(self::COMPLEMENTARIAS);
    }

    public function sembrar(array $porTipo): void
    {
        foreach ($porTipo as $tipo => $categorias) {
            $tipoId = TipoProducto::idPorNombre($tipo);

            if (! $tipoId) {
                continue;
            }

            foreach ($categorias as $nombre) {
                $existe = DB::table('categoria_producto')
                    ->where(DB::raw('LOWER(nombre_cap)'), mb_strtolower($nombre))
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
