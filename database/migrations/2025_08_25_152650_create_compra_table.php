<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compra', function (Blueprint $table) {
            $table->id('id_cmp');
            $table->timestamp('fecha_compra_cmp')->useCurrent();
            $table->decimal('total_cmp', 12, 2)->default(0);
            $table->string('estado_compra_cmp', 20)->default('Pendiente');
            $table->text('observacion_cmp')->nullable();
            $table->foreignId('prv_id')->constrained('proveedor', 'id_prv')->cascadeOnDelete();
            $table->foreignId('sed_id')->constrained('sede', 'id_sed');
            $table->foreignId('use_id')->nullable()->constrained('user', 'id_use')->nullOnDelete();
            $table->foreignId('mod_id')->nullable()->constrained('modulo', 'id_mod')->nullOnDelete();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra');
    }
};
