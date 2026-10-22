<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_deportivo', function (Blueprint $table) {
            $table->id('id_cld');
            $table->string('nombre_cld');
            $table->text('descripcion_cld')->nullable();
            $table->date('fecha_inicio_cld');
            $table->date('fecha_fin_cld');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('use_id')->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_deportivo');
    }
};
