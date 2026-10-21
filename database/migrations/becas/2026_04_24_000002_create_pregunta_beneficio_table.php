<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregunta_beneficio', function (Blueprint $table) {
            $table->id('id_pgb');
            $table->string('codigo_pgb', 100);
            $table->string('etiqueta_pgb', 255);
            $table->string('placeholder_pgb', 255)->nullable();
            $table->text('ayuda_pgb')->nullable();
            $table->string('tipo_campo_pgb', 50)->default('text');
            $table->boolean('obligatoria_pgb')->default(true);
            $table->decimal('valor_min_pgb', 15, 2)->nullable();
            $table->decimal('valor_max_pgb', 15, 2)->nullable();
            $table->unsignedInteger('min_length_pgb')->nullable();
            $table->unsignedInteger('max_length_pgb')->nullable();
            $table->string('regex_pgb', 255)->nullable();
            $table->unsignedInteger('orden_pgb')->default(0);
            $table->foreignId('bes_id')->constrained('beneficio_estudiantil', 'id_bes')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
            $table->unique(['bes_id', 'codigo_pgb']);
            $table->index(['bes_id', 'status_id', 'orden_pgb']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregunta_beneficio');
    }
};
