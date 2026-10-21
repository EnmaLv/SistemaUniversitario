<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->unsignedSmallInteger('intentos_fallidos')->default(0)->after('security_questions');
            $table->unsignedSmallInteger('intentos_llave_fallidos')->default(0)->after('intentos_fallidos');
            $table->timestamp('bloqueado_hasta')->nullable()->after('intentos_llave_fallidos');
            $table->unsignedSmallInteger('bloqueos_temporales')->default(0)->after('bloqueado_hasta');
            $table->timestamp('ventana_bloqueos_desde')->nullable()->after('bloqueos_temporales');
            $table->boolean('bloqueado')->default(false)->after('ventana_bloqueos_desde');
            $table->timestamp('bloqueado_en')->nullable()->after('bloqueado');
            $table->string('motivo_bloqueo', 100)->nullable()->after('bloqueado_en');
            $table->timestamp('ultimo_login')->nullable()->after('motivo_bloqueo');
            $table->string('ultimo_login_ip', 45)->nullable()->after('ultimo_login');

            $table->index('bloqueado', 'idx_usuario_bloqueado');
            $table->index('bloqueado_hasta', 'idx_usuario_bloqueado_hasta');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropIndex('idx_usuario_bloqueado');
            $table->dropIndex('idx_usuario_bloqueado_hasta');
            $table->dropColumn([
                'intentos_fallidos',
                'intentos_llave_fallidos',
                'bloqueado_hasta',
                'bloqueos_temporales',
                'ventana_bloqueos_desde',
                'bloqueado',
                'bloqueado_en',
                'motivo_bloqueo',
                'ultimo_login',
                'ultimo_login_ip',
            ]);
        });
    }
};
