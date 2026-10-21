<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proveedor_id')->constrained('proveedors')->onDelete('cascade');
            $table->foreignId('creado_por')->nullable()->references("id_usuario")->on("usuario")->onDelete('cascade');
            $table->foreignId('modulo_id')->nullable()->constrained('modulos')->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed');
            $table->timestamp('fecha')->useCurrent();
            $table->decimal('total', 10, 2);
            $table->string('estado', 50);
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
