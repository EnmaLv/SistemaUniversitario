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
        Schema::create('viaje_pasajeros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bus_viaje_id')->constrained('bus_viajes')->onDelete('cascade');

            $table->unsignedBigInteger('persona_id');
            $table->foreign('persona_id')->references('id_persona')->on('persona')->onDelete('cascade');

            $table->foreignId('bus_parada_id')->nullable()->constrained('bus_paradas')->nullOnDelete();

            $table->dateTime('escaneado_at');
            $table->timestamp('created_at')->nullable();

            $table->unique(['bus_viaje_id', 'persona_id'], 'viaje_pasajero_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viaje_pasajeros');
    }
};