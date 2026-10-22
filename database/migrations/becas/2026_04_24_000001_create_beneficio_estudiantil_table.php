<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficio_estudiantil', function (Blueprint $table) {
            $table->id('id_bes');
            $table->string('nombre_beneficio_bes', 255);
            $table->text('descripcion_bes')->nullable();
            $table->string('slug_bes', 255);
            $table->integer('cupones_disponibles_bes')->default(0);
            $table->integer('cupones_ocupados_bes')->default(0);
            $table->foreignId('tbe_id')->nullable()->constrained('tipo_beneficio_estudiantil', 'id_tbe')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficio_estudiantil');
    }
};
