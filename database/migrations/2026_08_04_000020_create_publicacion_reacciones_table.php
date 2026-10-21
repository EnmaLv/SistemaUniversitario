<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicacion_reacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacion_id')->constrained('publicaciones')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['publicacion_id', 'paciente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicacion_reacciones');
    }
};