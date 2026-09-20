<?php

namespace App\Services;

use App\Models\Becas\Beca;
use App\Services\BecaTutorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BecaService
{
    public function __construct(
        private BecaBeneficioService $beneficioService,
        private BecaAsignacionService $asignacionService,
        private BecaTutorService $tutorService,
    ) {
    }

    public function crear(array $data): Beca
    {
        return DB::transaction(function () use ($data) {
            $beca = Beca::create($this->datosBeca($data) + [
                'codigo' => $this->generarCodigoUnico(),
            ]);

            $this->beneficioService->sincronizar($beca, $data['beneficios'] ?? []);
            $this->tutorService->sincronizar($beca, $data['tutores'] ?? []);
            $this->asignacionService->sincronizar($beca, $data['asignaciones'] ?? []);

            // sincronizar preguntas (si vienen)
            if (!empty($data['preguntas']) && is_array($data['preguntas'])) {
                // crear preguntas nuevas relacionadas a la beca
                foreach ($data['preguntas'] as $p) {
                    if (empty($p['texto'])) continue;
                    $beca->preguntas()->create([
                        'texto' => $p['texto'],
                        'tipo' => $p['tipo'] ?? 'text',
                        'min' => $p['min'] ?? null,
                        'max' => $p['max'] ?? null,
                    ]);
                }
            }

            return $beca->load(['beneficios', 'tutores.tutor', 'asignacionesTrabajo.tutor', 'preguntas']);
        });
    }

    public function actualizar(Beca $beca, array $data): array
    {
        return DB::transaction(function () use ($beca, $data) {
            $beneficiosAntes = $beca->beneficios()->pluck('be_beneficios.id')->sort()->values()->all();

            $beca->update($this->datosBeca($data));
            $this->beneficioService->sincronizar($beca, $data['beneficios'] ?? []);
            $this->tutorService->sincronizar($beca, $data['tutores'] ?? []);
            $this->asignacionService->sincronizar($beca, $data['asignaciones'] ?? []);

            // sincronizar preguntas: eliminamos existentes y creamos las nuevas
            if (array_key_exists('preguntas', $data)) {
                $beca->preguntas()->delete();
                if (!empty($data['preguntas']) && is_array($data['preguntas'])) {
                    foreach ($data['preguntas'] as $p) {
                        if (empty($p['texto'])) continue;
                        $beca->preguntas()->create([
                            'texto' => $p['texto'],
                            'tipo' => $p['tipo'] ?? 'text',
                            'min' => $p['min'] ?? null,
                            'max' => $p['max'] ?? null,
                        ]);
                    }
                }
            }

            $beneficiosDespues = $beca->beneficios()->pluck('be_beneficios.id')->sort()->values()->all();

            return [
                'beca' => $beca->fresh(['beneficios', 'asignacionesTrabajo.tutor', 'preguntas']),
                'beneficios_cambiaron' => $beneficiosAntes !== $beneficiosDespues,
            ];
        });
    }

    public function cambiarEstado(Beca $beca): Beca
    {
        $beca->update(['activo' => !$beca->activo]);

        return $beca->fresh();
    }

    public function generarCodigoUnico(): string
    {
        do {
            $codigo = 'BE-' . now()->format('Y') . '-' . Str::upper(Str::random(5));
        } while (Beca::where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function datosBeca(array $data): array
    {
        return Arr::only($data, [
            'nombre',
            'descripcion',
            'activo',
            'requiere_tutor',
        ]);
    }
}
