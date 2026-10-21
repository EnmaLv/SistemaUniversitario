<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id_use')->onDelete('cascade');
            $table->string('nombre');
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_horarios');
    }
};
