<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campo_nota_evolucion', function (Blueprint $table) {
            $table->id('id_cne');
            $table->string('titulo_cne', 255);
            $table->foreignId('psicologo_use_id')->nullable()->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campo_nota_evolucion');
    }
};
