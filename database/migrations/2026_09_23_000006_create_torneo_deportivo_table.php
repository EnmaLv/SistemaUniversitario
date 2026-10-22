<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('torneo_deportivo', function (Blueprint $table) {
            $table->id('id_tde');
            $table->string('nombre_tde');
            $table->text('descripcion_tde')->nullable();
            $table->string('modalidad_tde', 100)->nullable();
            $table->string('estado_tde', 20)->default('abierto');
            $table->foreignId('hde_id')->constrained('horario_deportivo', 'id_hde')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('use_id')->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torneo_deportivo');
    }
};
