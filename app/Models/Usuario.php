<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use App\Support\Permisos\MapaRutas;
use App\Support\Permisos\ResolutorAccion;

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
        'ultima_actividad_chat',
        'chat_activo_user_id',
        'infracciones_reset_at',
    ];

    protected $hidden = ['password', 'master_key', 'security_questions', 'remember_token'];

    protected $casts = [
        'security_questions'     => 'array',
        'bloqueado'              => 'boolean',
        'bloqueado_hasta'        => 'datetime',
        'bloqueado_en'           => 'datetime',
        'ventana_bloqueos_desde' => 'datetime',
        'ultimo_login'           => 'datetime',
        'intentos_fallidos'      => 'integer',
        'intentos_llave_fallidos' => 'integer',
        'bloqueos_temporales'    => 'integer',
        'debe_cambiar_password'  => 'boolean',
        'password_cambiado_en'   => 'datetime',
    ];

    protected ?array $permisosEfectivosCache = null;

    protected ?bool $esSuperAdminCache = null;

    protected ?bool $esPsicologoCache = null;

    public function canAccessMenu($keys): bool
    {
        return $this->puedeAlguna(is_array($keys) ? $keys : [$keys], 'ver');
    }

    public function esSuperAdmin(): bool
    {
        if ($this->esSuperAdminCache !== null) {
            return $this->esSuperAdminCache;
        }

        return $this->esSuperAdminCache = $this->roles->contains(fn ($rol) => $rol->esSuperAdmin());
    }

    public function esPsicologo(): bool
    {
        if ($this->esPsicologoCache !== null) {
            return $this->esPsicologoCache;
        }

        $roles = array_map('mb_strtolower', (array) config('permisos.roles_psicologo', []));

        return $this->esPsicologoCache = $this->roles->contains(
            fn ($rol) => in_array(mb_strtolower((string) $rol->slug), $roles, true)
                || in_array(mb_strtolower((string) $rol->nombre), $roles, true)
        );
    }

    public static function buscarPorIdentificador(string $identificador): ?self
    {
        $identificador = trim($identificador);

        if ($identificador === '') {
            return null;
        }

        if (\App\Support\Cuentas\Cedula::esCorreo($identificador)) {
            return self::whereRaw('LOWER(username) = ?', [mb_strtolower($identificador)])->first();
        }

        $cedula = \App\Support\Cuentas\Cedula::normalizar($identificador);

        if ($cedula === null) {
            return self::where('username', $identificador)->first();
        }

        $usuario = self::where('username', $cedula)->first();

        if ($usuario) {
            return $usuario;
        }

        $porPersona = self::whereHas('persona', fn ($consulta) => $consulta->where('cedula_persona', $cedula))
            ->limit(2)
            ->get();

        return $porPersona->count() === 1 ? $porPersona->first() : null;
    }

    public static function claveIdentificador(string $identificador): string
    {
        $identificador = trim($identificador);

        if (\App\Support\Cuentas\Cedula::esCorreo($identificador)) {
            return mb_strtolower($identificador);
        }

        return \App\Support\Cuentas\Cedula::normalizar($identificador) ?? mb_strtolower($identificador);
    }

    public function permisosEfectivos(): array
    {
        if ($this->permisosEfectivosCache !== null) {
            return $this->permisosEfectivosCache;
        }

        $hoy = now()->toDateString();

        $deRol = DB::table('rol_usuario')
            ->join('rol', 'rol.id_rol', '=', 'rol_usuario.id_rol')
            ->join('rol_permiso', 'rol_permiso.rol_id', '=', 'rol.id_rol')
            ->join('permiso', 'permiso.id', '=', 'rol_permiso.permiso_id')
            ->join('vista', 'vista.id', '=', 'permiso.vista_id')
            ->where('rol_usuario.id_usuario', $this->id_usuario)
            ->whereNull('rol.deleted_at')
            ->where('permiso.activo', true)
            ->where('vista.activo', true)
            ->pluck('permiso.key')
            ->all();

        $directos = DB::table('usuario_permiso')
            ->join('permiso', 'permiso.id', '=', 'usuario_permiso.permiso_id')
            ->join('vista', 'vista.id', '=', 'permiso.vista_id')
            ->where('usuario_permiso.usuario_id', $this->id_usuario)
            ->where('permiso.activo', true)
            ->where('vista.activo', true)
            ->where(fn ($q) => $q->whereNull('usuario_permiso.fecha_inicio')->orWhere('usuario_permiso.fecha_inicio', '<=', $hoy))
            ->where(fn ($q) => $q->whereNull('usuario_permiso.fecha_fin')->orWhere('usuario_permiso.fecha_fin', '>=', $hoy))
            ->get(['permiso.key', 'usuario_permiso.tipo']);

        $efectivos = array_fill_keys($deRol, true);

        foreach ($directos->where('tipo', 'grant') as $directo) {
            $efectivos[$directo->key] = true;
        }

        foreach ($directos->where('tipo', 'revoke') as $directo) {
            unset($efectivos[$directo->key]);
        }

        return $this->permisosEfectivosCache = $efectivos;
    }

    public function puede(string $vista, string $accion = 'ver'): bool
    {
        return $this->puedeAlguna([$vista], $accion);
    }

    public function puedeAlguna(array $vistas, string $accion = 'ver'): bool
    {
        if ($this->esSuperAdmin()) {
            return true;
        }

        $efectivos = $this->permisosEfectivos();

        foreach ($vistas as $vista) {
            if (isset($efectivos["{$vista}.{$accion}"])) {
                return true;
            }
        }

        return false;
    }

    public function puedeRuta(string $nombreRuta): bool
    {
        if ($this->esSuperAdmin()) {
            return true;
        }

        $ruta = Route::getRoutes()->getByName($nombreRuta);
        if (! $ruta) {
            return false;
        }

        if (MapaRutas::esLibre($nombreRuta, $ruta->uri())) {
            return true;
        }

        $vistas = MapaRutas::vistasDeRuta($nombreRuta, $ruta->uri());
        if (empty($vistas)) {
            return false;
        }

        $metodo = collect($ruta->methods())->first(fn ($m) => $m !== 'HEAD') ?? 'GET';

        return $this->puedeAlguna($vistas, ResolutorAccion::desdeRuta($nombreRuta, $metodo));
    }

    public function vistasVisibles(): array
    {
        if ($this->esSuperAdmin()) {
            return DB::table('vista')->where('activo', true)->pluck('key')->all();
        }

        return collect(array_keys($this->permisosEfectivos()))
            ->filter(fn ($clave) => Str::afterLast($clave, '.') === 'ver')
            ->map(fn ($clave) => Str::beforeLast($clave, '.'))
            ->values()
            ->all();
    }

    public function olvidarPermisos(): void
    {
        $this->permisosEfectivosCache = null;
        $this->esSuperAdminCache = null;
        $this->esPsicologoCache = null;
    }

    public function permisosDirectos()
    {
        return $this->belongsToMany(Permiso::class, 'usuario_permiso', 'usuario_id', 'permiso_id')
            ->withPivot(['tipo', 'fecha_inicio', 'fecha_fin', 'asignado_por'])
            ->withTimestamps();
    }

    public function modulosExtra()
    {
        return $this->belongsToMany(Modulo::class, 'usuario_modulo', 'usuario_id', 'modulo_id')
            ->withPivot('asignado_por')
            ->withTimestamps();
    }



    public function citas()
    {
        return $this->hasMany(\App\Models\salud\Cita::class, 'user_id', 'id_usuario');
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

    public static function obtenerPacientesSinCita($busqueda = '', $psicologoId = null)
    {
        $busqueda = trim((string) $busqueda);

        if (!$psicologoId || mb_strlen($busqueda) < 3) {
            return collect();
        }

        $query = DB::table('usuario')
            ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
            ->join('rol_usuario', 'usuario.id_usuario', '=', 'rol_usuario.id_usuario')
            ->join('rol', 'rol_usuario.id_rol', '=', 'rol.id_rol')
            ->select(
                'usuario.id_usuario',
                'usuario.id_persona',
                'usuario.username',
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
            ->when(\App\Support\Sedes\SedeActiva::id(), fn ($q, $sede) => $q->where('persona.sed_id', $sede))
            ->where('usuario.id_usuario', '!=', $psicologoId)
            ->whereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('citas')
                    ->whereColumn('citas.user_id', 'usuario.id_usuario')
                    ->whereIn('citas.estado', ['pendiente', 'confirmada']);
            })
            ->where(function ($q) use ($psicologoId) {
                $q->whereNotExists(function ($subquery) use ($psicologoId) {
                    $subquery->select(DB::raw(1))
                        ->from('citas')
                        ->whereColumn('citas.user_id', 'usuario.id_usuario')
                        ->where('citas.psicologo_id', '!=', $psicologoId);
                })->orWhereExists(function ($subquery) use ($psicologoId) {
                    $subquery->select(DB::raw(1))
                        ->from('citas')
                        ->whereColumn('citas.user_id', 'usuario.id_usuario')
                        ->where('citas.psicologo_id', $psicologoId);
                });
            })
            ->distinct();

        $buscarNormalized = mb_strtolower($busqueda, 'UTF-8');
        $query->where(function ($q) use ($buscarNormalized) {
            $q->whereRaw("LOWER(COALESCE(persona.nombre_persona, '')) LIKE ?", ["%{$buscarNormalized}%"])
                ->orWhereRaw("LOWER(COALESCE(persona.apellido_persona, '')) LIKE ?", ["%{$buscarNormalized}%"])
                ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(persona.nombre_persona, ''), ' ', COALESCE(persona.apellido_persona, '')))) LIKE ?", ["%{$buscarNormalized}%"])
                ->orWhereRaw("LOWER(COALESCE(persona.cedula_persona, '')) LIKE ?", ["{$buscarNormalized}%"]);
        });

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
            ->when(\App\Support\Sedes\SedeActiva::id(), fn ($q, $sede) => $q->where('persona.sed_id', $sede))
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
        $user = self::with('roles')->find($userId);
        if (!$user) {
            return collect();
        }

        $roleSlugs = $user->roles
            ->pluck('slug')
            ->map(fn ($s) => strtolower((string) $s))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $isPacienteOBecario = in_array('paciente', $roleSlugs, true)
                        || in_array('becario', $roleSlugs, true);

        $contactIds = collect();

        if ($isPacienteOBecario) {
            // Solo los psicólogos con los que tiene cita
            $contactIds = $contactIds->merge(
                DB::table('citas')
                    ->where('user_id', $userId)
                    ->pluck('psicologo_id')
            );
        } else {
            // STAFF: todo el personal interno (cualquier rol que no sea paciente/becario)
            $staffIds = DB::table('rol_usuario')
                ->join('rol', 'rol_usuario.id_rol', '=', 'rol.id_rol')
                ->whereNotIn('rol.slug', ['paciente', 'becario'])
                ->where('rol_usuario.id_usuario', '!=', $userId)
                ->pluck('rol_usuario.id_usuario');

            $contactIds = $contactIds->merge($staffIds);

            // Si además es psicólogo, añade a sus pacientes
            if ($isPsicologo) {
                $contactIds = $contactIds->merge(
                    DB::table('citas')
                        ->where('psicologo_id', $userId)
                        ->pluck('user_id')
                );
            }
        }

        $contactIds = $contactIds->filter()->unique()->values();

        if ($contactIds->isEmpty()) {
            return collect();
        }

        return DB::table('usuario')
            ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
            ->select(
                'usuario.id_usuario',
                'usuario.username',
                'persona.nombre_persona',
                'persona.apellido_persona',
                DB::raw("CONCAT(persona.nombre_persona, ' ', persona.apellido_persona) as name")
            )
            ->whereIn('usuario.id_usuario', $contactIds)
            ->distinct()
            ->get()
            ->map(function ($u) {
                $firstName    = explode(' ', trim($u->nombre_persona ?? ''))[0]  ?? '';
                $firstLastName = explode(' ', trim($u->apellido_persona ?? ''))[0] ?? '';
                $shortName    = trim($firstName . ' ' . $firstLastName);
                $u->name      = $shortName ?: $u->name;
                return $u;
            });
    }
}
