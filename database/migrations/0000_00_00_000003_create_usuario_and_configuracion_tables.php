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
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->foreignId('id_persona')->constrained('persona', 'id_persona')->onDelete('cascade');
            $table->foreignId('id_perfil')->constrained('perfil', 'id_perfil')->onDelete('cascade');
            $table->string('username')->unique();
            $table->string('password');
            $table->text('master_key')->nullable();
            $table->json('security_questions')->nullable();
            $table->timestamp('ultima_actividad_chat')->nullable();
            $table->unsignedBigInteger('chat_activo_user_id')->nullable();
            $table->timestamp('infracciones_reset_at')->nullable();
            $table->boolean('debe_cambiar_password')->default(false);
            $table->timestamp('password_cambiado_en')->nullable();
            $table->timestamps();
        });

        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('clave_parametro')->unique();
            $table->text('valor_parametro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('configuracion_sistema');
    }
};
