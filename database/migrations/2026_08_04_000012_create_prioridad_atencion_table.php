<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prioridad_atencion', function (Blueprint $table) {
            $table->id('id_pat');
            $table->string('nombre_pat', 100);
            $table->integer('nivel_gravedad_pat')->default(1);
            $table->foreignId('psicologo_use_id')->nullable()->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['nombre_pat', 'psicologo_use_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prioridad_atencion');
    }
};
