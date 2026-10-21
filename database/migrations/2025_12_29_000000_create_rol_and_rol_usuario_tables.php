<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre_rol')->unique();
            $table->string('slug_rol')->nullable()->unique();
            $table->text('descripcion_rol')->nullable();
            $table->foreignId('dep_id')->nullable()->constrained('departamento', 'id_dep');
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id('id_usr');
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('use_id');
            $table->timestamps();
            $table->auditoria();

            $table->foreign('rol_id')->references('id_rol')->on('rol')->onDelete('cascade');
            $table->foreign('use_id')->references('id_use')->on('user')->onDelete('cascade');
            $table->unique(['rol_id', 'use_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
        Schema::dropIfExists('rol');
    }
};
