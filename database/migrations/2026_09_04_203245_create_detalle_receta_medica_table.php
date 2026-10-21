<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_receta_medica', function (Blueprint $table) {
            $table->id('id_drm');
            $table->decimal('cantidad_drm', 14, 2);
            $table->decimal('cantidad_prescrita_drm', 10, 2);
            $table->string('unidad_prescrita_drm', 100);
            $table->decimal('equivalencia_ml_drm', 10, 2);
            $table->string('frecuencia_drm', 100);
            $table->date('fecha_inicio_drm');
            $table->date('fecha_fin_drm');
            $table->text('instrucciones_drm')->nullable();
            $table->foreignId('rme_id')->constrained('receta_medica', 'id_rme')->cascadeOnDelete();
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->cascadeOnDelete();
            $table->foreignId('unm_id')->constrained('unidad_medida', 'id_unm')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_receta_medica');
    }
};
