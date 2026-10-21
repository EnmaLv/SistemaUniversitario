<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('direccion', function (Blueprint $table) {
            $table->id('id_direccion');
            $table->string('sector');
            $table->string('calle');
            $table->foreignId('id_persona')->references('id_persona')->on('persona')->onDelete('cascade');
            $table->foreignId('par_id')->constrained('parroquia', 'id_par')->cascadeOnDelete();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('direccion');
    }
};
