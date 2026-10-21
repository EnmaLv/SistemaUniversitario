<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->foreignId('psicologo_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->text('motivo')->nullable();
            $table->text('bloques_sugeridos')->nullable();
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->timestamp('confirmado_en')->nullable();
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'rechazada', 'realizada', 'no_asistio'])->default('pendiente');
            $table->boolean('status')->default(true);
            $table->foreignId('estado_animo_id')->nullable()->constrained('estado_animos')->onDelete('set null');
            $table->string('prioridad')->default('media');
            $table->text('notas')->nullable();
            $table->enum('cancelado_por', ['paciente', 'psicologo'])->nullable();
            $table->timestamps();
            $table->string('bloque_propuesto')->nullable();
            $table->text('bloques_propuestos')->nullable();
            $table->string('propuesta_estado')->nullable();
            $table->text('propuesta_bloque_seleccionado')->nullable();
            $table->text('motivo_rechazo_propuesta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
