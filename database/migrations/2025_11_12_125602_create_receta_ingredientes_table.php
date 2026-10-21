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
        Schema::create('receta_ingredientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recetas_id')->constrained('recetas')->onDelete('cascade');
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->onDelete('cascade');
            $table->decimal('cantidad_porcion', 14, 2)->notNull();
            $table->decimal('cantidad_gramos', 14, 2);
            $table->foreignId('unm_id')->constrained('unidad_medida', 'id_unm')->onDelete('cascade');
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta_ingredientes');
    }
};
