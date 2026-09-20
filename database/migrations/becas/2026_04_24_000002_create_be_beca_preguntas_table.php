<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('be_beca_preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_be_beneficio')
                ->constrained('be_beneficios')
                ->onDelete('cascade');
            $table->string('codigo', 60);
            $table->string('etiqueta');
            $table->string('placeholder')->nullable();
            $table->text('ayuda')->nullable();

            $table->enum('tipo', [
                'text', 'textarea', 'number', 'decimal',
                'email', 'date', 'select', 'radio',
                'checkbox', 'boolean'
            ])->default('text');
            $table->boolean('obligatoria')->default(true);
            $table->decimal('valor_min', 15, 2)->nullable();
            $table->decimal('valor_max', 15, 2)->nullable();
            $table->unsignedInteger('min_length')->nullable();
            $table->unsignedInteger('max_length')->nullable();
            $table->string('regex')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['id_be_beneficio', 'codigo']);
            $table->index(['id_be_beneficio', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('be_beca_preguntas');
    }
};
