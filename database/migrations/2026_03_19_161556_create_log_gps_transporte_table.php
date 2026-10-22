<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_gps_transporte', function (Blueprint $table) {
            $table->id('id_lgt');
            $table->uuid('local_id_lgt')->nullable()->unique();
            $table->decimal('lat_lgt', 11, 7);
            $table->decimal('lng_lgt', 11, 7);
            $table->decimal('velocidad_lgt', 6, 2)->nullable();
            $table->decimal('heading_lgt', 6, 2)->nullable();
            $table->timestamp('registrado_en_lgt')->nullable();
            $table->string('origen_lgt', 50)->default('app_conductor');
            $table->boolean('es_desvio_lgt')->default(false);
            $table->unsignedSmallInteger('tramo_desvio_lgt')->nullable();
            $table->foreignId('vjt_id')->constrained('viaje_transporte', 'id_vjt')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->index(['vjt_id', 'es_desvio_lgt'], 'log_gps_viaje_desvio_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_gps_transporte');
    }
};
