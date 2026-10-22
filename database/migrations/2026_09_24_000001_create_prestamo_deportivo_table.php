<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamo_deportivo', function (Blueprint $table) {
            $table->id('id_prd');
            $table->dateTime('fecha_prestamo_prd')->useCurrent();
            $table->dateTime('fecha_devolucion_esperada_prd');
            $table->dateTime('fecha_devolucion_real_prd')->nullable();
            $table->string('estado_prd', 20)->default('activo');
            $table->string('estado_devolucion_prd', 20)->nullable();
            $table->unsignedInteger('cantidad_prd')->default(1);
            $table->string('motivo_prd', 255);
            $table->string('lugar_prd', 255)->nullable();
            $table->text('observaciones_prd')->nullable();
            $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('lot_id')->constrained('lote', 'id_lot')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('per_id')->constrained('persona', 'id_per')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('epd_id')->nullable()->constrained('espacio_deportivo', 'id_epd')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('registrado_por_use_id')->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('recibido_por_use_id')->nullable()->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();

            $table->index(['per_id', 'estado_prd'], 'prestamo_deportivo_persona_estado_idx');
            $table->index(['estado_prd', 'fecha_devolucion_esperada_prd'], 'prestamo_deportivo_estado_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo_deportivo');
    }
};
