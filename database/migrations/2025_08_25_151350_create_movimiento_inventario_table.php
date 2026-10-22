<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_inventario', function (Blueprint $table) {
            $table->id('id_moi');
            $table->string('tipo_movimiento_moi', 50);
            $table->decimal('cantidad_moi', 14, 2);
            $table->decimal('cantidad_convertida_moi', 14, 2);
            $table->decimal('cantidad_anterior_moi', 14, 2)->nullable();
            $table->decimal('cantidad_final_moi', 14, 2)->nullable();
            $table->unsignedBigInteger('referencia_id_moi')->nullable();
            $table->string('referencia_type_moi', 50)->nullable();
            $table->text('observacion_moi')->nullable();
            $table->timestamp('fecha_movimiento_moi')->useCurrent();
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->cascadeOnDelete();
            $table->foreignId('lot_id')->constrained('lote', 'id_lot')->cascadeOnDelete();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->foreignId('unm_id')->constrained('unidad_medida', 'id_unm')->cascadeOnDelete();
            $table->foreignId('mod_id')->nullable()->constrained('modulo', 'id_mod')->nullOnDelete();
            $table->foreignId('use_id')->nullable()->constrained('user', 'id_use')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventario');
    }
};
