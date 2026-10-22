<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_deportiva', function (Blueprint $table) {
            $table->id('id_asd');
            $table->foreignId('sad_id')->constrained('sesion_asistencia_deportiva', 'id_sad')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('ide_id')->constrained('inscripcion_deportiva', 'id_ide')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('estado_asd', 20)->default('presente');
            $table->timestamps();
            $table->auditoria();

            $table->unique(['sad_id', 'ide_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_deportiva');
    }
};
