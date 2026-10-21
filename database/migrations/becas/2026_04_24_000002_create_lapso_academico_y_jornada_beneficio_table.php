<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lapso_academico', function (Blueprint $table) {
            $table->id('id_laa');
            $table->string('codigo_laa', 30)->unique();
            $table->date('fecha_inicio_laa');
            $table->date('fecha_fin_laa')->nullable();
            $table->boolean('es_actual_laa')->default(false);
            $table->boolean('permite_solicitudes_laa')->default(false);
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('condicion_estudiante', function (Blueprint $table) {
            $table->id('id_cde');
            $table->string('nombre_condicion_cde', 255);
            $table->foreignId('per_id')->constrained('persona', 'id_per')->cascadeOnDelete();
            $table->foreignId('laa_id')->constrained('lapso_academico', 'id_laa')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('jornada_beneficio', function (Blueprint $table) {
            $table->id('id_jnb');
            $table->string('nombre_jornada_jnb', 255);
            $table->text('descripcion_jornada_jnb')->nullable();
            $table->date('fecha_inicio_solicitud_jnb');
            $table->date('fecha_fin_solicitud_jnb');
            $table->integer('cupos_maximos_jnb')->default(0);
            $table->integer('cupos_asignados_jnb')->default(0);
            $table->foreignId('bes_id')->constrained('beneficio_estudiantil', 'id_bes')->cascadeOnDelete();
            $table->foreignId('laa_id')->constrained('lapso_academico', 'id_laa')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jornada_beneficio');
        Schema::dropIfExists('condicion_estudiante');
        Schema::dropIfExists('lapso_academico');
    }
};
