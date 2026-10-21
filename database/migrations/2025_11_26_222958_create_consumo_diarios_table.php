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
        Schema::create('consumo_diarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')
                ->constrained('recetas')
                ->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sede');
            $table->date('fecha_consumo_diario');
            $table->integer('porciones_servidas')->notNull();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumo_diarios');
    }
};
