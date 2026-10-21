<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterio_jornada_beneficio', function (Blueprint $table) {
            $table->id('id_cjb');
            $table->boolean('es_eliminatoria_cjb')->default(false);
            $table->string('operador_cjb', 20)->nullable();
            $table->string('valor_esperado_cjb', 255)->nullable();
            $table->decimal('peso_cjb', 8, 2)->default(0);
            $table->foreignId('jnb_id')->constrained('jornada_beneficio', 'id_jnb')->cascadeOnDelete();
            $table->foreignId('pgb_id')->constrained('pregunta_beneficio', 'id_pgb')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['jnb_id', 'pgb_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterio_jornada_beneficio');
    }
};
