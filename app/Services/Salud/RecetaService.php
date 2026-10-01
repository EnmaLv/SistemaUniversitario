<?php

namespace App\Services\Salud;

use App\Models\Producto;
use App\Models\Unidad;
use App\Models\salud\Consulta;
use App\Models\salud\RecetasMedica;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Paso 2: lógica de la receta médica.
 */
class RecetaService
{
    public const TIPO_PRODUCTO_SALUD = 2;

    /* ──────────────────────────────────────────────────────────────
     |  Catálogos del formulario
     ────────────────────────────────────────────────────────────── */

    public function productosSaludQuery(): Builder
    {
        return Producto::where('estado', true)
            ->whereHas('categoria', fn($q) => $q->where('tipo_producto_id', self::TIPO_PRODUCTO_SALUD));
    }

    public function productosSalud(): Collection
    {
        return $this->productosSaludQuery()->orderBy('nombre')->get();
    }

    public function unidades(): Collection
    {
        return Unidad::all();
    }

    /**
     * Estado inicial que consume Alpine.js en la vista de recetación.
     */
    public function estadoInicialFormulario(?RecetasMedica $receta): array
    {
        return [
            'fecha'       => old('fecha', optional($receta?->fecha)->format('Y-m-d') ?? now()->format('Y-m-d')),
            'vigencia'    => old('vigencia', optional($receta?->vigencia)->format('Y-m-d') ?? ''),
            'descripcion' => old('descripcion', $receta?->descripcion ?? ''),
            'detalles'    => old('detalles', $receta ? $this->detallesParaFormulario($receta) : []),
        ];
    }

    private function detallesParaFormulario(RecetasMedica $receta): array
    {
        return $receta->detalles->map(fn($det) => [
            'producto_id'     => $det->producto_id,
            'producto_nombre' => optional($det->producto)->nombre ?? '',
            'cantidad'        => $det->cantidad,
            'unidad_id'       => $det->unidad_id,
            'frecuencia'      => $det->frecuencia,
            'fecha_inicio'    => $this->formatearFecha($det->fecha_inicio),
            'fecha_fin'       => $this->formatearFecha($det->fecha_fin),
            'observaciones'   => $det->observaciones ?? '',
        ])->toArray();
    }

    private function formatearFecha($fecha): string
    {
        return $fecha ? Carbon::parse($fecha)->format('Y-m-d') : '';
    }

    /* ──────────────────────────────────────────────────────────────
     |  Persistencia
     ────────────────────────────────────────────────────────────── */

    /**
     * Crea o actualiza la cabecera de la receta y reemplaza sus detalles.
     *
     * @param array $datos Datos validados por RecetaRequest
     */
    public function guardar(Consulta $consulta, array $datos, ?int $usuarioId): RecetasMedica
    {
        return DB::transaction(function () use ($consulta, $datos, $usuarioId) {
            $receta = $this->guardarCabecera($consulta, $datos, $usuarioId);

            $this->limpiarDetallesSinEntregas($receta);
            $this->crearDetalles($receta, $datos);

            return $receta;
        });
    }

    private function guardarCabecera(Consulta $consulta, array $datos, ?int $usuarioId): RecetasMedica
    {
        return $consulta->receta()->updateOrCreate(
            ['consulta_id' => $consulta->id],
            [
                'id_persona'  => $consulta->pacienteIdParaReceta(),
                'medico_id'   => $consulta->medicoIdParaReceta($usuarioId),
                'fecha'       => $datos['fecha'],
                'vigencia'    => $datos['vigencia'] ?? null,
                'descripcion' => $datos['descripcion'] ?? null,
                'estado'      => Consulta::RECETA_ABIERTA,
            ]
        );
    }

    /**
     * Borra los ítems previos solo si ninguno tiene dispensaciones.
     */
    private function limpiarDetallesSinEntregas(RecetasMedica $receta): void
    {
        $tieneDispensaciones = $receta->detalles()->whereHas('dispensaciones')->exists();

        if (!$tieneDispensaciones) {
            $receta->detalles()->delete();
        }
    }

    private function crearDetalles(RecetasMedica $receta, array $datos): void
    {
        $unidades = Unidad::whereIn('id', collect($datos['detalles'])->pluck('unidad_id'))
            ->get()
            ->keyBy('id');

        foreach ($datos['detalles'] as $item) {
            $unidad = $unidades->get((int) $item['unidad_id']);

            $receta->detalles()->create([
                'producto_id'        => $item['producto_id'],
                'unidad_id'          => $item['unidad_id'],
                'cantidad'           => 0,
                'cantidad_prescrita' => $item['cantidad'],
                'unidad_prescrita'   => $unidad->nombre ?? 'Unidad',
                'equivalencia_ml'    => $item['equivalencia_ml'] ?? 0,
                'frecuencia'         => $item['frecuencia'],
                'fecha_inicio'       => $item['fecha_inicio'] ?? $datos['fecha'],
                'fecha_fin'          => $item['fecha_fin'] ?? $datos['vigencia'] ?? $datos['fecha'],
                'observaciones'      => $item['observaciones'] ?? null,
            ]);
        }
    }
}
