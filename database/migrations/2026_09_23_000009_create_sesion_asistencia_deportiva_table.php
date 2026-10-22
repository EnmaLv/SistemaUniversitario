<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesion_asistencia_deportiva', function (Blueprint $table) {
            $table->id('id_sad');
            $table->foreignId('hde_id')->constrained('horario_deportivo', 'id_hde')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('fecha_sad');
            $table->boolean('cerrada_sad')->default(false);
            $table->timestamp('cerrada_en_sad')->nullable();
            $table->foreignId('cerrada_por_use_id')->nullable()->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();

            $table->unique(['hde_id', 'fecha_sad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesion_asistencia_deportiva');
    }
};
