<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultorio', function (Blueprint $table) {
            $table->id('id_csr');
            $table->string('nombre_csr', 150);
            $table->text('descripcion_csr')->nullable();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultorio');
    }
};
