<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registro_diario;
use App\Models\Receta;
use App\Models\Persona;
use App\Utilities\PdfGeneratorUtil;
use App\Exports\RegistroDiarioExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DetalleRegistroDiario;

class RegistroDiarioController extends Controller
{
    public function index(Request $request)
    {
        //No dejar que se vea la pagina sin un desayuno registrado
        $hayReceta = Receta::first(["id_rct"]);

        if (!$hayReceta) {
            return redirect()->back()->with('mensaje', 'No hay recetas registradas')
            ->with('icono', 'error')
            ->with('texto', 'No hay recetas registradas, Intenta agregar una receta.');
        }

        //Recibir el valor del input
        $buscar = $request->input("buscar");

        $fecha_desde = $request->input("fecha_desde");
        $fecha_hasta = $request->input("fecha_hasta");

        $filter = [
            "fecha_desde" => $fecha_desde ?? null,
            "fecha_hasta" => $fecha_hasta ?? null,
            "buscar" => $buscar ?? null
        ];



        //Enviamos si hay un desayuno registrado para ese dia
        $receta_diario = DetalleRegistroDiario::whereDate('created_at', now())->exists();

        if ($filter != null) {
            $data = Registro_diario::showData($filter);
        } else {

            $data = Registro_diario::showData();
        }

        return view('admin.movimientos.registro_diario.index', compact('data', 'receta_diario'));
    }

    public function show($id)
    {
        if (!$id) {
            return redirect()->back()->with('error', 'ID no válido');
        }

        $registro = Registro_diario::getRegister($id);
        if (!$registro) {
            return redirect()->back()->with('error', 'Registro no encontrado');
        }

        return view('admin.movimientos.registro_diario.show', compact('registro'));
    }

    //Funcion para verrificar si la persona esta en la bd
    public function verificarPersona(Request $request)
    {
        return;
    }

    public function buscar(Request $request)
    {
        return;
    }

    //Reportes
    public function exportPdf(Request $request)
    {
        // Aumentar límites
        set_time_limit(120);
        ini_set('memory_limit', '512M');

        $Hoy = date('Y-m-d');
        
        $fecha_desde = $request->input('fecha_desde', date('Y-m-d', strtotime('-1 month')));
        $fecha_hasta = $request->input('fecha_hasta', $Hoy);

        $filter = [
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
        ];

        // Obtener registros con límite
        $register = Registro_diario::showData($filter, true);
        
        // Verificar si hay demasiados registros
        if ($register->count() >= 5000) {
            return redirect()->back()
                ->with('mensaje', 'Demasiados registros')
                ->with('icono', 'warning')
                ->with('texto', 'El rango de fechas contiene más de 5000 registros. Por favor, reduce el rango de fechas.');
        }

        $datos = [
            'registros' => $register,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'registros_pnf' => Registro_diario::getAllByPnf($filter)
        ];

        return PdfGeneratorUtil::ShowPdf('pdf.registro_diario', $datos, "Registro Diario");
    }

    public function exportExcel(Request $request)
    {

        $fileName = "registro_diario";
        //Verificamos primero si resivimos algun filtro de la peticion
        if (!$request->all()) { 

            return Excel::download(new RegistroDiarioExport([]), $fileName . ".xlsx");
        }

        $filtros = [
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
            'buscar' => $request->input('buscar')
        ];

        return Excel::download(new RegistroDiarioExport($filtros), $fileName . "_" . $filtros['fecha_desde'] . "_" . $filtros['fecha_hasta'] . ".xlsx");
    }
}
