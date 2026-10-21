<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carga_combustible', function (Blueprint $table) {
            $table->id('id_ccb');
            $table->date('fecha_ccb');
            $table->decimal('litros_ccb', 8, 2);
            $table->decimal('precio_litro_ccb', 10, 2);
            $table->decimal('total_ccb', 12, 2);
            $table->decimal('km_al_cargar_ccb', 10, 2);
            $table->text('observaciones_ccb')->nullable();
            $table->foreignId('vtr_id')->constrained('vehiculo_transporte', 'id_vtr')->cascadeOnDelete();
            $table->foreignId('tcb_id')->nullable()->constrained('tipo_combustible', 'id_tcb')->nullOnDelete();
            $table->foreignId('vjt_id')->nullable()->constrained('viaje_transporte', 'id_vjt')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carga_combustible');
    }
};
