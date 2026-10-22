<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TipoProducto extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    public const ALIMENTO = 'Alimento';

    public const MEDICAMENTO = 'Medicamento';

    public const IMPLEMENTO_DEPORTIVO = 'Implemento deportivo';

    public const MATERIAL_OFICINA = 'Material de oficina';

    public const SIN_VENCIMIENTO = [self::IMPLEMENTO_DEPORTIVO, self::MATERIAL_OFICINA];

    public const ADICIONALES_POR_MODULO = [
        'deporte' => [self::IMPLEMENTO_DEPORTIVO, self::MATERIAL_OFICINA],
    ];

    protected $table = 'tipo_producto';

    protected $primaryKey = 'id_tpr';

    protected $fillable = [
        'nombre_tpr',
        'mod_id',
        'status_id',
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'mod_id', 'id_mod');
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'tpr_id', 'id_tpr');
    }

    public static function productosDeTipos(array $nombres): Builder
    {
        return DB::table('producto')
            ->join('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
            ->join('tipo_producto', 'tipo_producto.id_tpr', '=', 'categoria_producto.tpr_id')
            ->whereIn(DB::raw('LOWER(tipo_producto.nombre_tpr)'), array_map('mb_strtolower', $nombres))
            ->whereNull('producto.deleted_at');
    }

    public static function idsParaModulo(?int $moduloId): array
    {
        if ($moduloId === null) {
            return [];
        }

        $clave = DB::table('modulo')->where('id_mod', $moduloId)->value('key_mod');
        $adicionales = array_map('mb_strtolower', self::ADICIONALES_POR_MODULO[$clave] ?? []);

        return DB::table('tipo_producto')
            ->whereNull('deleted_at')
            ->where(function ($consulta) use ($moduloId, $adicionales) {
                $consulta->where('mod_id', $moduloId);

                if ($adicionales !== []) {
                    $consulta->orWhereIn(DB::raw('LOWER(nombre_tpr)'), $adicionales);
                }
            })
            ->pluck('id_tpr')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function idPorNombre(string $nombre): ?int
    {
        $id = DB::table('tipo_producto')
            ->whereNull('deleted_at')
            ->where(DB::raw('LOWER(nombre_tpr)'), mb_strtolower($nombre))
            ->value('id_tpr');

        return $id === null ? null : (int) $id;
    }

    public static function productoEsDeTipo(int $productoId, string $nombre): bool
    {
        return static::productosDeTipos([$nombre])->where('producto.id_pdt', $productoId)->exists();
    }

    public static function idsProductosSinVencimiento(array $productoIds): array
    {
        if ($productoIds === []) {
            return [];
        }

        return static::productosDeTipos(self::SIN_VENCIMIENTO)
            ->whereIn('producto.id_pdt', $productoIds)
            ->pluck('producto.id_pdt')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
