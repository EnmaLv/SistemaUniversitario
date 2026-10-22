<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Services\AuthService;
use App\Support\Auditoria\CambiosPermisos;
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
            $adminRol = Rol::where('nombre_rol', 'Administrador')->first();
            $adminCount = $adminRol ? $adminRol->usuarios()->count() : 0;
        } catch (\Throwable $e) {
            $adminCount = Usuario::join('perfil', 'user.id_perfil', '=', 'perfil.id_perfil')
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
                    $query->whereRaw('LOWER(nombre_rol) = ?', ['administrador'])
                        ->orWhereRaw('LOWER(slug_rol) = ?', ['administrador']);
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
            'cedula' => ['required', 'digits_between:1,8', 'unique:persona,cedula_per'],
            'telefono' => ['nullable', 'regex:/^\d{4}-\d{7}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user,username_use', 'unique:persona,correo_per'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'security_questions_use' => ['required', 'array', 'size:2'],
            'security_questions_use.*.question' => ['required', 'string'],
            'security_questions_use.*.answer' => ['required', 'string'],
        ];

        if (!$this->adminExists()) {
            $rules['master_key_use'] = ['required', 'string', 'min:6'];
        } else {
            $rules['id_rol'] = ['required', 'exists:rol,id_rol'];

            $selectedRol = Rol::find($data['id_rol'] ?? null);
            if ($selectedRol && strtolower($selectedRol->nombre_rol) === 'administrador') {
                $rules['master_key_use'] = ['required', 'string', 'min:6'];
            } else {
                $rules['master_key_use'] = ['nullable'];
            }
        }

        return Validator::make($data, $rules, [
            'cedula.unique' => 'La cédula de identidad ya se encuentra registrada en el sistema.',
            'email.unique' => 'El correo electrónico ya está siendo utilizado por otro usuario.',
            'master_key_use.required' => 'La Llave Maestra es obligatoria para registrar perfiles de Administrador.',
        ]);
    }

    protected function create(array $data)
    {
        $authService = new AuthService();

        $personaData = [
            'nombres_per' => $data['first_name'],
            'segundo_nombre_per' => null,
            'apellidos_per' => $data['first_lastname'],
            'segundo_apellido_per' => null,
            'cedula_per' => $data['cedula'] ?? null,
            'genero_per' => '',
            'fecha_nacimiento_per' => now(),
            'correo_per' => $data['email'],
        ];

        $userData = [
            'username' => $data['email'],
            'password' => $data['password'],
            'master_key_use' => $data['master_key_use'] ?? null,
        ];

        if (!empty($data['telefono'])) {
            $digits = preg_replace('/\D+/', '', $data['telefono']);
            $personaData['telefono_per'] = substr($digits, 0, 4) . '-' . substr($digits, 4);
        }

        $usuario = $authService->register($personaData, $userData);

        try {
            $adminRol = Rol::where('nombre_rol', 'Administrador')->first();
            $adminCount = $adminRol ? $adminRol->usuarios()->count() : 0;

            if ($adminCount === 0) {
                $assignRol = Rol::firstOrCreate(
                    ['nombre_rol' => 'Administrador'],
                    ['descripcion_rol' => 'Rol por defecto Administrador', 'slug_rol' => 'administrador']
                );
            } else {
                $assignRol = Rol::find($data['id_rol']);
            }

            if ($assignRol) {
                CambiosPermisos::rolesUsuario($usuario, $usuario->roles()->sync([$assignRol->id_rol]), 'roles_usuario_registro');
            }

            if ($assignRol && $assignRol->nombre_rol === 'Administrador' && !empty($data['master_key_use'])) {
                try {
                    ConfiguracionSistema::updateMasterKey($data['master_key_use']);
                } catch (\Throwable $ex) {
                }
            }
        } catch (\Throwable $e) {
            throw $e;
        }

        if (!empty($data['master_key_use'])) {
            $usuario->master_key_use = $data['master_key_use'];
        }

        if (!empty($data['security_questions_use']) && is_array($data['security_questions_use'])) {
            $sq = [];
            foreach ($data['security_questions_use'] as $qa) {
                if (!empty($qa['question']) && !empty($qa['answer'])) {
                    $sq[] = [
                        'question' => $qa['question'],
                        'answer' => Hash::make($qa['answer']),
                    ];
                }
            }
            if (!empty($sq)) {
                $usuario->security_questions_use = $sq;
            }
        }

        $usuario->save();
        return $usuario;
    }
}
