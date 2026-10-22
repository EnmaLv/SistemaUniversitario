<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruta_parada_transporte', function (Blueprint $table) {
            $table->id('id_rpt');
            $table->integer('orden_rpt');
            $table->foreignId('rtr_id')->constrained('ruta_transporte', 'id_rtr')->cascadeOnDelete();
            $table->foreignId('ptr_id')->constrained('parada_transporte', 'id_ptr')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruta_parada_transporte');
    }
};
