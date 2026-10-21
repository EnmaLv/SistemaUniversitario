<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psicologo_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->string('titulo');
            $table->text('contenido');
            $table->string('tipo')->default('texto');
            $table->string('color_fondo')->nullable();
            $table->string('media_path')->nullable();
            $table->string('alcance')->default('todos');
            $table->timestamps();
            $table->integer('estatus')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
