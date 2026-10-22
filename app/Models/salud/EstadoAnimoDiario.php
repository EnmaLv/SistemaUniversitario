<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadoAnimoDiario
{
    protected static $table = 'estado_animo_diario';

    public static function create(array $data)
    {
        $data['created_id'] = $data['created_id'] ?? Auth::id();
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();
        
        return DB::table(self::$table)->insertGetId($data, 'id_ead');
    }

    public static function getTodayForUser($userId)
    {
        return DB::table(self::$table)
            ->where('paciente_use_id', $userId)
            ->whereDate('fecha_ead', Carbon::today())
            ->first();
    }
}
