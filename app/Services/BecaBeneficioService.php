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
                    $query->where('nombre_beneficio', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->where('status', $activo === '1')
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
