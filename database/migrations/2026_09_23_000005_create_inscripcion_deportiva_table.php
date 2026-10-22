<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_deportiva', function (Blueprint $table) {
            $table->id('id_ide');
            $table->date('fecha_registro_ide');
            $table->string('estado_ide', 20)->default('inscrito');
            $table->text('observaciones_ide')->nullable();
            $table->foreignId('hde_id')->constrained('horario_deportivo', 'id_hde')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('per_id')->constrained('persona', 'id_per')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('use_id')->nullable()->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();

            $table->unique(['hde_id', 'per_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_deportiva');
    }
};
