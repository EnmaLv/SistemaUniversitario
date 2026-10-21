<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class Persona extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use PerteneceASede;

    protected $columnaSede = 'sed_id';

    protected $sedeSoloExplicita = true;

    protected $table = 'persona';
    protected $primaryKey = 'id_per';
    public $timestamps = true;

    protected $fillable = [
        'nombres_per',
        'segundo_nombre_per',
        'apellidos_per',
        'segundo_apellido_per',
        'cedula_per',
        'telefono_per',
        'genero_per',
        'fecha_nacimiento_per',
        'correo_per',
        'semestre_per',
        'status_id',
        'id_perfil',
        'sed_id',
        
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
                'cedula_per' => $data['cedula'],
                'nombres_per' => $data['nombre'],
                'segundo_nombre_per' => $data['segundo_nombre'],
                'apellidos_per' => $data['apellido'],
                'segundo_apellido_per' => $data['segundo_apellido'],
                'telefono_per' => $telefono,
                'genero_per' => $data['genero'],
                'fecha_nacimiento_per' => $data['fecha_nacimiento'],
                'correo_per' => $data['email'],
                'semestre_per' => $data['semestreId'],
                'id_perfil' => 2,
                'sed_id' => $data['sedeId'] ?? null,
            ]);

            //Guardamos la direccion

            DB::table('direccion')->insert([
                'sector_dir' => $data['sector'],
                'calle_dir' => $data['calle'],
                'per_id' => $personaId,
                'par_id' => $data['parroquiaId'],
            ]);

            //Guardamos el pnf y la sede si es un estudiante


            //Asignar la sede
            DB::table('persona')->where('id_per', $personaId)->update([
                'sed_id' => $data['sedeId'],
            ]);


            //Asignar el pnf 
            DB::table('persona_programa')->insert([
                'per_id' => $personaId,
                'prg_id' => $data['pnfId'],
                'fecha_inicio_ppr' => Carbon::now(),
                'fecha_fin_ppr' => Carbon::now(),
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
            DB::table('persona')->where('id_per', $personaId)->update([
                'nombres_per'           => $data['nombre'],
                'segundo_nombre_per'   => $data['segundo_nombre'],
                'apellidos_per'         => $data['apellido'],
                'segundo_apellido_per' => $data['segundo_apellido'],
                'telefono_per'         => $data['telefono'],
                'genero_per'           => $data['genero'],
                'fecha_nacimiento_per' => $data['fecha_nacimiento'],
                'correo_per'            => $data['email'],
                'semestre_per'         => $data['semestreId'],
                'sed_id'                  => $data['sedeId'] ?? null,
            ]);

            $personaExistente = DB::table('persona')
                ->where('cedula_per', $data['cedula'])
                ->where('per_id', '!=', $personaId)
                ->where(function ($query) {
                    $query->where('id_perfil', '!=', 1)
                        ->orWhereNull('id_perfil');
                })
                ->first();

            if ($personaExistente) {
                throw new Exception('Ya existe otra persona con esa cédula');
            } else {
                DB::table('persona')
                    ->where('per_id', $personaId)
                    ->where(function ($query) {
                        $query->where('id_perfil', '!=', 1)
                              ->orWhereNull('id_perfil');
                    })
                    ->update([
                        'cedula_per' => $data['cedula']
                    ]);
            }

            // 2. Actualizar la dirección
            // Usamos updateOrInsert por si acaso la persona no tenía dirección previa
            DB::table('direccion')->updateOrInsert(
                ['per_id' => $personaId], // Condición de búsqueda
                [
                    'sector_dir'       => $data['sector'],
                    'calle_dir'        => $data['calle'],
                    'par_id' => $data['parroquiaId'],
                ]
            );


            // Actualizar o insertar el PNF relacionado
            DB::table('persona_programa')->updateOrInsert(
                ['per_id' => $personaId], // Condición
                [
                    'prg_id'       => $data['pnfId'],
                    'fecha_inicio_ppr' => Carbon::now(),
                    'fecha_fin_ppr'    => Carbon::now(),
                ]
            );

            DB::commit();
            return [true, null];
        } catch (Exception $e) {
            DB::rollBack();
            return [false, \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'id_perfil', 'id_perfil');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'per_id', 'id_per');
    }

    public function personaPnf()
    {
        return $this->hasMany(PersonaPnf::class, 'per_id', 'id_per');
    }
}
