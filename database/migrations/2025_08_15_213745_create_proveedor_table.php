<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedor', function (Blueprint $table) {
            $table->id('id_prv');
            $table->string('empresa_prv', 150)->nullable();
            $table->string('rif_prv', 30)->nullable();
            $table->string('nombre_contacto_prv', 150)->nullable();
            $table->string('telefono_prv', 30);
            $table->string('email_prv', 150);
            $table->text('direccion_prv')->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor');
    }
};
