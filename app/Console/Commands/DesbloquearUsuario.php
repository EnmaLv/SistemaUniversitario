<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Support\Seguridad\Bloqueos;
use Illuminate\Console\Command;

class DesbloquearUsuario extends Command
{
    protected $signature = 'seguridad:desbloquear {username : Username (correo) de la cuenta a desbloquear}';

    protected $description = 'Desbloquea una cuenta y reinicia sus contadores de intentos (recuperación de emergencia)';

    public function handle(): int
    {
        $usuario = Usuario::buscarPorIdentificador((string) $this->argument('username'));

        if (! $usuario) {
            $this->error('No existe un usuario con ese username.');

            return self::FAILURE;
        }

        $estado = Bloqueos::estado($usuario);
        Bloqueos::desbloquear($usuario, null, 'consola');

        $this->info($estado
            ? "Usuario {$usuario->username_use} desbloqueado (tenía bloqueo {$estado['tipo']})."
            : "Usuario {$usuario->username_use} no tenía bloqueos activos; se reiniciaron sus contadores.");

        return self::SUCCESS;
    }
}
