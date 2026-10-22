<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trazado_ruta_transporte', function (Blueprint $table) {
            $table->id('id_trt');
            $table->unsignedInteger('version_trt');
            $table->unsignedInteger('orden_trt');
            $table->decimal('lat_trt', 11, 7);
            $table->decimal('lng_trt', 11, 7);
            $table->boolean('vigente_trt')->default(true);
            $table->foreignId('rtr_id')->constrained('ruta_transporte', 'id_rtr')->cascadeOnDelete();
            $table->foreignId('vjt_id')->nullable()->constrained('viaje_transporte', 'id_vjt')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->index(['rtr_id', 'version_trt', 'orden_trt'], 'trazado_ruta_version_orden_idx');
            $table->index(['rtr_id', 'vigente_trt'], 'trazado_ruta_vigente_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trazado_ruta_transporte');
    }
};
