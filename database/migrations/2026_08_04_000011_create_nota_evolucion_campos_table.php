<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_evolucion_campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psicologo_id')->nullable()->constrained('user', 'id_use')->onDelete('cascade');
            $table->string('titulo');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_evolucion_campos');
    }
};
