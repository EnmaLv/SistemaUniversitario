<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_solicitud_beneficio', function (Blueprint $table) {
            $table->id('id_dsb');
            $table->string('nombre_documento_dsb', 255)->nullable();
            $table->string('ruta_archivo_dsb', 500)->nullable();
            $table->string('tipo_archivo_dsb', 50)->nullable();
            $table->foreignId('sob_id')->constrained('solicitud_beneficio', 'id_sob')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_solicitud_beneficio');
    }
};
