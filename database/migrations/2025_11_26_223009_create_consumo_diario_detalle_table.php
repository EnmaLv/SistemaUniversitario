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
        Schema::create('consumo_diario_detalle', function (Blueprint $table) {
            $table->id('id_cdd');
            $table->foreignId('cnd_id')
                ->constrained('consumo_diario', 'id_cnd')
                ->onDelete('cascade');
            $table->foreignId('pdt_id')
                ->constrained('producto', 'id_pdt')
                ->onDelete('cascade');
            $table->decimal('cantidad_total_cdd', 14, 2)->notNull();
            $table->foreignId('unm_id')
                ->constrained('unidad_medida', 'id_unm')
                ->onDelete('cascade');
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumo_diario_detalle');
    }
};
