<?php

namespace Database\Seeders;

use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\TorneoDeportivo;
use App\Models\Usuario;
use App\Services\Deporte\PrestamoService;
use App\Support\Deportes\ServicioActividades;
use App\Support\Deportes\ServicioAsistencia;
use App\Support\Deportes\ServicioInscripcion;
use App\Support\Inventario\ServicioInventario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeporteSeeder extends Seeder
{
    private const ATLETAS = [
        ['Luis', 'Pérez', 'Masculino'], ['María', 'González', 'Femenino'], ['José', 'Rodríguez', 'Masculino'], ['Ana', 'Martínez', 'Femenino'],
        ['Carlos', 'Hernández', 'Masculino'], ['Daniela', 'López', 'Femenino'], ['Miguel', 'Díaz', 'Masculino'], ['Gabriela', 'Torres', 'Femenino'],
        ['Andrés', 'Ramírez', 'Masculino'], ['Valentina', 'Flores', 'Femenino'], ['Jesús', 'Rojas', 'Masculino'], ['Paola', 'Castillo', 'Femenino'],
        ['Ricardo', 'Mendoza', 'Masculino'], ['Andrea', 'Silva', 'Femenino'], ['Oscar', 'Medina', 'Masculino'], ['Carla', 'Vargas', 'Femenino'],
    ];

    public function run(): void
    {
        $coordinador = DB::table('user')->where('username_use', 'admin@example.com')->value('id_use')
            ?? DB::table('user')->orderBy('id_use')->value('id_use');
        $sede = DB::table('sede')->where('status_id', 1)->whereNull('deleted_at')->orderBy('id_sed')->first(['id_sed', 'nombre_sed']);

        if (! $coordinador || ! $sede) {
            $this->command?->warn('DeporteSeeder: faltan usuarios o sedes; ejecútalo después de PersonaSeeder y SedeSeeder.');

            return;
        }

        $usuario = Usuario::query()->find($coordinador);
        Auth::setUser($usuario);

        try {
            $disciplinas = $this->disciplinas();
            $espaciosPorSede = [];

            foreach (DB::table('sede')->where('status_id', 1)->whereNull('deleted_at')->orderBy('id_sed')->get(['id_sed']) as $fila) {
                $espaciosPorSede[$fila->id_sed] = $this->espacios((int) $fila->id_sed);
            }

            if (DB::table('calendario_deportivo')->where('sed_id', $sede->id_sed)->exists()) {
                return;
            }

            $espacios = $espaciosPorSede[$sede->id_sed];
            $atletas = $this->atletas((int) $sede->id_sed);

            $this->jornada($disciplinas, $espacios, (int) $sede->id_sed);
            $this->entrenamiento($disciplinas, $espacios, (int) $sede->id_sed, array_slice($atletas, 0, 6));
            $this->torneo($disciplinas, $espacios, (int) $sede->id_sed, $coordinador, array_slice($atletas, 6, 10));
            $this->prestamos((int) $sede->id_sed, $espacios, array_slice($atletas, 0, 2));
        } finally {
            Auth::guard()->forgetUser();
        }
    }

    private function disciplinas(): array
    {
        $ids = [];

        foreach ([
            'futbol'     => ['Fútbol sala', 'Fútbol de salón en cancha techada.'],
            'voleibol'   => ['Voleibol', 'Voleibol mixto.'],
            'baloncesto' => ['Baloncesto', 'Baloncesto en cancha reglamentaria.'],
        ] as $clave => [$nombre, $descripcion]) {
            $ids[$clave] = DB::table('actividad_deportiva')->where('nombre_acd', $nombre)->value('id_acd')
                ?? DB::table('actividad_deportiva')->insertGetId([
                    'nombre_acd'      => $nombre,
                    'descripcion_acd' => $descripcion,
                    'tipo_acd'        => 'programada',
                    'status_id'       => 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ], 'id_acd');
        }

        return $ids;
    }

    private function espacios(int $sedeId): array
    {
        $ids = [];

        foreach ([
            'multiple'   => ['Cancha múltiple', 'Cancha techada de usos múltiples.', 'Área deportiva principal'],
            'voleibol'   => ['Cancha de voleibol', 'Cancha de arena para voleibol.', 'Detrás del edificio administrativo'],
            'baloncesto' => ['Cancha de baloncesto', 'Cancha de concreto con tableros.', 'Junto al estacionamiento'],
        ] as $clave => [$nombre, $descripcion, $ubicacion]) {
            $ids[$clave] = DB::table('espacio_deportivo')->where('sed_id', $sedeId)->where('nombre_epd', $nombre)->value('id_epd')
                ?? DB::table('espacio_deportivo')->insertGetId([
                    'nombre_epd'      => $nombre,
                    'descripcion_epd' => $descripcion,
                    'ubicacion_epd'   => $ubicacion,
                    'sed_id'          => $sedeId,
                    'status_id'       => 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ], 'id_epd');
        }

        return $ids;
    }

    private function atletas(int $sedeId): array
    {
        $perfil = DB::table('perfil')->where('nombre_perfil', 'Estudiante')->value('id_perfil')
            ?? DB::table('perfil')->orderBy('id_perfil')->value('id_perfil');
        $cedulas = [];

        foreach (self::ATLETAS as $indice => [$nombre, $apellido, $genero]) {
            $cedula = (string) (30100001 + $indice);

            if (! DB::table('persona')->where('cedula_per', $cedula)->exists()) {
                DB::table('persona')->insert([
                    'nombres_per'          => $nombre,
                    'apellidos_per'        => $apellido,
                    'cedula_per'           => $cedula,
                    'telefono_per'         => '0424-55500' . str_pad((string) $indice, 2, '0', STR_PAD_LEFT),
                    'genero_per'           => $genero,
                    'fecha_nacimiento_per' => Carbon::today()->subYears(20 + $indice % 5)->toDateString(),
                    'correo_per'           => 'atleta' . $cedula . '@uptp.example',
                    'id_perfil'            => $perfil,
                    'sed_id'               => $sedeId,
                    'status_id'            => 1,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }

            $cedulas[] = $cedula;
        }

        return $cedulas;
    }

    private function jornada(array $disciplinas, array $espacios, int $sedeId): void
    {
        $informatica = DB::table('programa')->whereRaw('UPPER(nombre_prg) LIKE ?', ['%INFORM%'])->value('id_prg');

        ServicioActividades::guardar([
            'nombre_cld'          => 'Jornada deportiva de Informática 631',
            'descripcion_cld'     => 'Jornada recreativa de la sección 631: fútbol sala, baloncesto y voleibol al mismo tiempo.',
            'tipo_cld'            => DeporteCalendario::TIPO_ESPORADICA,
            'fecha'               => Carbon::today()->addDays(2)->toDateString(),
            'seccion_cld'         => '631',
            'prg_id'              => $informatica,
            'con_inscripcion_cld' => false,
            'sed_id'              => $sedeId,
        ], [
            ['id' => null, 'acd_id' => $disciplinas['futbol'], 'epd_id' => $espacios['multiple'], 'hora_inicio' => '07:00', 'hora_fin' => '12:00', 'cupo' => null],
            ['id' => null, 'acd_id' => $disciplinas['baloncesto'], 'epd_id' => $espacios['baloncesto'], 'hora_inicio' => '07:00', 'hora_fin' => '12:00', 'cupo' => null],
            ['id' => null, 'acd_id' => $disciplinas['voleibol'], 'epd_id' => $espacios['voleibol'], 'hora_inicio' => '07:00', 'hora_fin' => '12:00', 'cupo' => null],
        ]);
    }

    private function entrenamiento(array $disciplinas, array $espacios, int $sedeId, array $cedulas): void
    {
        $actividad = ServicioActividades::guardar([
            'nombre_cld'          => 'Entrenamiento de la selección de fútbol sala',
            'descripcion_cld'     => 'Entrenamiento del equipo que representa a la universidad.',
            'tipo_cld'            => DeporteCalendario::TIPO_RECURRENTE,
            'fecha_inicio_cld'    => Carbon::today()->subWeeks(3)->toDateString(),
            'fecha_fin_cld'       => Carbon::today()->addMonths(3)->toDateString(),
            'dias'                => ['lunes', 'miercoles'],
            'con_inscripcion_cld' => true,
            'sed_id'              => $sedeId,
        ], [
            ['id' => null, 'acd_id' => $disciplinas['futbol'], 'epd_id' => $espacios['multiple'], 'hora_inicio' => '15:00', 'hora_fin' => '16:30', 'cupo' => 20],
        ]);

        $bloque = DeporteHorario::query()->where('cld_id', $actividad->id_cld)->with('calendario')->firstOrFail();

        foreach ($cedulas as $cedula) {
            ServicioInscripcion::inscribir($bloque, $cedula);
        }

        $pasadas = array_slice($actividad->fechasEntre($actividad->fecha_inicio_cld, Carbon::yesterday()), -3);

        foreach ($pasadas as $indice => $fecha) {
            $sesion = ServicioAsistencia::abrir($bloque, $fecha);
            ServicioAsistencia::marcarCedulas($sesion, implode("\n", array_slice($cedulas, 0, 4 + ($indice % 2))));
            ServicioAsistencia::cerrar($sesion);
        }
    }

    private function torneo(array $disciplinas, array $espacios, int $sedeId, int $coordinador, array $cedulas): void
    {
        $inicio = Carbon::today()->next(Carbon::SATURDAY);

        $actividad = ServicioActividades::guardar([
            'nombre_cld'       => 'Copa Aniversario de Baloncesto',
            'descripcion_cld'  => 'Todos contra todos a un partido.',
            'tipo_cld'         => DeporteCalendario::TIPO_TORNEO,
            'fecha_inicio_cld' => $inicio->toDateString(),
            'fecha_fin_cld'    => $inicio->copy()->addWeek()->toDateString(),
            'dias'             => ['sabado'],
            'sed_id'           => $sedeId,
        ], [
            ['id' => null, 'acd_id' => $disciplinas['baloncesto'], 'epd_id' => $espacios['baloncesto'], 'hora_inicio' => '14:00', 'hora_fin' => '18:00', 'cupo' => null],
        ]);

        $torneo = TorneoDeportivo::create([
            'nombre_tde'      => 'Copa Aniversario de Baloncesto',
            'descripcion_tde' => 'Todos contra todos a un partido.',
            'modalidad_tde'   => 'Mixto',
            'estado_tde'      => TorneoDeportivo::ESTADO_ABIERTO,
            'hde_id'          => $actividad->horarios->first()->id_hde,
            'use_id'          => $coordinador,
            'status_id'       => 1,
        ]);

        $programas = DB::table('programa')->whereNull('deleted_at')->orderBy('id_prg')->limit(2)->pluck('id_prg')->all();

        foreach (['Los Halcones', 'Las Panteras'] as $indice => $nombre) {
            $equipo = $torneo->equipos()->create([
                'nombre_equipo_etd' => $nombre,
                'estado_etd'        => 'inscrito',
                'prg_id'            => $programas[$indice] ?? null,
                'status_id'         => 1,
            ]);

            foreach (array_slice($cedulas, $indice * 5, 5) as $cedula) {
                $persona = ServicioInscripcion::buscarPersona($cedula);

                if ($persona) {
                    $equipo->integrantes()->create(['per_id' => $persona->id_per, 'status_id' => 1]);
                }
            }
        }
    }

    private function prestamos(int $sedeId, array $espacios, array $cedulas): void
    {
        (new CategoriaSeeder())->sembrarComplementarias();

        $categoria = DB::table('categoria_producto as c')
            ->join('tipo_producto as t', 't.id_tpr', '=', 'c.tpr_id')
            ->where('t.nombre_tpr', 'Implemento deportivo')
            ->where('c.nombre_cap', 'Balones')
            ->value('c.id_cap');
        $unidad = DB::table('unidad_medida')->where('nombre_unm', 'Unidad')->value('id_unm')
            ?? DB::table('unidad_medida')->orderBy('id_unm')->value('id_unm');

        if (! $categoria || ! $unidad || count($cedulas) < 2) {
            return;
        }

        $lotes = [];

        foreach ([
            'DEP-BAL-FS' => ['Balón de fútbol sala', 10],
            'DEP-BAL-VO' => ['Balón de voleibol', 4],
        ] as $codigo => [$nombre, $cantidad]) {
            $producto = DB::table('producto')->where('codigo_pdt', $codigo)->value('id_pdt')
                ?? DB::table('producto')->insertGetId([
                    'codigo_pdt'                => $codigo,
                    'nombre_pdt'                => $nombre,
                    'peso_contenido_pdt'        => 1,
                    'unidades_presentacion_pdt' => 1,
                    'stock_minimo_pdt'          => 3,
                    'unm_id'                    => $unidad,
                    'cap_id'                    => $categoria,
                    'status_id'                 => 1,
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ], 'id_pdt');

            $lotes[$codigo] = DB::table('lote')->insertGetId([
                'codigo_lote_lot'      => 'INI-' . $codigo . '-' . $sedeId,
                'fecha_entrada_lot'    => now()->toDateString(),
                'cantidad_inicial_lot' => $cantidad,
                'cantidad_actual_lot'  => 0,
                'status_id'            => 1,
                'pdt_id'               => $producto,
                'created_at'           => now(),
                'updated_at'           => now(),
            ], 'id_lot');

            app(ServicioInventario::class)->entrada($lotes[$codigo], $sedeId, $cantidad, $cantidad, [
                'mod_id'              => PrestamoService::moduloDeporteId(),
                'referencia_type_moi' => 'inventario_inicial',
                'observacion_moi'     => 'Implementos de ejemplo del módulo de deportes',
            ]);
        }

        $servicio = app(PrestamoService::class);

        $servicio->registrar([
            'cedula'                    => $cedulas[0],
            'lot_id'                    => $lotes['DEP-BAL-FS'],
            'cantidad'                  => 2,
            'motivo'                    => 'Práctica libre de fútbol sala',
            'epd_id'                    => $espacios['multiple'],
            'fecha_devolucion_esperada' => now()->addDay()->format('Y-m-d H:i:s'),
        ], $sedeId);

        $servicio->registrar([
            'cedula'                    => $cedulas[1],
            'lot_id'                    => $lotes['DEP-BAL-VO'],
            'cantidad'                  => 1,
            'motivo'                    => 'Entrenamiento de voleibol',
            'lugar'                     => 'Patio central',
            'fecha_prestamo'            => now()->subDays(3)->format('Y-m-d H:i:s'),
            'fecha_devolucion_esperada' => now()->subDays(2)->format('Y-m-d H:i:s'),
        ], $sedeId);

        $servicio->marcarVencidos();
    }
}
