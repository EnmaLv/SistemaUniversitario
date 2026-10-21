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
        Schema::create('dispensacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_medica_id')->constrained('recetas_medicas', 'id')->onDelete('cascade');
            $table->foreignId('detalle_receta_medica_id')->constrained('detalle_recetas_medicas', 'id')->onDelete('cascade');
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->onDelete('cascade');
            $table->foreignId('per_id')->constrained('persona', 'id_per')->onDelete('cascade');
            $table->foreignId('lot_id')->nullable()->constrained('lote', 'id_lot')->nullOnDelete();
            $table->decimal('cantidad', 14, 2);
            $table->foreignId('unm_id')->constrained('unidad_medida', 'id_unm')->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->date('fecha');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensacions');
    }
};
