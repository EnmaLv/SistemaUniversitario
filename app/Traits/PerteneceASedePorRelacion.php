<?php

namespace App\Traits;

use App\Support\Sedes\SedeActiva;
use Illuminate\Database\Eloquent\Builder;

trait PerteneceASedePorRelacion
{
    public static function bootPerteneceASedePorRelacion(): void
    {
        static::addGlobalScope('sede', function (Builder $consulta) {
            if (SedeActiva::id() === null) {
                return;
            }

            $consulta->whereHas($consulta->getModel()->relacionSede(), function (Builder $relacionada) {
                $modelo = $relacionada->getModel();

                if (method_exists($modelo, 'sedeComoAmbitoGlobal') && ! $modelo->sedeComoAmbitoGlobal()) {
                    $relacionada->deSedeActiva();
                }
            });
        });
    }

    public function relacionSede(): string
    {
        return $this->relacionSede;
    }

    public function scopeTodasLasSedes(Builder $consulta): Builder
    {
        return $consulta->withoutGlobalScope('sede');
    }
}
