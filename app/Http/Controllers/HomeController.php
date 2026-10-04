<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ExchangeRates;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\SolicitudBeca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Salud\PsicologiaHomeService;
use App\Services\Salud\SaludHomeService;
use App\Services\Transporte\TransporteHomeService;
use App\Services\Comedor\ComedorHomeService;
use App\Services\becas\BecasHomeService;
use App\Models\salud\Cita;

class HomeController extends Controller
{
    protected $psicologiaService;
    protected $saludService;
    protected $transporteService;
    protected $comedorService;
    protected $becasService;

    public function __construct(
        PsicologiaHomeService $psicologiaService,
        SaludHomeService $saludService,
        TransporteHomeService $transporteService,
        ComedorHomeService $comedorService,
        BecasHomeService $becasService
    ) {
        $this->middleware('auth');
        $this->psicologiaService = $psicologiaService;
        $this->saludService      = $saludService;
        $this->transporteService = $transporteService;
        $this->comedorService    = $comedorService;
        $this->becasService      = $becasService;
    }

    public function index()
    {
        $guardUser = Auth::user();
        $user = Usuario::query()->findOrFail(Auth::id());
        $guardSnapshot = [
            'id' => $guardUser?->getAuthIdentifier(),
        ];

        if ($guardUser instanceof Usuario) {
            $guardSnapshot += [
                'persona_id' => $guardUser->id_persona,
                'persona_relation_loaded' => $guardUser->relationLoaded('persona'),
                'persona_relation_id' => $guardUser->relationLoaded('persona')
                    ? $guardUser->getRelation('persona')?->id_persona
                    : null,
                'roles_relation_loaded' => $guardUser->relationLoaded('roles'),
                'roles' => $guardUser->relationLoaded('roles')
                    ? $guardUser->getRelation('roles')->pluck('nombre')->all()
                    : null,
            ];
        }

        $user->load(['persona', 'roles']);
        Auth::guard()->setUser($user);
        $userId = $user->id_usuario;
        $permisosAntes = [
            'usuario_id' => session('permisos_usuario_id'),
            'es_admin' => session('es_admin'),
            'modulos' => session('modulos_permitidos'),
            'menu' => session('menu_permissions_user'),
        ];

        (new \App\AdminLTE\Filters\ModuleFilter())->asegurarSesion($userId);

        Log::info('Home access diagnostic', [
            'usuario' => [
                'guard_id' => $guardUser?->getAuthIdentifier(),
                'id' => $userId,
                'username' => $user->username,
                'id_perfil' => $user->id_perfil,
                'role_legacy' => $user->getAttribute('role'),
            ],
            'guard_user_before_refresh' => $guardSnapshot,
            'roles_eloquent_activos' => $user->roles()
                ->get(['rol.id_rol', 'rol.nombre', 'rol.slug'])
                ->map(fn($role) => ['id' => $role->id_rol, 'nombre' => $role->nombre, 'slug' => $role->slug])
                ->values()
                ->all(),
            'roles_pivote_incluyendo_borrados' => DB::table('rol_usuario')
                ->join('rol', 'rol.id_rol', '=', 'rol_usuario.id_rol')
                ->where('rol_usuario.id_usuario', $userId)
                ->get(['rol.id_rol', 'rol.nombre', 'rol.slug', 'rol.deleted_at'])
                ->map(fn($role) => ['id' => $role->id_rol, 'nombre' => $role->nombre, 'slug' => $role->slug, 'deleted_at' => $role->deleted_at])
                ->all(),
            'permisos_sesion_antes' => $permisosAntes,
            'permisos_sesion_despues' => [
                'usuario_id' => session('permisos_usuario_id'),
                'es_admin' => session('es_admin'),
                'modulos' => session('modulos_permitidos'),
                'menu' => session('menu_permissions_user'),
            ],
        ]);

        $psicologiaData = $this->psicologiaService->getPacienteData();
        $saludData      = $this->saludService->getDashboardData();
        $transporteData = $this->transporteService->getDashboardData();
        $comedorData    = $this->comedorService->getDashboardData();
        $becasData      = $this->becasService->getDashboardData();
        $psicologiaAdminData = null;
        if ($user->tieneRol(['administrador', 'secretaria de bienestar'])) {
            $psicologiaAdminData = $this->construirPsicologiaAdminData();
        }
        $administracionData = ['secciones' => $this->construirResumenAdministracion()];

        $roleName = $this->resolveRoleName($user);

        if ($roleName && strtolower($roleName) === 'obrero') {
            return redirect()->route('admin.movimientos.registro_comida.index');
        }

        $visibleModules = $this->construirModulosVisibles($roleName);

        $hoy             = Carbon::now();
        $jornadasActivas = JornadaBeca::where('activa', 1)
            ->whereDate('fecha_inicio_solicitud', '<=', $hoy)
            ->whereDate('fecha_fin_solicitud', '>=', $hoy)
            ->with(['beneficio', 'lapso'])
            ->get();

        $jornadasRenovables = $this->obtenerJornadasRenovables($user, $jornadasActivas);

        $ultimaTasa = ExchangeRates::latest()->first();

        $resumenGeneral = is_null(session('modulo_activo'))
            ? $this->construirResumenGeneral()
            : null;

        return view('home', array_merge([
            'variacion_dolar' => $ultimaTasa?->variacion,
            'tasa_actual'     => $ultimaTasa?->tasa,
            'visibleModules'  => $visibleModules,
            'saludData'       => $saludData,
            'resumenGeneral'  => $resumenGeneral,
            'transporteData'  => $transporteData,
            'comedorData'     => $comedorData,
            'becasData'       => $becasData,
            'psicologiaAdminData' => $psicologiaAdminData,
            'administracionData' => $administracionData,
        ], $psicologiaData));
    }

