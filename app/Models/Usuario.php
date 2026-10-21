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
    protected $table = 'user';
    protected $primaryKey = 'id_use';
    public $timestamps = true;
    protected $appends = ['nombre_completo'];

    protected $fillable = [
        'per_id',
        'id_perfil',
        'username_use',
        'email_use',
        'ultima_actividad_chat_use',
        'chat_activo_use_id',
        'infracciones_reset_at_use',
    ];

    protected $hidden = ['password_use', 'master_key_use', 'security_questions_use', 'remember_token_use'];

    protected $casts = [
        'security_questions_use'     => 'array',
        'bloqueado_use'          => 'boolean',
        'bloqueado_hasta_use'        => 'datetime',
        'bloqueado_en_use'           => 'datetime',
        'ventana_bloqueos_desde_use' => 'datetime',
        'ultimo_login_use'           => 'datetime',
        'intentos_fallidos_use'      => 'integer',
        'intentos_llave_fallidos_use' => 'integer',
        'bloqueos_temporales_use'    => 'integer',
        'debe_cambiar_password_use'  => 'boolean',
        'password_cambiado_en_use'   => 'datetime',
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
            fn ($rol) => in_array(mb_strtolower((string) $rol->slug_rol), $roles, true)
                || in_array(mb_strtolower((string) $rol->nombre_rol), $roles, true)
        );
    }

    public static function buscarPorIdentificador(string $identificador): ?self
    {
        $identificador = trim($identificador);

        if ($identificador === '') {
            return null;
        }

        if (\App\Support\Cuentas\Cedula::esCorreo($identificador)) {
            return self::whereRaw('LOWER(username_use) = ?', [mb_strtolower($identificador)])->first();
        }

        $cedula = \App\Support\Cuentas\Cedula::normalizar($identificador);

        if ($cedula === null) {
            return self::where('username_use', $identificador)->first();
        }

        $usuario = self::where('username_use', $cedula)->first();

        if ($usuario) {
            return $usuario;
        }

        $porPersona = self::whereHas('persona', fn ($consulta) => $consulta->where('cedula_per', $cedula))
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

        $deRol = DB::table('usuario_rol')
            ->join('rol', 'rol.id_rol', '=', 'usuario_rol.rol_id')
            ->join('rol_permiso', 'rol_permiso.rol_id', '=', 'rol.id_rol')
            ->join('permiso', 'permiso.id_pmi', '=', 'rol_permiso.pmi_id')
            ->join('vista', 'vista.id_vis', '=', 'permiso.vis_id')
            ->where('usuario_rol.use_id', $this->id_use)
            ->whereNull('rol.deleted_at')
            ->where('permiso.status_id', true)
            ->where('vista.status_id', true)
            ->pluck('permiso.nombre_pmi')
            ->all();

        $directos = DB::table('usuario_permiso')
            ->join('permiso', 'permiso.id_pmi', '=', 'usuario_permiso.pmi_id')
            ->join('vista', 'vista.id_vis', '=', 'permiso.vis_id')
            ->where('usuario_permiso.use_id', $this->id_use)
            ->where('permiso.status_id', true)
            ->where('vista.status_id', true)
            ->where(fn ($q) => $q->whereNull('usuario_permiso.fecha_inicio_usp')->orWhere('usuario_permiso.fecha_inicio_usp', '<=', $hoy))
            ->where(fn ($q) => $q->whereNull('usuario_permiso.fecha_fin_usp')->orWhere('usuario_permiso.fecha_fin_usp', '>=', $hoy))
            ->get(['permiso.nombre_pmi', 'usuario_permiso.tipo_usp']);

        $efectivos = array_fill_keys($deRol, true);

        foreach ($directos->where('tipo_usp', 'grant') as $directo) {
            $efectivos[$directo->nombre_pmi] = true;
        }

        foreach ($directos->where('tipo_usp', 'revoke') as $directo) {
            unset($efectivos[$directo->nombre_pmi]);
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
            return DB::table('vista')->where('status_id', 1)->whereNull('deleted_at')->pluck('key_vis')->all();
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
        return $this->belongsToMany(Permiso::class, 'usuario_permiso', 'use_id', 'pmi_id')
            ->withPivot(['tipo_usp', 'fecha_inicio_usp', 'fecha_fin_usp', 'asignado_por_use_id'])
            ->withTimestamps();
    }

    public function modulosExtra()
    {
        return $this->belongsToMany(Modulo::class, 'usuario_modulo', 'use_id', 'mod_id')
            ->withPivot('asignado_por_use_id')
            ->withTimestamps();
    }



    public function citas()
    {
        return $this->hasMany(\App\Models\salud\Cita::class, 'paciente_use_id', 'id_use');
    }

    public function getAuthPassword()
    {
        return $this->password_use;
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'id_perfil', 'id_perfil');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'use_id', 'rol_id');
    }

    public function getAuthPasswordName()
    {
        return 'password_use';
    }

    public function getRememberTokenName()
    {
        return 'remember_token_use';
    }

    public function setPasswordUseAttribute($value)
    {
        $this->attributes['password_use'] = Hash::make($value);
    }

    public function setMasterKeyUseAttribute($value)
    {
        $this->attributes['master_key_use'] = Hash::make($value);
    }

    public function verifyMasterKey($candidate)
    {
        try {
            $stored = $this->getOriginal('master_key_use') ?: $this->master_key_use;
            if (is_null($stored) || $stored === '') {
                return false;
            }

            return Hash::check($candidate, $stored);
        } catch (\RuntimeException $e) {
            $stored = $this->getOriginal('master_key_use') ?: $this->master_key_use;
            if (is_string($stored) && $stored !== '') {
                return password_verify($candidate, $stored);
            }

            return false;
        }
    }

    public function getNombreCompletoAttribute(): string
    {
        if ($this->persona) {
            $nombre   = trim($this->persona->nombres_per ?? '');
            $apellido = trim($this->persona->apellidos_per ?? '');

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
                $query->whereIn('slug_rol', $roles)
                    ->orWhereIn('nombre_rol', $roles);
            })
            ->exists();
    }

    public function verifyPassword($candidate)
    {
        try {
            $stored = $this->getOriginal('password_use') ?: $this->password_use;
            if (is_null($stored) || $stored === '') {
                return false;
            }

            return Hash::check($candidate, $stored);
        } catch (\RuntimeException $e) {
            $stored = $this->getOriginal('password_use') ?: $this->password_use;
            if (is_string($stored) && $stored !== '') {
                return password_verify($candidate, $stored);
            }

            return false;
        }
    }

    public static function obtenerUsuarioPorId($id)
    {
        $userRaw = DB::table('user')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->join('usuario_rol', 'user.id_use', '=', 'usuario_rol.use_id')
            ->join('rol', 'usuario_rol.rol_id', '=', 'rol.id_rol')
            ->select('user.*', DB::raw("CONCAT(persona.nombres_per, ' ', persona.apellidos_per) as name"))
            ->where('user.id_use', $id)
            ->first();

        if ($userRaw) {
            $user = new self((array) $userRaw);
            $user->id = $userRaw->id_use;
            $user->id_use = $userRaw->id_use;

            if (!isset($user->profile_photo_path)) {
                $user->profile_photo_path = null;
            }

            $user->profile_photo_url = !empty($user->profile_photo_path) ? Storage::disk('public')->url($user->profile_photo_path) : null;
            $initials = strtoupper(substr($user->nombres_per ?? '', 0, 1) . substr($user->apellidos_per ?? '', 0, 1));
            $user->avatar = $user->profile_photo_url ?: ($initials ?: 'PR');

            $user->primera_cita = \App\Models\salud\Cita::obtenerFechaPrimeraCita($id, Auth::id());

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

        $query = DB::table('user')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->join('usuario_rol', 'user.id_use', '=', 'usuario_rol.use_id')
            ->join('rol', 'usuario_rol.rol_id', '=', 'rol.id_rol')
            ->select(
                'user.id_use',
                'user.per_id',
                'user.username_use',
                'persona.nombres_per as nombres',
                'persona.apellidos_per as apellidos',
                'persona.cedula_per as cedula',
                'persona.correo_per as email',
                DB::raw("TRIM(CONCAT(COALESCE(persona.nombres_per, ''), ' ', COALESCE(persona.apellidos_per, ''))) as name")
            )
            ->where(function ($q) {
                $q->where('rol.slug_rol', 'paciente')
                    ->orWhere('rol.nombre_rol', 'Paciente');
            })
            ->where('persona.status_id', 1)
            ->when(\App\Support\Sedes\SedeActiva::id(), fn ($q, $sede) => $q->where('persona.sed_id', $sede))
            ->where('user.id_use', '!=', $psicologoId)
            ->whereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('cita_psicologica')
                    ->whereColumn('cita_psicologica.paciente_use_id', 'user.id_use')
                    ->whereIn('cita_psicologica.estado_cita_cps', ['pendiente', 'confirmada']);
            })
            ->where(function ($q) use ($psicologoId) {
                $q->whereNotExists(function ($subquery) use ($psicologoId) {
                    $subquery->select(DB::raw(1))
                        ->from('cita_psicologica')
                        ->whereColumn('cita_psicologica.paciente_use_id', 'user.id_use')
                        ->where('cita_psicologica.psicologo_use_id', '!=', $psicologoId);
                })->orWhereExists(function ($subquery) use ($psicologoId) {
                    $subquery->select(DB::raw(1))
                        ->from('cita_psicologica')
                        ->whereColumn('cita_psicologica.paciente_use_id', 'user.id_use')
                        ->where('cita_psicologica.psicologo_use_id', $psicologoId);
                });
            })
            ->distinct();

        $buscarNormalized = mb_strtolower($busqueda, 'UTF-8');
        $query->where(function ($q) use ($buscarNormalized) {
            $q->whereRaw("LOWER(COALESCE(persona.nombres_per, '')) LIKE ?", ["%{$buscarNormalized}%"])
                ->orWhereRaw("LOWER(COALESCE(persona.apellidos_per, '')) LIKE ?", ["%{$buscarNormalized}%"])
                ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(persona.nombres_per, ''), ' ', COALESCE(persona.apellidos_per, '')))) LIKE ?", ["%{$buscarNormalized}%"])
                ->orWhereRaw("LOWER(COALESCE(persona.cedula_per, '')) LIKE ?", ["{$buscarNormalized}%"]);
        });

        $usuariosRaw = $query->orderBy('persona.nombres_per', 'asc')->limit(20)->get();

        return $usuariosRaw->map(function ($u) {
            $u->id = $u->id_use;

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
        $psicologos = DB::table('user')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->join('usuario_rol', 'user.id_use', '=', 'usuario_rol.use_id')
            ->join('rol', 'usuario_rol.rol_id', '=', 'rol.id_rol')
            ->join('grupo_horario_atencion', 'user.id_use', '=', 'grupo_horario_atencion.psicologo_use_id')
            ->select(
                'user.id_use',
                'user.username_use',
                'persona.nombres_per',
                'persona.apellidos_per',
                DB::raw("CONCAT(persona.nombres_per, ' ', persona.apellidos_per) as name")
            )
            ->where(function ($query) {
                $query->where('rol.slug_rol', 'psicologo')
                    ->orWhere('rol.slug_rol', 'administrador')
                    ->orWhere('rol.nombre_rol', 'like', '%psicologo%')
                    ->orWhere('rol.nombre_rol', 'like', '%administrador%');
            })
            ->where('grupo_horario_atencion.status_id', 1)
            ->whereNull('grupo_horario_atencion.deleted_at')
            ->when(\App\Support\Sedes\SedeActiva::id(), fn ($q, $sede) => $q->where('persona.sed_id', $sede))
            ->distinct()
            ->get();

        $diasMapSort = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Miercoles' => 3, 'Jueves' => 4, 'Viernes' => 5];

        foreach ($psicologos as $psicologo) {
            $psicologo->gruposHorarios = DB::table('grupo_horario_atencion')
                ->where('psicologo_use_id', $psicologo->id_use)
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->get();

            $slots = [];
            $diasLaborables = [];

            foreach ($psicologo->gruposHorarios as $grupo) {
                $horarios = DB::table('horario_profesional')
                    ->where('gha_id', $grupo->id_gha)
                    ->where('status_id', 1)
                    ->whereNull('deleted_at')
                    ->get();

                $horariosSorted = $horarios->sortBy(function ($h) use ($diasMapSort) {
                    return ($diasMapSort[$h->dia_hpr] ?? 9) . '-' . $h->hora_inicio_hpr;
                });

                foreach ($horariosSorted as $h) {
                    $diaName = $h->dia_hpr === 'Miercoles' ? 'Miércoles' : $h->dia_hpr;
                    if (!in_array($diaName, $diasLaborables)) {
                        $diasLaborables[] = $diaName;
                    }
                    $inicio = \Carbon\Carbon::parse($h->hora_inicio_hpr)->format('g:i A');
                    $fin = \Carbon\Carbon::parse($h->hora_fin_hpr)->format('g:i A');
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
            ->pluck('slug_rol')
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
                DB::table('cita_psicologica')
                    ->where('paciente_use_id', $userId)
                    ->whereNull('deleted_at')
                    ->pluck('psicologo_use_id')
            );
        } else {
            // STAFF: todo el personal interno (cualquier rol que no sea paciente/becario)
            $staffIds = DB::table('usuario_rol')
                ->join('rol', 'usuario_rol.rol_id', '=', 'rol.id_rol')
                ->whereNotIn('rol.slug_rol', ['paciente', 'becario'])
                ->where('usuario_rol.use_id', '!=', $userId)
                ->pluck('usuario_rol.use_id');

            $contactIds = $contactIds->merge($staffIds);

            // Si además es psicólogo, añade a sus pacientes
            if ($isPsicologo) {
                $contactIds = $contactIds->merge(
                    DB::table('cita_psicologica')
                        ->where('psicologo_use_id', $userId)
                        ->whereNull('deleted_at')
                        ->pluck('paciente_use_id')
                );
            }
        }

        $contactIds = $contactIds->filter()->unique()->values();

        if ($contactIds->isEmpty()) {
            return collect();
        }

        return DB::table('user')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->select(
                'user.id_use',
                'user.username_use',
                'persona.nombres_per',
                'persona.apellidos_per',
                DB::raw("CONCAT(persona.nombres_per, ' ', persona.apellidos_per) as name")
            )
            ->whereIn('user.id_use', $contactIds)
            ->distinct()
            ->get()
            ->map(function ($u) {
                $firstName    = explode(' ', trim($u->nombres_per ?? ''))[0]  ?? '';
                $firstLastName = explode(' ', trim($u->apellidos_per ?? ''))[0] ?? '';
                $shortName    = trim($firstName . ' ' . $firstLastName);
                $u->name      = $shortName ?: $u->name;
                return $u;
            });
    }
}
