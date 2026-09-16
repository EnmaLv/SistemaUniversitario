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
        Schema::create('recetas_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas', 'id')->onDelete('cascade');
            $table->foreignId('id_persona')->constrained('persona', 'id_persona')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('persona', 'id_persona')->onDelete('cascade');
            $table->date('fecha')->nullable();
            $table->text('descripcion');
            $table->date('vigencia');
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas_medicas');
    }
};
