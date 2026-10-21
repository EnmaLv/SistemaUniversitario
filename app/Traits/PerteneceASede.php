<?php

namespace App\Traits;

use App\Support\Sedes\SedeActiva;
use Illuminate\Database\Eloquent\Builder;

trait PerteneceASede
{
    public static function bootPerteneceASede(): void
    {
        if ((new \ReflectionClass(static::class))->getDefaultProperties()['sedeSoloExplicita'] ?? false) {
            static::creating(fn ($modelo) => $modelo->asignarSedeActiva());

            return;
        }

        static::addGlobalScope('sede', function (Builder $consulta) {
            $sede = SedeActiva::id();

            if ($sede !== null) {
                $consulta->where($consulta->getModel()->qualifyColumn($consulta->getModel()->columnaSede()), $sede);
            }
        });

        static::creating(fn ($modelo) => $modelo->asignarSedeActiva());
    }

    public function columnaSede(): string
    {
        return property_exists($this, 'columnaSede') ? $this->columnaSede : 'sed_id';
    }

    public function sedeComoAmbitoGlobal(): bool
    {
        return ! property_exists($this, 'sedeSoloExplicita') || ! $this->sedeSoloExplicita;
    }

    public function scopeDeSedeActiva(Builder $consulta): Builder
    {
        return SedeActiva::filtrar($consulta, $this->qualifyColumn($this->columnaSede()));
    }

    public function scopeDeSede(Builder $consulta, ?int $sedeId): Builder
    {
        return $sedeId === null ? $consulta : $consulta->where($this->qualifyColumn($this->columnaSede()), $sedeId);
    }

    public function scopeTodasLasSedes(Builder $consulta): Builder
    {
        return $consulta->withoutGlobalScope('sede');
    }

    protected function asignarSedeActiva(): void
    {
        $columna = $this->columnaSede();

        if (empty($this->{$columna}) && ($sede = SedeActiva::idParaCrear())) {
            $this->{$columna} = $sede;
        }
    }
}
