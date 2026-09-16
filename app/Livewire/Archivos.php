<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Archivo;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Persona;
use App\Models\PersonaPnf;

class Archivos extends Component
{
    use WithFileUploads;

    public $archivo;
    public $archivoKey;
    public $buscar = '';

    protected $rules = [
        'archivo' => 'required|file|mimes:xlsx,xls,pdf,txt|max:10240',
    ];

    public function mount()
    {
        $this->archivoKey = rand();
    }

    private function getColumnMapping(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $colLetter => $title) {
            if (!$title) continue;

            // Convertir a ASCII seguro con Laravel (CÉDULA -> CEDULA, N° -> N DEG)
            $slug = Str::ascii((string)$title);
            $slug = strtolower(trim($slug));
            $slug = preg_replace('/[^a-z0-9]/', '_', $slug);
            $slug = preg_replace('/_+/', '_', trim($slug, '_'));

            if (preg_match('/cedula|c_i|dni|identificacion/', $slug) && !isset($map['cedula'])) {
                $map['cedula'] = $colLetter;
            } elseif (preg_match('/segundo_nombre|nombre_2|nombre2/', $slug) && !isset($map['segundo_nombre'])) {
                $map['segundo_nombre'] = $colLetter;
            } elseif (preg_match('/nombre/', $slug) && !isset($map['nombre'])) {
                $map['nombre'] = $colLetter;
            } elseif (preg_match('/segundo_apellido|apellido_2|apellido2/', $slug) && !isset($map['segundo_apellido'])) {
                $map['segundo_apellido'] = $colLetter;
            } elseif (preg_match('/apellido/', $slug) && !isset($map['apellido'])) {
                $map['apellido'] = $colLetter;
            } elseif (preg_match('/telefono|celular|movil|tlf/', $slug) && !preg_match('/otro/', $slug) && !isset($map['telefono'])) {
                $map['telefono'] = $colLetter;
            } elseif (preg_match('/genero|sexo/', $slug) && !isset($map['genero'])) {
                $map['genero'] = $colLetter;
            } elseif (preg_match('/nacimiento|fecha_nac|fec_nac|a_nacimiento/', $slug) && !isset($map['fecha_nacimiento'])) {
                $map['fecha_nacimiento'] = $colLetter;
            } elseif (preg_match('/email|correo/', $slug) && !isset($map['email'])) {
                $map['email'] = $colLetter;
            } elseif (preg_match('/semestre|trayecto|nivel/', $slug) && !isset($map['semestre'])) {
                $map['semestre'] = $colLetter;
            } elseif (preg_match('/pnf|carrera|programa|especialidad|pfg/', $slug) && !isset($map['pnf'])) {
                $map['pnf'] = $colLetter;
            }
        }

