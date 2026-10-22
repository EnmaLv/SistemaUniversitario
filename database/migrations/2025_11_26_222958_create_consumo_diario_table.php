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
        Schema::create('consumo_diario', function (Blueprint $table) {
            $table->id('id_cnd');
            $table->foreignId('rct_id')
                ->constrained('receta', 'id_rct')
                ->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed');
            $table->date('fecha_consumo_cnd');
            $table->integer('porciones_servidas_cnd')->notNull();
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumo_diario');
    }
};
