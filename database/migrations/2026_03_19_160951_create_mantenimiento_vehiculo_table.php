<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimiento_vehiculo', function (Blueprint $table) {
            $table->id('id_mnv');
            $table->string('tipo_mnv', 20)->default('preventivo');
            $table->string('titulo_mnv', 150);
            $table->text('descripcion_mnv')->nullable();
            $table->decimal('costo_mnv', 10, 2)->nullable();
            $table->date('fecha_mnv');
            $table->decimal('km_al_servicio_mnv', 10, 2)->nullable();
            $table->decimal('proximo_km_mnv', 10, 2)->nullable();
            $table->date('proxima_fecha_mnv')->nullable();
            $table->string('estado_mnv', 20)->default('pendiente');
            $table->foreignId('vtr_id')->constrained('vehiculo_transporte', 'id_vtr')->cascadeOnDelete();
            $table->foreignId('use_id')->nullable()->constrained('user', 'id_use')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_vehiculo');
    }
};
