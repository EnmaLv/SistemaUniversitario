<?php

namespace Database\Seeders;

use App\Models\Perfil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        foreach (Perfil::NOMBRES as $nombre) {
            if (DB::table('perfil')->where('nombre_perfil', $nombre)->exists()) {
                continue;
            }

            DB::table('perfil')->insert([
                'nombre_perfil' => $nombre,
                'status_id'     => 1,
                'created_at'    => $ahora,
                'updated_at'    => $ahora,
            ]);
        }

        Perfil::limpiarCache();
    }
}
