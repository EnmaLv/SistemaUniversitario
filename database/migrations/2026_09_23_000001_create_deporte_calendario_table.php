<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deporte_calendario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->foreignId('id_sede')->constrained('sede', 'id')->onDelete('cascade');
            $table->tinyInteger('activo')->default(1);
            $table->foreignId('creado_por')->constrained('usuario', 'id_usuario')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deporte_calendario');
    }
};
