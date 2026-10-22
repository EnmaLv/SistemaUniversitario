<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensacion_medicamento', function (Blueprint $table) {
            $table->id('id_dsm');
            $table->decimal('cantidad_dsm', 14, 2);
            $table->date('fecha_dispensacion_dsm');
            $table->text('observaciones_dsm')->nullable();
            $table->foreignId('rme_id')->constrained('receta_medica', 'id_rme')->cascadeOnDelete();
            $table->foreignId('drm_id')->constrained('detalle_receta_medica', 'id_drm')->cascadeOnDelete();
            $table->foreignId('paciente_per_id')->constrained('persona', 'id_per')->cascadeOnDelete();
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->cascadeOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('lote', 'id_lot')->nullOnDelete();
            $table->foreignId('unm_id')->constrained('unidad_medida', 'id_unm')->cascadeOnDelete();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->foreignId('use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensacion_medicamento');
    }
};
