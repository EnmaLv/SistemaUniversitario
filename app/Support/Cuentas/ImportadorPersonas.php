<?php

namespace App\Support\Cuentas;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as FechaExcel;
use RuntimeException;

class ImportadorPersonas
{
    public const TAMANO_LOTE = 500;

    public const MAX_ERRORES_GUARDADOS = 200;

    private const PERFIL_ESTUDIANTE = 2;

    private ?array $pnfs = null;

    public function importar(string $rutaAbsoluta, int $idSede): array
    {
        $filas = $this->leerFilas($rutaAbsoluta);

        $resumen = [
            'total_filas'           => 0,
            'personas_creadas'      => 0,
            'personas_actualizadas' => 0,
            'filas_con_error'       => 0,
            'errores'               => [],
            'ids_persona'           => [],
        ];

        $validas = [];
        $vistas = [];

        foreach ($filas as $numero => $fila) {
            $resumen['total_filas']++;

            $cedulaCruda = trim((string) ($fila['cedula'] ?? ''));
            $cedula = Cedula::normalizar($cedulaCruda);
            $motivo = null;

            if ($cedulaCruda === '') {
                $motivo = 'Cédula vacía';
            } elseif ($cedula === null) {
                $motivo = 'Cédula mal formada';
            } elseif (isset($vistas[$cedula])) {
                $motivo = "Cédula duplicada en el archivo (ya aparece en la fila {$vistas[$cedula]})";
            } elseif (trim((string) ($fila['nombre'] ?? '')) === '' || trim((string) ($fila['apellido'] ?? '')) === '') {
                $motivo = 'Nombre o apellido vacío';
            }

            if ($motivo !== null) {
                $this->agregarError($resumen, $numero, $motivo);
                continue;
            }

            $vistas[$cedula] = $numero;
            $validas[$cedula] = $fila;
        }

        foreach (array_chunk($validas, self::TAMANO_LOTE, true) as $lote) {
            DB::transaction(function () use ($lote, $idSede, &$resumen) {
                $this->guardarLote($lote, $idSede, $resumen);
            });
        }

        return $resumen;
    }

    private function guardarLote(array $lote, int $idSede, array &$resumen): void
    {
        $cedulas = array_map('strval', array_keys($lote));
        $existentes = DB::table('persona')
            ->whereIn('cedula_per', $cedulas)
            ->get()
            ->keyBy('cedula_per');

        $ahora = now();
        $registros = [];

        foreach ($lote as $cedula => $fila) {
            $cedula = (string) $cedula;
            $previa = $existentes->get($cedula);
            $fechaNacimiento = $this->fechaNacimiento($fila['fecha_nacimiento'] ?? null);
            $correo = trim((string) ($fila['email'] ?? ''));

            $nuevos = [
                'nombres_per'           => $this->mayusculas($fila['nombre'] ?? ''),
                'segundo_nombre_per'   => $this->mayusculas($fila['segundo_nombre'] ?? '') ?: null,
                'apellidos_per'         => $this->mayusculas($fila['apellido'] ?? ''),
                'segundo_apellido_per' => $this->mayusculas($fila['segundo_apellido'] ?? '') ?: null,
                'telefono_per'         => preg_replace('/\D/', '', (string) ($fila['telefono'] ?? '')),
                'genero_per'           => $this->genero($fila['genero'] ?? ''),
                'fecha_nacimiento_per' => $fechaNacimiento,
                'correo_per'            => filter_var($correo, FILTER_VALIDATE_EMAIL) ? mb_strtolower($correo) : '',
                'semestre_per'         => trim((string) ($fila['semestre'] ?? '')) ?: null,
            ];

            if ($previa) {
                foreach ($nuevos as $columna => $valor) {
                    if ($valor === null || $valor === '' || ($columna === 'genero_per' && $valor === 'NO DEFINIDO')) {
                        $nuevos[$columna] = $previa->{$columna};
                    }
                }
            }

            $registros[] = array_merge($nuevos, [
                'cedula_per' => $cedula,
                'id_perfil'      => $previa->id_perfil ?? self::PERFIL_ESTUDIANTE,
                'sed_id'        => $previa->sed_id ?? $idSede,
                'status_id'      => 1,
                'created_at'     => $previa->created_at ?? $ahora,
                'updated_at'     => $ahora,
            ]);

            $previa ? $resumen['personas_actualizadas']++ : $resumen['personas_creadas']++;
        }

        DB::table('persona')->upsert(
            $registros,
            ['cedula_per'],
            [
                'nombres_per', 'segundo_nombre_per', 'apellidos_per', 'segundo_apellido_per',
                'telefono_per', 'genero_per', 'fecha_nacimiento_per',
                'correo_per', 'semestre_per', 'status_id', 'updated_at',
            ]
        );

        $ids = DB::table('persona')->whereIn('cedula_per', $cedulas)->pluck('id_per', 'cedula_per');
        $resumen['ids_persona'] = array_merge($resumen['ids_persona'], $ids->values()->all());

        $this->guardarPnf($lote, $ids->all());
    }

