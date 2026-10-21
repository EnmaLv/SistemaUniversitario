<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_producto', function (Blueprint $table) {
            $table->id('id_tpr');
            $table->string('nombre_tpr', 50)->unique();
            $table->foreignId('mod_id')->constrained('modulo', 'id_mod')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_producto');
    }
};
