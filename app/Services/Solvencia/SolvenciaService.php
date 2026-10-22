<?php

namespace App\Services\Solvencia;

use App\Models\Persona;
use App\Services\Solvencia\Verificadores\VerificadorDeportes;
use App\Support\Auditoria\Bitacora;

class SolvenciaService
{
    /**
     * @var array<int, class-string<VerificadorSolvencia>>
     */
    protected array $verificadores = [
        VerificadorDeportes::class,
    ];

    public function agregarVerificador(string $clase): static
    {
        if (! in_array($clase, $this->verificadores, true)) {
            $this->verificadores[] = $clase;
        }

        return $this;
    }

    public function consultar(Persona $persona, ?array $modulos = null): array
    {
        $resultados = [];

        foreach ($this->verificadores as $clase) {
            /** @var VerificadorSolvencia $verificador */
            $verificador = app($clase);

            if ($modulos !== null && ! in_array($verificador->modulo(), $modulos, true)) {
                continue;
            }

            $resultados[] = array_merge([
                'modulo'   => $verificador->modulo(),
                'etiqueta' => $verificador->etiqueta(),
            ], $verificador->verificar($persona));
        }

        return [
            'persona'        => $persona,
            'solvente'       => collect($resultados)->every(fn ($resultado) => $resultado['solvente']),
            'bloquea_titulo' => collect($resultados)->contains(fn ($resultado) => $resultado['bloquea_titulo']),
            'modulos'        => $resultados,
            'fecha'          => now(),
        ];
    }

    public function registrarConsulta(array $resultado, string $formato, string $modulo = 'deporte'): void
    {
        $personaId = (int) $resultado['persona']->id_per;

        Bitacora::registrar(
            accion: 'solvencia_consultada',
            modulo: $modulo,
            tablaAfectada: 'persona',
            registroId: $personaId,
            datosModificados: [
                'solvente' => $resultado['solvente'],
                'modulos'  => array_column($resultado['modulos'], 'modulo'),
                'formato'  => $formato,
            ],
            observacion: 'Consulta de solvencia de la persona id ' . $personaId,
        );
    }
}
