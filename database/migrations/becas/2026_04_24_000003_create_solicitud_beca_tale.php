<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('be_solicitud_becas', function (Blueprint $table) {
            $table->id();
            
            // Relación con el estudiante
            $table->foreignId('id_persona')
                  ->constrained('persona', 'id_persona')
                  ->onDelete('cascade');
            $table->foreignId('id_beneficio')
                  ->constrained('be_beneficios', 'id')
                  ->onDelete('cascade');
            
            $table->foreignId('jornada_id')
                  ->constrained('be_jornadas_becas')
                  ->onDelete('restrict');

            // Estados y tipos
            $table->enum('tipo_solicitud', ['nueva', 'renovacion'])->default('nueva');
            $table->tinyInteger('estado')->default(0)->comment('0: pendiente, 1: aprobada, 2: rechazada');
            
            // Datos académicos y de periodo
            $table->foreignId('id_lapso')
                  ->constrained('be_lapsos', 'id')
                  ->onDelete('cascade');
            $table->decimal('indice_academico', 4, 2)->comment('Índice de notas del estudiante');

            // Información de vivienda y gastos
            $table->string('direccion_temporal')->nullable()->comment('Si vive fuera de su residencia fija');
            $table->decimal('gasto_pasaje', 10, 2)->nullable()->comment('Gasto mensual estimado');

            /**
             * Campos JSON:
             * vivienda_transporte: {residencia, viaje_diario, tiempo_traslado}
             * datos_socioeconomicos: {vivienda, equipamiento, servicios, carga_familiar}
             */
            $table->json('vivienda_transporte')->nullable();
            $table->json('datos_socioeconomicos')->nullable();

            // Auditoría de verificación
            $table->text('comentario_verificador')->nullable()->comment('Razón en caso de rechazo');
            $table->foreignId('verificado_por')
                  ->nullable()
                  ->constrained('usuario', 'id_usuario');
            $table->timestamp('fecha_verificacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('be_solicitud_becas');
    }
};
