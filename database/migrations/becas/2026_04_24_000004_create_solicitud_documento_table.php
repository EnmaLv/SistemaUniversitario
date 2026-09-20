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
        Schema::create('solicitud_documento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitud')->constrained('be_solicitud_becas')->onDelete('cascade');
            $table->string('nombre_documento')->nullable();
            $table->text('ruta_archivo')->nullable();
            $table->string('tipo_archivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('be_beneficios');
    }
};
