<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // usuario table
        Schema::create('user', function (Blueprint $table) {
            $table->id('id_use');
            $table->foreignId('per_id')->unique()->constrained('persona', 'id_per')->onDelete('cascade');
            $table->foreignId('id_perfil')->constrained('perfil', 'id_perfil')->onDelete('cascade');
            $table->string('email_use')->nullable()->unique();
            $table->string('username_use', 100)->unique();
            $table->string('password_use');
            $table->text('master_key_use')->nullable();
            $table->json('security_questions_use')->nullable();
            $table->unsignedSmallInteger('intentos_fallidos_use')->default(0);
            $table->unsignedSmallInteger('intentos_llave_fallidos_use')->default(0);
            $table->timestamp('bloqueado_hasta_use')->nullable();
            $table->unsignedSmallInteger('bloqueos_temporales_use')->default(0);
            $table->timestamp('ventana_bloqueos_desde_use')->nullable();
            $table->boolean('bloqueado_use')->default(false);
            $table->timestamp('bloqueado_en_use')->nullable();
            $table->string('motivo_bloqueo_use', 100)->nullable();
            $table->timestamp('ultimo_login_use')->nullable();
            $table->string('ultimo_login_ip_use', 45)->nullable();
            $table->string('remember_token_use', 100)->nullable();
            $table->timestamp('ultima_actividad_chat_use')->nullable();
            $table->unsignedBigInteger('chat_activo_use_id')->nullable();
            $table->timestamp('infracciones_reset_at_use')->nullable();
            $table->boolean('debe_cambiar_password_use')->default(false);
            $table->timestamp('password_cambiado_en_use')->nullable();
            $table->timestamps();
            $table->auditoria();

            $table->index('bloqueado_use', 'idx_user_bloqueado');
            $table->index('bloqueado_hasta_use', 'idx_user_bloqueado_hasta');
        });

        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id('id_csi');
            $table->string('clave_csi')->unique();
            $table->text('valor_csi');
            $table->string('tipo_dato_csi')->nullable();
            $table->string('descripcion_csi')->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
        Schema::dropIfExists('configuracion_sistema');
    }
};
