<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enfermedad', function (Blueprint $table) {
            $table->id('id_efm');
            $table->string('codigo_efm', 50)->nullable()->unique();
            $table->string('nombre_efm', 255);
            $table->string('categoria_efm', 20)->default('fisica');
            $table->unsignedSmallInteger('nivel_gravedad_efm')->default(0);
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enfermedad');
    }
};
