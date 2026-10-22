<?php

namespace Database\Seeders;

use App\Models\TipoProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresentacionAreaSeeder extends Seeder
{
    public const PRESENTACIONES = [
        TipoProducto::IMPLEMENTO_DEPORTIVO => ['CAJA', 'PAQUETE', 'BOLSA', 'DOCENA'],
        TipoProducto::MATERIAL_OFICINA     => ['CAJA', 'PAQUETE', 'BOLSA', 'DOCENA', 'RESMA'],
    ];

    public function run(): void
    {
        static::sembrar(self::PRESENTACIONES);
    }

    public static function sembrar(array $porTipo): void
    {
        foreach ($porTipo as $tipo => $nombres) {
            $tipoId = TipoProducto::idPorNombre($tipo);

            if ($tipoId === null) {
                continue;
            }

            foreach ($nombres as $nombre) {
                $envase = DB::table('envase_primario')
                    ->whereNull('deleted_at')
                    ->where(DB::raw('LOWER(nombre_enp)'), mb_strtolower($nombre))
                    ->value('id_enp');

                $envase ??= DB::table('envase_primario')->insertGetId([
                    'nombre_enp' => $nombre,
                    'status_id'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'id_enp');

                $vinculado = DB::table('envase_tipo_producto')
                    ->where('enp_id', $envase)
                    ->where('tpr_id', $tipoId)
                    ->exists();

                if (! $vinculado) {
                    DB::table('envase_tipo_producto')->insert([
                        'enp_id'     => $envase,
                        'tpr_id'     => $tipoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
