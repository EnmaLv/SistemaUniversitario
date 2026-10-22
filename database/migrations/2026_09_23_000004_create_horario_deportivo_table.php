<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_deportivo', function (Blueprint $table) {
            $table->id('id_hde');
            $table->string('dia_semana_hde', 20)->nullable();
            $table->date('fecha_especifica_hde')->nullable();
            $table->date('semana_inicio_hde')->nullable()->index();
            $table->time('hora_inicio_hde');
            $table->time('hora_fin_hde');
            $table->integer('cupo_maximo_hde')->nullable();
            $table->text('observaciones_hde')->nullable();
            $table->string('estado_hde', 20)->default('activo');
            $table->text('motivo_cancelacion_hde')->nullable();
            $table->foreignId('cld_id')->constrained('calendario_deportivo', 'id_cld')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('acd_id')->constrained('actividad_deportiva', 'id_acd')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('epd_id')->constrained('espacio_deportivo', 'id_epd')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('use_id')->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();

            $table->index(['epd_id', 'estado_hde']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_deportivo');
    }
};
