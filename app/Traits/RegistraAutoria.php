<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait RegistraAutoria
{
    public const STATUS_ACTIVO = 1;

    public const STATUS_INACTIVO = 2;

    public static function bootRegistraAutoria(): void
    {
        static::creating(function ($modelo) {
            $id = Auth::id();

            if ($id && empty($modelo->created_id)) {
                $modelo->created_id = $id;
            }
        });

        static::updating(function ($modelo) {
            if ($id = Auth::id()) {
                $modelo->updated_id = $id;
            }
        });
    }

    public function scopeActivos($consulta)
    {
        return $consulta->where($this->qualifyColumn('status_id'), self::STATUS_ACTIVO);
    }

    public function estaActivo(): bool
    {
        return (int) $this->status_id === self::STATUS_ACTIVO;
    }

    public function alternarStatus(): static
    {
        $this->status_id = $this->estaActivo() ? self::STATUS_INACTIVO : self::STATUS_ACTIVO;
        $this->save();

        return $this;
    }
}
