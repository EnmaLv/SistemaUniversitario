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
        Schema::create('bus_rutas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->decimal('distancia_km', 8, 2)->default(0.00);
            $table->text('descripcion')->nullable();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->onDelete('cascade');
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_rutas');
    }
};