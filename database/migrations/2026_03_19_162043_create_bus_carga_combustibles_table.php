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
        Schema::create('carga_combustibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');
            $table->foreignId('bus_viaje_id')->constrained('bus_viajes')->onDelete('cascade');
            $table->date('fecha');
            $table->decimal('litros', 8, 2);
            $table->decimal('precio_litros', 10, 2);
            $table->decimal('total', 12, 2); 
            $table->decimal('km_al_cargar', 10, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carga_combustibles');
    }
};
