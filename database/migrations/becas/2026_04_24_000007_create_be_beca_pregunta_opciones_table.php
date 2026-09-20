<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('be_beca_pregunta_opciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_pregunta')
                ->constrained('be_beca_preguntas')
                ->onDelete('cascade');

            $table->string('etiqueta')->comment('Lo que ve el usuario: "Sí"');
            $table->string('valor', 100)->comment('Lo que se guarda: "1"');
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('be_beca_preguntas');
    }
};
