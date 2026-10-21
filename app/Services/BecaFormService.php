<?php

namespace App\Services;

use App\Models\Becas\Beneficio;
use App\Models\Persona;
use App\Models\Rol;

class BecaFormService
{
    public function datosFormulario(): array
    {
        // obtener ids de roles asociados al módulo de beca
        $becaRoleIds = Rol::whereHas('modulos', function ($q) {
            $q->where('key', 'beca');
        })->pluck('id_rol')->toArray();

        return [
            'beneficios' => Beneficio::where('status', true)
                ->orderBy('nombre_beneficio')
                ->get(),
            'tutores' => Persona::whereHas('usuarios', function ($q) use ($becaRoleIds) {
                    $q->whereHas('roles', function ($q2) use ($becaRoleIds) {
                        $q2->whereIn('rol.id_rol', $becaRoleIds);
                    });
                })
                ->with(['usuarios.roles' => function ($q) use ($becaRoleIds) {
                    $q->whereIn('rol.id_rol', $becaRoleIds);
                }])
                ->orderBy('nombres_per')
                ->get(),
            'roles' => Rol::orderBy('nombre')->get(),
            'estudiantes' => Persona::deSedeActiva()->where('status_id', 1)
                ->whereHas('perfil', function ($query) {
                    $query->where('nombre_perfil', 'Estudiante');
                })
                ->orderBy('nombres_per')
                ->get(),
        ];
    }
}
