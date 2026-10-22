<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficio_asignado', function (Blueprint $table) {
            $table->id('id_bfa');
            $table->date('fecha_inicio_bfa');
            $table->date('fecha_fin_bfa')->nullable();
            $table->string('estado_bfa', 30)->default('activa');
            $table->text('motivo_revocacion_bfa')->nullable();
            $table->foreignId('sob_id')->constrained('solicitud_beneficio', 'id_sob')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficio_asignado');
    }
};
