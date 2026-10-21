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
        Schema::create('tasa_cambio', function (Blueprint $table) {
            $table->id('id_tca');
            $table->string('nombre_tca')->default('Oficial');
            $table->string('fuente_tca')->default('BCV');
            $table->decimal('promedio_tca', 12, 2);
            $table->string('variacion_tca', 10)->default('igual');
            $table->date('fecha_vigencia_tca');
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasa_cambio');
    }
};
