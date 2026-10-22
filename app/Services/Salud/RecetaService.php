<?php

namespace App\Services\Salud;

use App\Models\Producto;
use App\Models\salud\Consulta;
use App\Models\salud\RecetasMedica;
use App\Models\salud\UnidadDosis;
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

    public function productosSaludQuery(): Builder
    {
        return Producto::where('status_id', Producto::STATUS_ACTIVO)
            ->whereHas('categoria', fn($q) => $q->where('tpr_id', self::TIPO_PRODUCTO_SALUD));
    }

    public function __construct(private readonly ConversorDosis $conversor) {}

    public function productosSalud(): Collection
    {
        return $this->productosSaludQuery()->with('unidad')->orderBy('nombre_pdt')->get();
    }

    public function unidadesDosis(): Collection
    {
        return UnidadDosis::catalogo();
    }

    /**
     * Medicamentos con la unidad de dosis sugerida y el factor de conversión a su unidad de inventario.
     */
    public function productosParaFormulario(?Collection $unidadesDosis = null): array
    {
        $unidadesDosis ??= $this->unidadesDosis();

        return $this->productosSalud()->map(fn (Producto $producto) => [
            'id'           => (string) $producto->id_pdt,
            'nombre'       => $producto->nombre_pdt,
            'unidad_dosis' => $this->conversor->unidadDosisSugerida($producto) ?? '',
            'unidad_base'  => $producto->unidad?->abreviatura_unm ?: ($producto->unidad?->nombre_unm ?? ''),
            'factores'     => $unidadesDosis->mapWithKeys(fn ($unidad) => [
                $unidad->nombre_udo => $this->conversor->factor($producto, $unidad->nombre_udo),
            ])->all(),
        ])->values()->all();
    }

    /**
     * Unidades de dosis aceptadas: el catálogo activo más las ya guardadas en la receta.
     */
    public function unidadesDosisPermitidas(?Consulta $consulta = null): array
    {
        $existentes = $consulta?->receta
            ? $consulta->receta->detalles()->pluck('unidad_prescrita_drm')->filter()->all()
            : [];

        return array_values(array_unique(array_merge($this->unidadesDosis()->pluck('nombre_udo')->all(), $existentes)));
    }

    /**
     * Estado inicial que consume Alpine.js en la vista de recetación.
     */
    public function estadoInicialFormulario(?RecetasMedica $receta): array
    {
        return [
            'fecha'       => old('fecha', optional($receta?->fecha_emision_rme)->format('Y-m-d') ?? now()->format('Y-m-d')),
            'vigencia'    => old('vigencia', optional($receta?->fecha_vigencia_rme)->format('Y-m-d') ?? ''),
            'descripcion' => old('descripcion', $receta?->descripcion_rme ?? ''),
            'detalles'    => old('detalles', $receta ? $this->detallesParaFormulario($receta) : []),
        ];
    }

    private function detallesParaFormulario(RecetasMedica $receta): array
    {
        return $receta->detalles->map(fn($det) => [
            'producto_id'     => $det->pdt_id,
            'producto_nombre' => optional($det->producto)->nombre_pdt ?? '',
            'cantidad'        => $det->cantidad_prescrita_drm,
            'unidad_dosis'    => $det->unidad_prescrita_drm,
            'frecuencia'      => $det->frecuencia_drm,
            'fecha_inicio'    => $this->formatearFecha($det->fecha_inicio_drm),
            'fecha_fin'       => $this->formatearFecha($det->fecha_fin_drm),
            'observaciones'   => $det->instrucciones_drm ?? '',
        ])->toArray();
    }

    private function formatearFecha($fecha): string
    {
        return $fecha ? Carbon::parse($fecha)->format('Y-m-d') : '';
    }

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
            ['cme_id' => $consulta->id_cme],
            [
                'paciente_per_id'    => $consulta->pacienteIdParaReceta(),
                'medico_per_id'      => $consulta->medicoIdParaReceta($usuarioId),
                'fecha_emision_rme'  => $datos['fecha'],
                'fecha_vigencia_rme' => $datos['vigencia'] ?? null,
                'descripcion_rme'    => $datos['descripcion'] ?? null,
                'estado_rme'         => Consulta::RECETA_ABIERTA,
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
        $productos = Producto::whereIn('id_pdt', collect($datos['detalles'])->pluck('producto_id'))
            ->get()
            ->keyBy('id_pdt');

        foreach ($datos['detalles'] as $item) {
            $producto = $productos->get((int) $item['producto_id']);

            $receta->detalles()->create([
                'pdt_id'                 => $item['producto_id'],
                'unm_id'                 => $producto->unm_id,
                'cantidad_drm'           => 0,
                'cantidad_prescrita_drm' => $item['cantidad'],
                'unidad_prescrita_drm'   => $item['unidad_dosis'],
                'equivalencia_ml_drm'    => $item['equivalencia_ml_drm'] ?? 0,
                'frecuencia_drm'         => $item['frecuencia'],
                'fecha_inicio_drm'       => $item['fecha_inicio'] ?? $datos['fecha'],
                'fecha_fin_drm'          => $item['fecha_fin'] ?? $datos['vigencia'] ?? $datos['fecha'],
                'instrucciones_drm'      => $item['observaciones'] ?? null,
            ]);
        }
    }
}
