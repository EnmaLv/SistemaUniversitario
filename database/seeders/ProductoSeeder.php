<?php

namespace Database\Seeders;

use App\Models\TipoProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    private const PRODUCTOS = [
        ['CER-HAR-001', 'Harina', 'Cereales', 'Kilo', 1000, 5, 100],
        ['EMB-MOR-002', 'Mortadela', 'Embutidos', 'Kilo', 1000, 5, 100],
        ['CAR-AZU-003', 'Azucar', 'Carbohidrato', 'Kilo', 1000, 5, 100],
        ['FRU-PUL-004', 'Pulpa de Mora', 'Frutas', 'Kilo', 1000, 5, 100],
        ['GRA-MAN-005', 'Mantequilla 1k', 'Grasas', 'Kilo', 1000, 5, 100],
        ['GRA-MAN-005-1', 'Mantequilla 500g', 'Grasas', 'Unidad', 500, 5, 100],
        ['CAR-CAR-006', 'Carne Molida', 'Carnes', 'Kilo', 1000, 5, 100],
        ['VER-PAP-007', 'Papa', 'Verduras', 'Kilo', 1000, 5, 100],
        ['VER-ZAN-008', 'Zanahoria', 'Verduras', 'Kilo', 1000, 5, 100],
        ['VER-ALI-009', 'Aliños Verdes', 'Verduras', 'Kilo', 1000, 5, 100],
        ['VER-CEB-010', 'Cebolla', 'Verduras', 'Kilo', 1000, 5, 100],
        ['FRU-TOM-011', 'Tomate', 'Frutas', 'Kilo', 1000, 5, 100],
        ['VER-AJO-012', 'Ajo', 'Verduras', 'Kilo', 1000, 5, 100],
        ['EMB-SAL-013', 'Salchicha', 'Embutidos', 'Kilo', 1000, 5, 100],
        ['CAR-POL-014', 'Pollo', 'Carnes', 'Kilo', 1000, 5, 100],
        ['LAC-QUE-015', 'Queso Blanco', 'Lácteos', 'Kilo', 1000, 5, 100],
        ['LAC-QUE-015-1', 'Queso Amarillo', 'Lácteos', 'Unidad', 300, 5, 100],
        ['CAR-RES-016', 'Carne de Res', 'Carnes', 'Kilo', 1000, 5, 80],
        ['CAR-CER-017', 'Carne de Cerdo', 'Carnes', 'Kilo', 1000, 5, 80],
        ['FRU-MAN-018', 'Manzana', 'Frutas', 'Kilo', 1000, 10, 150],
        ['FRU-BAN-019', 'Cambur', 'Frutas', 'Kilo', 1000, 10, 150],
        ['VER-LEC-020', 'Lechuga', 'Verduras', 'Unidad', 500, 5, 60],
        ['VER-PIM-021', 'Pimentón', 'Verduras', 'Unidad', 500, 5, 60],
        ['CER-ARR-022', 'Arroz', 'Cereales', 'Kilo', 1000, 10, 200],
        ['CAR-PAST-023', 'Pasta', 'Carbohidrato', 'Unidad', 500, 10, 200],
        ['LAC-LEC-024', 'Leche', 'Lácteos', 'Litro', 1000, 10, 120],
        ['LAC-YOG-025', 'Yogurt', 'Lácteos', 'Unidad', 500, 10, 80],
        ['BEB-AGU-026', 'Agua Mineral', 'Bebidas', 'Litro', 1500, 20, 300],
        ['BEB-REF-027', 'Refresco', 'Bebidas', 'Litro', 2000, 20, 300],
        ['CON-PIM-028', 'Pimienta', 'Condimentos', 'Unidad', 100, 5, 40],
        ['CON-COM-029', 'Comino', 'Condimentos', 'Unidad', 100, 5, 40],
    ];

    public function run(): void
    {
        foreach (self::PRODUCTOS as [$codigo, $nombre, $categoria, $unidad, $peso, $minimo, $maximo]) {
            if (DB::table('producto')->where('codigo_pdt', $codigo)->exists()) {
                continue;
            }

            DB::table('producto')->insert([
                'cap_id'             => self::categoria(TipoProducto::ALIMENTO, $categoria),
                'codigo_pdt'         => $codigo,
                'nombre_pdt'         => $nombre,
                'descripcion_pdt'    => null,
                'imagen_pdt'         => null,
                'stock_minimo_pdt'   => $minimo,
                'stock_maximo_pdt'   => $maximo,
                'peso_contenido_pdt' => $peso,
                'unm_id'             => self::unidad($unidad),
                'enp_id'             => null,
                'status_id'          => 1,
            ]);
        }
    }

    public static function categoria(string $tipo, string $nombre): int
    {
        $id = DB::table('categoria_producto as c')
            ->join('tipo_producto as t', 't.id_tpr', '=', 'c.tpr_id')
            ->where(DB::raw('LOWER(t.nombre_tpr)'), mb_strtolower($tipo))
            ->where(DB::raw('LOWER(c.nombre_cap)'), mb_strtolower($nombre))
            ->whereNull('c.deleted_at')
            ->value('c.id_cap');

        if ($id === null) {
            throw new \RuntimeException("No existe la categoría \"{$nombre}\" del tipo \"{$tipo}\". Ejecuta antes el seeder de categorías.");
        }

        return (int) $id;
    }

    public static function unidad(string $nombre): int
    {
        $id = DB::table('unidad_medida')->where('nombre_unm', $nombre)->value('id_unm');

        if ($id === null) {
            throw new \RuntimeException("No existe la unidad de medida \"{$nombre}\".");
        }

        return (int) $id;
    }

    public static function producto(string $codigo): int
    {
        $id = DB::table('producto')->where('codigo_pdt', $codigo)->value('id_pdt');

        if ($id === null) {
            throw new \RuntimeException("No existe el producto con código \"{$codigo}\".");
        }

        return (int) $id;
    }
}
