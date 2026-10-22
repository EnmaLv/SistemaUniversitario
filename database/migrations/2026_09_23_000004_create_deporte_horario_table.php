<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deporte_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_calendario')->constrained('deporte_calendario')->onDelete('cascade');
            $table->foreignId('id_actividad')->constrained('deporte_actividad')->onDelete('cascade');
            $table->foreignId('id_espacio')->constrained('deporte_espacio')->onDelete('cascade');
            $table->string('dia_semana')->nullable();
            $table->date('fecha_especifica')->nullable();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('cupo_maximo')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado')->default('activo');
            $table->foreignId('creado_por')->constrained('usuario', 'id_usuario')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deporte_horario');
    }
};
