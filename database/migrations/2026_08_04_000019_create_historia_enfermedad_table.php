<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_enfermedad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_clinica_id')->constrained('historia_clinicas')->onDelete('cascade');
            $table->foreignId('efm_id')->constrained('enfermedad', 'id_efm')->onDelete('cascade');
            $table->string('contexto')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=activo, 0=desvinculado/eliminado (soft delete)');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_enfermedad');
    }
};
