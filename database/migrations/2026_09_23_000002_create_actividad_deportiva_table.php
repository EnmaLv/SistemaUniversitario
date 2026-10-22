<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad_deportiva', function (Blueprint $table) {
            $table->id('id_acd');
            $table->string('nombre_acd', 150);
            $table->text('descripcion_acd')->nullable();
            $table->string('tipo_acd', 30)->default('programada');
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_deportiva');
    }
};
