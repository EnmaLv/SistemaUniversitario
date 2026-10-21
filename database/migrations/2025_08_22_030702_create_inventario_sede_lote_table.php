<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_sede_lote', function (Blueprint $table) {
            $table->id('id_isl');
            $table->decimal('cantidad_isl', 14, 2)->default(0);
            $table->decimal('cantidad_convertida_isl', 14, 2)->default(0);
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->foreignId('lot_id')->constrained('lote', 'id_lot')->cascadeOnDelete();
            $table->unique(['sed_id', 'lot_id']);
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_sede_lote');
    }
};
