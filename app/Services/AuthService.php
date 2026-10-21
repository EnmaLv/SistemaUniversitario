<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Persona;
use App\Models\ConfiguracionSistema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
    public function register(array $personaData, array $userData): Usuario
    {
        return DB::transaction(function () use ($personaData, $userData) {
            $sedeId = $this->ensureSede(); 

            $isFirstUser = Usuario::count() === 0;
            $perfilNombre = $isFirstUser ? 'Administrador' : 'Usuario';

            $perfilObj = $this->ensurePerfil($perfilNombre);
            $perfilId = $perfilObj->id_perfil;

            if (empty($personaData['id_perfil'])) {
                $personaData['id_perfil'] = $perfilId;
            }
            if (empty($personaData['sed_id']) && empty($personaData['sed_id'])) {
                $personaData['sed_id'] = $sedeId;
                $personaData['sed_id'] = $sedeId;
            }

            $persona = Persona::create($personaData);

            $usuario = new Usuario([
                'per_id' => $persona->id_per,
                'id_perfil'  => $perfilId,
                'username_use' => $userData['username'],
            ]);
            $usuario->password_use = $userData['password'];

            if ($isFirstUser) {
                $usuario->master_key_use = ! empty($userData['master_key_use']) ? $userData['master_key_use'] : 'admin123';
            }

            $usuario->save();

            if ($isFirstUser) {
                $this->assignRol($usuario->id_use, $perfilNombre);
            }

            return $usuario;
        });
    }

    private function assignRol(int $usuarioId, string $rolNombre): void
    {
        $rol = DB::table('rol')->where('nombre_rol', $rolNombre)->first();
        
        if (!$rol) {
            return;
        }

        DB::table('usuario_rol')->updateOrInsert(
            ['use_id' => $usuarioId, 'rol_id' => $rol->id_rol],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    private function ensureSede(): int
    {
        $row = DB::table('sede')->orderBy('id_sed')->first();
        if ($row) return $row->id_sed;

        return DB::table('sede')->insertGetId([
            'nombre_sed'    => 'Acarigua',
            'direccion_sed' => 'Avenida Circunvalacion Sur, Sector Bellas Artes',
            'telefono_sed'  => '0424-5556666',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function ensurePerfil(string $nombre)
    {
        $row = DB::table('perfil')->where('nombre_perfil', $nombre)->first();
        if ($row) return (object)['id_perfil' => $row->id_perfil];

        $id = DB::table('perfil')->insertGetId([
            'nombre_perfil' => $nombre, 
            'status_id'     => 1, 
            'created_at'    => now(), 
            'updated_at'    => now()
        ]);
        return (object)['id_perfil' => $id];
    }

    public function validateCredentials(string $username, string $password): ?Usuario
    {
        $usuario = Usuario::where('username_use', $username)->first();
        if (!$usuario) {
            return null;
        }

        if (!Hash::check($password, $usuario->getOriginal('password_use'))) {
            return null;
        }

        return $usuario;
    }

    public function verifyMasterKeyForUsuario(Usuario $usuario, string $masterKey): bool
    {
        try {
            $isAdmin = $usuario->roles()->where('nombre_rol', 'Administrador')->exists();
        } catch (\Throwable $e) {
            $perfil = $usuario->perfil()->first();
            $isAdmin = $perfil && $perfil->nombre_perfil === 'Administrador';
        }

        if ($isAdmin) {
            return ConfiguracionSistema::checkMasterKey($masterKey);
        }

        return true;
    }

    public function login(string $username, string $password, ?string $masterKey = null): Usuario
    {
        $usuario = $this->validateCredentials($username, $password);
        if (!$usuario) {
            throw new AuthenticationException('Credenciales inválidas');
        }

        try {
            $isAdmin = $usuario->roles()->where('nombre_rol', 'Administrador')->exists();
        } catch (\Throwable $e) {
            $perfil = $usuario->perfil()->first();
            $isAdmin = $perfil && $perfil->nombre_perfil === 'Administrador';
        }

        if ($isAdmin) {
            if (empty($masterKey) || !ConfiguracionSistema::checkMasterKey($masterKey)) {
                throw new AuthenticationException('Llave maestra requerida o inválida');
            }
        }

        return $usuario;
    }
}