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
        Schema::create('detalle_recetas_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas_medicas', 'id')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos', 'id')->onDelete('cascade');
            $table->foreignId('unidad_id')->constrained('unidades', 'id')->onDelete('cascade');
            $table->decimal('cantidad', 14, 2);
            $table->decimal('cantidad_prescrita', 10, 2);
            $table->string('unidad_prescrita', 100);
            $table->decimal('equivalencia_ml', 10, 2);
            $table->string('frecuencia', 100);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_recetas_medicas');
    }
};
