<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_seccion_personalizada', function (Blueprint $table) {
            $table->id('id_hsp');
            $table->string('titulo_hsp', 255);
            $table->string('descripcion_general_hsp')->nullable();
            $table->integer('orden_hsp')->default(0);
            $table->foreignId('hcl_id')->constrained('historia_clinica', 'id_hcl')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_seccion_personalizada');
    }
};
