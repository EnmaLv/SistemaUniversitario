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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->string('modulo', 100);
            $table->string('accion', 100);
            $table->string('tabla_afectada', 100)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('nombre_usuario', 255)->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_modificados')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('navegador', 255)->nullable();
            $table->string('sistema_operativo', 100)->nullable();
            $table->string('metodo_http', 10)->nullable();
            $table->string('ruta', 191)->nullable();
            $table->string('url', 2048)->nullable();
            $table->unsignedSmallInteger('codigo_respuesta')->nullable();
            $table->string('nivel', 20)->default('info');
            $table->string('session_id', 64)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_hora')->useCurrent();

            $table->foreign('usuario_id')->references('id_usuario')->on('usuario')->nullOnDelete();
            $table->index('usuario_id', 'idx_bitacora_usuario');
            $table->index('modulo', 'idx_bitacora_modulo');
            $table->index('accion', 'idx_bitacora_accion');
            $table->index('fecha_hora', 'idx_bitacora_fecha_hora');
            $table->index('ip_address', 'idx_bitacora_ip');
        });

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        try {
            DB::unprepared("
                CREATE TRIGGER trg_bitacora_sin_update BEFORE UPDATE ON bitacora
                FOR EACH ROW
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La bitacora es de solo insercion: UPDATE no permitido'
            ");

            DB::unprepared("
                CREATE TRIGGER trg_bitacora_sin_delete BEFORE DELETE ON bitacora
                FOR EACH ROW
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La bitacora es de solo insercion: DELETE no permitido'
            ");
        } catch (\Throwable $e) {
            Log::warning('No se pudieron crear los triggers de inmutabilidad de la bitacora: ' . $e->getMessage());

            if (app()->runningInConsole()) {
                fwrite(STDERR, "ADVERTENCIA: triggers de la bitacora no creados ({$e->getMessage()}). Habilita log_bin_trust_function_creators o usa un usuario con privilegio SUPER.\n");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_sin_update');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_sin_delete');
        }

        Schema::dropIfExists('bitacora');
    }
};
