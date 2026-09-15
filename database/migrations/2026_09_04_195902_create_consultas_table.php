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
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_persona')->constrained('persona', 'id_persona')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('persona', 'id_persona')->onDelete('cascade');
            $table->foreignId('consultorio_id')->constrained('consultorios', 'id')->onDelete('cascade');
            $table->date('fecha')->nullable();
            $table->text('motivo');
            $table->text('observaciones');
            $table->text('diagnostico');
            $table->foreignId('creado_por')->constrained('usuario', 'id_usuario')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('consulta_enfermedad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->onDelete('cascade');
            $table->foreignId('enfermedad_id')->constrained('enfermedades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_enfermedad');
        Schema::dropIfExists('consultas');
    }
};