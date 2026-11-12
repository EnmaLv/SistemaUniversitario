<?php

namespace App\Services\Salud;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Throwable;

/**
 * Arma los listados del panel estadístico de Salud.
 *
 * Devuelve una "definición de reporte" (columnas + filas + resumen) que
 * consumen por igual la vista PDF y el exportador de Excel, de modo que
 * ambos formatos muestran siempre los mismos datos.
 */
class ListadoSaludService
{
    public const CONSULTAS = 'consultas';
    public const DISPENSACIONES = 'dispensaciones';

    public const TIPOS = [self::CONSULTAS, self::DISPENSACIONES];

    /** Relaciones adicionales que necesita cada listado (evita consultas N+1) */
    public const RELACIONES = [
        self::CONSULTAS => [],
        self::DISPENSACIONES => [
            'receta.detalles.dispensaciones.lote',
            'receta.detalles.dispensaciones.usuario.persona',
        ],
    ];

    private const RECETA_CERRADA = 2;

    public const ESTADO_COMPLETADA = 'Completada';
    public const ESTADO_EN_PROCESO = 'En proceso';
    public const ESTADO_SIN_RECETA = 'Sin receta';

    /**
     * @param iterable $consultas Consultas ya filtradas (las mismas del panel)
     */
    public function construir(string $tipo, iterable $consultas): array
    {
        return match ($tipo) {
            self::CONSULTAS      => $this->consultas($consultas),
            self::DISPENSACIONES => $this->dispensaciones($consultas),
            default => throw new InvalidArgumentException("Listado no soportado: {$tipo}"),
        };
    }

    /* ──────────────────────────────────────────────────────────────
     |  Listado de consultas
     ────────────────────────────────────────────────────────────── */

    private function consultas(iterable $consultas): array
    {
        $filas = [];
        $pacientes = [];
        $porEstado = [
            self::ESTADO_COMPLETADA => 0,
            self::ESTADO_EN_PROCESO => 0,
            self::ESTADO_SIN_RECETA => 0,
        ];

        foreach ($consultas as $c) {
            $estado = $this->estadoConsulta($c);
            $porEstado[$estado]++;

            if ($c->paciente) {
                $pacientes[$c->paciente->id_per ?? spl_object_id($c->paciente)] = true;
            }

            $enfermedades = [];
            foreach ($c->enfermedades ?? [] as $enfermedad) {
                $enfermedades[] = $enfermedad->nombre_efm ?? 'Sin nombre';
            }

            // Calcular edad
            $edad = null;
            if ($c->paciente?->fecha_nacimiento_per) {
                $fechaNac = \Carbon\Carbon::parse($c->paciente->fecha_nacimiento_per);
                $fechaRef = $c->fecha_cme ? \Carbon\Carbon::parse($c->fecha_cme) : now();
                $edad = (int) $fechaNac->diffInYears($fechaRef);
            }

            // Obtener PNF (usando el nuevo método)
            $pnf = $c->paciente?->pnfActual() ?? '—';

            $filas[] = [
                'fecha'        => $this->fecha($c->fecha_cme),
                'cedula'       => $c->paciente->cedula_per ?? null,
                'paciente'     => $this->nombre($c->paciente),
                'edad'         => $edad,
                'genero'       => $c->paciente->genero_per ?? null,
                'pnf'          => $pnf,
                'perfil'       => $c->paciente?->perfil?->nombre_perfil,
                'medico'       => $this->nombre($c->medico),
                'consultorio'  => $c->consultorio->nombre_csr ?? null,
                'motivo'       => $c->motivo_cme,
                'diagnostico'  => $c->diagnostico_cme,
                'enfermedades' => implode(', ', $enfermedades) ?: null,
                'estado'       => $estado,
            ];
        }

        return [
            'tipo'        => self::CONSULTAS,
            'titulo'      => 'Listado de consultas',
            'descripcion' => 'Consultas médicas registradas en el período',
            'archivo'     => 'listado-consultas',
            'hoja'        => 'Consultas',
            'vacio'       => 'No hay consultas en el período con los filtros aplicados.',
            'columnas'    => [
                $this->columna('fecha', 'Fecha', 'fecha', 6, 12),
                $this->columna('cedula', 'Cédula', 'texto', 6, 14),
                $this->columna('paciente', 'Paciente', 'texto', 12, 30, true),
                $this->columna('edad', 'Edad', 'entero', 4, 8),
                $this->columna('genero', 'Género', 'texto', 5, 12),
                $this->columna('pnf', 'PNF', 'texto', 9, 25),
                $this->columna('perfil', 'Perfil', 'texto', 7, 18),
                $this->columna('medico', 'Médico', 'texto', 10, 28),
                $this->columna('consultorio', 'Consultorio', 'texto', 8, 22),
                $this->columna('motivo', 'Motivo', 'texto', 11, 32),
                $this->columna('diagnostico', 'Diagnóstico', 'texto', 11, 32),
                $this->columna('enfermedades', 'Enfermedades', 'texto', 10, 32),
                $this->columna('estado', 'Estado', 'estado', 6, 14),
            ],
            'filas'   => $filas,
            'resumen' => [
                ['Consultas', count($filas)],
                ['Pacientes', count($pacientes)],
                ['Completadas', $porEstado[self::ESTADO_COMPLETADA]],
                ['En proceso', $porEstado[self::ESTADO_EN_PROCESO]],
                ['Sin receta', $porEstado[self::ESTADO_SIN_RECETA]],
            ],
            'anexo' => null,
        ];
    }

