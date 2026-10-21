<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opcion_pregunta_beneficio', function (Blueprint $table) {
            $table->id('id_opb');
            $table->string('etiqueta_opb', 255);
            $table->string('valor_opb', 255);
            $table->unsignedInteger('orden_opb')->default(0);
            $table->foreignId('pgb_id')->constrained('pregunta_beneficio', 'id_pgb')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opcion_pregunta_beneficio');
    }
};
