<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viaje_transporte', function (Blueprint $table) {
            $table->id('id_vjt');
            $table->string('turno_vjt', 100)->nullable();
            $table->string('firebase_id_vjt', 100)->nullable();
            $table->timestamp('fecha_inicio_vjt')->nullable();
            $table->decimal('km_inicio_vjt', 10, 2)->default(0);
            $table->decimal('km_fin_vjt', 10, 2)->default(0);
            $table->decimal('distancia_km_vjt', 8, 2)->default(0);
            $table->decimal('litros_gastados_vjt', 8, 2)->default(0);
            $table->integer('total_pasajeros_vjt')->default(0);
            $table->boolean('hubo_desvio_vjt')->default(false);
            $table->string('motivo_desvio_vjt', 255)->nullable();
            $table->string('estado_viaje_vjt', 20)->default('programado');
            $table->foreignId('vtr_id')->constrained('vehiculo_transporte', 'id_vtr')->cascadeOnDelete();
            $table->foreignId('rtr_id')->constrained('ruta_transporte', 'id_rtr')->cascadeOnDelete();
            $table->foreignId('conductor_use_id')->nullable()->constrained('user', 'id_use')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viaje_transporte');
    }
};
