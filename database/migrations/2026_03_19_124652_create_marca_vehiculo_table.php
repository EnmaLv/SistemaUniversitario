<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marca_vehiculo', function (Blueprint $table) {
            $table->id('id_mav');
            $table->string('nombre_mav', 100);
            $table->string('descripcion_mav', 255)->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marca_vehiculo');
    }
};