    public function becasEstadisticas(Request $request)
    {
        $data = $this->becasService->getDashboardData([
            'start_date'     => $request->query('start_date'),
            'end_date'       => $request->query('end_date'),
            'beneficio_id'   => $request->query('beneficio_id'),
            'jornada_id'     => $request->query('jornada_id'),
            'estado'         => $request->query('estado'),
            'tipo_solicitud' => $request->query('tipo_solicitud'),
            'lapso_id'       => $request->query('lapso_id'),
        ]);

        return response()->json(['resumen' => $data['resumen']]);
    }

    public function comedorEstadisticas(Request $request)
    {
        $data = $this->comedorService->getDashboardData([
            'start_date' => $request->query('start_date'),
            'end_date'   => $request->query('end_date'),
            'pnf_id'     => $request->query('pnf_id'),
        ]);

        return response()->json(['resumen' => $data['resumen']]);
    }

    public function transporteEstadisticas(Request $request)
    {
        $data = $this->transporteService->getDashboardData([
            'start_date'   => $request->query('start_date'),
            'end_date'     => $request->query('end_date'),
            'vehiculo_id'  => $request->query('vehiculo_id'),
            'ruta_id'      => $request->query('ruta_id'),
            'conductor_id' => $request->query('conductor_id'),
            'turno'        => $request->query('turno'),
            'estado'       => $request->query('estado'),
        ]);

        return response()->json(['resumen' => $data['resumen']]);
    }

    protected function construirPsicologiaAdminData(): array
    {
        $fechaInicio = Carbon::now()->subDays(30)->toDateString();
        $fechaFin    = Carbon::now()->toDateString();

        return [
            'fechaInicio'  => $fechaInicio,
            'fechaFin'     => $fechaFin,
            'avances'      => DB::table('avances_sesion')
                ->where('status', 1)
                ->orderBy('nombre', 'asc')
                ->get(),
            'estadosAnimo' => DB::table('estado_animos')
                ->where('status', 1)
                ->orderBy('valor', 'asc')
                ->get(),
        ];
    }

    public function psicologiaEstadisticasGenerales(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->tieneRol(['administrador', 'secretaria de bienestar'])) {
            abort(403, 'Solo administradores pueden acceder a las estadísticas generales.');
        }

        $fechaInicio = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $fechaFin    = $request->input('end_date', Carbon::now()->toDateString());

        $estado          = $request->input('estado');
        $avanceId        = $request->input('avance_id');
        $estadoAnimoId   = $request->input('estado_animo_id');
        $prioridad       = $request->input('prioridad');
        $perfilAcademico = $request->input('perfil_academico');
        $pnf             = $request->input('pnf');

        // null → agrega TODOS los psicólogos
        $citas = Cita::obtenerEstadisticas(
            null, $fechaInicio, $fechaFin,
            $estado, $avanceId, $estadoAnimoId,
            $prioridad, $perfilAcademico, $pnf
        );

        $resumen = Cita::obtenerResumenEstadistico($citas, $fechaInicio, $fechaFin, null);
        $resumen['por_estado'] = $citas->groupBy('estado')->map->count()->toArray();

        $porPsicologo = Cita::obtenerEstadisticasPorPsicologo($fechaInicio, $fechaFin);
        $resumen['total_psicologos_activos'] = count($porPsicologo);

