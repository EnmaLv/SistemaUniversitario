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

        Schema::create('persona_pnf', function (Blueprint $table) {
            $table->id('id_persona_pnf');
            $table->foreignId('id_persona')
                ->references("id_persona")->on("persona")
                ->onDelete('cascade');
            $table->foreignId('prg_id')
                ->references('id_prg')->on("programa")
                ->onDelete('cascade');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona_pnf');
        Schema::dropIfExists('programa');
    }
};
