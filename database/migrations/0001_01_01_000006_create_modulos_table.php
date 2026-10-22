<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('modulo', function (Blueprint $table) {
            $table->id('id_mod');
            $table->string('key_mod')->unique();
            $table->string('nombre_mod');
            $table->text('descripcion_mod')->nullable();
            $table->string('icono_mod', 100)->nullable();
            $table->integer('orden_mod')->default(0);
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down()
    {
        Schema::dropIfExists('modulo');
    }
};
