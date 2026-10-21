<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_plantillas_globales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psicologo_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->json('secciones');
            $table->tinyInteger('status')->default(2)->comment('1 = Activo, 2 = Predeterminado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_plantillas_globales');
    }
};
