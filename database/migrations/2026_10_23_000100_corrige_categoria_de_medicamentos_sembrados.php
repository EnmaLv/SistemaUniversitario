<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MEDICAMENTOS = [
        'ANT-ACE-001' => 'ANTIPIRÉTICOS',
        'ANT-LOR-006' => 'ANTIHISTAMÍNICOS',
        'ANT-IBU-008' => 'ANTIINFLAMATORIOS',
        'ANA-ATA-001' => 'ANALGÉSICOS',
        'ANT-BRU-002' => 'ANTIINFLAMATORIOS',
    ];

    public function up(): void
    {
        $tipo = DB::table('tipo_producto')
            ->where(DB::raw('LOWER(nombre_tpr)'), 'medicamento')
            ->whereNull('deleted_at')
            ->value('id_tpr');

        if ($tipo === null) {
            return;
        }

        foreach (self::MEDICAMENTOS as $codigo => $nombreCategoria) {
            $categoria = DB::table('categoria_producto')
                ->where('tpr_id', $tipo)
                ->where(DB::raw('LOWER(nombre_cap)'), mb_strtolower($nombreCategoria))
                ->whereNull('deleted_at')
                ->value('id_cap');

            if ($categoria === null) {
                continue;
            }

            DB::table('producto')
                ->where('codigo_pdt', $codigo)
                ->whereNotIn('cap_id', fn ($consulta) => $consulta->select('id_cap')->from('categoria_producto')->where('tpr_id', $tipo))
                ->update(['cap_id' => $categoria, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
    }
};
