<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidad_medida', function (Blueprint $table) {
            $table->id('id_unm');
            $table->string('nombre_unm', 50);
            $table->string('abreviatura_unm', 20);
            $table->decimal('factor_conversion_unm', 14, 3)->default(1);
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad_medida');
    }
};
