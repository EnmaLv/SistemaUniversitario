<?php

use App\Support\Permisos\VersionPermisos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NUEVA = 'implementos_materiales';

    private const NOMBRE = 'Implementos y materiales';

    private const RENOMBRADA = 'deportes_implementos';

    private const ABSORBIDA = 'material_oficina';

    public function up(): void
    {
        if (! Schema::hasTable('vista') || ! Schema::hasTable('permiso')) {
            return;
        }

        DB::transaction(function () {
            foreach ([self::RENOMBRADA, self::ABSORBIDA] as $clave) {
                $origen = $this->vista($clave);

                if (! $origen) {
                    continue;
                }

                $destino = $this->vista(self::NUEVA);

                if ($destino) {
                    $this->absorber($origen, $destino);

                    continue;
                }

                DB::table('vista')->where('id_vis', $origen->id_vis)->update([
                    'key_vis'    => self::NUEVA,
                    'nombre_vis' => self::NOMBRE,
                    'updated_at' => now(),
                ]);

                foreach (DB::table('permiso')->where('vis_id', $origen->id_vis)->get() as $permiso) {
                    DB::table('permiso')->where('id_pmi', $permiso->id_pmi)->update([
                        'nombre_pmi'   => self::NUEVA . '.' . $permiso->accion_pmi,
                        'etiqueta_pmi' => $this->etiqueta($permiso->accion_pmi),
                        'updated_at'   => now(),
                    ]);
                }
            }
        });

        VersionPermisos::invalidar();
    }

    public function down(): void
    {
    }

    private function vista(string $clave): ?object
    {
        return DB::table('vista')->where('key_vis', $clave)->first();
    }

    private function etiqueta(string $accion): string
    {
        return config("permisos.acciones.{$accion}.label", ucfirst($accion)) . ' · ' . self::NOMBRE;
    }

    private function absorber(object $origen, object $destino): void
    {
        foreach (DB::table('permiso')->where('vis_id', $origen->id_vis)->get() as $permiso) {
            $destinoId = DB::table('permiso')
                ->where('vis_id', $destino->id_vis)
                ->where('accion_pmi', $permiso->accion_pmi)
                ->value('id_pmi');

            $destinoId ??= DB::table('permiso')->insertGetId([
                'nombre_pmi'   => self::NUEVA . '.' . $permiso->accion_pmi,
                'etiqueta_pmi' => $this->etiqueta($permiso->accion_pmi),
                'accion_pmi'   => $permiso->accion_pmi,
                'vis_id'       => $destino->id_vis,
                'mod_id'       => $destino->mod_id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ], 'id_pmi');

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

        DB::table('vista')->where('id_vis', $origen->id_vis)->delete();
    }
};
