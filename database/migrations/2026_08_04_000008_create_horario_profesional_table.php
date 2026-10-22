<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_profesional', function (Blueprint $table) {
            $table->id('id_hpr');
            $table->string('dia_hpr', 20);
            $table->time('hora_inicio_hpr');
            $table->time('hora_fin_hpr');
            $table->text('descripcion_hpr')->nullable();
            $table->foreignId('gha_id')->nullable()->constrained('grupo_horario_atencion', 'id_gha')->cascadeOnDelete();
            $table->foreignId('psicologo_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_profesional');
    }
};
