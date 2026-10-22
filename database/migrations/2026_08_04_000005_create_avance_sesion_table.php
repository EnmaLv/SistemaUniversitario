<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avance_sesion', function (Blueprint $table) {
            $table->id('id_avs');
            $table->unsignedTinyInteger('valor_avs')->default(0);
            $table->string('nombre_avs', 150);
            $table->text('descripcion_avs')->nullable();
            $table->boolean('es_sistema_avs')->default(false);
            $table->boolean('activo_avs')->default(true);
            $table->foreignId('psicologo_use_id')->nullable()->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avance_sesion');
    }
};
