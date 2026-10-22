<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sancion_deportiva', function (Blueprint $table) {
            $table->id('id_snd');
            $table->text('motivo_snd');
            $table->text('descripcion_snd')->nullable();
            $table->date('fecha_sancion_snd');
            $table->date('fecha_levantamiento_snd')->nullable();
            $table->string('estado_snd', 20)->default('activa');
            $table->string('forma_levantamiento_snd', 20)->nullable();
            $table->foreignId('prd_id')->constrained('prestamo_deportivo', 'id_prd')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('per_id')->constrained('persona', 'id_per')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('lot_id')->nullable()->constrained('lote', 'id_lot')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('aplicada_por_use_id')->nullable()->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('levantada_por_use_id')->nullable()->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();

            $table->index(['per_id', 'estado_snd'], 'sancion_deportiva_persona_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sancion_deportiva');
    }
};
