<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_medica', function (Blueprint $table) {
            $table->id('id_cme');
            $table->date('fecha_cme')->nullable();
            $table->text('motivo_cme');
            $table->text('observaciones_cme');
            $table->text('diagnostico_cme');
            $table->foreignId('paciente_per_id')->constrained('persona', 'id_per')->cascadeOnDelete();
            $table->foreignId('medico_per_id')->constrained('persona', 'id_per')->cascadeOnDelete();
            $table->foreignId('csr_id')->constrained('consultorio', 'id_csr')->cascadeOnDelete();
            $table->foreignId('use_id')->constrained('user', 'id_use')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('consulta_enfermedad', function (Blueprint $table) {
            $table->id('id_cen');
            $table->foreignId('cme_id')->constrained('consulta_medica', 'id_cme')->cascadeOnDelete();
            $table->foreignId('efm_id')->constrained('enfermedad', 'id_efm')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_enfermedad');
        Schema::dropIfExists('consulta_medica');
    }
};
