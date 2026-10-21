<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avances_sesion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psicologo_id')->nullable()->constrained('user', 'id_use')->onDelete('cascade');
            $table->unsignedTinyInteger('valor')->default(0)->comment('Nivel del 1 al 10');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->boolean('es_sistema')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->tinyInteger('status')->default(1);

            $table->unique(['psicologo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avances_sesion');
    }
};
