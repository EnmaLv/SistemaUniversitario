<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasajero_viaje_transporte', function (Blueprint $table) {
            $table->id('id_pvt');
            $table->timestamp('escaneado_at_pvt');
            $table->foreignId('vjt_id')->constrained('viaje_transporte', 'id_vjt')->cascadeOnDelete();
            $table->foreignId('per_id')->constrained('persona', 'id_per')->cascadeOnDelete();
            $table->foreignId('ptr_id')->nullable()->constrained('parada_transporte', 'id_ptr')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['vjt_id', 'per_id'], 'pasajero_viaje_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasajero_viaje_transporte');
    }
};
