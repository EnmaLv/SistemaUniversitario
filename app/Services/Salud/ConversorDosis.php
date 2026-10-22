<?php

namespace App\Services\Salud;

use App\Models\Producto;
use App\Models\Unidad;
use App\Models\salud\DetalleRecetasMedica;
use App\Models\salud\UnidadDosis;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ConversorDosis
{
    public const MASA = 'masa';
    public const VOLUMEN = 'volumen';
    public const CONTEO = 'conteo';

    private const DIMENSIONES = [
        self::MASA    => ['g', 'gr', 'gramo', 'gramos', 'kg', 'kilo', 'kilos', 'kilogramo', 'kilogramos', 'mg', 'miligramo', 'miligramos'],
        self::VOLUMEN => ['ml', 'mililitro', 'mililitros', 'l', 'lt', 'litro', 'litros'],
        self::CONTEO  => [
            'u', 'und', 'unidad', 'unidades', 'tab', 'tableta', 'tabletas', 'past', 'pastilla', 'pastillas',
            'caps', 'capsula', 'capsulas', 'amp', 'ampolla', 'ampollas', 'sob', 'sobre', 'sobres',
            'sup', 'supositorio', 'supositorios',
        ],
    ];

    private const CONTEO_GENERICO = ['u', 'und', 'unidad', 'unidades'];

    private ?Collection $unidadesMedida = null;

    private ?Collection $unidadesDosis = null;

    public function factorDetalle(DetalleRecetasMedica $detalle): ?float
    {
        return $this->factor($detalle->producto, $detalle->unidad_prescrita_drm);
    }

    public function aInventario(DetalleRecetasMedica $detalle, float $cantidadDosis): ?float
    {
        $factor = $this->factorDetalle($detalle);

        return $factor === null ? null : round($cantidadDosis * $factor, 2);
    }

    public function factor(?Producto $producto, ?string $unidadDosis): ?float
    {
        $base = $producto?->unidad;

        if (! $base || blank($unidadDosis)) {
            return null;
        }

        $clavesDosis = $this->clavesDosis($unidadDosis);
        $clavesBase = $this->claves($base->nombre_unm, $base->abreviatura_unm);

        if (array_intersect($clavesDosis, $clavesBase)) {
            return 1.0;
        }

        $dimensionBase = $this->dimension($clavesBase);
        $dimensionDosis = $this->dimension($clavesDosis);

        if ($dimensionDosis === self::CONTEO) {
            return $dimensionBase === self::CONTEO && array_intersect($clavesBase, self::CONTEO_GENERICO) ? 1.0 : null;
        }

        if (! in_array($dimensionDosis, [self::MASA, self::VOLUMEN], true) || $dimensionDosis !== $dimensionBase) {
            return null;
        }

        $medidaDosis = $this->unidadMedida($clavesDosis);
        $factorDosis = (float) ($medidaDosis?->factor_conversion_unm ?? 0);
        $factorBase = (float) $base->factor_conversion_unm;

        if ($factorDosis <= 0 || $factorBase <= 0) {
            return null;
        }

        return $factorDosis / $factorBase;
    }

    public function factorEnvase(Producto $producto): float
    {
        $factor = $producto->enp_id
            ? (float) $producto->unidades_presentacion_pdt
            : (float) $producto->peso_contenido_pdt;

        return $factor > 0 ? $factor : 1.0;
    }

    public function unidadDosisSugerida(Producto $producto): ?string
    {
        if (blank($producto->presentacion_dispensacion_pdt)) {
            return null;
        }

        $claves = $this->claves($producto->presentacion_dispensacion_pdt);

        return $this->catalogoDosis()
            ->first(fn ($unidad) => array_intersect($claves, $this->claves($unidad->nombre_udo, $unidad->abreviatura_udo)))
            ?->nombre_udo;
    }

    public static function normalizar(?string $texto): string
    {
        return rtrim(mb_strtolower(trim(Str::ascii((string) $texto))), '.');
    }

    private function clavesDosis(string $unidadDosis): array
    {
        $claves = $this->claves($unidadDosis);
        $nombre = $claves[0] ?? '';
        $catalogo = $this->catalogoDosis()->first(fn ($unidad) => static::normalizar($unidad->nombre_udo) === $nombre);

        return $catalogo ? $this->claves($catalogo->nombre_udo, $catalogo->abreviatura_udo) : $claves;
    }

    private function claves(?string ...$textos): array
    {
        return array_values(array_unique(array_filter(array_map(fn ($texto) => static::normalizar($texto), $textos))));
    }

    private function dimension(array $claves): ?string
    {
        foreach (self::DIMENSIONES as $dimension => $nombres) {
            if (array_intersect($claves, $nombres)) {
                return $dimension;
            }
        }

        return null;
    }

    private function unidadMedida(array $claves): ?Unidad
    {
        $this->unidadesMedida ??= Unidad::all();

        return $this->unidadesMedida->first(
            fn ($unidad) => array_intersect($claves, $this->claves($unidad->nombre_unm, $unidad->abreviatura_unm))
        );
    }

    private function catalogoDosis(): Collection
    {
        return $this->unidadesDosis ??= UnidadDosis::all();
    }
}
