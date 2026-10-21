<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_animo', function (Blueprint $table) {
            $table->id('id_esa');
            $table->unsignedTinyInteger('valor_esa');
            $table->string('nombre_esa', 100);
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_animo');
    }
};
