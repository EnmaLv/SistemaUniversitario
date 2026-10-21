<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_animo_diarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->integer('valor');
            $table->date('fecha');
            $table->timestamps();

            $table->unique(['paciente_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_animo_diarios');
    }
};
