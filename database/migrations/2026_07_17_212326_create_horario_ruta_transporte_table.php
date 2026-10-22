<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_ruta_transporte', function (Blueprint $table) {
            $table->id('id_hrt');
            $table->time('hora_salida_hrt');
            $table->string('tipo_viaje_hrt', 30)->default('entrada');
            $table->foreignId('rtr_id')->constrained('ruta_transporte', 'id_rtr')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_ruta_transporte');
    }
};
