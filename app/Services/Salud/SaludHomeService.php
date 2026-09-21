<?php

namespace App\Services\Salud;

use App\Models\salud\Consulta;
use App\Models\salud\Consultorio;
use App\Models\salud\HorarioConsultorio;
use Carbon\Carbon;

class SaludHomeService
{
    /**
     * Devuelve todos los datos iniciales del panel estadístico de Salud.
     */
    public function getDashboardData(array $filtros = []): array
    {
        $fechaInicio = $filtros['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $fechaFin    = $filtros['end_date']   ?? Carbon::now()->toDateString();

        $consultas = $this->consultar($filtros, $fechaInicio, $fechaFin);
        $resumen   = $this->calcularResumen($consultas, $fechaInicio, $fechaFin, $filtros['medico_id'] ?? null);

        return [
            'fechaInicio'  => $fechaInicio,
            'fechaFin'     => $fechaFin,
            'medicoId'     => $filtros['medico_id'] ?? null,
            'consultas'    => $consultas,
            'resumen'      => $resumen,
            'consultorios' => Consultorio::where('activo', true)->orderBy('nombre')->get(),
            'medicos'      => HorarioConsultorio::usuariosElegibles(),
        ];
    }

    /**
     * Query armada con scopes del modelo.
     */
    protected function consultar(array $f, string $inicio, string $fin)
    {
        return Consulta::with([
            'paciente.perfil',
            'paciente.personaPnf.pnf',
            'medico',
            'consultorio',
            'enfermedades',
            'receta.detalles.dispensaciones',
            'receta.detalles.producto',
            'receta.detalles.unidad',
        ])
            ->delPeriodo($inicio, $fin)
            ->delMedico($f['medico_id'] ?? null)
            ->delConsultorio($f['consultorio_id'] ?? null)
            ->conEnfermedades($f['enfermedad_ids'] ?? [])
            ->conEstadoReceta($f['estado_receta'] ?? null)
            ->conPacienteFiltros($f['perfil_academico'] ?? null, $f['pnf'] ?? null)
            ->latest('fecha')
            ->get();
    }

    /**
     * Toda la lógica de cálculo
     */
    protected function calcularResumen($consultas, $fechaInicio, $fechaFin, $medicoId): array
    {
        $resumen = [
            'total_consultas'   => $consultas->count(),
            'total_pacientes'   => 0,
            'total_recetas'     => 0,
            'total_dispensados' => 0,
            'genero'            => ['masculino' => 0, 'femenino' => 0, 'otro' => 0],
            'edades'            => ['rangos' => ['0-17' => 0, '18-25' => 0, '26-35' => 0, '36-50' => 0, '51+' => 0], 'promedio' => 0, 'mediana' => 0, 'moda' => 0],
            'perfil_academico'  => ['Estudiante' => 0, 'Profesor' => 0, 'Obrero' => 0, 'Administrativo' => 0, 'Pre-escolar' => 0, 'Otros' => 0, 'No especificado' => 0],
            'pnf' => [
                'ADMINISTRACION' => 0,
                'MECANICA' => 0,
                'MANTENIMIENTO' => 0,
                'ELECTRICIDAD' => 0,
                'VETERINARIA' => 0,
                'INFORMATICA' => 0,
                'PROC. Y DIST. DE ALIMENTOS' => 0,
                'DISTRIBUCIÓN LOGÍSTICA' => 0,
                'AGROALIMENTACION' => 0,
                'SEGURIDAD ALIMENTARIA' => 0,
                'No especificado' => 0,
                'No aplica' => 0,
            ],
            'enfermedades'      => [],
            'productos_top'     => [],
            'medicos'           => [],
            'consultorios'      => [],
            'estado_recetas'    => ['Vigente' => 0, 'Anulada' => 0, 'Completa' => 0, 'Con pendientes' => 0, 'Sin receta' => 0],
            'distribucion_horas' => [],
        ];

        $pacientes = [];
        $edadesList = [];
        $horasBloques = [];
        $citasSemanales = [];
        $totalConReceta = 0;
        $recetasDispensadas = 0;

        foreach ($consultas as $c) {
            // Semana
            if ($c->fecha) {
                $semanaKey = Carbon::parse($c->fecha)->format('W-Y');
                $citasSemanales[$semanaKey] = ($citasSemanales[$semanaKey] ?? 0) + 1;
            }

            // Hora (usa created_at como aproximación)
            $horaRef = $c->fecha ? Carbon::parse($c->fecha) : null;
            if ($horaRef) {
                $bloque = $horaRef->format('h:00 A');
                $horasBloques[$bloque] = ($horasBloques[$bloque] ?? 0) + 1;
            }

            // Enfermedades
            foreach ($c->enfermedades as $enf) {
                $nombre = $enf->nombre ?? 'Sin nombre';
                $resumen['enfermedades'][$nombre] = ($resumen['enfermedades'][$nombre] ?? 0) + 1;
            }

            // Médico
            $nombreMedico = trim(($c->medico->nombre_persona ?? '') . ' ' . ($c->medico->apellido_persona ?? '')) ?: 'No asignado';
            $resumen['medicos'][$nombreMedico] = ($resumen['medicos'][$nombreMedico] ?? 0) + 1;

            // Consultorio
            $nombreCons = $c->consultorio->nombre ?? 'Sin consultorio';
            $resumen['consultorios'][$nombreCons] = ($resumen['consultorios'][$nombreCons] ?? 0) + 1;

            // Receta y dispensaciones
            if ($c->receta) {
                $resumen['total_recetas']++;
                $totalConReceta++;

                $estaCompleta = true;
                foreach ($c->receta->detalles as $det) {
                    $disp = (float) $det->dispensaciones->sum('cantidad');
                    $resumen['total_dispensados'] += $disp;

                    // Producto top (por cantidad total dispensada)
                    $prodNombre = $det->producto->nombre ?? 'Producto #' . $det->producto_id;
                    $resumen['productos_top'][$prodNombre] = ($resumen['productos_top'][$prodNombre] ?? 0) + $disp;

                    if ($disp < (float) $det->cantidad) $estaCompleta = false;
                }

                if ($c->receta->estado == 0) {
                    $resumen['estado_recetas']['Anulada']++;
                } elseif ($estaCompleta && count($c->receta->detalles) > 0) {
                    $resumen['estado_recetas']['Completa']++;
                    $recetasDispensadas++;
                } else {
                    $resumen['estado_recetas']['Con pendientes']++;
                }
            } else {
                $resumen['estado_recetas']['Sin receta']++;
            }

            // Datos únicos del paciente
            $paciente = $c->paciente;
            if ($paciente && !isset($pacientes[$paciente->id_persona])) {
                $pacientes[$paciente->id_persona] = true;
                $resumen['total_pacientes']++;

                // Género
                $genero = strtolower(trim($paciente->genero_persona ?? ''));
                if (in_array($genero, ['masculino', 'hombre', 'm'])) $resumen['genero']['masculino']++;
                elseif (in_array($genero, ['femenino', 'mujer', 'f'])) $resumen['genero']['femenino']++;
                else $resumen['genero']['otro']++;

                // Edad
                if ($paciente->fecha_nacimiento_persona) {
                    $edad = Carbon::parse($paciente->fecha_nacimiento_persona)->age;
                    $edadesList[] = $edad;
                    if ($edad <= 17) $resumen['edades']['rangos']['0-17']++;
                    elseif ($edad <= 25) $resumen['edades']['rangos']['18-25']++;
                    elseif ($edad <= 35) $resumen['edades']['rangos']['26-35']++;
                    elseif ($edad <= 50) $resumen['edades']['rangos']['36-50']++;
                    else $resumen['edades']['rangos']['51+']++;
                }

                // Perfil académico
                $perfil = $paciente->perfil->nombre_perfil ?? 'No especificado';
                if (!array_key_exists($perfil, $resumen['perfil_academico'])) $perfil = 'No especificado';
                $resumen['perfil_academico'][$perfil]++;

                // PNF
                $pnfVal = 'No especificado';
                $personaPnf = $paciente->personaPnf;
                if ($personaPnf instanceof \Illuminate\Support\Collection) $personaPnf = $personaPnf->first();
                if ($personaPnf && $personaPnf->pnf) {
                    $pnfVal = $personaPnf->pnf->nombre_pnf ?? 'No especificado';
                }
                if ($pnfVal === 'Agroalimentaria') $pnfVal = 'AGROALIMENTACION';
                if ($pnfVal === 'Electrica') $pnfVal = 'ELECTRICIDAD';
                if (!array_key_exists($pnfVal, $resumen['pnf'])) {
                    $pnfVal = ($perfil === 'Estudiante') ? 'No especificado' : 'No aplica';
                }
                $resumen['pnf'][$pnfVal] = ($resumen['pnf'][$pnfVal] ?? 0) + 1;
            }
        }

        // Estadísticas de edades
        if (count($edadesList) > 0) {
            $resumen['edades']['promedio'] = round(array_sum($edadesList) / count($edadesList), 1);
            $sorted = $edadesList;
            sort($sorted);
            $n = count($sorted);
            $mid = intdiv($n - 1, 2);
            $resumen['edades']['mediana'] = ($n % 2 === 0) ? ($sorted[$mid] + $sorted[$mid + 1]) / 2 : $sorted[$mid];
            $counts = array_count_values($edadesList);
            arsort($counts);
            $resumen['edades']['moda'] = array_key_first($counts);
        }

        // Hora pico
        arsort($horasBloques);
        $resumen['hora_pico'] = !empty($horasBloques) ? array_key_first($horasBloques) : 'N/A';
        $resumen['distribucion_horas'] = $horasBloques;

        // Promedio semanal
        $semanas = 1;
        if ($fechaInicio && $fechaFin) {
            $semanas = max(1, Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin))) / 7;
        }
        $resumen['promedio_semanal'] = round($resumen['total_consultas'] / max(0.1, $semanas), 1);

