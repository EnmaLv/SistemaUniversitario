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
        Schema::create('registro_diario_c', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_persona')
                ->constrained('persona', 'id_persona')
                ->onDelete('cascade');
            $table->foreignId('id_persona_pnf')
                ->constrained('persona_pnf', 'id_persona_pnf')
                ->onDelete('cascade');
            $table->string('tipo_comida', 20)->default('desayuno');
            $table->date('fecha_regis_diario_c');
            $table->time('hora')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_diario_c');
    }
};
