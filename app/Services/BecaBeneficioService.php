<?php

namespace App\Services;

use App\Models\Becas\Beneficio;
use Illuminate\Support\Str;

class BecaBeneficioService
{
    public function listar(array $filters)
    {
        $activo = array_key_exists('activo', $filters)
            ? (string) $filters['activo']
            : '1';

        return Beneficio::query()
            ->when($filters['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre_beneficio_bes', 'like', "%{$buscar}%")
                        ->orWhere('descripcion_bes', 'like', "%{$buscar}%");
                });
            })
            ->where('status_id', $activo === '1' ? Beneficio::STATUS_ACTIVO : Beneficio::STATUS_INACTIVO)
            ->latest()
            ->paginate(10)
            ->appends($filters);
    }

    public function crear(array $data): Beneficio
    {
        return Beneficio::create($this->datosBeneficio($data) + [
            'cupones_disponibles_bes' => 0,
            'cupones_ocupados_bes'    => 0,
        ]);
    }

    public function actualizar(Beneficio $beneficio, array $data): Beneficio
    {
        $beneficio->update($this->datosBeneficio($data));

        return $beneficio->fresh();
    }

    public function cambiarEstado(Beneficio $beneficio): Beneficio
    {
        return $beneficio->alternarStatus();
    }

    private function datosBeneficio(array $data): array
    {
        $nombre = $data['nombre_beneficio'];

        return [
            'nombre_beneficio_bes' => $nombre,
            'descripcion_bes'      => $data['descripcion'] ?? null,
            'slug_bes'             => Str::slug($nombre),
            'status_id'            => !empty($data['status']) ? Beneficio::STATUS_ACTIVO : Beneficio::STATUS_INACTIVO,
        ];
    }
}
