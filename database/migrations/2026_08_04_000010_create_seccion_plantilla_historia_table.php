<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seccion_plantilla_historia', function (Blueprint $table) {
            $table->id('id_sph');
            $table->string('titulo_sph', 255);
            $table->string('descripcion_general_sph')->nullable();
            $table->json('segmentos_sph')->nullable();
            $table->foreignId('psicologo_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['psicologo_use_id', 'titulo_sph']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seccion_plantilla_historia');
    }
};
