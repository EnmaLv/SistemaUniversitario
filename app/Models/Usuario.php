<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = true;
    protected $appends = ['nombre_completo'];

    protected $fillable = [
        'id_persona',
        'id_perfil',
        'username',
        'password',
        'master_key',
        'security_questions',
        'extra_permissions',
        'ultima_actividad_chat',
        'chat_activo_user_id',
        'infracciones_reset_at',
    ];

    protected $hidden = ['password', 'master_key'];

    protected $casts = [
        'security_questions' => 'array',
        'extra_permissions' => 'array',
    ];

    public function canAccessMenu($keys): bool
    {
        $keysToCheck = is_array($keys) ? $keys : [$keys];
        foreach ($this->roles ?? [] as $r) {
            $nombreRol = is_object($r) ? ($r->nombre ?? '') : ($r['nombre'] ?? '');
            if (in_array(mb_strtolower($nombreRol), ['administrador', 'secretaria de bienestar'])) {
                return true;
            }
        }

        $extra = is_array($this->extra_permissions)
            ? $this->extra_permissions
            : (is_string($this->extra_permissions) ? json_decode($this->extra_permissions, true) : []);

        $userDeny = $extra['deny'] ?? [];
        $userAllow = $extra['allow'] ?? [];

        $rolePermissions = collect($this->roles)
            ->pluck('menu_permissions')
            ->flatten()
            ->filter()
            ->unique()
            ->all();

        foreach ($keysToCheck as $key) {
            if (in_array($key, $userDeny)) {
                continue;
            }

            if (in_array($key, $userAllow)) {
                return true;
            }

            if (in_array($key, $rolePermissions)) {
                return true;
            }
        }

        return false;
    }



    public function paciente()
    {
        return $this->belongsTo(Usuario::class, 'user_id', 'id_usuario');
    }

    public function psicologo()
    {
        return $this->belongsTo(Usuario::class, 'psicologo_id', 'id_usuario');
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'id_perfil', 'id_perfil');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'id_usuario', 'id_rol');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function setMasterKeyAttribute($value)
    {
        $this->attributes['master_key'] = Hash::make($value);
    }

    public function verifyMasterKey($candidate)
    {
        try {
            $stored = $this->getOriginal('master_key') ?: $this->master_key;
            if (is_null($stored) || $stored === '') {
                return false;
            }

            return Hash::check($candidate, $stored);
        } catch (\RuntimeException $e) {
            $stored = $this->getOriginal('master_key') ?: $this->master_key;
            if (is_string($stored) && $stored !== '') {
                return password_verify($candidate, $stored);
            }

            return false;
        }
    }

    public function getNombreCompletoAttribute(): string
    {
        if ($this->persona) {
            $nombre   = trim($this->persona->nombre_persona ?? '');
            $apellido = trim($this->persona->apellido_persona ?? '');

            $nombreCompleto = trim("{$nombre} {$apellido}");

            if (!empty($nombreCompleto)) {
                return $nombreCompleto;
            }
        }

        return 'Sin nombre registrado';
    }

    public function tieneRol(array|string $roles): bool
    {
        $roles = (array) $roles;

        return $this->roles()
            ->where(function ($query) use ($roles) {
                $query->whereIn('slug', $roles)
                    ->orWhereIn('nombre', $roles);
            })
            ->exists();
    }

    public function verifyPassword($candidate)
    {
        try {
            $stored = $this->getOriginal('password') ?: $this->password;
            if (is_null($stored) || $stored === '') {
                return false;
            }

            return Hash::check($candidate, $stored);
        } catch (\RuntimeException $e) {
            $stored = $this->getOriginal('password') ?: $this->password;
            if (is_string($stored) && $stored !== '') {
                return password_verify($candidate, $stored);
            }

            return false;
        }
    }

    public static function obtenerUsuarioPorId($id)
    {
        $userRaw = DB::table('usuario')
            ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
            ->join('rol_usuario', 'usuario.id_usuario', '=', 'rol_usuario.id_usuario')
            ->join('rol', 'rol_usuario.id_rol', '=', 'rol.id_rol')
            ->select('usuario.*', DB::raw("CONCAT(persona.nombre_persona, ' ', persona.apellido_persona) as name"))
            ->where('usuario.id_usuario', $id)
            ->first();

        if ($userRaw) {
            $user = new self((array) $userRaw);
            $user->id = $userRaw->id_usuario;
            $user->id_usuario = $userRaw->id_usuario;

            if (!isset($user->profile_photo_path)) {
                $user->profile_photo_path = null;
            }

            $user->profile_photo_url = !empty($user->profile_photo_path) ? Storage::disk('public')->url($user->profile_photo_path) : null;
            $initials = strtoupper(substr($user->nombre_persona ?? '', 0, 1) . substr($user->apellido_persona ?? '', 0, 1));
            $user->avatar = $user->profile_photo_url ?: ($initials ?: 'PR');

            $user->primera_cita = DB::table('citas')
                ->where('user_id', $id)
                ->whereNotNull('fecha')
                ->orderBy('fecha', 'asc')
                ->value('fecha');

            return $user;
        }

        return null;
    }

    public static function obtenerPacientesSinCita($busqueda = '')
    {
        $query = DB::table('usuario')
            ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
            ->join('rol_usuario', 'usuario.id_usuario', '=', 'rol_usuario.id_usuario')
            ->join('rol', 'rol_usuario.id_rol', '=', 'rol.id_rol')
            ->select(
                'usuario.*',
                'persona.nombre_persona as nombres',
                'persona.apellido_persona as apellidos',
                'persona.cedula_persona as cedula',
                'persona.email_persona as email',
                DB::raw("TRIM(CONCAT(COALESCE(persona.nombre_persona, ''), ' ', COALESCE(persona.apellido_persona, ''))) as name")
            )
            ->where(function ($q) {
                $q->where('rol.slug', 'paciente')
                    ->orWhere('rol.nombre', 'Paciente');
            })
            ->where('persona.estado', 1)
            ->whereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('citas')
                    ->whereColumn('citas.user_id', 'usuario.id_usuario')
                    ->whereIn('citas.estado', ['pendiente', 'confirmada']);
            })
            ->distinct();

        if ($busqueda) {
            $buscarNormalized = mb_strtolower($busqueda, 'UTF-8');
            $query->where(function ($q) use ($buscarNormalized) {
                $q->whereRaw("LOWER(COALESCE(persona.nombre_persona, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(persona.apellido_persona, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(persona.nombre_persona, ''), ' ', COALESCE(persona.apellido_persona, '')))) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(persona.email_persona, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(persona.cedula_persona, '')) LIKE ?", ["{$buscarNormalized}%"]);
            });
        }

        $usuariosRaw = $query->orderBy('persona.nombre_persona', 'asc')->limit(20)->get();

        return $usuariosRaw->map(function ($u) {
            $u->id = $u->id_usuario;

            $user = new self();
            $user->setRawAttributes((array) $u, true);
            $user->exists = true;

            return $user;
        });
    }

    public static function contarMensajesNoLeidos($userId)
    {
        $conversationIds = DB::table('conversations')->where('user_one_id', $userId)->orWhere('user_two_id', $userId)->pluck('id');
        return DB::table('messages')->whereIn('conversation_id', $conversationIds)->where('sender_id', '!=', $userId)->whereNull('read_at')->count();
    }

    public static function obtenerPsicologosDisponibles()
    {
        $psicologos = DB::table('usuario')
            ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
            ->join('rol_usuario', 'usuario.id_usuario', '=', 'rol_usuario.id_usuario')
            ->join('rol', 'rol_usuario.id_rol', '=', 'rol.id_rol')
            ->join('grupos_horarios', 'usuario.id_usuario', '=', 'grupos_horarios.user_id')
            ->select(
                'usuario.id_usuario',
                'usuario.username',
                'persona.nombre_persona',
                'persona.apellido_persona',
                DB::raw("CONCAT(persona.nombre_persona, ' ', persona.apellido_persona) as name")
            )
            ->where(function ($query) {
                $query->where('rol.slug', 'psicologo')
                    ->orWhere('rol.slug', 'administrador')
                    ->orWhere('rol.nombre', 'like', '%psicologo%')
                    ->orWhere('rol.nombre', 'like', '%administrador%');
            })
            ->where('grupos_horarios.activo', 1)
            ->distinct()
            ->get();

        $diasMapSort = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Miercoles' => 3, 'Jueves' => 4, 'Viernes' => 5];

        foreach ($psicologos as $psicologo) {
            $psicologo->gruposHorarios = DB::table('grupos_horarios')
                ->where('user_id', $psicologo->id_usuario)
                ->where('activo', 1)
                ->get();

            $slots = [];
            $diasLaborables = [];

            foreach ($psicologo->gruposHorarios as $grupo) {
                $horarios = DB::table('horarios')
                    ->where('grupo_horario_id', $grupo->id)
                    ->whereIn('activo', [1, true])
                    ->get();

                $horariosSorted = $horarios->sortBy(function ($h) use ($diasMapSort) {
                    return ($diasMapSort[$h->dia] ?? 9) . '-' . $h->hora_inicio;
                });

                foreach ($horariosSorted as $h) {
                    $diaName = $h->dia === 'Miercoles' ? 'Miércoles' : $h->dia;
                    if (!in_array($diaName, $diasLaborables)) {
                        $diasLaborables[] = $diaName;
                    }
                    $inicio = \Carbon\Carbon::parse($h->hora_inicio)->format('g:i A');
                    $fin = \Carbon\Carbon::parse($h->hora_fin)->format('g:i A');
                    $blockStr = $diaName . ': ' . $inicio . ' - ' . $fin;
                    if (!in_array($blockStr, $slots)) {
                        $slots[] = $blockStr;
                    }
                }
            }

            $psicologo->dias_laborables = $diasLaborables;
            $psicologo->slots = $slots;
        }

        return $psicologos;
    }

    public static function obtenerContactosParaChat($userId, $isPsicologo)
    {
        if ($isPsicologo) {
            $pacientesIds = DB::table('citas')->where('psicologo_id', $userId)->pluck('user_id')->unique();
            return DB::table('usuario')
                ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
                ->select(
                    'usuario.id_usuario',
                    'usuario.username',
                    'persona.nombre_persona',
                    'persona.apellido_persona',
                    DB::raw("CONCAT(persona.nombre_persona, ' ', persona.apellido_persona) as name")
                )
                ->whereIn('usuario.id_usuario', $pacientesIds)
                ->distinct()
                ->get()
                ->map(function ($u) {
                    $firstName = explode(' ', trim($u->nombre_persona ?? ''))[0] ?? '';
                    $firstLastName = explode(' ', trim($u->apellido_persona ?? ''))[0] ?? '';
                    $shortName = trim($firstName . ' ' . $firstLastName);
                    $u->name = $shortName ?: $u->name;
                    return $u;
                });
        } else {
            $psicologosIds = DB::table('citas')->where('user_id', $userId)->pluck('psicologo_id')->unique();
            return DB::table('usuario')
                ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
                ->select(
                    'usuario.id_usuario',
                    'usuario.username',
                    'persona.nombre_persona',
                    'persona.apellido_persona',
                    DB::raw("CONCAT(persona.nombre_persona, ' ', persona.apellido_persona) as name")
                )
                ->whereIn('usuario.id_usuario', $psicologosIds)
                ->distinct()
                ->get()
                ->map(function ($psicologo) {
                    $firstName = explode(' ', trim($psicologo->nombre_persona ?? ''))[0] ?? '';
                    $firstLastName = explode(' ', trim($psicologo->apellido_persona ?? ''))[0] ?? '';
                    $shortName = trim($firstName . ' ' . $firstLastName);
                    $psicologo->name = $shortName ?: $psicologo->name;
                    return $psicologo;
                });
        }
    }
}
