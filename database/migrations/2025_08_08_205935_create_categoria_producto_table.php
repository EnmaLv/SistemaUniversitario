<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_producto', function (Blueprint $table) {
            $table->id('id_cap');
            $table->string('nombre_cap', 100);
            $table->text('descripcion_cap')->nullable();
            $table->foreignId('tpr_id')->constrained('tipo_producto', 'id_tpr')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_producto');
    }
};
