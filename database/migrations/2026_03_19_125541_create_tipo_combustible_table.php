<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_combustible', function (Blueprint $table) {
            $table->id('id_tcb');
            $table->string('nombre_tcb', 100);
            $table->string('descripcion_tcb', 255)->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_combustible');
    }
};
