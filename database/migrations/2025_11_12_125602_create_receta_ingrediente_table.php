<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Livewire\after;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receta_ingrediente', function (Blueprint $table) {
            $table->id('id_rci');
            $table->foreignId('rct_id')->constrained('receta', 'id_rct')->onDelete('cascade');
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->onDelete('cascade');
            $table->decimal('cantidad_porcion_rci', 14, 2)->notNull();
            $table->decimal('cantidad_convertida_rci', 14, 2);
            $table->foreignId('unm_id')->constrained('unidad_medida', 'id_unm')->onDelete('cascade');
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta_ingrediente');
    }
};
