<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->id('id_pdt');
            $table->string('codigo_pdt', 50)->unique();
            $table->string('nombre_pdt', 150);
            $table->text('descripcion_pdt')->nullable();
            $table->string('imagen_pdt', 255)->nullable();
            $table->decimal('precio_compra_pdt', 10, 2)->nullable();
            $table->decimal('stock_minimo_pdt', 14, 2)->default(0);
            $table->decimal('stock_maximo_pdt', 14, 2)->default(0);
            $table->decimal('peso_contenido_pdt', 14, 2)->nullable()->default(0);
            $table->decimal('unidades_presentacion_pdt', 14, 3)->default(1);
            $table->string('presentacion_dispensacion_pdt', 100)->nullable();
            $table->foreignId('cap_id')->constrained('categoria_producto', 'id_cap')->cascadeOnDelete();
            $table->foreignId('unm_id')->constrained('unidad_medida', 'id_unm')->cascadeOnDelete();
            $table->foreignId('enp_id')->nullable()->constrained('envase_primario', 'id_enp')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
