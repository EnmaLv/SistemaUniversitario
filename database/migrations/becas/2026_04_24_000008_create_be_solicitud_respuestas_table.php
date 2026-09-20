<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('be_solicitud_respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitud')
                ->constrained('be_solicitud_becas')
                ->onDelete('cascade');
            $table->foreignId('id_pregunta')
                ->constrained('be_beca_preguntas')
                ->onDelete('restrict');
            $table->text('valor')->nullable();
            $table->json('valor_json')->nullable();
            $table->boolean('cumple_criterio')->nullable();
            $table->timestamps();
            $table->unique(['id_solicitud', 'id_pregunta'], 'uq_solicitud_pregunta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('be_beca_preguntas');
    }
};
