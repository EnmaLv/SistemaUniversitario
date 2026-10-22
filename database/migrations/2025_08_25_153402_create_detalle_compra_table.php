<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_compra', function (Blueprint $table) {
            $table->id('id_dcm');
            $table->decimal('cantidad_dcm', 14, 2);
            $table->decimal('cantidad_convertida_dcm', 14, 2);
            $table->decimal('precio_unitario_dcm', 10, 2);
            $table->decimal('subtotal_dcm', 10, 2);
            $table->foreignId('cmp_id')->constrained('compra', 'id_cmp')->cascadeOnDelete();
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->cascadeOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('lote', 'id_lot')->cascadeOnDelete();
            $table->foreignId('unm_id')->constrained('unidad_medida', 'id_unm')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_compra');
    }
};
