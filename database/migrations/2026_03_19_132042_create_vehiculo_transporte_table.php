<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculo_transporte', function (Blueprint $table) {
            $table->id('id_vtr');
            $table->string('placa_vtr', 20)->unique();
            $table->integer('anio_vtr');
            $table->string('color_vtr', 50)->nullable();
            $table->decimal('peso_vtr', 8, 2)->default(0);
            $table->integer('capacidad_pasajeros_vtr')->default(0);
            $table->integer('cantidad_cilindros_vtr')->default(1);
            $table->decimal('capacidad_tanque_litros_vtr', 8, 2)->default(0);
            $table->decimal('nivel_combustible_actual_vtr', 8, 2)->default(0);
            $table->decimal('consumo_urbano_vtr', 6, 3)->default(0);
            $table->decimal('consumo_carretera_vtr', 6, 3)->default(0);
            $table->decimal('consumo_ralenti_vtr', 6, 3)->default(0);
            $table->decimal('km_actual_vtr', 10, 2)->default(0);
            $table->decimal('km_proximo_mantenimiento_vtr', 10, 2)->default(0);
            $table->string('estado_operativo_vtr', 20)->default('disponible');
            $table->foreignId('mov_id')->constrained('modelo_vehiculo', 'id_mov')->cascadeOnDelete();
            $table->foreignId('tcb_id')->constrained('tipo_combustible', 'id_tcb')->cascadeOnDelete();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_transporte');
    }
};
