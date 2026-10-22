<?php

namespace App\Http\Controllers\Auth;

use App\AdminLTE\Filters\ModuleFilter;
use App\Http\Controllers\Controller;
use App\Models\Bitacora as BitacoraModelo;
use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use App\Support\Cuentas\Cedula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CambioPasswordController extends Controller
{
    public function mostrar()
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        return view('auth.cambiar_password', ['obligatorio' => (bool) $usuario->debe_cambiar_password_use]);
    }

    public function guardar(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $request->validate([
            'password_actual' => ['required', 'string'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_actual.required' => 'Ingresa tu contraseña actual.',
            'password.required'        => 'Ingresa la nueva contraseña.',
            'password.confirmed'       => 'La confirmación no coincide con la nueva contraseña.',
            'password.min'             => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        if (! Hash::check($request->input('password_actual'), (string) $usuario->getOriginal('password_use'))) {
            Bitacora::registrar(
                accion: 'password_cambio_rechazado',
                modulo: 'autenticacion',
                tablaAfectada: 'user',
                registroId: $usuario->getKey(),
                nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
                observacion: 'Contraseña actual incorrecta',
            );

            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $nueva = (string) $request->input('password');
        $cedulaNueva = Cedula::normalizar($nueva);
        $cedulas = array_filter([
            Cedula::normalizar($usuario->persona?->cedula_per),
            Cedula::normalizar($usuario->username_use),
        ]);

        if ($cedulaNueva !== null && in_array($cedulaNueva, $cedulas, true)) {
            return back()->withErrors(['password' => 'La nueva contraseña no puede ser tu cédula.']);
        }

        if (Hash::check($nueva, (string) $usuario->getOriginal('password_use'))) {
            return back()->withErrors(['password' => 'La nueva contraseña debe ser distinta de la actual.']);
        }

        $obligatorio = (bool) $usuario->debe_cambiar_password_use;

        $usuario->password_use = $nueva;
        $usuario->debe_cambiar_password_use = false;
        $usuario->password_cambiado_en_use = now();
        $usuario->save();

        Bitacora::registrar(
            accion: 'password_cambiado',
            modulo: 'autenticacion',
            tablaAfectada: 'user',
            registroId: $usuario->getKey(),
            observacion: $obligatorio ? 'Cambio obligatorio en el primer inicio de sesión' : 'Cambio voluntario',
        );

        $request->session()->regenerate();

        return redirect()
            ->to((new ModuleFilter())->resolveInitialRoute($usuario->id_use))
            ->with('success', 'Tu contraseña se actualizó correctamente.');
    }
}