        return response()->json([
            'resumen'       => $resumen,
            'por_psicologo' => $porPsicologo,
            'fechaInicio'   => $fechaInicio,
            'fechaFin'      => $fechaFin,
        ]);
    }

    protected function construirResumenAdministracion(): array
    {
        return [
            [
                'titulo'      => 'Catálogos maestros',
                'icon'        => 'fa-database',
                'descripcion' => 'Datos base que alimentan todos los módulos del sistema.',
                'funcionalidades' => [
                    'Sedes donde opera la institución',
                    'Categorías y tipos de producto',
                    'Catálogo general de productos',
                    'Proveedores que abastecen al sistema',
                    'Programas nacionales de formación (PNF)',
                    'Envases primarios para medicamentos',
                ],
            ],
            [
                'titulo'      => 'Operaciones',
                'icon'        => 'fa-arrow-right-arrow-left',
                'descripcion' => 'Registros de actividad diaria que alimentan el inventario.',
                'funcionalidades' => [
                    'Compras: registro y seguimiento del ciclo completo',
                    'Movimientos: entradas y salidas del inventario',
                    'Lotes: control de vencimientos y trazabilidad',
                    'Registro diario del comedor',
                    'Inventario disponible por sede',
                    'Historial completo de operaciones',
                ],
            ],
            [
                'titulo'      => 'Configuración del sistema',
                'icon'        => 'fa-sliders',
                'descripcion' => 'Usuarios, roles, permisos y ajustes generales de la plataforma.',
                'funcionalidades' => [
                    'Gestión de usuarios y sus datos',
                    'Roles con permisos por módulo',
                    'Permisos granulares por acción',
                    'Configuración general del sistema',
                    'Gestor de archivos cargados',
                    'Clave maestra de administración',
                ],
            ],
            [
                'titulo'      => 'Salud del sistema',
                'icon'        => 'fa-heart-pulse',
                'descripcion' => 'Catálogos auxiliares y datos de referencia transversal.',
                'funcionalidades' => [
                    'PNF y perfiles institucionales',
                    'Tipos de producto y categorías médicas',
                    'Consultorios y horarios de atención',
                    'Enfermedades y estados de ánimo',
                    'Estados, municipios y localidades',
                    'Prioridades y avances clínicos',
                ],
            ],
        ];
    }

    protected function resolveRoleName($user): ?string
    {
        if (! $user || ! method_exists($user, 'roles')) {
            return null;
        }

        $roles = $user->roles;
        $privilegedRole = $roles->first(function ($role) {
            return in_array(mb_strtolower($role->nombre ?? ''), ['administrador', 'secretaria de bienestar'], true);
        });

        return $privilegedRole?->nombre ?? $roles->first()?->nombre;
    }

    protected function construirModulosVisibles(?string $roleName): array
    {
        $rol             = $roleName ? Rol::where('nombre', $roleName)->first() : null;
        $menuPermissions = $rol?->menu_permissions ?? [];
        $isAdministrator = $roleName && strtolower($roleName) === 'administrador';
        $isSecretaria    = $roleName && strtolower($roleName) === 'secretaria de bienestar';

        $menuConfig = config('adminlte.menu');

        $findKeyForUrl = function ($items, $targetUrl) use (&$findKeyForUrl) {
            foreach ($items as $item) {
                if (isset($item['url']) && trim($item['url'], '/') === trim($targetUrl, '/')) {
                    return $item['key'] ?? null;
                }
                if (isset($item['submenu']) && is_array($item['submenu'])) {
                    $found = $findKeyForUrl($item['submenu'], $targetUrl);
                    if ($found !== null) return $found;
                }
            }
            return null;
        };

        $modules = [
            'envases_primarios' => 'admin/salud/maestros/envases_primarios',
            'sedes'             => 'admin/maestros/sedes',
            'categorias'        => 'admin/maestros/categorias',
            'productos'         => 'admin/maestros/productos',
            'proveedores'       => 'admin/maestros/proveedores',
            'compras'           => 'admin/movimientos/compras',
            'comidas'           => 'admin/maestros/recetas',
            'por_vencer'        => 'admin/movimientos/lotes',
            'registro_comida'   => 'admin/movimientos/registro_comida',
            'bus_marcas'            => 'admin/transporte/maestros/bus_marcas',
            'bus_modelos'           => 'admin/transporte/maestros/bus_modelos',
            'bus_tipo_combustibles' => 'admin/transporte/maestros/bus_tipo_combustibles',
            'bus_vehiculos'     => 'admin/transporte/maestros/bus_vehiculos',
            'bus_rutas'         => 'admin/transporte/maestros/bus_rutas',
            'bus_paradas'       => 'admin/transporte/maestros/bus_paradas',
            'bus_mantenimientos' => 'admin/transporte/maestros/bus_mantenimientos',
            'bus_viajes'        => 'admin/transporte/maestros/bus_viajes',
            'bus_carga_combustibles' => 'admin/transporte/maestros/bus_carga_combustibles',
            'jornada_becas'     => 'admin/becas/jornada',
            'beneficios'        => null,
        ];

        $visibleModules = [];
        foreach ($modules as $key => $url) {
            $menuKey = $url ? $findKeyForUrl($menuConfig, $url) : null;
            $visible = $isAdministrator
                || $isSecretaria
                || ($menuKey && in_array($menuKey, $menuPermissions));
            $visibleModules[$key] = $visible;
        }

        return $visibleModules;
    }

    protected function obtenerJornadasRenovables($user, $jornadasActivas)
    {
        $renovables = collect();

        if (!$user || !$user->id_persona) {
            return $renovables;
        }

        foreach ($jornadasActivas as $jornada) {
            $aprobadoAnterior = SolicitudBeca::where('id_beneficio', $jornada->beneficio_id)
                ->where('estado', 1)
                ->where('id_lapso', '!=', $jornada->lapsos_id)
                ->where('id_persona', $user->id_persona)
                ->exists();

            $postuladoActual = SolicitudBeca::where('jornada_id', $jornada->id)
                ->where('id_persona', $user->id_persona)
                ->exists();

            if ($aprobadoAnterior && !$postuladoActual) {
                $renovables->push($jornada);
            }
        }

        return $renovables;
    }

    protected function construirResumenGeneral(): array
    {
        $permitidos = is_array(session('modulos_permitidos')) ? session('modulos_permitidos') : [];
        $esAdmin    = (bool) (session('es_admin', false) ?? false);
        $puedeVer   = fn($key) => $esAdmin || in_array($key, $permitidos, true);

        $catalogo = [
            'comedor' => [
                'nombre'      => 'Comedor',
                'icon'        => 'fa-utensils',
                'descripcion' => 'Gestión integral del servicio de alimentación universitario: control de inventario, recetas, compras y registro diario de comidas.',
                'funcionalidades' => [
                    'Registro diario de comidas servidas',
                    'Control de inventario y lotes',
                    'Recetas, platos y productos',
                    'Compras y proveedores',
                    'Alertas por vencimiento y stock mínimo',
                ],
            ],
            'salud' => [
                'nombre'      => 'Salud',
                'icon'        => 'fa-heartbeat',
                'descripcion' => 'Atención médica a la comunidad universitaria: consultas, emisión de recetas y dispensación de medicamentos.',
                'funcionalidades' => [
                    'Registro de consultas médicas',
                    'Emisión de recetas',
                    'Dispensación de medicamentos',
                    'Gestión de consultorios y horarios',
                    'Reportes y estadísticas de atención',
                ],
            ],
            'psicologia' => [
                'nombre'      => 'Psicología',
                'icon'        => 'fa-brain',
                'descripcion' => 'Acompañamiento psicológico a estudiantes y personal, con agenda de citas y seguimiento clínico de cada paciente.',
                'funcionalidades' => [
                    'Solicitud y agenda de citas',
                    'Historias clínicas',
                    'Seguimiento de pacientes',
                    'Gestión de horarios del psicólogo',
                    'Reportes y estadísticas',
                ],
            ],
            'beca' => [
                'nombre'      => 'Becas',
                'icon'        => 'fa-graduation-cap',
                'descripcion' => 'Administración de programas de becas, jornadas de postulación y beneficios para los estudiantes de la universidad.',
                'funcionalidades' => [
                    'Jornadas de postulación',
                    'Verificación de solicitudes',
                    'Gestión de beneficios',
                    'Seguimiento de becarios',
                    'Historial de asignaciones',
                ],
            ],
            'transporte' => [
                'nombre'      => 'Transporte',
                'icon'        => 'fa-bus',
                'descripcion' => 'Control de la flota vehicular universitaria: rutas, viajes, combustible y mantenimiento de las unidades.',
                'funcionalidades' => [
                    'Gestión de vehículos y marcas',
                    'Rutas y paradas',
                    'Registro de viajes',
                    'Control de carga de combustible',
                    'Mantenimiento de unidades',
                ],
            ],
            'administracion' => [
                'nombre'      => 'Administración',
                'icon'        => 'fa-cog',
                'descripcion' => 'Configuración general del sistema: catálogos maestros, usuarios, roles y permisos de la plataforma.',
                'funcionalidades' => [
                    'Gestión de sedes y categorías',
                    'Administración de usuarios',
                    'Roles y permisos',
                    'Configuración del sistema',
                    'Catálogos maestros',
                ],
            ],
        ];

        $visibles = [];
        foreach ($catalogo as $key => $info) {
            if ($puedeVer($key)) {
                $visibles[$key] = $info;
            }
        }

        return [
            'modulos'      => $visibles,
            'totalModulos' => count($visibles),
        ];
    }
}
