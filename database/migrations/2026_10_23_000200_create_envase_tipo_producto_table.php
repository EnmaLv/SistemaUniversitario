<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envase_tipo_producto', function (Blueprint $table) {
            $table->id('id_etp');
            $table->foreignId('enp_id')->constrained('envase_primario', 'id_enp')->cascadeOnDelete();
            $table->foreignId('tpr_id')->constrained('tipo_producto', 'id_tpr')->cascadeOnDelete();
            $table->timestamps();
            $table->auditoria();

            $table->unique(['enp_id', 'tpr_id'], 'uq_envase_tipo_producto');
        });

        $medicamento = DB::table('tipo_producto')
            ->where(DB::raw('LOWER(nombre_tpr)'), 'medicamento')
            ->whereNull('deleted_at')
            ->value('id_tpr');

        if ($medicamento === null) {
            return;
        }

        foreach (DB::table('envase_primario')->pluck('id_enp') as $envase) {
            DB::table('envase_tipo_producto')->insert([
                'enp_id'     => $envase,
                'tpr_id'     => $medicamento,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('envase_tipo_producto');
    }
};
