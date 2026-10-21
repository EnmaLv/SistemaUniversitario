<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respuesta_solicitud_beneficio', function (Blueprint $table) {
            $table->id('id_rsb');
            $table->text('valor_rsb')->nullable();
            $table->json('valor_json_rsb')->nullable();
            $table->boolean('cumple_criterio_rsb')->nullable();
            $table->foreignId('sob_id')->constrained('solicitud_beneficio', 'id_sob')->cascadeOnDelete();
            $table->foreignId('pgb_id')->constrained('pregunta_beneficio', 'id_pgb')->restrictOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['sob_id', 'pgb_id'], 'uq_respuesta_solicitud_pregunta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuesta_solicitud_beneficio');
    }
};
