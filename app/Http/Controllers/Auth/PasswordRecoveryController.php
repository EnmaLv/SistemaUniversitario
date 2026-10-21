<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Bitacora as BitacoraModelo;
use App\Support\Auditoria\Bitacora;
use App\Support\Seguridad\Bloqueos;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class PasswordRecoveryController extends Controller
{
    private const MENSAJE_NO_DISPONIBLE = 'No es posible recuperar la cuenta con los datos indicados.';

    public function showEmailForm()
    {
        return view('auth.recover_email');
    }

    public function postEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $claveIp = 'recuperacion-consulta:' . $request->ip();
        if (RateLimiter::tooManyAttempts($claveIp, (int) config('seguridad.max_consultas_recuperacion_ip', 10))) {
            return back()->withErrors(['email' => Bloqueos::mensaje(['tipo' => 'temporal', 'segundos' => RateLimiter::availableIn($claveIp)])]);
        }
        RateLimiter::hit($claveIp, (int) config('seguridad.minutos_bloqueo_temporal', 5) * 60);

        session()->forget(['recovery_user_id', 'recovery_verified_id', 'recovery_verified_at']);

        $user = Usuario::where('username', $request->email)->first();
        $questions = $user ? $this->preguntas($user) : [];

        if (!$user || empty($questions)) {
            Bitacora::registrar(
                accion: 'recuperacion_solicitud_invalida',
                modulo: 'autenticacion',
                nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
                observacion: $user ? 'Usuario sin preguntas de seguridad' : 'Usuario inexistente',
                usuario: $user,
                nombreUsuario: $request->email,
            );

            return back()->withErrors(['email' => self::MENSAJE_NO_DISPONIBLE]);
        }

        if ($estado = Bloqueos::estado($user)) {
            return back()->withErrors(['email' => Bloqueos::mensaje($estado)]);
        }

        Bitacora::registrar(accion: 'recuperacion_iniciada', modulo: 'autenticacion', usuario: $user);

        session(['recovery_user_id' => $user->id_usuario]);
        return view('auth.recover_questions', ['questions' => $this->soloPreguntas($questions)]);
    }

    public function verifyAnswers(Request $request)
    {
        $id = session('recovery_user_id');
        if (!$id) {
            return redirect()->route('password.recover.email')->withErrors(['email' => 'Sesión expirada.']);
        }

        $user = Usuario::find($id);
        if (!$user) {
            session()->forget('recovery_user_id');
            return redirect()->route('password.recover.email')->withErrors(['email' => self::MENSAJE_NO_DISPONIBLE]);
        }

        if ($estado = Bloqueos::estado($user)) {
            session()->forget('recovery_user_id');
            return redirect()->route('password.recover.email')->withErrors(['email' => Bloqueos::mensaje($estado)]);
        }

        $max = (int) config('seguridad.max_intentos_recuperacion', 3);
        $decaimiento = (int) config('seguridad.minutos_bloqueo_temporal', 5) * 60;
        $claveUsuario = 'recuperacion-usuario:' . $user->id_usuario;
        $claveIp = 'recuperacion-ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($claveIp, $max * 2)) {
            return back()->withErrors(['answers' => Bloqueos::mensaje(['tipo' => 'temporal', 'segundos' => RateLimiter::availableIn($claveIp)])]);
        }

        $stored = $this->preguntas($user);
        $answers = (array) $request->input('answers', []);
        $correctas = count($stored) > 0 && count($answers) === count($stored);

        if ($correctas) {
            foreach ($stored as $i => $qa) {
                if (!isset($answers[$i]) || !is_string($answers[$i]) || !Hash::check($answers[$i], $qa['answer'])) {
                    $correctas = false;
                    break;
                }
            }
        }

        if (!$correctas) {
            RateLimiter::hit($claveUsuario, $decaimiento);
            RateLimiter::hit($claveIp, $decaimiento);
            $intentos = RateLimiter::attempts($claveUsuario);

            Bitacora::registrar(
                accion: 'recuperacion_preguntas_incorrectas',
                modulo: 'autenticacion',
                nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
                observacion: "Intento {$intentos} de {$max}",
                usuario: $user,
            );

            if ($intentos >= $max) {
                RateLimiter::clear($claveUsuario);
                $estado = Bloqueos::aplicarBloqueoTemporal($user, Bloqueos::MOTIVO_RECUPERACION);
                session()->forget('recovery_user_id');

                return redirect()->route('password.recover.email')->withErrors(['email' => Bloqueos::mensaje($estado)]);
            }

            return back()->withErrors(['answers' => 'Respuestas incorrectas.']);
        }

        RateLimiter::clear($claveUsuario);
        session(['recovery_verified_id' => $user->id_usuario, 'recovery_verified_at' => time()]);

        Bitacora::registrar(accion: 'recuperacion_preguntas_correctas', modulo: 'autenticacion', usuario: $user);

        if ($this->esAdministrador($user)) {
            return view('auth.admin_recovery_choice', ['user' => $user]);
        }

        return view('auth.reset_password', ['user' => $user]);
    }

    public function showVerifyForm(Request $request)
    {
        $id = session('recovery_user_id');
        if (!$id) {
            return redirect()->route('password.recover.email')->withErrors(['email' => 'Sesión expirada.']);
        }

        $user = Usuario::find($id);
        $questions = $user ? $this->preguntas($user) : [];
        if (!$user || empty($questions)) {
            return redirect()->route('password.recover.email')->withErrors(['email' => self::MENSAJE_NO_DISPONIBLE]);
        }

        return view('auth.recover_questions', ['questions' => $this->soloPreguntas($questions)]);
    }

    public function resetPassword(Request $request)
    {
        $user = $this->usuarioVerificado();
        if (!$user) {
            return redirect()->route('password.recover.email')->withErrors(['email' => 'Debes responder las preguntas de seguridad antes de restablecer la contraseña.']);
        }

        $request->validate(['password' => 'required|string|min:8|confirmed']);
        $user->password = $request->password;
        $user->save();

        Bitacora::registrar(
            accion: 'recuperacion_reseteo_password',
            modulo: 'autenticacion',
            tablaAfectada: 'usuario',
            registroId: $user->id_usuario,
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            usuario: $user,
        );

        $this->limpiarSesionRecuperacion();
        return redirect()->route('login')->with('status', 'Contraseña restablecida Exitosamente.');
    }

    public function resetMasterKey(Request $request)
    {
        $user = $this->usuarioVerificado();
        if (!$user || !$this->esAdministrador($user)) {
            return redirect()->route('password.recover.email')->withErrors(['email' => 'Debes responder las preguntas de seguridad antes de restablecer la llave maestra.']);
        }

        $request->validate(['master_key' => 'required|string|min:6']);
        $user->master_key = $request->master_key;
        $user->save();

        Bitacora::registrar(
            accion: 'recuperacion_reseteo_llave',
            modulo: 'autenticacion',
            tablaAfectada: 'usuario',
            registroId: $user->id_usuario,
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            usuario: $user,
        );

        $this->limpiarSesionRecuperacion();
        return redirect()->route('login')->with('status', 'Llave maestra restablecida Exitosamente.');
    }

    private function usuarioVerificado(): ?Usuario
    {
        $id = session('recovery_user_id');
        $verificado = session('recovery_verified_id');
        $momento = (int) session('recovery_verified_at', 0);
        $validez = (int) config('seguridad.minutos_validez_recuperacion', 10) * 60;

        if (!$id || (int) $id !== (int) $verificado || time() - $momento > $validez) {
            return null;
        }

        $user = Usuario::find($id);

        return $user && !Bloqueos::estaBloqueado($user) ? $user : null;
    }

    private function limpiarSesionRecuperacion(): void
    {
        session()->forget(['recovery_user_id', 'recovery_verified_id', 'recovery_verified_at']);
    }

    private function esAdministrador(Usuario $user): bool
    {
        return $user->roles()->where('nombre', 'Administrador')->exists();
    }

    private function preguntas(Usuario $user): array
    {
        $stored = $user->security_questions ?? [];
        if (is_string($stored)) {
            $stored = json_decode($stored, true) ?: [];
        }
        if (!is_array($stored)) {
            return [];
        }

        if (array_key_exists('pregunta_1', $stored) || array_key_exists('respuesta_1', $stored)) {
            $lista = [];
            foreach ([1, 2] as $n) {
                if (!empty($stored["pregunta_{$n}"]) && !empty($stored["respuesta_{$n}"])) {
                    $lista[] = ['question' => $stored["pregunta_{$n}"], 'answer' => $stored["respuesta_{$n}"]];
                }
            }

            return $lista;
        }

        return array_values(array_filter($stored, fn ($qa) => is_array($qa) && !empty($qa['question']) && !empty($qa['answer'])));
    }

    private function soloPreguntas(array $questions): array
    {
        return array_map(fn ($qa) => ['question' => $qa['question']], $questions);
    }
}
