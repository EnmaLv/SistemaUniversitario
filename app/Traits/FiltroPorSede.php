<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltroPorSede
{
    protected static function bootFiltroPorSede(): void
    {
        if (!auth()->check()) {
            return;
        }

        $usuario = auth()->user();
        $persona = $usuario->persona ?? null;
        $sedeId = $persona->id_sede ?? null;

        if (!$sedeId) {
            return;
        }

        $esAdmin = false;
        foreach ($usuario->roles ?? [] as $r) {
            if (isset($r->nombre) && in_array(mb_strtolower($r->nombre), ['administrador', 'secretaria de bienestar'])) {
                $esAdmin = true;
                break;
            }
        }

        if (!$esAdmin) {
            static::addGlobalScope('sede_scope', function (Builder $builder) use ($sedeId) {
                $builder->where($builder->getModel()->getTable() . '.sede_id', $sedeId);
            });
        }

        static::creating(function ($model) use ($sedeId) {
            if (empty($model->sede_id)) {
                $model->sede_id = $sedeId;
            }
        });
    }
}