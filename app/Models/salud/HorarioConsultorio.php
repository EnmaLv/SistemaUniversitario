<?php

namespace App\Models\salud;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class HorarioConsultorio extends Model
{
    use HasFactory;

    protected $table = 'horario_consultorios';

    protected $fillable = [
        'consultorio_id',
        'id_rol_usuario',
        'dia',
        'hora_inicio',
        'hora_fin',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
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
     * Accessor para formatear hora_inicio a HH:mm
     */
    protected function horaInicio(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('H:i') : null,
        );
    }

    /**
     * Accessor para formatear hora_fin a HH:mm
     */
    protected function horaFin(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('H:i') : null,
        );
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class, 'consultorio_id');
    }

    public function rolUsuario()
    {
        return $this->belongsTo(\App\Models\RolUsuario::class, 'id_rol_usuario');
    }

    /**
     * Nombre de la persona asignada a este bloque (vía rol_usuario -> usuario -> persona).
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
     * Usuarios elegibles para asignar horarios: filas de rol_usuario cuyo
     * id_rol sea Administrador de Salud (4) o Secretaria de Salud (5).
     */
    public static function usuariosElegibles()
    {
        $rolesPermitidos = [4, 5];

        return \App\Models\RolUsuario::with(['usuario.persona', 'rol'])
            ->whereIn('id_rol', $rolesPermitidos)
            ->get()
            ->map(function ($ru) {
                // Obtenemos la relación persona desde el usuario
                $persona = $ru->usuario->persona ?? null;

                // Consultar el consultorio asignado a este rol en los horarios
                $horario = self::with('consultorio')
                    ->where('id_rol_usuario', $ru->id)
                    ->where('activo', true)
                    ->first();

                return (object) [
                    'id_persona'      => $persona?->id_persona ?? $persona?->id, // ID necesario para la validación
                    'id_usuario'      => $ru->usuario?->id,
                    'id_rol_usuario'  => $ru->id,
                    'nombre_completo' => $ru->nombre_completo,
                    'nombre_rol'      => $ru->nombre_rol,
                    'consultorio_id'     => $horario?->consultorio_id ?? '',
                    'consultorio_nombre' => $horario?->consultorio?->nombre ?? 'Sin consultorio asignado',
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
            ->where('consultorio_id', $consultorioId)
            ->where('activo', true)
            ->get()
            ->groupBy('dia');
    }

    /**
     * Mapa [consultorio_id => ['lunes|07:00|08:15|10', ...]]
     */
    public static function ocupadosPorConsultorio()
    {
        return self::where('activo', true)
            ->get()
            ->groupBy('consultorio_id')
            ->map(function ($items) {
                return $items->map(function ($h) {
                    $inicio = Carbon::parse($h->hora_inicio)->format('H:i');
                    $fin    = Carbon::parse($h->hora_fin)->format('H:i');
                    return [
                        'clave'          => "{$h->dia}|{$inicio}|{$fin}",
                        'rol_usuario_id' => $h->id_rol_usuario,
                        'full'           => "{$h->dia}|{$inicio}|{$fin}|{$h->id_rol_usuario}"
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
            $inicio = Carbon::parse($h->hora_inicio)->format('H:i');
            $fin    = Carbon::parse($h->hora_fin)->format('H:i');

            return $inicio === $horaInicio && $fin === $horaFin;
        });
    }
}