    private function guardarPnf(array $lote, array $idsPorCedula): void
    {
        $conPnf = DB::table('persona_programa')
            ->whereIn('per_id', array_values($idsPorCedula))
            ->pluck('prg_id', 'per_id');

        $insertar = [];
        $actualizar = [];
        $hoy = now()->toDateString();

        foreach ($lote as $cedula => $fila) {
            $idPersona = $idsPorCedula[(string) $cedula] ?? null;
            if (! $idPersona) {
                continue;
            }

            $textoPnf = trim((string) ($fila['pnf'] ?? ''));
            $idPnf = $this->pnf($textoPnf);

            if ($idPnf === null) {
                continue;
            }

            if (! $conPnf->has($idPersona)) {
                $insertar[] = ['per_id' => $idPersona, 'prg_id' => $idPnf, 'fecha_inicio_pep' => $hoy, 'fecha_fin_pep' => $hoy];
            } elseif ($textoPnf !== '' && (int) $conPnf[$idPersona] !== $idPnf) {
                $actualizar[$idPnf][] = $idPersona;
            }
        }

        if ($insertar !== []) {
            DB::table('persona_programa')->insert($insertar);
        }

        foreach ($actualizar as $idPnf => $idsPersona) {
            DB::table('persona_programa')->whereIn('per_id', $idsPersona)->update(['prg_id' => $idPnf]);
        }
    }

    private function leerFilas(string $rutaAbsoluta): array
    {
        $extension = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
        $lector = in_array($extension, ['csv', 'txt'], true)
            ? IOFactory::createReader('Csv')
            : IOFactory::createReaderForFile($rutaAbsoluta);
        $lector->setReadDataOnly(true);

        $hoja = $lector->load($rutaAbsoluta)->getActiveSheet();
        $filas = $hoja->toArray(null, true, false, true);

        if ($filas === []) {
            throw new RuntimeException('El archivo cargado está vacío.');
        }

        $mapa = null;
        foreach ($filas as $numero => $fila) {
            unset($filas[$numero]);
            $texto = Str::ascii(implode(' ', array_map('strval', array_values($fila))));

            if (preg_match('/cedula|c_i|dni|nombre|apellido/i', $texto)) {
                $mapa = $this->mapaColumnas($fila);
                break;
            }
        }

        if ($mapa === null) {
            throw new RuntimeException('No se encontró la fila de encabezados en el archivo.');
        }

        if (! isset($mapa['cedula'])) {
            throw new RuntimeException('No se detectó la columna de cédula en el archivo.');
        }

        $resultado = [];
        foreach ($filas as $numero => $fila) {
            $valores = [];
            foreach ($mapa as $campo => $columna) {
                $valores[$campo] = $fila[$columna] ?? null;
            }

            if (trim(implode('', array_map('strval', $valores))) === '') {
                continue;
            }

            $resultado[$numero] = $valores;
        }

        return $resultado;
    }

