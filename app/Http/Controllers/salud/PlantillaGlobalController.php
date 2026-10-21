<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\salud\PlantillaGlobal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class PlantillaGlobalController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $plantilla = PlantillaGlobal::obtenerPorPsicologo($userId);

        if (!$plantilla) {
            // 1. Intentar ejecutar el seeder
            Artisan::call('db:seed', [
                '--class' => 'PlantillaGlobalSeeder',
                '--force' => true
            ]);

            $plantilla = PlantillaGlobal::obtenerPorPsicologo($userId);

            // 2. Si aun sigue siendo null, creamos la plantilla por defecto para el usuario actual
            if (!$plantilla && $userId) {
                $seccionesPredefinidas = [
                    [
                        'titulo' => 'Antecedentes Personales',
                        'descripcion_general' => 'En el ámbito de salud general',
                        'segmentos' => ['Salud Mental', 'Salud General']
                    ],
                    [
                        'titulo' => 'Antecedentes Familiares',
                        'descripcion_general' => 'Record de salud desde el lado familiar Paterno',
                        'segmentos' => ['Salud Mental', 'Salud General']
                    ],
                    [
                        'titulo' => 'Antecedentes',
                        'descripcion_general' => 'Record de salud desde el lado familiar Materno',
                        'segmentos' => ['Salud Mental', 'Salud General']
                    ],
                    [
                        'titulo' => 'Diagnostico General',
                        'descripcion_general' => 'Este abarcará todo momento con el paciente',
                        'segmentos' => ['Observaciones y Diagnosticos', 'Plan de Acción para la recuperación']
                    ]
                ];

                DB::table('plantilla_historia_clinica')->insert([
                    'psicologo_use_id' => $userId,
                    'titulo_phc' => 'Expediente General de Pacientes',
                    'descripcion_phc' => 'Especificaciones del record de salud del paciente',
                    'secciones_phc' => json_encode($seccionesPredefinidas),
                    'estado_phc' => PlantillaGlobal::ESTADO_PREDETERMINADA,
                    'created_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $plantilla = PlantillaGlobal::obtenerPorPsicologo($userId);
            }
        }

        return view('admin.psicologia.maestros.plantillas_globales.index', compact('plantilla'));
    }

    public function update(Request $request)
    {
        $plantilla = PlantillaGlobal::obtenerPorPsicologo(Auth::id());

        if (!$plantilla) {
            abort(404);
        }

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'secciones_estructura' => 'required|array|min:1',
            'secciones_estructura.*.titulo' => 'required|string|max:255',
            'secciones_estructura.*.descripcion_general' => 'nullable|string|max:255',
            'secciones_estructura.*.segmentos' => 'required|array|min:1',
            'secciones_estructura.*.segmentos.*' => 'required|string|max:255',
        ]);

        PlantillaGlobal::actualizar(Auth::id(), $data);

        $mensaje = 'Esquema general guardado y activado exitosamente.';
        
        if ($request->input('aplicar_a_todos') == '1') {
            $resultado = PlantillaGlobal::aplicarATodos(Auth::id());
            if ($resultado['success']) {
                $mensaje .= ' ' . $resultado['message'];
            } else {
                $mensaje .= ' Sin embargo, hubo un error al aplicar a pacientes: ' . $resultado['message'];
            }
        }

        return redirect()->route('admin.psicologia.maestros.plantillas_globales.index')
            ->with('success', $mensaje);
    }

    public function apply()
    {
        $plantilla = PlantillaGlobal::obtenerPorPsicologo(Auth::id());

        if (!$plantilla || (int) $plantilla->estado_phc !== PlantillaGlobal::ESTADO_ACTIVA) {
            return redirect()->route('admin.psicologia.maestros.plantillas_globales.index')
                ->with('error', 'Debe activar el esquema general antes de aplicarlo a todos los pacientes.');
        }

        $resultado = PlantillaGlobal::aplicarATodos(Auth::id());

        if ($resultado['success']) {
            return redirect()->route('admin.psicologia.maestros.plantillas_globales.index')
                ->with('success', $resultado['message']);
        }

        return redirect()->route('admin.psicologia.maestros.plantillas_globales.index')
            ->with('error', $resultado['message']);
    }
}