        return $map;
    }

    private function normalizarPnf(string $pnfExcel): int
    {
        if (empty($pnfExcel)) return 1;

        $pnfExcel = strtoupper(trim(Str::ascii($pnfExcel)));
        $pnfExcel = preg_replace('/\s+/', ' ', $pnfExcel);

        $pnfs = DB::table('pnf')
            ->where('id_estatus', 1)
            ->get(['id_pnf', 'nombre_pnf']);

        $mejorCoincidencia = null;
        $mejorSimilitud = 0;

        foreach ($pnfs as $pnf) {
            $nombreBD = strtoupper(Str::ascii($pnf->nombre_pnf));

            similar_text($pnfExcel, $nombreBD, $porcentaje);

            if ($porcentaje > $mejorSimilitud) {
                $mejorSimilitud = $porcentaje;
                $mejorCoincidencia = $pnf->id_pnf;
            }

            if ($porcentaje >= 90) {
                return $pnf->id_pnf;
            }
        }

        if ($mejorSimilitud >= 70) {
            return $mejorCoincidencia;
        }

        return 1;
    }

    private function parseFechaNacimiento($fecha)
    {
        if ($fecha === null || $fecha === '' || trim((string)$fecha) === '') {
            return null;
        }

        if (is_numeric($fecha)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha)
                )->format('Y-m-d');
            } catch (\Exception $e) {
            }
        }

        $fechaLimpia = preg_replace('/[^\x20-\x7E]/u', '', (string)$fecha);
        $fechaLimpia = preg_replace('/[^0-9\/\-]/', '', $fechaLimpia);
        $fechaLimpia = str_replace(['-', '.', ','], '/', $fechaLimpia);

        if (substr_count($fechaLimpia, '/') !== 2) {
            return null;
        }

        $partes = explode('/', $fechaLimpia);
        if (count($partes) !== 3) return null;

        try {
            $fechaCreada = Carbon::createFromFormat('d/m/Y', $fechaLimpia);
            if ($fechaCreada && $fechaCreada->year > 1920 && $fechaCreada->year <= now()->year) {
                return $fechaCreada->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $ruta = $this->archivo->store('informacion', 'public');
            $fullPath = storage_path('app/public/' . $ruta);

            $spreadsheet = IOFactory::load($fullPath);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

            if (empty($rows)) {
                throw new \Exception('El archivo cargado está vacío.');
            }

            // Buscar dinámicamente la fila de encabezados en las primeras 10 filas
            $headerRow = null;
            foreach ($rows as $index => $row) {
                $rowString = Str::ascii(implode(' ', array_values($row)));
                if (preg_match('/cedula|c_i|dni|nombre|apellido/i', $rowString)) {
                    $headerRow = $row;
                    $rows = array_slice($rows, $index + 1);
                    break;
                }
            }

            if (!$headerRow) {
                throw new \Exception('No se encontró la fila de encabezados en el archivo Excel.');
            }

            $map = $this->getColumnMapping($headerRow);

            if (!isset($map['cedula'])) {
                throw new \Exception('No se detectó la columna de "Cédula" en la tabla cargada.');
            }

            $getValue = function ($key, $row) use ($map) {
                $col = $map[$key] ?? null;
                return $col ? trim((string)($row[$col] ?? '')) : '';
            };

            $cedulasProcesadas = [];
            $stats = [
                'total' => 0,
                'insertados' => 0,
                'actualizados' => 0,
                'omitidos_fecha' => 0,
                'omitidos_cedula' => 0,
                'omitidos_duplicados' => 0
            ];

            foreach ($rows as $row) {
                $stats['total']++;

                // Extraer solo dígitos numéricos de la cédula
                $cedulaRaw = $getValue('cedula', $row);
                $cedula = preg_replace('/\D/', '', $cedulaRaw);

                if (!$cedula) {
                    $stats['omitidos_cedula']++;
                    continue;
                }

                if (in_array($cedula, $cedulasProcesadas)) {
                    $stats['omitidos_duplicados']++;
                    continue;
                }

                $cedulasProcesadas[] = $cedula;

                $fechaNacimiento = $this->parseFechaNacimiento($getValue('fecha_nacimiento', $row));
                $edad = $fechaNacimiento ? Carbon::parse($fechaNacimiento)->age : null;

                if (!$fechaNacimiento) {
                    $stats['omitidos_fecha']++;
                }

                $sexoRaw = strtoupper($getValue('genero', $row));
                $sexo = match ($sexoRaw) {
                    'M', 'MASCULINO' => 'MASCULINO',
                    'F', 'FEMENINO'  => 'FEMENINO',
                    default          => 'NO DEFINIDO',
                };

                $telefono = preg_replace('/\D/', '', $getValue('telefono', $row));

                $persona = Persona::updateOrCreate(
                    ['cedula_persona' => $cedula],
                    [
                        'nombre_persona'            => $getValue('nombre', $row),
                        'segundo_nombre_persona'    => $getValue('segundo_nombre', $row) ?: null,
                        'apellido_persona'          => $getValue('apellido', $row),
                        'segundo_apellido_persona'  => $getValue('segundo_apellido', $row) ?: null,
                        'telefono_persona'          => $telefono ?: null,
                        'genero_persona'            => $sexo,
                        'edad_persona'              => $edad,
                        'fecha_nacimiento_persona'  => $fechaNacimiento,
                        'email_persona'             => $getValue('email', $row) ?: null,
                        'semestre_persona'          => $getValue('semestre', $row) ?: null,
                        'id_perfil'                 => 2,
                        'id_sede'                   => 1,
                    ]
                );

                if ($persona->wasRecentlyCreated) {
                    $stats['insertados']++;
                } else {
                    $stats['actualizados']++;
                }

                PersonaPnf::updateOrCreate(
                    ['id_persona' => $persona->id_persona],
                    [
                        'id_pnf'       => $this->normalizarPnf($getValue('pnf', $row)),
                        'fecha_inicio' => now()->toDateString(),
                        'fecha_fin'    => now()->toDateString(),
                    ]
                );
            }

            Archivo::create([
                'info_estudiantes' => 'Estudiantes UPTP - ' . now()->format('Y-m-d H:i:s'),
                'fecha' => now()->toDateString(),
                'estado' => 'Procesado',
            ]);

            DB::commit();

            $this->reset('archivo');
            $this->archivoKey = rand();

            $this->dispatch('swal', icon: 'success', title: '¡Éxito!', text: 'Procesado: ' . $stats['insertados'] . ' nuevos y ' . $stats['actualizados'] . ' actualizados.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('swal', icon: 'error', title: 'Error', text: 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.archivos', [
            'archivos' => Archivo::latest()
                ->where('info_estudiantes', 'like', "%{$this->buscar}%")
                ->get(),
        ]);
    }
}