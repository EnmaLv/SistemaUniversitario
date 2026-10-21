<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_animo_diario', function (Blueprint $table) {
            $table->id('id_ead');
            $table->integer('valor_ead');
            $table->date('fecha_ead');
            $table->foreignId('paciente_use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['paciente_use_id', 'fecha_ead']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_animo_diario');
    }
};
