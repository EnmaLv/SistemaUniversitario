<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruta_transporte', function (Blueprint $table) {
            $table->id('id_rtr');
            $table->string('nombre_rtr', 150);
            $table->decimal('distancia_km_rtr', 10, 2)->default(0);
            $table->text('descripcion_rtr')->nullable();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruta_transporte');
    }
};