        // Tasas
        $resumen['tasa_con_receta'] = $resumen['total_consultas'] > 0
            ? round(($totalConReceta / $resumen['total_consultas']) * 100, 1) : 0;
        $resumen['tasa_dispensada'] = $totalConReceta > 0
            ? round(($recetasDispensadas / $totalConReceta) * 100, 1) : 0;

        // Flujo semanal ordenado
        ksort($citasSemanales);
        $resumen['flujo_semanal'] = $citasSemanales;

        // Ordenar top
        arsort($resumen['enfermedades']);
        arsort($resumen['productos_top']);
        arsort($resumen['medicos']);
        arsort($resumen['consultorios']);

        // Comparativa vs período anterior
        $resumen['comparativa_consultas'] = 0;
        if ($fechaInicio && $fechaFin) {
            $inicio = Carbon::parse($fechaInicio);
            $fin    = Carbon::parse($fechaFin);
            $dias   = $inicio->diffInDays($fin);
            $prevFin = $inicio->copy()->subDay();
            $prevInicio = $prevFin->copy()->subDays($dias);

            $prev = Consulta::query()
                ->delPeriodo($prevInicio->toDateString(), $prevFin->toDateString())
                ->delMedico($medicoId)
                ->count();

            $resumen['comparativa_consultas'] = 0;
            if ($prev > 0) {
                $resumen['comparativa_consultas'] = round((($resumen['total_consultas'] - $prev) / $prev) * 100, 1);
            } elseif ($resumen['total_consultas'] > 0) {
                $resumen['comparativa_consultas'] = 100;
            }
        }

        return $resumen;
    }
}
