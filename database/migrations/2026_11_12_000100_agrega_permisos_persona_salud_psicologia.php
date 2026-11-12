<?php

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Vista;
use App\Support\Permisos\ResolutorAccion;
use App\Support\Permisos\SincronizadorPermisos;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACCIONES = ['ver', 'crear', 'editar'];

    public function up(): void
    {
        if (! Schema::hasTable('vista') || ! Schema::hasTable('permiso')) {
            return;
        }

        DB::transaction(function () {
            $modSaludId = DB::table('modulo')->where('key_mod', 'salud')->value('id_mod');
            $modPsicoId = DB::table('modulo')->where('key_mod', 'psicologia')->value('id_mod');

            $vistasConfig = [
                'persona_salud' => [
                    'nombre' => 'Personas',
                    'modulo_id' => $modSaludId,
                    'orden' => 90,
                ],
                'persona_psicologia' => [
                    'nombre' => 'Personas',
                    'modulo_id' => $modPsicoId,
                    'orden' => 91,
                ],
            ];

            foreach ($vistasConfig as $key => $datos) {
                $vista = Vista::firstOrNew(['key_vis' => $key]);
                $vista->nombre_vis = $datos['nombre'];
                $vista->orden_vis = $datos['orden'];
                $vista->mod_id = $datos['modulo_id'];
                $vista->save();

                foreach (self::ACCIONES as $accion) {
                    $permiso = Permiso::firstOrNew([
                        'vis_id' => $vista->id_vis,
                        'accion_pmi' => $accion,
                    ]);
                    $permiso->nombre_pmi = "{$key}.{$accion}";
                    $permiso->etiqueta_pmi = ResolutorAccion::etiqueta($accion) . ' · ' . $vista->nombre_vis;
                    $permiso->mod_id = $vista->mod_id;
                    $permiso->save();
                }
            }

            SincronizadorPermisos::otorgarTodoASuperAdmins();

            $permisosSaludIds = Permiso::whereHas('vista', fn ($q) => $q->where('key_vis', 'persona_salud'))
                ->whereIn('accion_pmi', self::ACCIONES)
                ->pluck('id_pmi')
                ->all();

            $rolesSalud = ['Administrador de Salud', 'Doctor(a)', 'Enfermero(a)'];
            foreach ($rolesSalud as $nombreRol) {
                $rol = Rol::where('nombre_rol', $nombreRol)->first();
                if ($rol && ! empty($permisosSaludIds)) {
                    $rol->permisos()->syncWithoutDetaching($permisosSaludIds);
                }
            }

            $permisosPsicoIds = Permiso::whereHas('vista', fn ($q) => $q->where('key_vis', 'persona_psicologia'))
                ->whereIn('accion_pmi', self::ACCIONES)
                ->pluck('id_pmi')
                ->all();

            $rolPsico = Rol::where('nombre_rol', 'Psicólogo')
                ->orWhere('slug_rol', 'psicologo')
                ->first();

            if ($rolPsico && ! empty($permisosPsicoIds)) {
                $rolPsico->permisos()->syncWithoutDetaching($permisosPsicoIds);
            }
        });

        VersionPermisos::invalidar();
    }

    public function down(): void
    {
        if (! Schema::hasTable('vista') || ! Schema::hasTable('permiso')) {
            return;
        }

        DB::transaction(function () {
            $vistas = Vista::whereIn('key_vis', ['persona_salud', 'persona_psicologia'])->get();
            foreach ($vistas as $vista) {
                $permisoIds = Permiso::where('vis_id', $vista->id_vis)->pluck('id_pmi')->all();
                if (! empty($permisoIds)) {
                    DB::table('rol_permiso')->whereIn('pmi_id', $permisoIds)->delete();
                    DB::table('usuario_permiso')->whereIn('pmi_id', $permisoIds)->delete();
                    Permiso::whereIn('id_pmi', $permisoIds)->delete();
                }
                $vista->delete();
            }
        });

        VersionPermisos::invalidar();
    }
};

