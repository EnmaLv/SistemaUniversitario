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
        Schema::create('programa', function (Blueprint $table) {
            $table->id('id_prg');
            $table->string('codigo_prg', 30)->unique();
            $table->string('nombre_prg');
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('persona_programa', function (Blueprint $table) {
            $table->id('id_ppr');
            $table->foreignId('per_id')
                ->references('id_per')->on('persona')
                ->onDelete('cascade');
            $table->foreignId('prg_id')
                ->references('id_prg')->on("programa")
                ->onDelete('cascade');
            $table->date('fecha_inicio_ppr');
            $table->date('fecha_fin_ppr')->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona_programa');
        Schema::dropIfExists('programa');
    }
};
