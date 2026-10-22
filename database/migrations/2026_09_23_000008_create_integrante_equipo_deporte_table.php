<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrante_equipo_deporte', function (Blueprint $table) {
            $table->id('id_ied');
            $table->foreignId('etd_id')->constrained('equipo_torneo_deportivo', 'id_etd')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('per_id')->constrained('persona', 'id_per')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();

            $table->unique(['etd_id', 'per_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrante_equipo_deporte');
    }
};
