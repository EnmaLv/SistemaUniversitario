<?php

namespace App\Services\Solvencia\Verificadores;

use App\Models\Deporte\PrestamoDeportivo;
use App\Models\Deporte\SancionDeportiva;
use App\Models\Persona;
use App\Services\Deporte\PrestamoService;
use App\Services\Solvencia\VerificadorSolvencia;

class VerificadorDeportes implements VerificadorSolvencia
{
    public function __construct(private PrestamoService $prestamos)
    {
    }

    public function modulo(): string
    {
        return 'deporte';
    }

    public function etiqueta(): string
    {
        return 'Deportes';
    }

    public function verificar(Persona $persona): array
    {
        $this->prestamos->marcarVencidos((int) $persona->id_per);

        $sanciones = SancionDeportiva::todasLasSedes()
            ->with(['prestamo' => fn ($consulta) => $consulta->todasLasSedes(), 'prestamo.producto', 'prestamo.sede'])
            ->where('per_id', $persona->id_per)
            ->activas()
            ->orderBy('fecha_sancion_snd')
            ->get();

        $prestamos = PrestamoDeportivo::todasLasSedes()
            ->with(['producto', 'sede'])
            ->where('per_id', $persona->id_per)
            ->pendientes()
            ->orderBy('fecha_prestamo_prd')
            ->get();

        $pendientes = [];

        foreach ($sanciones as $sancion) {
            $pendientes[] = [
                'tipo'    => 'sancion',
                'titulo'  => 'Sanción activa #' . $sancion->id_snd,
                'detalle' => trim($sancion->motivo_snd . ' Implemento: ' . ($sancion->prestamo?->producto?->nombre_pdt ?? '—') . '.'),
                'fecha'   => $sancion->fecha_sancion_snd?->format('d/m/Y'),
            ];
        }

        foreach ($prestamos as $prestamo) {
            $pendientes[] = [
                'tipo'    => 'prestamo',
                'titulo'  => 'Préstamo #' . $prestamo->id_prd . ' (' . PrestamoDeportivo::etiquetaEstado($prestamo->estado_prd) . ')',
                'detalle' => sprintf(
                    '%s x%d, sede %s. Devolución esperada: %s.',
                    $prestamo->producto?->nombre_pdt ?? 'Implemento',
                    $prestamo->cantidad_prd,
                    $prestamo->sede?->nombre_sed ?? '—',
                    $prestamo->fecha_devolucion_esperada_prd?->format('d/m/Y H:i') ?? '—'
                ),
                'fecha'   => $prestamo->fecha_prestamo_prd?->format('d/m/Y'),
            ];
        }

        return [
            'solvente'       => $pendientes === [],
            'bloquea_titulo' => $sanciones->isNotEmpty(),
            'pendientes'     => $pendientes,
        ];
    }
}
