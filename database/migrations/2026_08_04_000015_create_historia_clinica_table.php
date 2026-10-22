<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_clinica', function (Blueprint $table) {
            $table->id('id_hcl');
            $table->foreignId('paciente_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->foreignId('psicologo_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['paciente_use_id', 'psicologo_use_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_clinica');
    }
};
