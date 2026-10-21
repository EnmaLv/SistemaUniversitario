<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('perfil', function (Blueprint $table) {
            $table->id('id_perfil');
            $table->string('nombre_perfil');
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('persona', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('nombre_persona');
            $table->string('segundo_nombre_persona')->nullable();
            $table->string('apellido_persona');
            $table->string('segundo_apellido_persona')->nullable();
            $table->string('cedula_persona')->unique();
            $table->string('telefono_persona');
            $table->string('genero_persona');
            $table->integer('edad_persona')->nullable();
            $table->date('fecha_nacimiento_persona')->nullable();
            $table->string('email_persona');
            $table->string('semestre_persona')->nullable();
            $table->boolean('estado')->default(true);
            $table->foreignId('id_perfil')->references('id_perfil')->on('perfil')->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
