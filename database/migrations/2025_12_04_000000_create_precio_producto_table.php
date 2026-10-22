<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precio_producto', function (Blueprint $table) {
            $table->id('id_ppr');
            $table->decimal('costo_usd_ppr', 10, 2);
            $table->decimal('margen_ppr', 5, 2)->default(0);
            $table->decimal('precio_usd_ppr', 10, 2)->nullable();
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precio_producto');
    }
};
