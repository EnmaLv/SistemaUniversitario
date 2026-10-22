<?php

namespace App\Models\salud;

use App\Models\RolUsuario;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class HorarioConsultorio extends Model
{
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'horario_consultorio';

    protected $primaryKey = 'id_hrc';

    protected $fillable = [
        'csr_id',
        'usr_id',
        'dia_hrc',
        'hora_inicio_hrc',
        'hora_fin_hrc',
        'descripcion_hrc',
        'status_id',
    ];

    /**
     * Días hábiles (lunes a viernes).
     */
    public const DIAS = [
        'lunes'     => 'Lunes',
        'martes'    => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves'    => 'Jueves',
        'viernes'   => 'Viernes',
    ];

    /**
     * Bloques de hora fijos por jornada (formato 24h).
     */
    public const BLOQUES = [
        'Matutino' => [
            ['inicio' => '07:00', 'fin' => '08:15'],
            ['inicio' => '08:15', 'fin' => '09:00'],
            ['inicio' => '09:20', 'fin' => '10:00'],
            ['inicio' => '10:00', 'fin' => '10:45'],
            ['inicio' => '10:45', 'fin' => '11:30'],
            ['inicio' => '11:30', 'fin' => '12:00'],
        ],
        'Vespertino' => [
            ['inicio' => '13:45', 'fin' => '14:25'],
            ['inicio' => '14:25', 'fin' => '15:05'],
            ['inicio' => '15:05', 'fin' => '15:45'],
            ['inicio' => '15:45', 'fin' => '16:40'],
            ['inicio' => '16:40', 'fin' => '17:20'],
            ['inicio' => '17:20', 'fin' => '18:00'],
        ],
        'Nocturno' => [
            ['inicio' => '18:00', 'fin' => '18:35'],
            ['inicio' => '18:35', 'fin' => '19:10'],
            ['inicio' => '19:10', 'fin' => '19:45'],
            ['inicio' => '19:45', 'fin' => '20:20'],
            ['inicio' => '20:20', 'fin' => '20:55'],
            ['inicio' => '20:55', 'fin' => '21:30'],
        ],
    ];

    /**
     * Accessor para formatear hora_inicio_hrc a HH:mm
     */
    protected function horaInicioHrc(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('H:i') : null,
        );
    }

    /**
     * Accessor para formatear hora_fin_hrc a HH:mm
     */
    protected function horaFinHrc(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('H:i') : null,
        );
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class, 'csr_id', 'id_csr');
    }

    public function rolUsuario()
    {
        return $this->belongsTo(RolUsuario::class, 'usr_id', 'id_usr');
    }

    /**
     * Nombre de la persona asignada a este bloque (vía usuario_rol -> user -> persona).
     */
    public function getNombrePersonalAttribute(): string
    {
        return optional($this->rolUsuario)->nombre_completo ?? 'Asignado';
    }

    /**
     * Nombre del rol con el que la persona fue asignada a este bloque.
     */
    public function getNombreRolAsignadoAttribute(): string
    {
        return optional($this->rolUsuario)->nombre_rol ?? 'Sin Rol';
    }

    /**
     * Usuarios elegibles para asignar horarios: filas de usuario_rol cuyo
     * rol_id sea Administrador de Salud (4) o Secretaria de Salud (5).
     */
    public static function usuariosElegibles()
    {
        $rolesPermitidos = [4, 5];

        return RolUsuario::with(['usuario.persona', 'rol'])
            ->whereIn('rol_id', $rolesPermitidos)
            ->get()
            ->map(function ($ru) {
                // Obtenemos la relación persona desde el usuario
                $persona = $ru->usuario->persona ?? null;

                // Consultar el consultorio asignado a este rol en los horarios
                $horario = self::with('consultorio')
                    ->where('usr_id', $ru->id_usr)
                    ->where('status_id', self::STATUS_ACTIVO)
                    ->first();

                return (object) [
                    'per_id'      => $persona?->id_per, // ID necesario para la validación
                    'id_use'      => $ru->usuario?->id_use,
                    'usr_id'  => $ru->id_usr,
                    'nombre_completo' => $ru->nombre_completo,
                    'nombre_rol'      => $ru->nombre_rol,
                    'consultorio_id'     => $horario?->csr_id ?? '',
                    'consultorio_nombre' => $horario?->consultorio?->nombre_csr ?? 'Sin consultorio asignado',
                ];
            })
            ->values();
    }

    /**
     * Horarios activos de un consultorio, agrupados por día.
     */
    public static function porConsultorioAgrupado(int $consultorioId)
    {
        return self::with('rolUsuario.usuario.persona', 'rolUsuario.rol')
            ->where('csr_id', $consultorioId)
            ->where('status_id', self::STATUS_ACTIVO)
            ->get()
            ->groupBy('dia_hrc');
    }

    /**
     * Mapa [csr_id => ['lunes|07:00|08:15|10', ...]]
     */
    public static function ocupadosPorConsultorio()
    {
        return self::where('status_id', self::STATUS_ACTIVO)
            ->get()
            ->groupBy('csr_id')
            ->map(function ($items) {
                return $items->map(function ($h) {
                    $inicio = Carbon::parse($h->hora_inicio_hrc)->format('H:i');
                    $fin    = Carbon::parse($h->hora_fin_hrc)->format('H:i');
                    return [
                        'clave'          => "{$h->dia_hrc}|{$inicio}|{$fin}",
                        'rol_usuario_id' => $h->usr_id,
                        'full'           => "{$h->dia_hrc}|{$inicio}|{$fin}|{$h->usr_id}"
                    ];
                })->values();
            });
    }

    /**
     * Retorna la colección de todos los usuarios asignados a un bloque específico.
     */
    public static function buscarRegistrosEnBloque($horariosDia, string $horaInicio, string $horaFin)
    {
        if (!$horariosDia) {
            return collect();
        }

        return $horariosDia->filter(function ($h) use ($horaInicio, $horaFin) {
            $inicio = Carbon::parse($h->hora_inicio_hrc)->format('H:i');
            $fin    = Carbon::parse($h->hora_fin_hrc)->format('H:i');

            return $inicio === $horaInicio && $fin === $horaFin;
        });
    }
}
