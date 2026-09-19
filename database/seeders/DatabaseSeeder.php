<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EnfermedadesSeeder::class,
            DepartamentoSeeder::class,
            EstatusSeeder::class,
            UnidadSeeder::class,
            ModulosSeeder::class,
            TipoProductoSeeder::class,
            CategoriaSeeder::class,
            SedeSeeder::class,
            RecetaSeeder::class,
            EstadoSeeder::class,
            MunicipioSeeder::class,
            LocalidadSeeder::class,
            PerfilSeeder::class,
            PnfSeeder::class,
            PersonaSeeder::class,
            ProductoSeeder::class,
            RecetaIngredienteSeeder::class,
            PrecioProductoSeeder::class,
            EnvasePrimarioSeeder::class,
            CategoriaMedicamentoSeeder::class,
            RolModuloSeeder::class,
            GrupoHorarioSeeder::class,
            AvanceSesionSeeder::class,
            NotaEvolucionSeeder::class,
            EstadoAnimoSeeder::class,
            PlantillaGlobalSeeder::class,
            PrioridadesSeeder::class,
            MedicamentoSeeder::class,
            ConsultorioSeeder::class,
            HorarioConsultoriosSeeder::class,
        ]);
        \App\Models\Proveedor::factory(1)->create();
    }
}
