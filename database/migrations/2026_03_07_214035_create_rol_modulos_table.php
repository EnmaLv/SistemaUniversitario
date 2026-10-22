<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('rol_modulo', function (Blueprint $table) {
            $table->id('id_rmo');
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('mod_id');
            $table->timestamps();
            $table->auditoria();

            $table->foreign('rol_id')->references('id_rol')->on('rol')->onDelete('cascade');
            $table->foreign('mod_id')->references('id_mod')->on('modulo')->onDelete('cascade');

            $table->unique(['rol_id', 'mod_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rol_modulo');
    }
};
