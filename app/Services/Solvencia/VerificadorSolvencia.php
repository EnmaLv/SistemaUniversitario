<?php

namespace App\Services\Solvencia;

use App\Models\Persona;

interface VerificadorSolvencia
{
    public function modulo(): string;

    public function etiqueta(): string;

    /**
     * @return array{solvente: bool, bloquea_titulo: bool, pendientes: array<int, array{tipo: string, titulo: string, detalle: string, fecha: ?string}>}
     */
    public function verificar(Persona $persona): array;
}
