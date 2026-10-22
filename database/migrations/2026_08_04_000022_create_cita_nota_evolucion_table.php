<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cita_nota_evolucion', function (Blueprint $table) {
            $table->id('id_cno');
            $table->text('contenido_cno')->nullable();
            $table->foreignId('cps_id')->constrained('cita_psicologica', 'id_cps')->cascadeOnDelete();
            $table->foreignId('cne_id')->constrained('campo_nota_evolucion', 'id_cne')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_nota_evolucion');
    }
};
