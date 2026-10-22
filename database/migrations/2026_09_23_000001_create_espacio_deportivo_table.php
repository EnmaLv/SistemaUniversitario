<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espacio_deportivo', function (Blueprint $table) {
            $table->id('id_epd');
            $table->string('nombre_epd', 150);
            $table->text('descripcion_epd')->nullable();
            $table->string('ubicacion_epd')->nullable();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espacio_deportivo');
    }
};
