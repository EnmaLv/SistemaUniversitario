<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cita_psicologica', function (Blueprint $table) {
            $table->id('id_cps');
            $table->text('motivo_cps')->nullable();
            $table->text('bloques_sugeridos_cps')->nullable();
            $table->date('fecha_cps');
            $table->time('hora_cps')->nullable();
            $table->timestamp('confirmado_en_cps')->nullable();
            $table->string('estado_cita_cps', 30)->default('pendiente');
            $table->string('prioridad_texto_cps', 100)->default('media');
            $table->text('notas_cps')->nullable();
            $table->string('cancelado_por_cps', 50)->nullable();
            $table->string('bloque_propuesto_cps')->nullable();
            $table->text('bloques_propuestos_cps')->nullable();
            $table->string('propuesta_estado_cps')->nullable();
            $table->text('propuesta_bloque_seleccionado_cps')->nullable();
            $table->text('motivo_rechazo_propuesta_cps')->nullable();
            $table->foreignId('paciente_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->foreignId('psicologo_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->foreignId('esa_id')->nullable()->constrained('estado_animo', 'id_esa')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_psicologica');
    }
};