    private function mapaColumnas(array $encabezados): array
    {
        $mapa = [];

        foreach ($encabezados as $columna => $titulo) {
            if (! $titulo) {
                continue;
            }

            $clave = strtolower(trim(Str::ascii((string) $titulo)));
            $clave = preg_replace('/[^a-z0-9]/', '_', $clave);
            $clave = preg_replace('/_+/', '_', trim($clave, '_'));

            $campo = match (true) {
                (bool) preg_match('/cedula|c_i|dni|identificacion/', $clave) => 'cedula',
                (bool) preg_match('/segundo_nombre|nombre_2|nombre2/', $clave) => 'segundo_nombre',
                (bool) preg_match('/nombre/', $clave) => 'nombre',
                (bool) preg_match('/segundo_apellido|apellido_2|apellido2/', $clave) => 'segundo_apellido',
                (bool) preg_match('/apellido/', $clave) => 'apellido',
                preg_match('/telefono|celular|movil|tlf/', $clave) && ! preg_match('/otro/', $clave) => 'telefono',
                (bool) preg_match('/genero|sexo/', $clave) => 'genero',
                (bool) preg_match('/nacimiento|fecha_nac|fec_nac/', $clave) => 'fecha_nacimiento',
                (bool) preg_match('/email|correo/', $clave) => 'email',
                (bool) preg_match('/semestre|trayecto|nivel/', $clave) => 'semestre',
                (bool) preg_match('/pnf|carrera|programa|especialidad|pfg/', $clave) => 'pnf',
                default => null,
            };

            if ($campo !== null && ! isset($mapa[$campo])) {
                $mapa[$campo] = $columna;
            }
        }

        return $mapa;
    }

    private function pnf(string $texto): ?int
    {
        $this->pnfs ??= DB::table('programa')->where('status_id', 1)->get(['id_prg', 'nombre_prg'])->all();

        if ($this->pnfs === []) {
            return null;
        }

        $porDefecto = collect($this->pnfs)->contains(fn ($pnf) => (int) $pnf->id_prg === 1) ? 1 : null;

        if ($texto === '') {
            return $porDefecto;
        }

        $buscado = preg_replace('/\s+/', ' ', strtoupper(Str::ascii($texto)));
        $mejor = null;
        $similitudMejor = 0;

        foreach ($this->pnfs as $pnf) {
            similar_text($buscado, strtoupper(Str::ascii($pnf->nombre_prg)), $porcentaje);

            if ($porcentaje >= 90) {
                return (int) $pnf->id_prg;
            }

            if ($porcentaje > $similitudMejor) {
                $similitudMejor = $porcentaje;
                $mejor = (int) $pnf->id_prg;
            }
        }

        return $similitudMejor >= 70 ? $mejor : $porDefecto;
    }

    private function fechaNacimiento(mixed $valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        if (is_numeric($valor)) {
            try {
                return Carbon::instance(FechaExcel::excelToDateTimeObject((float) $valor))->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $limpia = str_replace(['-', '.'], '/', preg_replace('/[^0-9\/\-.]/', '', (string) $valor));

        if (substr_count($limpia, '/') !== 2) {
            return null;
        }

        try {
            $fecha = Carbon::createFromFormat('d/m/Y', $limpia);

            return $fecha && $fecha->year > 1920 && $fecha->year <= now()->year ? $fecha->format('Y-m-d') : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function genero(mixed $valor): string
    {
        return match (strtoupper(trim((string) $valor))) {
            'M', 'MASCULINO' => 'MASCULINO',
            'F', 'FEMENINO'  => 'FEMENINO',
            default          => 'NO DEFINIDO',
        };
    }

    private function mayusculas(mixed $valor): string
    {
        return mb_strtoupper(trim((string) $valor), 'UTF-8');
    }

    private function agregarError(array &$resumen, int $fila, string $motivo): void
    {
        $resumen['filas_con_error']++;

        if (count($resumen['errores']) < self::MAX_ERRORES_GUARDADOS) {
            $resumen['errores'][] = ['fila' => $fila, 'motivo' => $motivo];
        }
    }
}
