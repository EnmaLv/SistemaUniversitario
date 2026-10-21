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
        Schema::create('parroquia', function (Blueprint $table) {
            $table->id('id_par');
            $table->string('nombre_par');
            $table->foreignId('mun_id')->constrained('municipio', 'id_mun')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('localidad', function (Blueprint $table) {
            $table->id('id_loc');
            $table->string('nombre_loc');
            $table->foreignId('par_id')->constrained('parroquia', 'id_par')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });

        Schema::table('sede', function (Blueprint $table) {
            $table->foreign('loc_id')->references('id_loc')->on('localidad');
        });

        Schema::table('universidad', function (Blueprint $table) {
            $table->foreign('loc_id')->references('id_loc')->on('localidad');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sede', fn (Blueprint $table) => $table->dropForeign(['loc_id']));
        Schema::table('universidad', fn (Blueprint $table) => $table->dropForeign(['loc_id']));
        Schema::dropIfExists('localidad');
        Schema::dropIfExists('parroquia');
        Schema::dropIfExists('direccion');
    }
};
