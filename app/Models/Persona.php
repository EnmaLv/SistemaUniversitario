<?php

namespace App\Models;

use App\Models\Becas\BecaBeneficiario;
use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class Persona extends Model
{
    use ConvierteAMayusculasNoEloquent;

    protected $table = 'persona';
    protected $primaryKey = 'id_persona';
    public $timestamps = true;

    protected $fillable = [
        'nombre_persona',
        'segundo_nombre_persona',
        'apellido_persona',
        'segundo_apellido_persona',
        'cedula_persona',
        'telefono_persona',
        'genero_persona',
        'edad_persona',
        'fecha_nacimiento_persona',
        'email_persona',
        'semestre_persona',
        'estado',
        'id_perfil',
        'id_sede',
        
    ];
    private static function formatearTelefono($numero)
    {
        // 1. Limpiamos el valor por si trae espacios o caracteres extraños
        $limpio = preg_replace('/\D/', '', $numero);

        // 2. Verificamos que tenga la longitud esperada (ej. 10 dígitos)
        if (strlen($limpio) == 10) {
            return "(" . substr($limpio, 0, 3) . ") " . substr($limpio, 3, 3) . "-" . substr($limpio, 6);
        }

        // Si es un número con 11 dígitos (típico en Venezuela: 04121234567)
        if (strlen($limpio) == 11) {
            return "(" . substr($limpio, 0, 4) . ") " . substr($limpio, 4, 3) . "-" . substr($limpio, 7);
        }

        // Si no cumple el formato, devolvemos el original
        return $numero;
    }

    public static function crearPersona($data)
    {
        $telefono = self::formatearTelefono($data['telefono']);

        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'segundo_nombre', 'apellido', 'segundo_apellido', 'sector', 'calle']);

        DB::beginTransaction();

        try {
            //Primero creamos la persona
            $personaId = DB::table('persona')->insertGetId([
                'cedula_persona' => $data['cedula'],
                'nombre_persona' => $data['nombre'],
                'segundo_nombre_persona' => $data['segundo_nombre'],
                'apellido_persona' => $data['apellido'],
                'segundo_apellido_persona' => $data['segundo_apellido'],
                'telefono_persona' => $telefono,
                'genero_persona' => $data['genero'],
                'edad_persona' => Carbon::parse($data['fecha_nacimiento'])->age,
                'fecha_nacimiento_persona' => $data['fecha_nacimiento'],
                'email_persona' => $data['email'],
                'semestre_persona' => $data['semestreId'],
                'id_perfil' => 2,
                'id_sede' => $data['sedeId'] ?? null,
            ]);

            //Guardamos la direccion

            DB::table('direccion')->insert([
                'sector' => $data['sector'],
                'calle' => $data['calle'],
                'id_persona' => $personaId,
                'id_localidad' => $data['parroquiaId'],
            ]);

            //Guardamos el pnf y la sede si es un estudiante


            //Asignar la sede
            DB::table('persona')->where('id_persona', $personaId)->update([
                'id_sede' => $data['sedeId'],
            ]);


            //Asignar el pnf 
            DB::table('persona_pnf')->insert([
                'id_persona' => $personaId,
                'id_pnf' => $data['pnfId'],
                'fecha_inicio' => Carbon::now(),
                'fecha_fin' => Carbon::now(),
            ]);

            DB::commit();
            return true;
        } 
        catch (Exception $e) {
            DB::rollBack();
            report($e);
            return false;
        }
    }

    public static function actualizarPersona($data, $personaId)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'segundo_nombre', 'apellido', 'segundo_apellido', 'sector', 'calle']);

        DB::beginTransaction();

        try {
            // 1. Actualizar los datos básicos de la persona
            DB::table('persona')->where('id_persona', $personaId)->update([
                'nombre_persona'           => $data['nombre'],
                'segundo_nombre_persona'   => $data['segundo_nombre'],
                'apellido_persona'         => $data['apellido'],
                'segundo_apellido_persona' => $data['segundo_apellido'],
                'telefono_persona'         => $data['telefono'],
                'genero_persona'           => $data['genero'],
                'edad_persona'             => Carbon::parse($data['fecha_nacimiento'])->age,
                'fecha_nacimiento_persona' => $data['fecha_nacimiento'],
                'email_persona'            => $data['email'],
                'semestre_persona'         => $data['semestreId'],
                'id_sede'                  => $data['sedeId'] ?? null,
            ]);

            $personaExistente = DB::table('persona')
                ->where('cedula_persona', $data['cedula'])
                ->where('id_persona', '!=', $personaId)
                ->where(function ($query) {
                    $query->where('id_perfil', '!=', 1)
                        ->orWhereNull('id_perfil');
                })
                ->first();

            if ($personaExistente) {
                throw new Exception('Ya existe otra persona con esa cédula');
            } else {
                DB::table('persona')
                    ->where('id_persona', $personaId)
                    ->where(function ($query) {
                        $query->where('id_perfil', '!=', 1)
                              ->orWhereNull('id_perfil');
                    })
                    ->update([
                        'cedula_persona' => $data['cedula']
                    ]);
            }

            // 2. Actualizar la dirección
            // Usamos updateOrInsert por si acaso la persona no tenía dirección previa
            DB::table('direccion')->updateOrInsert(
                ['id_persona' => $personaId], // Condición de búsqueda
                [
                    'sector'       => $data['sector'],
                    'calle'        => $data['calle'],
                    'id_localidad' => $data['parroquiaId'],
                ]
            );


            // Actualizar o insertar el PNF relacionado
            DB::table('persona_pnf')->updateOrInsert(
                ['id_persona' => $personaId], // Condición
                [
                    'id_pnf'       => $data['pnfId'],
                    'fecha_inicio' => Carbon::now(), // O mantener la original si fuera necesario
                    'fecha_fin'    => Carbon::now(),
                ]
            );

            DB::commit();
            return [true, null];
        } catch (Exception $e) {
            DB::rollBack();
            return [false, $e->getMessage()];
        }
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'id_perfil', 'id_perfil');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_persona', 'id_persona');
    }

    public function becasBeneficiario()
    {
        return $this->hasMany(BecaBeneficiario::class, 'persona_id', 'id_persona');
    }

    public function personaPnf()
    {
        return $this->hasMany(PersonaPnf::class, 'id_persona', 'id_persona');
    }
}
