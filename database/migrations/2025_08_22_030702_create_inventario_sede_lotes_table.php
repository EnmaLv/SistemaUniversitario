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
        Schema::create('inventario_sede_lotes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->onDelete('cascade');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->decimal('cantidad', 14, 2)->default(0);
            $table->decimal('cantidad_convertida', 14, 2)->default(0);

            $table->timestamps();

            $table->unique(['sed_id', 'lote_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_sede_lotes');
    }
};