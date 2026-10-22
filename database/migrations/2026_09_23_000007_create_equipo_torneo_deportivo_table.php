<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_torneo_deportivo', function (Blueprint $table) {
            $table->id('id_etd');
            $table->string('nombre_equipo_etd', 150);
            $table->string('estado_etd', 20)->default('inscrito');
            $table->foreignId('tde_id')->constrained('torneo_deportivo', 'id_tde')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('prg_id')->nullable()->constrained('programa', 'id_prg')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();

            $table->unique(['tde_id', 'prg_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_torneo_deportivo');
    }
};
