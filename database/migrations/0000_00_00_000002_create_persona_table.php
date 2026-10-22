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
            $table->id('id_per');
            $table->string('cedula_per', 15)->unique();
            $table->string('nacionalidad_per', 1)->default('V');
            $table->string('nombres_per');
            $table->string('segundo_nombre_per')->nullable();
            $table->string('apellidos_per');
            $table->string('segundo_apellido_per')->nullable();
            $table->string('telefono_per');
            $table->string('genero_per');
            $table->date('fecha_nacimiento_per')->nullable();
            $table->string('correo_per');
            $table->string('semestre_per')->nullable();
            $table->foreignId('id_perfil')->references('id_perfil')->on('perfil')->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
