<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pais = DB::table('pais')->where('nombre_pai', 'Venezuela')->value('id_pai')
            ?? DB::table('pais')->insertGetId(['nombre_pai' => 'Venezuela', 'created_at' => now(), 'updated_at' => now()]);

        $estados = [
            ['id_esd' => 1, 'nombre_esd' => 'Amazonas', 'pai_id' => $pais],
            ['id_esd' => 2, 'nombre_esd' => 'Anzoátegui', 'pai_id' => $pais],
            ['id_esd' => 3, 'nombre_esd' => 'Apure', 'pai_id' => $pais],
            ['id_esd' => 4, 'nombre_esd' => 'Aragua', 'pai_id' => $pais],
            ['id_esd' => 5, 'nombre_esd' => 'Barinas', 'pai_id' => $pais],
            ['id_esd' => 6, 'nombre_esd' => 'Bolívar', 'pai_id' => $pais],
            ['id_esd' => 7, 'nombre_esd' => 'Carabobo', 'pai_id' => $pais],
            ['id_esd' => 8, 'nombre_esd' => 'Cojedes', 'pai_id' => $pais],
            ['id_esd' => 9, 'nombre_esd' => 'Delta Amacuro', 'pai_id' => $pais],
            ['id_esd' => 10, 'nombre_esd' => 'Distrito Capital', 'pai_id' => $pais],
            ['id_esd' => 11, 'nombre_esd' => 'Falcón', 'pai_id' => $pais],
            ['id_esd' => 12, 'nombre_esd' => 'Guárico', 'pai_id' => $pais],
            ['id_esd' => 13, 'nombre_esd' => 'Lara', 'pai_id' => $pais],
            ['id_esd' => 14, 'nombre_esd' => 'Mérida', 'pai_id' => $pais],
            ['id_esd' => 15, 'nombre_esd' => 'Miranda', 'pai_id' => $pais],
            ['id_esd' => 16, 'nombre_esd' => 'Monagas', 'pai_id' => $pais],
            ['id_esd' => 17, 'nombre_esd' => 'Nueva Esparta', 'pai_id' => $pais],
            ['id_esd' => 18, 'nombre_esd' => 'Portuguesa', 'pai_id' => $pais],
            ['id_esd' => 19, 'nombre_esd' => 'Sucre', 'pai_id' => $pais],
            ['id_esd' => 20, 'nombre_esd' => 'Táchira', 'pai_id' => $pais],
            ['id_esd' => 21, 'nombre_esd' => 'Trujillo', 'pai_id' => $pais],
            ['id_esd' => 22, 'nombre_esd' => 'Vargas', 'pai_id' => $pais],
            ['id_esd' => 23, 'nombre_esd' => 'Yaracuy', 'pai_id' => $pais],
            ['id_esd' => 24, 'nombre_esd' => 'Zulia', 'pai_id' => $pais],
        ];
        $this->command->info('Estados insertados Exitosamente.');

        DB::table('estado')->insert($estados);
    }
}
