<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('be_jornada_criterios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jornada')
                ->constrained('be_jornadas_becas')
                ->onDelete('cascade');
            $table->foreignId('id_pregunta')
                ->constrained('be_beca_preguntas')
                ->onDelete('cascade');
            $table->boolean('es_eliminatoria')->default(false);
            $table->enum('operador', [
                '=', '!=', '>', '>=', '<', '<=',
                'in', 'not_in', 'between'
            ])->nullable();
            $table->string('valor_esperado')->nullable();
            $table->decimal('peso', 8, 2)->default(0);
            $table->timestamps();
            $table->unique(['id_jornada', 'id_pregunta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('be_beca_preguntas');
    }
};
