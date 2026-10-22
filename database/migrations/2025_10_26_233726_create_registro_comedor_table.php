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
        Schema::create('registro_comedor', function (Blueprint $table) {
            $table->id('id_rgc');
            $table->foreignId('per_id')
                ->constrained('persona', 'id_per')
                ->onDelete('cascade');
            $table->foreignId('pep_id')
                ->constrained('persona_programa', 'id_pep')
                ->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed');
            $table->string('tipo_comida_rgc', 20)->default('desayuno');
            $table->date('fecha_comedor_rgc');
            $table->time('hora_comedor_rgc')->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_comedor');
    }
};