    /* ──────────────────────────────────────────────────────────────
     |  Listado de medicamentos dispensados
     ────────────────────────────────────────────────────────────── */
    private function dispensaciones(iterable $consultas): array
    {
        $filas = [];
        $pacientes = [];
        $porMedicamento = [];

        foreach ($consultas as $c) {
            foreach ($c->receta->detalles ?? [] as $detalle) {
                $medicamento = $detalle->producto->nombre_pdt ?? ('Producto #' . $detalle->pdt_id);
                $unidad = $detalle->unidad->nombre_udo ?? null;

                foreach ($detalle->dispensaciones ?? [] as $dispensacion) {
                    $cantidad = (float) $dispensacion->cantidad_dsm;

                    $filas[] = [
                        'fecha'       => $this->fecha($dispensacion->created_at),
                        'cedula'      => $c->paciente->cedula_per ?? null,
                        'paciente'    => $this->nombre($c->paciente),
                        'medicamento' => $medicamento,
                        'cantidad'    => $cantidad,
                        'unidad'      => $unidad,
                        'lote'        => $dispensacion->lote->codigo_lote ?? null,
                        'entrego'     => $this->nombre($dispensacion->usuario?->persona),
                    ];

                    if ($c->paciente) {
                        $pacientes[$c->paciente->id_per ?? spl_object_id($c->paciente)] = true;
                    }

                    $clave = $detalle->pdt_id . '|' . ($detalle->udo_id ?? '');
                    $porMedicamento[$clave] ??= [
                        'medicamento' => $medicamento,
                        'unidad'      => $unidad,
                        'entregas'    => 0,
                        'cantidad'    => 0.0,
                    ];
                    $porMedicamento[$clave]['entregas']++;
                    $porMedicamento[$clave]['cantidad'] += $cantidad;
                }
            }
        }

        // Más recientes primero
        usort($filas, fn($a, $b) => [$b['fecha'], $a['paciente']] <=> [$a['fecha'], $b['paciente']]);

        // Los medicamentos con más entregas primero
        $totales = array_values($porMedicamento);
        usort($totales, fn($a, $b) => [$b['entregas'], $a['medicamento']] <=> [$a['entregas'], $b['medicamento']]);

        return [
            'tipo'        => self::DISPENSACIONES,
            'titulo'      => 'Listado de medicamentos dispensados',
            'descripcion' => 'Entregas de medicamentos de las consultas del período',
            'archivo'     => 'listado-medicamentos-dispensados',
            'hoja'        => 'Dispensaciones',
            'vacio'       => 'No hay medicamentos dispensados en el período con los filtros aplicados.',
            'columnas'    => [
                $this->columna('fecha', 'Fecha de entrega', 'fecha', 10, 16),
                $this->columna('cedula', 'Cédula', 'texto', 9, 14),
                $this->columna('paciente', 'Paciente', 'texto', 19, 32),
                $this->columna('medicamento', 'Medicamento', 'texto', 21, 36, true),
                $this->columna('cantidad', 'Cantidad', 'numero', 8, 12),
                $this->columna('unidad', 'Unidad', 'texto', 8, 12),
                $this->columna('lote', 'Lote', 'texto', 10, 16),
                $this->columna('entrego', 'Entregado por', 'texto', 15, 30),
            ],
            'filas'   => $filas,
            'resumen' => [
                ['Entregas', count($filas)],
                ['Pacientes', count($pacientes)],
                ['Medicamentos distintos', count($totales)],
            ],
            'anexo' => $totales ? [
                'titulo'   => 'Totales por medicamento',
                'hoja'     => 'Por medicamento',
                'columnas' => [
                    $this->columna('medicamento', 'Medicamento', 'texto', 50, 40, true),
                    $this->columna('entregas', 'Entregas', 'entero', 15, 12),
                    $this->columna('cantidad', 'Cantidad total', 'numero', 20, 16),
                    $this->columna('unidad', 'Unidad', 'texto', 15, 12),
                ],
                'filas' => $totales,
            ] : null,
        ];
    }

    /* ──────────────────────────────────────────────────────────────
     |  Utilidades
     ────────────────────────────────────────────────────────────── */

    /**
     * @param string $tipo    texto | fecha | numero | entero | estado
     * @param int    $ancho   ancho en el PDF (porcentaje)
     * @param int    $excel   ancho en Excel (caracteres)
     * @param bool   $fuerte  resalta la columna principal en el PDF
     */
    private function columna(string $clave, string $titulo, string $tipo, int $ancho, int $excel, bool $fuerte = false): array
    {
        return compact('clave', 'titulo', 'tipo', 'ancho', 'excel', 'fuerte');
    }

    private function estadoConsulta(object $consulta): string
    {
        if (!$consulta->receta) {
            return self::ESTADO_SIN_RECETA;
        }

        return (int) $consulta->receta->estado_rme === self::RECETA_CERRADA
            ? self::ESTADO_COMPLETADA
            : self::ESTADO_EN_PROCESO;
    }

    private function nombre(?object $persona): ?string
    {
        if (!$persona) {
            return null;
        }

        return trim(($persona->nombres_per ?? '') . ' ' . ($persona->apellidos_per ?? '')) ?: null;
    }

    /**
     * Acepta fechas ya convertidas (Carbon) o cadenas de la base de datos.
     */
    private function fecha(mixed $valor): ?DateTimeInterface
    {
        if ($valor instanceof DateTimeInterface) {
            return $valor;
        }

        if (!$valor) {
            return null;
        }

        try {
            return new DateTimeImmutable((string) $valor);
        } catch (Throwable) {
            return null;
        }
    }
}
