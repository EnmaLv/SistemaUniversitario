<?php

use App\Support\Permisos\VersionPermisos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DESTINO = 'deportes_prestamos';

    private const ABSORBIDA = 'deportes_entregas';

    public function up(): void
    {
        if (! Schema::hasTable('vista') || ! Schema::hasTable('permiso')) {
            return;
        }

        DB::transaction(function () {
            $origen = DB::table('vista')->where('key_vis', self::ABSORBIDA)->first();

            if (! $origen) {
                return;
            }

            $destino = DB::table('vista')->where('key_vis', self::DESTINO)->first();

            if ($destino) {
                $this->absorber($origen, $destino);
            }

            DB::table('vista')->where('id_vis', $origen->id_vis)->delete();
        });

        VersionPermisos::invalidar();
    }

    public function down(): void
    {
    }

    private function absorber(object $origen, object $destino): void
    {
        foreach (DB::table('permiso')->where('vis_id', $origen->id_vis)->get() as $permiso) {
            $destinoId = DB::table('permiso')
                ->where('vis_id', $destino->id_vis)
                ->where('accion_pmi', $permiso->accion_pmi)
                ->value('id_pmi');

            if (! $destinoId) {
                continue;
            }

            foreach (DB::table('rol_permiso')->where('pmi_id', $permiso->id_pmi)->whereNull('deleted_at')->get() as $fila) {
                if (DB::table('rol_permiso')->where('rol_id', $fila->rol_id)->where('pmi_id', $destinoId)->exists()) {
                    continue;
                }

                DB::table('rol_permiso')->insert([
                    'rol_id'     => $fila->rol_id,
                    'pmi_id'     => $destinoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (DB::table('usuario_permiso')->where('pmi_id', $permiso->id_pmi)->whereNull('deleted_at')->get() as $fila) {
                if (DB::table('usuario_permiso')->where('use_id', $fila->use_id)->where('pmi_id', $destinoId)->exists()) {
                    continue;
                }

                DB::table('usuario_permiso')->insert([
                    'use_id'              => $fila->use_id,
                    'pmi_id'              => $destinoId,
                    'tipo_usp'            => $fila->tipo_usp,
                    'fecha_inicio_usp'    => $fila->fecha_inicio_usp,
                    'fecha_fin_usp'       => $fila->fecha_fin_usp,
                    'asignado_por_use_id' => $fila->asignado_por_use_id,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    }
};
