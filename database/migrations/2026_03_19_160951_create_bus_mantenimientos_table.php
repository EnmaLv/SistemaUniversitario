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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');
            $table->enum('tipo', ['preventivo', 'correctivo']);
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->date('fecha'); 
            $table->decimal('km_al_servicio', 10, 2)->nullable();
            $table->decimal('proximo_km', 10, 2)->nullable();
            $table->date('proxima_fecha')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado'])->default('pendiente');
            $table->unsignedBigInteger('usuario_registro_id')->nullable();
            $table->foreign('usuario_registro_id')->references('id_use')->on('user')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
