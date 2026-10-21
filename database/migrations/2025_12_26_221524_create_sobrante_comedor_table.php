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
        Schema::create('sobrante_comedor', function (Blueprint $table) {
            $table->id('id_sbc');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed');
            $table->date('fecha_sbc');
            $table->integer('cantidad_sobrante_sbc');
            $table->text('motivo_sbc');
            $table->text('accion_tomada_sbc');
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sobrante_comedor');
    }
};
