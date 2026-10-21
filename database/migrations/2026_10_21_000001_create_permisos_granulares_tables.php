<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vista', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150)->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('ruta')->nullable();
            $table->string('icono', 100)->nullable();
            $table->integer('orden')->default(0);
            $table->unsignedBigInteger('modulo_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('modulo_id')->references('id')->on('modulos')->nullOnDelete();
        });

        Schema::create('permiso', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('accion', 30);
            $table->unsignedBigInteger('vista_id');
            $table->unsignedBigInteger('modulo_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('vista_id')->references('id')->on('vista')->cascadeOnDelete();
            $table->foreign('modulo_id')->references('id')->on('modulos')->nullOnDelete();
            $table->unique(['vista_id', 'accion'], 'uq_permiso_vista_accion');
        });

        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('permiso_id');
            $table->timestamps();

            $table->foreign('rol_id')->references('id_rol')->on('rol')->cascadeOnDelete();
            $table->foreign('permiso_id')->references('id')->on('permiso')->cascadeOnDelete();
            $table->unique(['rol_id', 'permiso_id'], 'uq_rol_permiso');
        });

        Schema::create('usuario_permiso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('permiso_id');
            $table->string('tipo', 10)->default('grant');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->unsignedBigInteger('asignado_por')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id_usuario')->on('usuario')->cascadeOnDelete();
            $table->foreign('permiso_id')->references('id')->on('permiso')->cascadeOnDelete();
            $table->foreign('asignado_por')->references('id_usuario')->on('usuario')->nullOnDelete();
            $table->unique(['usuario_id', 'permiso_id'], 'uq_usuario_permiso');
        });

        Schema::create('usuario_modulo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('modulo_id');
            $table->unsignedBigInteger('asignado_por')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id_usuario')->on('usuario')->cascadeOnDelete();
            $table->foreign('modulo_id')->references('id')->on('modulos')->cascadeOnDelete();
            $table->foreign('asignado_por')->references('id_usuario')->on('usuario')->nullOnDelete();
            $table->unique(['usuario_id', 'modulo_id'], 'uq_usuario_modulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_modulo');
        Schema::dropIfExists('usuario_permiso');
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('permiso');
        Schema::dropIfExists('vista');
    }
};
