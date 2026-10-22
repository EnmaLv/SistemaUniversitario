<?php

namespace App\Services\Salud;

use App\Models\salud\Consulta;
use App\Models\salud\Consultorio;
use App\Models\salud\HorarioConsultorio;
use App\Models\salud\RecetasMedica;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaludHomeService
{
    public const RANGOS_EDAD = [
        'Menos de 18' => [0, 17],
        '18 a 24'     => [18, 24],
        '25 a 34'     => [25, 34],
        '35 a 44'     => [35, 44],
        '45 a 59'     => [45, 59],
        '60 o más'    => [60, null],
    ];

    public const SIN_FECHA_NACIMIENTO = 'Sin fecha de nacimiento';
    public const SIN_PNF = 'Sin PNF';
    public const SIN_TIPO_PERSONA = 'Sin tipo de persona';

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
            'consultorios' => Consultorio::where('status_id', 1)->orderBy('nombre_csr')->get(),
            'medicos'      => HorarioConsultorio::usuariosElegibles(),
            'tiposPersona' => DB::table('perfil')->whereNull('deleted_at')->where('status_id', 1)->orderBy('nombre_perfil')->pluck('nombre_perfil'),
            'programas'    => DB::table('programa')->whereNull('deleted_at')->orderBy('nombre_prg')->pluck('nombre_prg'),
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
            ->latest('consulta_medica.fecha_cme')
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
            'edades'            => ['promedio' => 0, 'mediana' => 0, 'moda' => 0],
            'edad_consulta'     => array_fill_keys(array_merge(array_keys(self::RANGOS_EDAD), [self::SIN_FECHA_NACIMIENTO]), 0),
            'pnf_consulta'      => [],
            'tipo_persona_consulta' => [],
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
            $this->acumularDemografia($resumen, $c, $edadesList);

            // Semana
            if ($c->fecha_cme) {
                $semanaKey = Carbon::parse($c->fecha_cme)->format('W-Y');
                $citasSemanales[$semanaKey] = ($citasSemanales[$semanaKey] ?? 0) + 1;
            }

            // Hora (usa created_at como aproximación)
            $horaRef = $c->fecha_cme ? Carbon::parse($c->fecha_cme) : null;
            if ($horaRef) {
                $bloque = $horaRef->format('h:00 A');
                $horasBloques[$bloque] = ($horasBloques[$bloque] ?? 0) + 1;
            }

            // Enfermedades
            foreach ($c->enfermedades as $enf) {
                $nombre = $enf->nombre_efm ?? 'Sin nombre';
                $resumen['enfermedades'][$nombre] = ($resumen['enfermedades'][$nombre] ?? 0) + 1;
            }

            // Médico
            $nombreMedico = trim(($c->medico->nombres_per ?? '') . ' ' . ($c->medico->apellidos_per ?? '')) ?: 'No asignado';
            $resumen['medicos'][$nombreMedico] = ($resumen['medicos'][$nombreMedico] ?? 0) + 1;

            // Consultorio
            $nombreCons = $c->consultorio->nombre_csr ?? 'Sin consultorio';
            $resumen['consultorios'][$nombreCons] = ($resumen['consultorios'][$nombreCons] ?? 0) + 1;

            // Receta y dispensaciones
            if ($c->receta) {
                $resumen['total_recetas']++;
                $totalConReceta++;

                $estaCompleta = true;
                foreach ($c->receta->detalles as $det) {
                    $disp = (float) $det->dispensaciones->sum('cantidad_dsm');
                    $resumen['total_dispensados'] += $disp;

                    // Producto top (por cantidad total dispensada)
                    $prodNombre = $det->producto->nombre_pdt ?? 'Producto #' . $det->pdt_id;
                    $resumen['productos_top'][$prodNombre] = ($resumen['productos_top'][$prodNombre] ?? 0) + $disp;

                    if ((float) $det->cantidad_drm < (float) $det->cantidad_prescrita_drm) $estaCompleta = false;
                }

                if ((int) $c->receta->status_id === RecetasMedica::STATUS_INACTIVO) {
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
            if ($paciente && !isset($pacientes[$paciente->id_per])) {
                $pacientes[$paciente->id_per] = true;
                $resumen['total_pacientes']++;

                // Género
                $genero = strtolower(trim($paciente->genero_per ?? ''));
                if (in_array($genero, ['masculino', 'hombre', 'm'])) $resumen['genero']['masculino']++;
                elseif (in_array($genero, ['femenino', 'mujer', 'f'])) $resumen['genero']['femenino']++;
                else $resumen['genero']['otro']++;
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
        arsort($resumen['pnf_consulta']);
        arsort($resumen['tipo_persona_consulta']);

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

    protected function acumularDemografia(array &$resumen, Consulta $consulta, array &$edades): void
    {
        $paciente = $consulta->paciente;
        $edad = static::edadAlConsultar($paciente?->fecha_nacimiento_per, $consulta->fecha_cme ?? $consulta->created_at);

        if ($edad === null) {
            $resumen['edad_consulta'][self::SIN_FECHA_NACIMIENTO]++;
        } else {
            $edades[] = $edad;
            $resumen['edad_consulta'][static::rangoEdad($edad)]++;
        }

        $pnf = $paciente?->personaPnf?->first()?->pnf?->nombre_prg;
        $pnf = filled($pnf) ? trim($pnf) : self::SIN_PNF;
        $resumen['pnf_consulta'][$pnf] = ($resumen['pnf_consulta'][$pnf] ?? 0) + 1;

        $tipo = $paciente?->perfil?->nombre_perfil;
        $tipo = filled($tipo) ? trim($tipo) : self::SIN_TIPO_PERSONA;
        $resumen['tipo_persona_consulta'][$tipo] = ($resumen['tipo_persona_consulta'][$tipo] ?? 0) + 1;
    }

    public static function edadAlConsultar($fechaNacimiento, $fechaConsulta): ?int
    {
        if (blank($fechaNacimiento) || blank($fechaConsulta)) {
            return null;
        }

        $nacimiento = Carbon::parse($fechaNacimiento)->startOfDay();
        $consulta = Carbon::parse($fechaConsulta)->startOfDay();

        if ($nacimiento->gt($consulta)) {
            return null;
        }

        $edad = $consulta->year - $nacimiento->year;

        if ($consulta->month < $nacimiento->month || ($consulta->month === $nacimiento->month && $consulta->day < $nacimiento->day)) {
            $edad--;
        }

        return $edad;
    }

    public static function rangoEdad(int $edad): string
    {
        foreach (self::RANGOS_EDAD as $etiqueta => [$desde, $hasta]) {
            if ($edad >= $desde && ($hasta === null || $edad <= $hasta)) {
                return $etiqueta;
            }
        }

        return array_key_last(self::RANGOS_EDAD);
    }
}
