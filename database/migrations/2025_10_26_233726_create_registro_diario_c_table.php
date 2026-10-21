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
            $table->foreignId('per_id')
                ->constrained('persona', 'id_per')
                ->onDelete('cascade');
            $table->foreignId('pep_id')
                ->constrained('persona_programa', 'id_pep')
                ->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed');
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
