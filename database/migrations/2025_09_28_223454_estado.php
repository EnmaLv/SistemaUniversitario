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
        Schema::create('pais', function (Blueprint $table) {
            $table->id('id_pai');
            $table->string('nombre_pai');
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('estado', function (Blueprint $table) {
            $table->id('id_esd');
            $table->string('nombre_esd');
            $table->foreignId('pai_id')->constrained('pais', 'id_pai');
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado');
        Schema::dropIfExists('pais');
    }
};
