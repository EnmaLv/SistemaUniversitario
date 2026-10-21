<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_horario_atencion', function (Blueprint $table) {
            $table->id('id_gha');
            $table->string('nombre_gha', 100);
            $table->foreignId('psicologo_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_horario_atencion');
    }
};
