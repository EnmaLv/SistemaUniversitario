<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_plantillas_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psicologo_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->string('titulo');
            $table->string('descripcion_general')->nullable();
            $table->json('segmentos')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique(['psicologo_id', 'titulo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_plantillas_secciones');
    }
};
