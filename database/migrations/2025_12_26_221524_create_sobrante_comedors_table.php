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
        Schema::create('sobrantes_comedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed');
            $table->date('fecha');
            $table->integer('cantidad_sobrante');
            $table->text('motivo');
            $table->text('accion_tomada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sobrante_comedors');
    }
};
