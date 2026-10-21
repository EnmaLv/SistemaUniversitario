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
        Schema::create('movimiento_inventarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->foreignId('sed_id')->constrained('sede', 'id_sed')->onDelete('cascade');
            $table->string('tipo_movimiento', 50);
            $table->foreignId('modulo_origen_id')->nullable()->constrained('modulos')->onDelete('cascade');
            $table->decimal('cantidad', 14, 2);
            $table->decimal('cantidad_convertida', 14, 2);
            $table->decimal('cantidad_anterior', 14, 2)->nullable();
            $table->decimal('cantidad_final', 14, 2)->nullable();
            $table->string('referencia_type', 50)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario', 'id_usuario')->nullOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades')->onDelete('cascade');
            $table->timestamp('fecha')->useCurrent();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventarios');
    }
};