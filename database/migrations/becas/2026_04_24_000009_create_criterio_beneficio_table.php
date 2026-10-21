<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterio_beneficio', function (Blueprint $table) {
            $table->id('id_crb');
            $table->boolean('es_eliminatoria_crb')->default(false);
            $table->string('operador_crb', 20)->nullable();
            $table->string('valor_esperado_crb', 255)->nullable();
            $table->decimal('peso_crb', 8, 2)->default(0);
            $table->foreignId('bes_id')->constrained('beneficio_estudiantil', 'id_bes')->cascadeOnDelete();
            $table->foreignId('pgb_id')->constrained('pregunta_beneficio', 'id_pgb')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['bes_id', 'pgb_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterio_beneficio');
    }
};
