<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_sistema', function (Blueprint $table) {
            $table->id('id_bis');
            $table->string('modulo_bis', 100)->nullable();
            $table->string('tipo_evento_bis', 100);
            $table->string('tabla_afectada_bis', 100)->nullable();
            $table->string('nombre_usuario_bis', 255)->nullable();
            $table->json('datos_anteriores_bis')->nullable();
            $table->json('datos_nuevos_bis')->nullable();
            $table->string('direccion_ip_bis', 45)->nullable();
            $table->string('navegador_bis', 255)->nullable();
            $table->string('sistema_operativo_bis', 100)->nullable();
            $table->text('observacion_bis')->nullable();
            $table->unsignedBigInteger('use_id')->nullable();
            $table->unsignedBigInteger('registro_id_bis')->nullable();
            $table->string('metodo_http_bis', 10)->nullable();
            $table->string('ruta_bis', 191)->nullable();
            $table->string('url_bis', 2048)->nullable();
            $table->unsignedSmallInteger('codigo_respuesta_bis')->nullable();
            $table->string('nivel_bis', 20)->default('info');
            $table->string('session_id_bis', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->auditoria();

            $table->foreign('use_id')->references('id_use')->on('user')->nullOnDelete();
            $table->index('use_id', 'idx_bitacora_sistema_usuario');
            $table->index('modulo_bis', 'idx_bitacora_sistema_modulo');
            $table->index('tipo_evento_bis', 'idx_bitacora_sistema_evento');
            $table->index('created_at', 'idx_bitacora_sistema_fecha');
            $table->index('direccion_ip_bis', 'idx_bitacora_sistema_ip');
        });

        try {
            match (DB::getDriverName()) {
                'mysql', 'mariadb' => $this->triggersMysql(),
                'pgsql'            => $this->triggersPostgres(),
                default            => null,
            };
        } catch (\Throwable $e) {
            Log::warning('No se pudieron crear los triggers de inmutabilidad de la bitacora: ' . $e->getMessage());

            if (app()->runningInConsole()) {
                fwrite(STDERR, "ADVERTENCIA: triggers de la bitacora no creados ({$e->getMessage()}). Habilita log_bin_trust_function_creators o usa un usuario con privilegio SUPER.\n");
            }
        }
    }

    public function down(): void
    {
        match (DB::getDriverName()) {
            'mysql', 'mariadb' => array_map(DB::unprepared(...), ['DROP TRIGGER IF EXISTS trg_bitacora_sistema_sin_update', 'DROP TRIGGER IF EXISTS trg_bitacora_sistema_sin_delete']),
            'pgsql'            => array_map(DB::unprepared(...), ['DROP TRIGGER IF EXISTS trg_bitacora_sistema_inmutable ON bitacora_sistema', 'DROP FUNCTION IF EXISTS fn_bitacora_sistema_inmutable()']),
            default            => null,
        };

        Schema::dropIfExists('bitacora_sistema');
    }

    private function triggersMysql(): void
    {
        DB::unprepared("
            CREATE TRIGGER trg_bitacora_sistema_sin_update BEFORE UPDATE ON bitacora_sistema
            FOR EACH ROW
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La bitacora es de solo insercion: UPDATE no permitido'
        ");

        DB::unprepared("
            CREATE TRIGGER trg_bitacora_sistema_sin_delete BEFORE DELETE ON bitacora_sistema
            FOR EACH ROW
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La bitacora es de solo insercion: DELETE no permitido'
        ");
    }

    private function triggersPostgres(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_bitacora_sistema_inmutable() RETURNS trigger AS \$\$
            BEGIN
                RAISE EXCEPTION 'La bitacora es de solo insercion: % no permitido', TG_OP USING ERRCODE = '45000';
            END;
            \$\$ LANGUAGE plpgsql
        ");

        DB::unprepared("
            CREATE TRIGGER trg_bitacora_sistema_inmutable BEFORE UPDATE OR DELETE ON bitacora_sistema
            FOR EACH ROW EXECUTE FUNCTION fn_bitacora_sistema_inmutable()
        ");
    }
};
