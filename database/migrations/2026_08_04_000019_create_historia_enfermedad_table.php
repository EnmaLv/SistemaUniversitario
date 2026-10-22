<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_enfermedad', function (Blueprint $table) {
            $table->id('id_hie');
            $table->string('contexto_hie', 255)->nullable();
            $table->foreignId('hcl_id')->constrained('historia_clinica', 'id_hcl')->cascadeOnDelete();
            $table->foreignId('efm_id')->constrained('enfermedad', 'id_efm')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_enfermedad');
    }
};
