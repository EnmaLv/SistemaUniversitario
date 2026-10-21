<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_segmento_personalizado', function (Blueprint $table) {
            $table->id('id_hsm');
            $table->string('titulo_hsm', 255)->nullable();
            $table->string('subtitulo_hsm', 255)->nullable();
            $table->string('icono_hsm', 255)->nullable();
            $table->longText('contenido_hsm')->nullable();
            $table->integer('orden_hsm')->default(0);
            $table->foreignId('hsp_id')->constrained('historia_seccion_personalizada', 'id_hsp')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_segmento_personalizado');
    }
};
