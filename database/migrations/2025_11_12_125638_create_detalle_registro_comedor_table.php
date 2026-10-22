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
        Schema::create('detalle_registro_comedor', function (Blueprint $table) {
            $table->id('id_drc');
            $table->foreignId('rct_id')->constrained('receta', 'id_rct')->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed');
            $table->string('tipo_comida_drc', 20)->default('desayuno');
            $table->bigInteger('cantidad_servida_drc');
            $table->date('fecha_drc');
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_registro_comedor');
    }
};
