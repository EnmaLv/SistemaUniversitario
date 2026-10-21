<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receta', function (Blueprint $table) {
            $table->id('id_rct');
            $table->string('nombre_rct');
            $table->text('descripcion_rct')->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta');
    }
};
