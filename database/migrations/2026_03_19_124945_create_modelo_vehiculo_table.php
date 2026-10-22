<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modelo_vehiculo', function (Blueprint $table) {
            $table->id('id_mov');
            $table->string('nombre_mov', 100);
            $table->string('descripcion_mov', 255)->nullable();
            $table->foreignId('mav_id')->constrained('marca_vehiculo', 'id_mav')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelo_vehiculo');
    }
};
