<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class ConfiguracionSistema extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'configuracion_sistema';

    protected $primaryKey = 'id_csi';
    protected $fillable = ['clave_csi', 'valor_csi'];

    public static function getParametro(string $clave)
    {
        return self::where('clave_csi', $clave)->first();
    }

    public static function checkMasterKey(string $candidate): bool
    {
        $row = self::getParametro('master_key');
        if (!$row) return false;
        return Hash::check($candidate, $row->valor_csi);
    }

    public static function updateMasterKey(string $newKey): void
    {
        $hash = Hash::make($newKey);
        $row = self::updateOrCreate(['clave_csi' => 'master_key'], ['valor_csi' => $hash]);
    }
}
