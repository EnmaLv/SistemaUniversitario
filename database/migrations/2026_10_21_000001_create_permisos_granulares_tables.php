<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vista', function (Blueprint $table) {
            $table->id('id_vis');
            $table->string('key_vis', 150)->unique();
            $table->string('nombre_vis');
            $table->text('descripcion_vis')->nullable();
            $table->string('ruta_vis')->nullable();
            $table->string('icono_vis', 100)->nullable();
            $table->integer('orden_vis')->default(0);
            $table->unsignedBigInteger('mod_id')->nullable();
            $table->timestamps();
            $table->auditoria();

            $table->foreign('mod_id')->references('id_mod')->on('modulo')->nullOnDelete();
        });

        Schema::create('permiso', function (Blueprint $table) {
            $table->id('id_pmi');
            $table->string('nombre_pmi', 191)->unique();
            $table->string('etiqueta_pmi', 150);
            $table->text('descripcion_pmi')->nullable();
            $table->string('accion_pmi', 30);
            $table->unsignedBigInteger('vis_id');
            $table->unsignedBigInteger('mod_id')->nullable();
            $table->timestamps();
            $table->auditoria();

            $table->foreign('vis_id')->references('id_vis')->on('vista')->cascadeOnDelete();
            $table->foreign('mod_id')->references('id_mod')->on('modulo')->nullOnDelete();
            $table->unique(['vis_id', 'accion_pmi'], 'uq_permiso_vista_accion');
        });

        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->id('id_rop');
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('pmi_id');
            $table->timestamps();
            $table->auditoria();

            $table->foreign('rol_id')->references('id_rol')->on('rol')->cascadeOnDelete();
            $table->foreign('pmi_id')->references('id_pmi')->on('permiso')->cascadeOnDelete();
            $table->unique(['rol_id', 'pmi_id'], 'uq_rol_permiso');
        });

        Schema::create('usuario_permiso', function (Blueprint $table) {
            $table->id('id_usp');
            $table->unsignedBigInteger('use_id');
            $table->unsignedBigInteger('pmi_id');
            $table->string('tipo_usp', 10)->default('grant');
            $table->date('fecha_inicio_usp')->nullable();
            $table->date('fecha_fin_usp')->nullable();
            $table->unsignedBigInteger('asignado_por_use_id')->nullable();
            $table->timestamps();
            $table->auditoria();

            $table->foreign('use_id')->references('id_use')->on('user')->cascadeOnDelete();
            $table->foreign('pmi_id')->references('id_pmi')->on('permiso')->cascadeOnDelete();
            $table->foreign('asignado_por_use_id')->references('id_use')->on('user')->nullOnDelete();
            $table->unique(['use_id', 'pmi_id'], 'uq_usuario_permiso');
        });

        Schema::create('usuario_modulo', function (Blueprint $table) {
            $table->id('id_umo');
            $table->unsignedBigInteger('use_id');
            $table->unsignedBigInteger('mod_id');
            $table->unsignedBigInteger('asignado_por_use_id')->nullable();
            $table->timestamps();
            $table->auditoria();

            $table->foreign('use_id')->references('id_use')->on('user')->cascadeOnDelete();
            $table->foreign('mod_id')->references('id_mod')->on('modulo')->cascadeOnDelete();
            $table->foreign('asignado_por_use_id')->references('id_use')->on('user')->nullOnDelete();
            $table->unique(['use_id', 'mod_id'], 'uq_usuario_modulo');
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
