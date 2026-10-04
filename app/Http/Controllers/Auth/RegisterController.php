<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Services\AuthService;
use App\Models\ConfiguracionSistema;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    use RegistersUsers {
        register as traitRegister;
    }

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except(['showRegistrationForm', 'register']);
    }

    public function showRegistrationForm()
    {
        $roles = collect();

        if ($this->adminExists()) {
            if (!auth()->check() || !$this->isAdmin(auth()->user())) {
                abort(403, 'Acceso restringido');
            }
            $roles = Rol::all();
        }

        return view('auth.register', compact('roles'));
    }

    public function register(Request $request)
    {
        if (!auth()->check() && $this->adminExists()) {
            abort(403, 'Acceso restringido');
        }

        if (auth()->check()) {
            if (!$this->isAdmin(auth()->user())) {
                abort(403, 'No autorizado');
            }

            $this->validator($request->all())->validate();
            $user = $this->create($request->all());
            event(new Registered($user));

            return redirect()->route('admin.configuracion.empleados.index');
        }

        return $this->traitRegister($request);
    }

    private function adminExists()
    {
        try {
            $adminRol = Rol::where('nombre', 'Administrador')->first();
            $adminCount = $adminRol ? $adminRol->usuarios()->count() : 0;
        } catch (\Throwable $e) {
            $adminCount = Usuario::join('perfil', 'usuario.id_perfil', '=', 'perfil.id_perfil')
                ->where('perfil.nombre_perfil', 'Administrador')
                ->count();
        }

        return ($adminCount > 0);
    }

    private function isAdmin($user)
    {
        if (!$user) return false;

        try {
            return $user->roles()
                ->where(function ($query) {
                    $query->whereRaw('LOWER(nombre) = ?', ['administrador'])
                        ->orWhereRaw('LOWER(slug) = ?', ['administrador']);
                })
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function validator(array $data)
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'first_lastname' => ['required', 'string', 'max:255'],
            'cedula' => ['required', 'digits_between:1,8', 'unique:persona,cedula_persona'],
            'telefono' => ['nullable', 'regex:/^\d{4}-\d{7}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuario,username', 'unique:persona,email_persona'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'security_questions' => ['required', 'array', 'size:2'],
            'security_questions.*.question' => ['required', 'string'],
            'security_questions.*.answer' => ['required', 'string'],
        ];

        if (!$this->adminExists()) {
            $rules['master_key'] = ['required', 'string', 'min:6'];
        } else {
            $rules['id_rol'] = ['required', 'exists:rol,id_rol'];

            $selectedRol = Rol::find($data['id_rol'] ?? null);
            if ($selectedRol && strtolower($selectedRol->nombre) === 'administrador') {
                $rules['master_key'] = ['required', 'string', 'min:6'];
            } else {
                $rules['master_key'] = ['nullable'];
            }
        }

        return Validator::make($data, $rules, [
            'cedula.unique' => 'La cédula de identidad ya se encuentra registrada en el sistema.',
            'email.unique' => 'El correo electrónico ya está siendo utilizado por otro usuario.',
            'master_key.required' => 'La Llave Maestra es obligatoria para registrar perfiles de Administrador.',
        ]);
    }

    protected function create(array $data)
    {
        $authService = new AuthService();

        $personaData = [
            'nombre_persona' => $data['first_name'],
            'segundo_nombre_persona' => null,
            'apellido_persona' => $data['first_lastname'],
            'segundo_apellido_persona' => null,
            'cedula_persona' => $data['cedula'] ?? null,
            'genero_persona' => '',
            'edad_persona' => 0,
            'fecha_nacimiento_persona' => now(),
            'email_persona' => $data['email'],
        ];

        $userData = [
            'username' => $data['email'],
            'password' => $data['password'],
            'master_key' => $data['master_key'] ?? null,
        ];

        if (!empty($data['telefono'])) {
            $digits = preg_replace('/\D+/', '', $data['telefono']);
            $personaData['telefono_persona'] = substr($digits, 0, 4) . '-' . substr($digits, 4);
        }

        $usuario = $authService->register($personaData, $userData);

        try {
            $adminRol = Rol::where('nombre', 'Administrador')->first();
            $adminCount = $adminRol ? $adminRol->usuarios()->count() : 0;

            if ($adminCount === 0) {
                $assignRol = Rol::firstOrCreate(
                    ['nombre' => 'Administrador'],
                    ['descripcion' => 'Rol por defecto Administrador', 'slug' => 'administrador']
                );
            } else {
                $assignRol = Rol::find($data['id_rol']);
            }

            if ($assignRol) {
                $usuario->roles()->sync([$assignRol->id_rol]);
            }

            if ($assignRol && $assignRol->nombre === 'Administrador' && !empty($data['master_key'])) {
                try {
                    ConfiguracionSistema::updateMasterKey($data['master_key']);
                } catch (\Throwable $ex) {
                }
            }
        } catch (\Throwable $e) {
            throw $e;
        }

        if (!empty($data['master_key'])) {
            $usuario->master_key = $data['master_key'];
        }

        if (!empty($data['security_questions']) && is_array($data['security_questions'])) {
            $sq = [];
            foreach ($data['security_questions'] as $qa) {
                if (!empty($qa['question']) && !empty($qa['answer'])) {
                    $sq[] = [
                        'question' => $qa['question'],
                        'answer' => Hash::make($qa['answer']),
                    ];
                }
            }
            if (!empty($sq)) {
                $usuario->security_questions = $sq;
            }
        }

        $usuario->save();
        return $usuario;
    }
}
