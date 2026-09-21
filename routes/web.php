<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AdminMasterKeyController;
use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\Admin\Configuracion\EmpleosController;
use App\Http\Controllers\Admin\Configuracion\PermisosController;
use App\Http\Controllers\Admin\Configuracion\RolesController;
use App\Http\Controllers\RegistroDiarioController;
use App\Http\Controllers\DetalleRegistroDiarioController;
use App\Http\Controllers\PnfController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\LocalidadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\InventarioSedeLoteController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaIngredienteController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\salud\ConsultaController;
use App\Http\Controllers\salud\CategoriaMedicamentoController;
use App\Http\Controllers\salud\EnvasePrimarioController;
use App\Http\Controllers\salud\MedicamentoController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\salud\ConsultorioController;
use App\Http\Controllers\salud\EnfermedadController;
use App\Http\Controllers\salud\HorarioConsultorioController;
use App\Http\Controllers\salud\NotificationController;

Auth::routes();

// Landing page personalizada para Bienestar Estudiantil UPTP
use App\Models\Usuario;

Route::get('/', function () {
    $hasEmployees = Usuario::count() > 0;
    return view('landing_uptp', compact('hasEmployees'));
});

Route::middleware(['auth'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // TODAS tus rutas protegidas aquí
    Route::prefix('/admin')->middleware(\App\Http\Middleware\CheckMenuPermission::class)->group(function () {

        Route::get('/notificaciones/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notificaciones/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

        /* Categorias */

        //Index 
        Route::get('/maestros/categorias', [CategoriaController::class, 'index'])->name('admin.maestros.categorias.index');

        //Crear una nueva categoría
        Route::get('/maestros/categorias/create', [CategoriaController::class, 'create'])->name('admin.maestros.categorias.create');

        //Almacenar una nueva categoría
        Route::post('/maestros/categorias/store', [CategoriaController::class, 'store'])->name('admin.maestros.categorias.store');

        //Mostrar una categoría específica
        Route::get('/maestros/categorias/{categoria}', [CategoriaController::class, 'show'])->name('admin.maestros.categorias.show');

        //Editar una categoría específica
        Route::get('/maestros/categorias/{categoria}/edit', [CategoriaController::class, 'edit'])->name('admin.maestros.categorias.edit');

        //Actualizar una categoría específica
        Route::put('/maestros/categorias/{categoria}', [CategoriaController::class, 'update'])->name('admin.maestros.categorias.update');

        //Eliminar una categoria especifica
        Route::delete('/maestros/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('admin.maestros.categorias.destroy');

        //Activar una categoria especifica
        Route::put('/maestros/categorias/{categoria}/activar', [CategoriaController::class, 'activar'])->name('admin.maestros.productos.activar');

        /* Sedes */
        Route::get('/maestros/sedes', [SedeController::class, 'index'])->name('admin.maestros.sedes.index');
        Route::get('/maestros/sedes/create', [SedeController::class, 'create'])->name('admin.maestros.sedes.create');
        Route::post('/maestros/sedes/store', [SedeController::class, 'store'])->name('admin.maestros.sedes.store');
        Route::get('/maestros/sedes/{sede}', [SedeController::class, 'show'])->name('admin.maestros.sedes.show');
        Route::get('/maestros/sedes/{sede}/edit', [SedeController::class, 'edit'])->name('admin.maestros.sedes.edit');
        Route::put('/maestros/sedes/{sede}', [SedeController::class, 'update'])->name('admin.maestros.sedes.update');
        Route::delete('/maestros/sedes/{id}', [SedeController::class, 'destroy'])->name('admin.maestros.sedes.destroy');
        Route::put('/maestros/sedes/{sede}/activar', [SedeController::class, 'activar'])->name('admin.maestros.sedes.activar');

        /* Productos */

        Route::get('/maestros/productos', [ProductoController::class, 'index'])->name('admin.maestros.productos.index');

        Route::get('/maestros/productos/create', [ProductoController::class, 'create'])->name('admin.maestros.productos.create');

        Route::post('/maestros/productos/store', [ProductoController::class, 'store'])->name('admin.maestros.productos.store');

        Route::get('/maestros/productos/{producto}', [ProductoController::class, 'show'])->name('admin.maestros.productos.show');

        Route::get('/maestros/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('admin.maestros.productos.edit');

        Route::put('/maestros/productos/{producto}', [ProductoController::class, 'update'])->name('admin.maestros.productos.update');

        Route::delete('/maestros/productos/{producto}', [ProductoController::class, 'destroy'])->name('admin.maestros.productos.destroy');

        Route::put('/maestros/productos/{producto}/activar', [ProductoController::class, 'activar'])->name('admin.maestros.productos.activar');

        /* Proveedores */

        Route::get('/maestros/proveedores', [ProveedorController::class, 'index'])->name('admin.maestros.proveedores.index');

        Route::get('/maestros/proveedores/create', [ProveedorController::class, 'create'])->name('admin.maestros.proveedores.create');

        Route::post('/maestros/proveedores/store', [ProveedorController::class, 'store'])->name('admin.maestros.proveedores.store');

        Route::get('/maestros/proveedores/{proveedor}', [ProveedorController::class, 'show'])->name('admin.maestros.proveedores.show');

        Route::get('/maestros/proveedores/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('admin.maestros.proveedores.edit');

        Route::put('/maestros/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('admin.maestros.proveedores.update');

        Route::delete('/maestros/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('admin.maestros.proveedores.destroy');

        Route::put('/maestros/proveedores/{proveedor}/activar', [ProveedorController::class, 'activar'])->name('admin.maestros.proveedores.activar');

        /* Requisicion de Compra */

        Route::get('/movimientos/compras', [CompraController::class, 'index'])->name('admin.movimientos.compras.index');

        Route::get('/movimientos/compras/create', [CompraController::class, 'create'])->name('admin.movimientos.compras.create');

        Route::post('/movimientos/compras/store', [CompraController::class, 'store'])->name('admin.movimientos.compras.store');

        Route::get('/movimientos/compras/{id}', [CompraController::class, 'show'])->name('admin.movimientos.compras.show');

        Route::get('/movimientos/compras/{id}/edit', [CompraController::class, 'edit'])->name('admin.movimientos.compras.edit');

        Route::get('/movimientos/compras/{compra}/enviar-correo', [CompraController::class, 'enviarCorreo'])->name('admin.movimientos.compras.enviarCorreo');

        Route::post('/movimientos/compras/{compra}/finalizar-compra', [CompraController::class, 'finalizarCompra'])->name('admin.movimientos.compras.finalizarCompra');

        Route::delete('/movimientos/compras/{id}', [CompraController::class, 'destroy'])->name('admin.movimientos.compras.destroy');

        Route::get('/movimientos/compras/e/export-pdf', [CompraController::class, 'exportPdf'])->name('admin.movimientos.compras.export_pdf');

        Route::post('admin/movimientos/compras/{compra}/cancelar', [CompraController::class, 'cancelar'])->name('admin.movimientos.compras.cancelar');

        /* Lotes */

        Route::get('/movimientos/lotes', [LoteController::class, 'index'])->name('admin.movimientos.lotes.index');

        Route::get('/movimientos/lotes/create', [LoteController::class, 'create'])->name('admin.movimientos.lotes.create');

        Route::post('/movimientos/lotes/store', [LoteController::class, 'store'])->name('admin.movimientos.lotes.store');

        Route::get('/movimientos/lotes/{id}', [LoteController::class, 'show'])->name('admin.movimientos.lotes.show');

        Route::get('/movimientos/lotes/{id}/edit', [LoteController::class, 'edit'])->name('admin.movimientos.lotes.edit');

        Route::put('/movimientos/lotes/{id}', [LoteController::class, 'update'])->name('admin.movimientos.lotes.update');

        Route::delete('/movimientos/lotes/{id}', [LoteController::class, 'destroy'])->name('admin.movimientos.lotes.destroy');

        Route::post('/movimientos/lotes/mermar-vencidos', [LoteController::class, 'mermarVencidos'])->name('admin.movimientos.lotes.mermar');

        /* Sedes por lotes */
        Route::get('/movimientos/sedes_lotes', [InventarioSedeLoteController::class, 'index'])->name('admin.movimientos.sedes_lotes');

        Route::get('/movimientos/sedes_lotes/show/{id}', [InventarioSedeLoteController::class, 'show'])->name('admin.movimientos.sedes_lotes.show');

        /* Registro diario */

        Route::get('/movimientos/registro_diario', [RegistroDiarioController::class, 'index'])->name('admin.movimientos.registro_diario.index');

        Route::get('/movimientos/registro_diario/registro/{id}', [RegistroDiarioController::class, 'show'])->name('admin.movimientos.registro_diario.show');

        /* Recetas */

        Route::get('/maestros/recetas', [RecetaController::class, 'index'])->name('admin.maestros.recetas.index');

        Route::get('/maestros/recetas/create', [RecetaController::class, 'create'])->name('admin.maestros.recetas.create');

        Route::post('/maestros/recetas/store', [RecetaController::class, 'store'])->name('admin.maestros.recetas.store');

        Route::get('/maestros/recetas/{receta}/edit', [RecetaController::class, 'edit'])->name('admin.maestros.recetas.edit');

        Route::put('/maestros/recetas/{receta}', [RecetaController::class, 'update'])->name('admin.maestros.recetas.update');

        Route::delete('/maestros/recetas/{receta}', [RecetaController::class, 'destroy'])->name('admin.maestros.recetas.destroy');

        Route::put('/maestros/recetas/{receta}/activar', [RecetaController::class, 'activar'])->name('admin.maestros.recetas.activar');

        /* Receta Ingredientes */

        Route::get('/maestros/receta_ingredientes', [RecetaIngredienteController::class, 'index'])->name('admin.maestros.receta_ingredientes.index');

        Route::get('/maestros/receta_ingredientes/create', [RecetaIngredienteController::class, 'create'])->name('admin.maestros.receta_ingredientes.create');

        Route::post('/maestros/receta_ingredientes/store', [RecetaIngredienteController::class, 'store'])->name('admin.maestros.receta_ingredientes.store');

        Route::get('/maestros/receta_ingredientes/{id}/edit', [RecetaIngredienteController::class, 'edit'])->name('admin.maestros.receta_ingredientes.edit');

        Route::put('/maestros/receta_ingredientes/receta/{id}', [RecetaIngredienteController::class, 'update'])->name('admin.maestros.receta_ingredientes.update');

        Route::delete('/maestros/receta_ingredientes/{id}', [RecetaIngredienteController::class, 'destroy'])->name('admin.maestros.receta_ingredientes.destroy');

        Route::put('/maestros/receta_ingredientes/{id}/activar', [RecetaIngredienteController::class, 'activar'])->name('admin.maestros.receta_ingredientes.activar');

        /* Historial de Movimientos */

        Route::get('/movimientos/historial_movimientos', [MovimientoInventarioController::class, 'index'])->name('admin.movimientos.historial_movimientos.index');

        Route::get('/movimientos/historial_movimientos/export-pdf', [MovimientoInventarioController::class, 'generarPdf'])->name('admin.movimientos.historial_movimientos.export_pdf');

        /* Registro Diario */

        Route::get('/movimientos/registro_diario', [RegistroDiarioController::class, 'index'])->name('admin.movimientos.registro_diario.index');

        Route::get('/movimientos/registro_diario/export-pdf', [RegistroDiarioController::class, 'exportPdf'])->name('admin.movimientos.registro_diario.export_pdf');

        Route::get('/movimientos/registro_diario/export-excel', [RegistroDiarioController::class, 'exportExcel'])->name('admin.movimientos.registro_diario.export_excel');

        /* Registro de Comida */
        Route::get('/movimientos/registro_comida', [DetalleRegistroDiarioController::class, 'index'])->name('admin.movimientos.registro_comida.index');

        /* PNF */

        Route::get('/maestros/pnf', [PnfController::class, 'index'])->name('admin.maestros.pnf.index');


        Route::post('/maestros/pnf/store', [PnfController::class, 'store'])->name('admin.maestros.pnf.store');

        Route::get('/maestros/pnf/edit/{id}', [PnfController::class, 'edit'])->name('admin.maestros.pnf.edit');

        Route::put('/maestros/pnf/update', [PnfController::class, 'update'])->name('admin.maestros.pnf.update');

        Route::delete('/maestros/pnf/destroy/{id}', [PnfController::class, 'destroy'])->name('admin.maestros.pnf.destroy');

        Route::put('/maestros/pnf/activar/{id}', [PnfController::class, 'activar'])->name('admin.maestros.pnf.activar');

        /* Persona */

        Route::get('/persona', [PersonaController::class, 'index'])->name('admin.configuracion.persona.index');

        Route::get('/persona/create', [PersonaController::class, 'create'])->name('admin.configuracion.persona.create');

        Route::get('/persona/edit/{id}', [PersonaController::class, 'edit'])->name('admin.configuracion.persona.edit');

        Route::get('/persona/show/{id}', [PersonaController::class, 'show'])->name('admin.configuracion.persona.show');

        //Rutas para Consultas
        Route::get('consultas/reportes', [ReporteController::class, 'index'])->name('admin.consultas.reportes.index');

        //Rutas para Configuracion
        Route::get('configuracion', [AdminController::class, 'index'])->name('admin.configuracion.index');


        /* Rutas de direcciones */

        Route::get('/estado', function () {
            return view('admin.estado.index');
        })->name('admin.estado.index');

        Route::get('estado/verificar', [EstadoController::class, 'verificarExistencia'])->name('estado.verificar');

        Route::get('/municipio', function () {
            return view('admin.municipio.index');
        })->name('admin.municipio.index');

        Route::get('municipio/verificar', [MunicipioController::class, 'verificarExistencia'])->name('municipio.verificar');

        Route::get('/localidad', function () {
            return view('admin.localidad.index');
        })->name('admin.localidad.index');

        Route::get('localidad/verificar', [LocalidadController::class, 'verificarExistencia'])->name('localidad.verificar');

        Route::get('/configuracion/archivos', [ArchivoController::class, 'index'])->name('admin.configuracion.archivos.index');
        Route::get('/configuracion/archivos/ver/{archivo}', function ($archivo) {
            $path = storage_path('app/public/' . $archivo);

            abort_unless(file_exists($path), 404);

            return response()->file($path, [
                'Content-Disposition' => 'inline'
            ]);
        })->where('archivo', '.*');

        // SALUD        

        // Envases Primarios
        Route::get('/salud/maestros/envases_primarios', [EnvasePrimarioController::class, 'index'])->name('admin.salud.maestros.envases_primarios.index');
        Route::post('/salud/maestros/envases_primarios/store', [EnvasePrimarioController::class, 'store'])->name('admin.salud.maestros.envases_primarios.store');
        Route::put('/salud/maestros/envases_primarios/{envase}', [EnvasePrimarioController::class, 'update'])->name('admin.salud.maestros.envases_primarios.update');
        Route::delete('/salud/maestros/envases_primarios/{envase}', [EnvasePrimarioController::class, 'destroy'])->name('admin.salud.maestros.envases_primarios.destroy');
        Route::put('/salud/maestros/envases_primarios/{envase}/activar', [EnvasePrimarioController::class, 'activar'])->name('admin.salud.maestros.envases_primarios.activar');

        //Categorias de medicamentos
        Route::get('/salud/maestros/categorias', [CategoriaMedicamentoController::class, 'index'])->name('admin.salud.maestros.categorias.index');
        Route::post('/salud/maestros/categorias/store', [CategoriaMedicamentoController::class, 'store'])->name('admin.salud.maestros.categorias.store');
        Route::put('/salud/maestros/categorias/{categoria}', [CategoriaMedicamentoController::class, 'update'])->name('admin.salud.maestros.categorias.update');
        Route::delete('/salud/maestros/categorias/{categoria}', [CategoriaMedicamentoController::class, 'destroy'])->name('admin.salud.maestros.categorias.destroy');
        Route::put('/salud/maestros/categorias/{categoria}/activar', [CategoriaMedicamentoController::class, 'activar'])->name('admin.salud.maestros.categorias.activar');

        //Medicamentos
        Route::get('/salud/maestros/medicamentos', [MedicamentoController::class, 'index'])->name('admin.salud.maestros.medicamentos.index');
        Route::get('/salud/maestros/medicamentos/create', [MedicamentoController::class, 'create'])->name('admin.salud.maestros.medicamentos.create');
        Route::post('/salud/maestros/medicamentos/store', [MedicamentoController::class, 'store'])->name('admin.salud.maestros.medicamentos.store');
        Route::get('/salud/maestros/medicamentos/{medicamento}/edit', [MedicamentoController::class, 'edit'])->name('admin.salud.maestros.medicamentos.edit');
        Route::get('/salud/maestros/medicamentos/{medicamento}', [MedicamentoController::class, 'show'])->name('admin.salud.maestros.medicamentos.show');
        Route::put('/salud/maestros/medicamentos/{medicamento}', [MedicamentoController::class, 'update'])->name('admin.salud.maestros.medicamentos.update');
        Route::delete('/salud/maestros/medicamentos/{medicamento}', [MedicamentoController::class, 'destroy'])->name('admin.salud.maestros.medicamentos.destroy');
        Route::put('/salud/maestros/medicamentos/{medicamento}/activar', [MedicamentoController::class, 'activar'])->name('admin.salud.maestros.medicamentos.activar');

        //Consultorios
        Route::get('/salud/maestros/consultorios', [ConsultorioController::class, 'index'])->name('admin.salud.maestros.consultorios.index');
        Route::post('/salud/maestros/consultorios/store', [ConsultorioController::class, 'store'])->name('admin.salud.maestros.consultorios.store');
        Route::get('/salud/maestros/consultorios/{consultorio}', [ConsultorioController::class, 'show'])->name('admin.salud.maestros.consultorios.show');
        Route::put('/salud/maestros/consultorios/{consultorio}', [ConsultorioController::class, 'update'])->name('admin.salud.maestros.consultorios.update');
        Route::delete('/salud/maestros/consultorios/{consultorio}', [ConsultorioController::class, 'destroy'])->name('admin.salud.maestros.consultorios.destroy');
        Route::put('/salud/maestros/consultorios/{consultorio}/activar', [ConsultorioController::class, 'activar'])->name('admin.salud.maestros.consultorios.activar');


        //Horarios
        Route::get('/salud/movimientos/horarios', [HorarioConsultorioController::class, 'index'])->name('admin.salud.movimientos.horarios.index');
        Route::get('/salud/movimientos/horarios/create', [HorarioConsultorioController::class, 'create'])->name('admin.salud.movimientos.horarios.create');
        Route::post('/salud/movimientos/horarios', [HorarioConsultorioController::class, 'store'])->name('admin.salud.movimientos.horarios.store');
        Route::delete('/salud/movimientos/horarios/{horario}', [HorarioConsultorioController::class, 'destroy'])->name('admin.salud.movimientos.horarios.destroy');
        Route::get('/salud/movimientos/horarios/pdf', [HorarioConsultorioController::class, 'exportarPdf'])->name('admin.salud.movimientos.horarios.pdf');

        // Consultas
        Route::prefix('/salud/movimientos/consultas')
            ->name('admin.salud.movimientos.consultas.')
            ->middleware(['auth'])
            ->group(function () {
                Route::get('/', [ConsultaController::class, 'index'])->name('index');

                Route::get('estadisticas', [ConsultaController::class, 'estadisticas'])->name('estadisticas');
                Route::get('/buscar-enfermedades', [ConsultaController::class, 'buscarEnfermedades'])->name('buscar-enfermedades');
                Route::get('/buscar-personas', [ConsultaController::class, 'buscarPersonas'])->name('buscar-personas');

                // Paso 1: Crear / Editar Consulta
                Route::get('/crear/{consulta?}', [ConsultaController::class, 'create'])->name('create');
                Route::post('/', [ConsultaController::class, 'store'])->name('store');
                Route::put('/{consulta}', [ConsultaController::class, 'update'])->name('update');

                // Paso 2: Recetación
                Route::get('/{consulta}/recetacion', [ConsultaController::class, 'recetacion'])->name('recetacion');
                Route::post('/{consulta}/receta', [ConsultaController::class, 'storeReceta'])->name('receta.store');

                // Paso 3: Dispensación
                Route::get('/{consulta}/dispensacion', [ConsultaController::class, 'dispensacion'])->name('dispensacion');
                Route::post('/{consulta}/dispensacion', [ConsultaController::class, 'storeDispensacion'])->name('dispensacion.store');

                // Generar Recipe
                Route::get('/{consulta}/recipe', [ConsultaController::class, 'generarRecipePdf'])->name('recipe_pdf');

                // Ver detalle
                Route::get('/{consulta}', [ConsultaController::class, 'show'])->name('show');
            });

        // TRANSPORTE
        require __DIR__ . '/transporte.php';

        // PSICOLOGIA
        require __DIR__ . '/psicologia.php';

        // BECA
        require __DIR__ . '/beca.php';


        Route::prefix('configuracion')
            ->middleware(\App\Http\Middleware\CheckMenuPermission::class)
            ->group(function () {
                Route::get('/empleados', [EmpleosController::class, 'index'])->name('admin.configuracion.empleados.index');
                Route::get('/empleados/{id}/edit', [EmpleosController::class, 'edit'])->name('admin.configuracion.empleados.edit');
                Route::get('/empleados/{id}', [EmpleosController::class, 'show'])->name('admin.configuracion.empleados.show');
                Route::put('/empleados/{id}', [EmpleosController::class, 'update'])->name('admin.configuracion.empleados.update');
                Route::delete('/empleados/{id}', [EmpleosController::class, 'destroy'])->name('admin.configuracion.empleados.destroy');

                Route::get('/permisos', [PermisosController::class, 'index'])->name('admin.configuracion.permisos.index');
                Route::get('/permisos/{id}/edit', [PermisosController::class, 'edit'])->name('admin.configuracion.permisos.edit');
                Route::put('/permisos/{id}', [PermisosController::class, 'update'])->name('admin.configuracion.permisos.update');

                Route::get('/roles', [RolesController::class, 'index'])->name('admin.configuracion.roles.index');
                Route::get('/roles/create', [RolesController::class, 'create'])->name('admin.configuracion.roles.create');
                Route::post('/roles', [RolesController::class, 'store'])->name('admin.configuracion.roles.store');
                Route::get('/roles/{id}/edit', [RolesController::class, 'edit'])->name('admin.configuracion.roles.edit');
                Route::put('/roles/{id}', [RolesController::class, 'update'])->name('admin.configuracion.roles.update');
                Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('admin.configuracion.roles.destroy');

                Route::get('/master-key', [EmpleosController::class, 'masterKeyForm'])->name('admin.configuracion.master_key.form');
                Route::post('/master-key/verify', [EmpleosController::class, 'verifyMasterKey'])->name('admin.configuracion.master_key.verify');
            });
    });
});

Route::post('/tasa/ignorar-hoy', function (\Illuminate\Http\Request $request) {
    session()->put('tasa_ignorada_hasta', now()->toDateString());
    return response()->json(['ok' => true]);
})->middleware('auth')->name('tasa.ignorar');
Route::post('/admin/maestros/productos/actualizar-tasa', [ProductoController::class, 'actualizarTasaDolar'])->name('productos.actualizar.tasa');

Route::prefix('/admin')->group(function () {

    Route::get('modulos/seleccionar', [ModuloController::class, 'seleccionarForm'])
        ->middleware(['auth'])
        ->name('admin.modulos.seleccionar');

    Route::post('modulos/cambiar', [ModuloController::class, 'cambiar'])
        ->middleware(['auth'])
        ->name('admin.modulos.cambiar');
});

Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

Route::middleware('auth')->group(function () {
    Route::get('security-questions', [App\Http\Controllers\Auth\RegisterController::class, 'showSecurityQuestionsForm'])->name('security.questions');
    Route::post('security-questions', [App\Http\Controllers\Auth\RegisterController::class, 'saveSecurityQuestions'])->name('security.questions.save');
});

Route::get('admin/master-key', [AdminMasterKeyController::class, 'showForm'])->name('admin.master_key.form');
Route::post('admin/master-key/verify', [AdminMasterKeyController::class, 'verify'])->name('admin.master_key.verify');

Route::get('admin/master-key/manage', [App\Http\Controllers\Admin\MasterKeyController::class, 'showForm'])->name('admin.master_key.manage')->middleware('auth');
Route::post('admin/master-key/manage', [App\Http\Controllers\Admin\MasterKeyController::class, 'update'])->name('admin.master_key.update')->middleware('auth');

Route::get('password/recover', [PasswordRecoveryController::class, 'showEmailForm'])->name('password.recover.email');
Route::post('password/recover', [PasswordRecoveryController::class, 'postEmail'])->name('password.recover.post_email');
Route::get('password/recover/verify', [PasswordRecoveryController::class, 'showVerifyForm'])->name('password.recover.verify.form');
Route::post('password/recover/verify', [PasswordRecoveryController::class, 'verifyAnswers'])->name('password.recover.verify');
Route::post('password/recover/reset-password', [PasswordRecoveryController::class, 'resetPassword'])->name('password.recover.reset_password');
Route::post('password/recover/reset-masterkey', [PasswordRecoveryController::class, 'resetMasterKey'])->name('password.recover.reset_masterkey');

Route::middleware(['auth'])->group(function () {
    Route::post('/broadcasting/auth', function () {
        return \Illuminate\Support\Facades\Broadcast::auth(request());
    });

    Route::get('/mensajes', [\App\Http\Controllers\salud\ChatController::class, 'index'])->name('chat.index');
    Route::get('/mensajes/contactos/lista', [\App\Http\Controllers\salud\ChatController::class, 'fetchContacts'])->name('chat.contacts');
    Route::post('/mensajes/ping', [\App\Http\Controllers\salud\ChatController::class, 'ping'])->name('chat.ping');
    Route::get('/mensajes/{user}', [\App\Http\Controllers\salud\ChatController::class, 'fetchMessages'])->name('chat.fetch');
    Route::post('/mensajes/{user}', [\App\Http\Controllers\salud\ChatController::class, 'sendMessage'])->name('chat.store');
});
