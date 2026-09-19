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

        //Tabla de beneficios(Tabla configuracion de las becas)
        Schema::create('be_beneficios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_beneficio')->nullable(false);
            $table->text('descripcion')->nullable(true);
            $table->string('slug')->nullable(false);
            $table->integer('cupones_disponibles')->nullable(true)->default(0);
            $table->integer('cupones_ocupados')->nullable(true)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('be_beneficios');
    }
};
