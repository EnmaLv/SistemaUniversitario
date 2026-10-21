<?php

namespace App\Console\Commands;

use App\Support\Cuentas\CuentasComunidad;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerarUsuariosFaltantes extends Command
{
    protected $signature = 'usuarios:generar-faltantes {--simular : Solo cuenta las personas sin usuario}';

    protected $description = 'Crea la cuenta (usuario = cédula, contraseña inicial = cédula, roles Becario y Paciente) a cada persona que no tenga usuario';

    public function handle(): int
    {
        $pendientes = DB::table('persona')
            ->whereNotExists(function ($consulta) {
                $consulta->select(DB::raw(1))
                    ->from('usuario')
                    ->whereColumn('usuario.id_persona', 'persona.id_persona');
            });

        $total = (clone $pendientes)->count();
        $this->info("Personas sin usuario: {$total}");

        if ($total === 0 || $this->option('simular')) {
            return self::SUCCESS;
        }

        $totales = ['usuarios_creados' => 0, 'usuarios_existentes' => 0, 'roles_agregados' => 0, 'errores' => 0];
        $barra = $this->output->createProgressBar($total);

        $pendientes->orderBy('id_persona')->chunkById(CuentasComunidad::TAMANO_LOTE, function ($personas) use (&$totales, $barra) {
            $resumen = CuentasComunidad::procesarLote($personas->pluck('id_persona')->all(), 'consola');

            $totales['usuarios_creados'] += $resumen['usuarios_creados'];
            $totales['usuarios_existentes'] += $resumen['usuarios_existentes'];
            $totales['roles_agregados'] += $resumen['roles_agregados'];
            $totales['errores'] += count($resumen['errores']);

            foreach ($resumen['errores'] as $error) {
                $this->newLine();
                $this->warn("Persona {$error['id_persona']}: {$error['motivo']}");
            }

            $barra->advance($personas->count());
        }, 'id_persona');

        $barra->finish();
        $this->newLine(2);

        $this->table(['Concepto', 'Cantidad'], [
            ['Usuarios creados', $totales['usuarios_creados']],
            ['Personas con usuario previo', $totales['usuarios_existentes']],
            ['Roles agregados a cuentas existentes', $totales['roles_agregados']],
            ['Personas con error', $totales['errores']],
        ]);

        return self::SUCCESS;
    }
}
