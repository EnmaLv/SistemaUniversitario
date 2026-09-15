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
        Schema::create('dispensacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_medica_id')->constrained('recetas_medicas', 'id')->onDelete('cascade');
            $table->foreignId('detalle_receta_medica_id')->constrained('detalle_recetas_medicas', 'id')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos', 'id')->onDelete('cascade');
            $table->foreignId('id_persona')->constrained('persona', 'id_persona')->onDelete('cascade');
            $table->foreignId('lote_id')->nullable()->constrained('lotes', 'id')->nullOnDelete();
            $table->decimal('cantidad', 14, 2);
            $table->foreignId('unidad_id')->constrained('unidades', 'id')->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sede', 'id')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuario', 'id_usuario')->onDelete('cascade');
            $table->date('fecha');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensacions');
    }
};
