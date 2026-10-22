<?php

namespace App\Support\Inventario;

use App\Models\Producto;
use App\Models\TipoProducto;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UnidadCompra
{
    public const MODO_PESO = 'peso';

    public const MODO_ENVASE = 'envase';

    public const MODO_UNIDAD = 'unidad';

    private const UNIDADES_PESO = ['g', 'kg', 'mg', 'gramo', 'gramos', 'kilo', 'kilos', 'kilogramo', 'kilogramos', 'miligramo', 'miligramos'];

    private const UNIDADES_VOLUMEN = ['ml', 'l', 'lt', 'lts', 'litro', 'litros', 'mililitro', 'mililitros'];

    private const DISPENSACION_CONTABLE = ['pastilla', 'cápsula', 'capsula', 'tableta'];

    public function __construct(
        public readonly int $productoId,
        public readonly string $modo,
        public readonly ?string $tipo,
        public readonly string $unidad,
        public readonly string $unidadPlural,
        public readonly float $factor,
        public readonly string $unidadBase,
        public readonly ?int $unidadId,
        public readonly string $nombreProducto,
    ) {
    }

    public static function de(Producto|int $producto): self
    {
        $id = $producto instanceof Producto ? (int) $producto->id_pdt : $producto;

        $fila = DB::table('producto')
            ->leftJoin('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
            ->leftJoin('tipo_producto', 'tipo_producto.id_tpr', '=', 'categoria_producto.tpr_id')
            ->leftJoin('unidad_medida', 'unidad_medida.id_unm', '=', 'producto.unm_id')
            ->leftJoin('envase_primario', 'envase_primario.id_enp', '=', 'producto.enp_id')
            ->where('producto.id_pdt', $id)
            ->select(
                'producto.id_pdt',
                'producto.nombre_pdt',
                'producto.unm_id',
                'producto.enp_id',
                'producto.peso_contenido_pdt',
                'producto.unidades_presentacion_pdt',
                'producto.presentacion_dispensacion_pdt',
                'tipo_producto.nombre_tpr',
                'unidad_medida.nombre_unm',
                'unidad_medida.abreviatura_unm',
                'unidad_medida.factor_conversion_unm',
                'envase_primario.nombre_enp'
            )
            ->first();

        if (! $fila) {
            throw new InvalidArgumentException('El producto elegido no existe.');
        }

        return static::desdeFila($fila);
    }

    public static function desdeFila(object $fila): self
    {
        $tipo = $fila->nombre_tpr ?? null;
        $tipoNormalizado = $tipo === null ? null : mb_strtolower($tipo);
        $abreviatura = mb_strtolower(trim((string) ($fila->abreviatura_unm ?? '')));
        $nombreUnidad = mb_strtolower(trim((string) ($fila->nombre_unm ?? '')));
        $esPeso = in_array($abreviatura, self::UNIDADES_PESO, true) || in_array($nombreUnidad, self::UNIDADES_PESO, true);
        $esVolumen = in_array($abreviatura, self::UNIDADES_VOLUMEN, true) || in_array($nombreUnidad, self::UNIDADES_VOLUMEN, true);
        $porPresentacion = (float) ($fila->unidades_presentacion_pdt ?? 0);
        $tienePresentacion = ! empty($fila->enp_id) || $porPresentacion > 1;
        $nombreEnvase = $fila->nombre_enp ? mb_strtolower(trim($fila->nombre_enp)) : null;

        $modo = match ($tipoNormalizado) {
            mb_strtolower(TipoProducto::ALIMENTO) => ($esPeso || $esVolumen) ? self::MODO_PESO : ($tienePresentacion ? self::MODO_ENVASE : self::MODO_UNIDAD),
            mb_strtolower(TipoProducto::MEDICAMENTO) => self::MODO_ENVASE,
            mb_strtolower(TipoProducto::IMPLEMENTO_DEPORTIVO), mb_strtolower(TipoProducto::MATERIAL_OFICINA) => $tienePresentacion ? self::MODO_ENVASE : self::MODO_UNIDAD,
            default => ! empty($fila->enp_id) ? self::MODO_ENVASE : (($esPeso || $esVolumen) ? self::MODO_PESO : self::MODO_UNIDAD),
        };

        if ($modo === self::MODO_PESO) {
            $factor = (float) ($fila->factor_conversion_unm ?? 0);

            return new self(
                (int) $fila->id_pdt,
                $modo,
                $tipo,
                $abreviatura !== '' ? $abreviatura : $nombreUnidad,
                $abreviatura !== '' ? $abreviatura : $nombreUnidad,
                $factor > 0 ? $factor : 1.0,
                $esVolumen ? 'ml' : 'g',
                $fila->unm_id === null ? null : (int) $fila->unm_id,
                (string) $fila->nombre_pdt,
            );
        }

        if ($modo === self::MODO_ENVASE) {
            $envase = $nombreEnvase ?? (mb_strtolower(TipoProducto::MEDICAMENTO) === $tipoNormalizado ? 'envase' : 'caja');

            return new self(
                (int) $fila->id_pdt,
                $modo,
                $tipo,
                $envase,
                static::plural($envase),
                $porPresentacion > 0 ? $porPresentacion : 1.0,
                static::unidadMinima($fila, $esPeso || $esVolumen ? $abreviatura : null),
                $fila->unm_id === null ? null : (int) $fila->unm_id,
                (string) $fila->nombre_pdt,
            );
        }

        $peso = (float) ($fila->peso_contenido_pdt ?? 0);
        $alimentoConPeso = $tipoNormalizado === mb_strtolower(TipoProducto::ALIMENTO) && $peso > 0;

        return new self(
            (int) $fila->id_pdt,
            $modo,
            $tipo,
            'unidad',
            'unidades',
            $alimentoConPeso ? $peso : 1.0,
            $alimentoConPeso ? 'g' : 'unidades',
            $fila->unm_id === null ? null : (int) $fila->unm_id,
            (string) $fila->nombre_pdt,
        );
    }

    public function inventarioEnUnidades(): bool
    {
        return TipoProducto::cuentaPorUnidad($this->tipo);
    }

    public function fraccionable(): bool
    {
        return $this->modo === self::MODO_PESO;
    }

    public function convertir(float $cantidad): float
    {
        return round($cantidad * $this->factor, 2);
    }

    public function etiquetaCantidad(): string
    {
        return match ($this->modo) {
            self::MODO_PESO => "Cantidad ({$this->unidad})",
            self::MODO_ENVASE => 'Cantidad de ' . $this->unidadPlural,
            default => 'Cantidad (unidades)',
        };
    }

    public function placeholder(): string
    {
        return match ($this->modo) {
            self::MODO_PESO => 'Ej: 7.5',
            self::MODO_ENVASE => 'Ej: 5',
            default => 'Ej: 10',
        };
    }

    public function paso(): string
    {
        return $this->fraccionable() ? '0.01' : '1';
    }

    public function minimo(): string
    {
        return $this->fraccionable() ? '0.01' : '1';
    }

    public function ayuda(): string
    {
        return match ($this->modo) {
            self::MODO_PESO => "Este producto se compra por peso o volumen. Escribe cuántos {$this->unidad} compras; puedes usar decimales (por ejemplo 7.5).",
            self::MODO_ENVASE => 'Cada ' . $this->unidad . ' trae ' . static::numero($this->factor) . ' ' . $this->unidadBase . '. Escribe cuántos ' . $this->unidadPlural . ' compras (número entero).',
            default => $this->unidadBase === 'g'
                ? 'Escribe cuántas unidades compras (número entero). Cada unidad equivale a ' . static::numero($this->factor) . ' g en el inventario.'
                : 'Escribe cuántas unidades compras (número entero).',
        };
    }

    public function precioPor(): string
    {
        return 'por ' . $this->unidad;
    }

    public function resumen(float $cantidad): string
    {
        $convertida = $this->convertir($cantidad);

        return match ($this->modo) {
            self::MODO_PESO => static::numero($cantidad) . " {$this->unidad} = " . static::numero($convertida) . " {$this->unidadBase} en el inventario",
            self::MODO_ENVASE => static::numero($cantidad) . ' ' . ($cantidad == 1 ? $this->unidad : $this->unidadPlural) . ' × ' . static::numero($this->factor) . " {$this->unidadBase} = " . static::numero($convertida) . " {$this->unidadBase}",
            default => $this->unidadBase === 'g'
                ? static::numero($cantidad) . ' unidades = ' . static::numero($convertida) . ' g en el inventario'
                : static::numero($cantidad) . ($cantidad == 1 ? ' unidad' : ' unidades'),
        };
    }

    public function describirCantidad(float $cantidad): string
    {
        return static::numero($cantidad) . ' ' . ($this->fraccionable() || $cantidad == 1 ? $this->unidad : $this->unidadPlural);
    }

    public function aArreglo(): array
    {
        return [
            'producto_id'     => $this->productoId,
            'producto'        => $this->nombreProducto,
            'modo'            => $this->modo,
            'tipo'            => $this->tipo,
            'unidad'          => $this->unidad,
            'unidad_plural'   => $this->unidadPlural,
            'factor'          => $this->factor,
            'unidad_base'     => $this->unidadBase,
            'fraccionable'    => $this->fraccionable(),
            'etiqueta'        => $this->etiquetaCantidad(),
            'placeholder'     => $this->placeholder(),
            'paso'            => $this->paso(),
            'minimo'          => $this->minimo(),
            'ayuda'           => $this->ayuda(),
            'precio_por'      => $this->precioPor(),
        ];
    }

    public static function numero(float $valor): string
    {
        $texto = number_format($valor, 2, ',', '.');

        return str_contains($texto, ',') ? rtrim(rtrim($texto, '0'), ',') : $texto;
    }

    private static function unidadMinima(object $fila, ?string $abreviaturaMedida): string
    {
        $dispensacion = mb_strtolower(trim((string) ($fila->presentacion_dispensacion_pdt ?? '')));

        if (in_array($dispensacion, self::DISPENSACION_CONTABLE, true)) {
            return $dispensacion . 's';
        }

        if ($abreviaturaMedida !== null && $abreviaturaMedida !== '') {
            return $abreviaturaMedida;
        }

        if ($dispensacion === 'ml') {
            return 'ml';
        }

        return 'unidades';
    }

    private static function plural(string $nombre): string
    {
        $partes = explode(' ', $nombre, 2);
        $palabra = $partes[0];
        $palabra .= preg_match('/[aeiouáéíóú]$/u', $palabra) ? 's' : 'es';

        return isset($partes[1]) ? $palabra . ' ' . $partes[1] : $palabra;
    }
}
