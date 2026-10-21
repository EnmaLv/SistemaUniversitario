<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote', function (Blueprint $table) {
            $table->id('id_lot');
            $table->string('codigo_lote_lot', 50)->nullable();
            $table->date('fecha_entrada_lot');
            $table->date('fecha_vencimiento_lot')->nullable();
            $table->decimal('cantidad_inicial_lot', 14, 2)->default(0);
            $table->decimal('cantidad_actual_lot', 14, 2)->default(0);
            $table->decimal('precio_compra_lot', 10, 2)->nullable();
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->cascadeOnDelete();
            $table->foreignId('prv_id')->nullable()->constrained('proveedor', 'id_prv')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote');
    }
};
