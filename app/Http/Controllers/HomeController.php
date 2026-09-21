<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sede;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Compra;
use App\Models\Lote;
use App\Models\ExchangeRates;
use App\Models\Rol;
use App\Models\salud\EnvasePrimario;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\Beneficio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
// MÓDULO TRANSPORTE — Abdias
use \App\Models\BusMarca;
use \App\Models\BusModelo;
use \App\Models\BusTipoCombustible;
use \App\Models\BusVehiculo;
use \App\Models\BusRuta;
use \App\Models\BusParada;
use \App\Models\BusMantenimiento;
use \App\Models\BusViaje;
use \App\Models\BusCargaCombustible;
use App\Services\Salud\PsicologiaHomeService;
use App\Services\Salud\SaludHomeService;

class HomeController extends Controller
{
    protected $psicologiaService;
    protected $saludService;

    public function __construct(
        PsicologiaHomeService $psicologiaService,
        SaludHomeService $saludService
    ) {
        $this->middleware('auth');
        $this->psicologiaService = $psicologiaService;
        $this->saludService      = $saludService;
    }

    public function index()
    {
        $psicologiaData = $this->psicologiaService->getPacienteData();
        $saludData = $this->saludService->getDashboardData();
        $hoy = Carbon::now();
        $limite = Carbon::now()->addDays(7);
        $sedeId = Auth::user()->persona?->sede_id ?? 1;
        $user = Auth::user();

        $roleName = $user->role ?? null;
        if (empty($roleName) && method_exists($user, 'roles')) {
            $firstRole = $user->roles()->first();
            $roleName = $firstRole?->nombre ?? null;
        }

        if (is_null(session('modulos_permitidos')) || is_null(session('menu_permissions_user'))) {
            (new \App\AdminLTE\Filters\ModuleFilter)->transform(['key' => 'init_check']);
        }

        if ($roleName && strtolower($roleName) === 'obrero') {
            return redirect()->route('admin.movimientos.registro_comida.index');
        }

        $rol = $roleName ? Rol::where('nombre', $roleName)->first() : null;
        $menuPermissions = $rol?->menu_permissions ?? [];
        $isAdministrator = $roleName && strtolower($roleName) === 'administrador';
        $isSecretaria = $roleName && strtolower($roleName) === 'secretaria de bienestar';
        $total_envases_primarios = EnvasePrimario::count();
        $total_sedes             = Sede::count();
        $total_categorias        = Categoria::count();
        $total_productos         = Producto::count();
        $total_proveedores       = Proveedor::count();
        $total_compras           = Compra::count();
        $total_jornadas_becas    = JornadaBeca::count();
        $total_beneficios        = Beneficio::count();

        $jornadasActivas = JornadaBeca::where('activa', 1)
            ->whereDate('fecha_inicio_solicitud', '<=', $hoy)
            ->whereDate('fecha_fin_solicitud', '>=', $hoy)
            ->with(['beneficio', 'lapso'])
            ->get();

        $total_lotes_vencidos = Lote::whereDate('fecha_vencimiento', '<=', $hoy)
            ->where('estado', 1)
            ->count();

        $total_lotes_por_vencer = Lote::whereBetween(
            'fecha_vencimiento',
            [$hoy, $limite]
        )->count();

        $ultimaTasa = ExchangeRates::latest()->first();

        $productos_stock_minimo = Producto::select(
            'productos.id',
            'productos.nombre',
            'productos.stock_minimo',
            DB::raw('COALESCE(SUM(inventario_sede_lotes.cantidad_convertida), 0) as stock_actual')
        )
            ->join('lotes', 'lotes.producto_id', '=', 'productos.id')
            ->join('inventario_sede_lotes', function ($join) use ($sedeId) {
                $join->on('inventario_sede_lotes.lote_id', '=', 'lotes.id')
                    ->where('inventario_sede_lotes.sede_id', '=', $sedeId);
            })
            ->where('productos.estado', 1)
            ->groupBy('productos.id', 'productos.nombre', 'productos.stock_minimo')
            ->havingRaw('SUM(inventario_sede_lotes.cantidad_convertida) <= productos.stock_minimo')
            ->havingRaw('SUM(inventario_sede_lotes.cantidad_convertida) > 0')
            ->orderBy('stock_actual', 'asc')
            ->get();

        $total_productos_stock_minimo = $productos_stock_minimo->count();
        $total_bus_marcas            = BusMarca::count();
        $total_bus_modelos           = BusModelo::count();
        $total_bus_tipo_combustibles = BusTipoCombustible::count();
        $total_bus_vehiculos = BusVehiculo::count();
        $total_bus_rutas = BusRuta::count();
        $total_bus_paradas = BusParada::count();
        $total_bus_mantenimientos = BusMantenimiento::count();
        $total_bus_viajes = BusViaje::count();
        $total_bus_cargas = BusCargaCombustible::count();

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
            // ── Becas ──────────────────────────────────────────
            'jornada_becas'         => 'admin/becas/jornada',
            'beneficios'
        ];

        $visibleModules = [];
        foreach ($modules as $key => $url) {
            $menuKey = $findKeyForUrl($menuConfig, $url);
            $visible = $isAdministrator || $isSecretaria || ($menuKey && in_array($menuKey, $menuPermissions));
            $visibleModules[$key] = $visible;
        }

        $jornadasRenovables = collect();
        if ($user && $user->id_persona) {
            foreach ($jornadasActivas as $jornada) {
                $aprobadoAnterior = \App\Models\Becas\SolicitudBeca::where('id_beneficio', $jornada->beneficio_id)
                    ->where('estado', 1)
                    ->where('id_lapso', '!=', $jornada->lapsos_id)
                    ->where('id_persona', $user->id_persona)
                    ->exists();
                $postuladoActual = \App\Models\Becas\SolicitudBeca::where('jornada_id', $jornada->id)
                    ->where('id_persona', $user->id_persona)
                    ->exists();
                
                if ($aprobadoAnterior && !$postuladoActual) {
                    $jornadasRenovables->push($jornada);
                }
            }
        }

        return view('home', array_merge([
            'variacion_dolar' => $ultimaTasa?->variacion,
            'tasa_actual'     => $ultimaTasa?->tasa,
            'visibleModules'  => $visibleModules,
            'saludData'       => $saludData,
        ], $psicologiaData), compact(
            'total_sedes',
            'total_categorias',
            'total_productos',
            'total_proveedores',
            'total_compras',
            'total_lotes_vencidos',
            'total_lotes_por_vencer',
            'productos_stock_minimo',
            'total_productos_stock_minimo',
            'total_envases_primarios',
            'total_bus_marcas',
            'total_bus_modelos',
            'total_bus_tipo_combustibles',
            'total_bus_vehiculos',
            'total_bus_rutas',
            'total_bus_paradas',
            'total_bus_mantenimientos',
            'total_bus_viajes',
            'total_bus_cargas',
            // ── Becas ──────────────────────────────────────────
            'total_jornadas_becas',
            'total_beneficios',
            'jornadasActivas',
            'jornadasRenovables'
        ));
    }
}
