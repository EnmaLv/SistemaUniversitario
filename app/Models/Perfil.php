<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Perfil extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    public const USUARIO = 'Usuario';
    public const ESTUDIANTE = 'Estudiante';
    public const DOCENTE = 'Docente';
    public const OBRERO = 'Obrero';
    public const ADMINISTRATIVO = 'Administrativo';
    public const COMUNIDAD = 'Comunidad universitaria';
    public const ADMINISTRADOR = 'Administrador';

    public const NOMBRES = [
        self::USUARIO,
        self::ESTUDIANTE,
        self::DOCENTE,
        self::OBRERO,
        self::ADMINISTRATIVO,
        self::COMUNIDAD,
    ];

    public const INTERNOS = [self::USUARIO, self::ADMINISTRADOR];

    protected $table = 'perfil';
    protected $primaryKey = 'id_perfil';
    public $timestamps = true;

    protected $fillable = ['nombre_perfil', 'status_id'];

    private static ?array $cacheIds = null;

    public static function idsPorNombre(): array
    {
        if (self::$cacheIds === null) {
            self::$cacheIds = [];

            foreach (DB::table('perfil')->whereNull('deleted_at')->orderBy('id_perfil')->get(['id_perfil', 'nombre_perfil']) as $perfil) {
                $clave = self::normalizar($perfil->nombre_perfil);
                self::$cacheIds[$clave] ??= (int) $perfil->id_perfil;
            }
        }

        return self::$cacheIds;
    }

    public static function idPorNombre(string $nombre): ?int
    {
        return self::idsPorNombre()[self::normalizar($nombre)] ?? null;
    }

    public static function idEstudiante(): ?int
    {
        return self::idPorNombre(self::ESTUDIANTE);
    }

    public static function esEstudiante(int|string|null $idPerfil): bool
    {
        return $idPerfil !== null && $idPerfil !== '' && (int) $idPerfil === self::idEstudiante();
    }

    public static function idsInternos(): array
    {
        return array_values(array_filter(array_map(fn ($nombre) => self::idPorNombre($nombre), self::INTERNOS)));
    }

    public static function limpiarCache(): void
    {
        self::$cacheIds = null;
    }

    public static function normalizar(string $nombre): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim(Str::ascii($nombre))));
    }

    public function scopeComunidad($consulta)
    {
        return $consulta->where('status_id', 1)->whereNotIn('id_perfil', self::idsInternos());
    }
}
