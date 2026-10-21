<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_consultorio', function (Blueprint $table) {
            $table->id('id_hrc');
            $table->string('dia_hrc', 20);
            $table->time('hora_inicio_hrc');
            $table->time('hora_fin_hrc');
            $table->text('descripcion_hrc')->nullable();
            $table->foreignId('csr_id')->constrained('consultorio', 'id_csr')->cascadeOnDelete();
            $table->foreignId('usr_id')->constrained('usuario_rol', 'id_usr')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_consultorio');
    }
};
