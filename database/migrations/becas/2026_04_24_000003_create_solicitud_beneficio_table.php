<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_beneficio', function (Blueprint $table) {
            $table->id('id_sob');
            $table->string('tipo_solicitud_sob', 20)->default('nueva');
            $table->unsignedTinyInteger('estado_solicitud_sob')->default(0);
            $table->text('comentario_verificador_sob')->nullable();
            $table->timestamp('fecha_verificacion_sob')->nullable();
            $table->foreignId('per_id')->constrained('persona', 'id_per')->cascadeOnDelete();
            $table->foreignId('bes_id')->constrained('beneficio_estudiantil', 'id_bes')->cascadeOnDelete();
            $table->foreignId('jnb_id')->constrained('jornada_beneficio', 'id_jnb')->restrictOnDelete();
            $table->foreignId('laa_id')->constrained('lapso_academico', 'id_laa')->cascadeOnDelete();
            $table->foreignId('verificado_por_use_id')->nullable()->constrained('user', 'id_use')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_beneficio');
    }
};
