<?php

namespace App\Jobs;

use App\Models\Archivo;
use App\Models\Usuario;
use App\Support\Cuentas\CuentasComunidad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerarCuentasComunidad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public array $idsPersona,
        public ?int $archivoId = null,
        public ?int $usuarioId = null,
    ) {
    }

    public function handle(): void
    {
        $actor = $this->usuarioId ? Usuario::find($this->usuarioId) : null;
        $resumen = CuentasComunidad::procesarLote($this->idsPersona, 'importacion', $actor);

        if (! $this->archivoId) {
            return;
        }

        DB::transaction(function () use ($resumen) {
            $archivo = Archivo::query()->lockForUpdate()->find($this->archivoId);

            if (! $archivo) {
                return;
            }

            $datos = $archivo->resumen ?? [];
            $cuentas = $datos['cuentas'] ?? ['usuarios_creados' => 0, 'usuarios_existentes' => 0, 'roles_agregados' => 0, 'errores' => 0];

            $cuentas['usuarios_creados'] += $resumen['usuarios_creados'];
            $cuentas['usuarios_existentes'] += $resumen['usuarios_existentes'];
            $cuentas['roles_agregados'] += $resumen['roles_agregados'];
            $cuentas['errores'] += count($resumen['errores']);

            $datos['cuentas'] = $cuentas;
            $datos['lotes_pendientes'] = max(0, (int) ($datos['lotes_pendientes'] ?? 1) - 1);

            $archivo->resumen = $datos;
            $archivo->estado = $datos['lotes_pendientes'] === 0 ? 'Procesado' : 'Generando cuentas';
            $archivo->save();
        });
    }
}
