<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_historia_clinica', function (Blueprint $table) {
            $table->id('id_phc');
            $table->string('titulo_phc', 255);
            $table->text('descripcion_phc')->nullable();
            $table->json('secciones_phc');
            $table->unsignedTinyInteger('estado_phc')->default(2);
            $table->foreignId('psicologo_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_historia_clinica');
    }
};
