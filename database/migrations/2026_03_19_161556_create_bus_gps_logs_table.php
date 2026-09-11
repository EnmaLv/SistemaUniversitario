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
        Schema::create('bus_gps_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('local_id')->nullable()->unique()->after('id');
            $table->foreignId('bus_viaje_id')->constrained('bus_viajes')->onDelete('cascade');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('velocidad', 6, 2)->nullable();
            $table->decimal('heading', 6, 2)->nullable();
            $table->dateTime('registrado_en')->nullable()->change();
            $table->string('origen', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_gps_logs');
    }
};
