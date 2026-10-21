<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_total', function (Blueprint $table) {
            $table->id('id_int');
            $table->decimal('cantidad_total_int', 14, 2)->default(0);
            $table->decimal('cantidad_convertida_total_int', 14, 2)->default(0);
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->cascadeOnDelete();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->unique(['pdt_id', 'sed_id']);
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_total');
    }
};
