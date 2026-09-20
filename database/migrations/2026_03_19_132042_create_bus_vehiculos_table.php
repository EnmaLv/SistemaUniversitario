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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 20);
            $table->foreignId('modelo_id')->constrained('modelos')->onDelete('cascade');
            $table->integer('anio');
            $table->string('color', 50);
            $table->decimal('peso', 8, 2)->default(0);
            $table->integer('cantidad_pasajeros')->default(0);
            $table->foreignId('tipo_combustible_id')->constrained('tipo_combustibles')->onDelete('cascade');
            $table->integer('cantidad_cilindros')->default(1);
            $table->decimal('capacidad_tanque_litros', 8, 2)->default(0);
            $table->decimal('nivel_combustible_actual', 8, 2)->default(0);
            $table->decimal('consumo_urbano', 6, 3)->default(0);
            $table->decimal('consumo_carretera', 6, 3)->default(0);
            $table->decimal('consumo_relenti', 6, 3)->default(0);
            $table->decimal('km_actual', 10, 2)->default(0);
            $table->decimal('km_proximo_mantenimiento', 10, 2)->default(0);
            $table->foreignId('sede_id')->constrained('sede')->onDelete('cascade');
            $table->tinyInteger('activo')->default(1);
            $table->enum('estado', ['disponible', 'en_ruta', 'mantenimiento'])->default('disponible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};