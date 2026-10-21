<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receta_medica', function (Blueprint $table) {
            $table->id('id_rme');
            $table->date('fecha_emision_rme')->nullable();
            $table->date('fecha_vigencia_rme')->nullable();
            $table->text('descripcion_rme')->nullable();
            $table->unsignedTinyInteger('estado_rme')->default(1);
            $table->foreignId('cme_id')->constrained('consulta_medica', 'id_cme')->cascadeOnDelete();
            $table->foreignId('paciente_per_id')->constrained('persona', 'id_per')->cascadeOnDelete();
            $table->foreignId('medico_per_id')->constrained('persona', 'id_per')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta_medica');
    }
};
