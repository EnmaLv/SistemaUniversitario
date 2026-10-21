<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_beneficio_estudiantil', function (Blueprint $table) {
            $table->id('id_tbe');
            $table->string('nombre_tbe', 255);
            $table->text('descripcion_tbe')->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_beneficio_estudiantil');
    }
};
