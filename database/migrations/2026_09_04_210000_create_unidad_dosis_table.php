<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidad_dosis', function (Blueprint $table) {
            $table->id('id_udo');
            $table->string('nombre_udo', 50)->unique();
            $table->string('abreviatura_udo', 20);
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad_dosis');
    }
};
