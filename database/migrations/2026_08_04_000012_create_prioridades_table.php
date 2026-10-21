<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('prioridades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('nivel_gravedad')->default(1);
            $table->boolean('activo')->default(true);
            $table->foreignId('psicologo_id')->nullable()->constrained('user', 'id_use')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['nombre', 'psicologo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prioridades');
    }
};
