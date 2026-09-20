<?php

namespace App\Services;

use App\Models\Becas\Beneficio;
use Illuminate\Support\Str;

class BecaBeneficioService
{
    public function listar(array $filters)
    {
        return Beneficio::query()
            ->when($filters['buscar'] ?? null, function ($query, $buscar) {
                $query->where('nombre_beneficio', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            })
            ->when(($filters['activo'] ?? '') !== '', function ($query) use ($filters) {
                $query->where('status', (bool) ($filters['activo'] ?? 1));
            })
            ->latest()
            ->paginate(10)
            ->appends($filters);
    }

    public function crear(array $data): Beneficio
    {
        return Beneficio::create($this->datosBeneficio($data));
    }

    public function actualizar(Beneficio $beneficio, array $data): Beneficio
    {
        $beneficio->update($this->datosBeneficio($data));

        return $beneficio->fresh();
    }

    public function cambiarEstado(Beneficio $beneficio): Beneficio
    {
        $beneficio->update(['status' => !$beneficio->status]);

        return $beneficio->fresh();
    }

    public function sincronizar(Beneficio $beca, array $beneficios): void
    {
        $sync = [];

        foreach ($beneficios as $beneficio) {
            if (empty($beneficio['id'])) {
                continue;
            }

            $sync[$beneficio['id']] = [
                'observacion' => $beneficio['observacion'] ?? null,
                'activo' => isset($beneficio['activo']),
            ];
        }

        $beca->beneficios()->sync($sync);
    }

    private function datosBeneficio(array $data): array
    {
        $nombre = $data['nombre_beneficio'];

        return [
            'nombre_beneficio' => $nombre,
            'descripcion' => $data['descripcion'] ?? null,
            'slug' => Str::slug($nombre),
            'cupones_disponibles' => 0,
            'cupones_ocupados' => 0,
            'status' => $data['status'] ?? false,
        ];
    }
}
