<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrega_material', function (Blueprint $table) {
            $table->id('id_ema');
            $table->dateTime('fecha_ema')->useCurrent();
            $table->string('dependencia_ema', 150)->nullable();
            $table->string('motivo_ema', 255);
            $table->foreignId('per_id')->nullable()->constrained('persona', 'id_per')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('use_id')->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();

            $table->index(['sed_id', 'fecha_ema'], 'entrega_material_sede_fecha_idx');
        });

        Schema::create('detalle_entrega_material', function (Blueprint $table) {
            $table->id('id_dem');
            $table->decimal('cantidad_dem', 14, 2);
            $table->foreignId('ema_id')->constrained('entrega_material', 'id_ema')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_entrega_material');
        Schema::dropIfExists('entrega_material');
    }
};
