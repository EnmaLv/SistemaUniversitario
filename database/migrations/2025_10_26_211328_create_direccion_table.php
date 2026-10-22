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
            $table->id('id_dir');
            $table->string('sector_dir');
            $table->string('calle_dir');
            $table->foreignId('per_id')->references('id_per')->on('persona')->onDelete('cascade');
            $table->foreignId('par_id')->constrained('parroquia', 'id_par')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('direccion');
    }
};
