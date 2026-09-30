<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ExchangeRates;
use App\Models\Rol;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\Beneficio;
use App\Models\Becas\SolicitudBeca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Salud\PsicologiaHomeService;
use App\Services\Salud\SaludHomeService;
use App\Services\Transporte\TransporteHomeService;
use App\Services\Comedor\ComedorHomeService;
use App\Services\becas\BecasHomeService;
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
        BecasHomeService $becasService,
    ) {
        $this->middleware('auth');
        $this->psicologiaService     = $psicologiaService;
        $this->saludService          = $saludService;
        $this->transporteService     = $transporteService;
        $this->comedorService        = $comedorService;
        $this->becasService          = $becasService;
    }

    public function index()
    {
        $psicologiaData = $this->psicologiaService->getPacienteData();
        $saludData      = $this->saludService->getDashboardData();
        $transporteData = $this->transporteService->getDashboardData();
        $comedorData    = $this->comedorService->getDashboardData();
        $becasData      = $this->becasService->getDashboardData();
        $administracionData = ['secciones' => $this->construirResumenAdministracion()];

        $user     = Auth::user();
        $roleName = $this->resolveRoleName($user);

        if (is_null(session('modulos_permitidos')) || is_null(session('menu_permissions_user'))) {
            (new \App\AdminLTE\Filters\ModuleFilter)->transform(['key' => 'init_check']);
        }

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

        return view(
            'home',
            array_merge([
                'variacion_dolar' => $ultimaTasa?->variacion,
                'tasa_actual'     => $ultimaTasa?->tasa,
                'visibleModules'  => $visibleModules,
                'saludData'       => $saludData,
                'resumenGeneral'  => $resumenGeneral,
                'transporteData'  => $transporteData,
                'comedorData'     => $comedorData,
                'becasData'       => $becasData,
                'administracionData' => $administracionData,
                'psicologiaData'       => $psicologiaData
            ])
        );
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

    protected function resolveRoleName($user): ?string
    {
        $roleName = $user->role ?? null;

        if (empty($roleName) && method_exists($user, 'roles')) {
            $roleName = optional($user->roles()->first())->nombre;
        }

        return $roleName;
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

    protected function construirResumenGeneral(): array
    {
        $permitidos = session('modulos_permitidos', []);
        $esAdmin    = session('es_admin', false);
        $puedeVer   = fn($key) => $esAdmin || in_array($key, $permitidos);

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
