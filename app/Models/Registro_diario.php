<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Registro_diario extends Model
{
    use PerteneceASede;

    protected $table = 'registro_diario_c';

    protected $fillable = [
        'per_id',
        'pep_id',
        'fecha_regis_diario_c',
        'hora',
    ];

    public $timestamps = false;

    public function receta_ingrediente()
    {
        return $this->belongsTo(RecetaIngrediente::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }


    public function persona_pnf()
    {
        return $this->belongsTo(PersonaPnf::class, 'pep_id', 'id_pep');
    }

    private static function relacionTable()
    {
        return \App\Support\Sedes\SedeActiva::filtrar(DB::table('registro_diario_c'), 'registro_diario_c.sed_id')
            ->join('persona', 'registro_diario_c.per_id', '=', 'persona.id_per')
            ->leftJoin('direccion', 'persona.id_per', '=', 'direccion.per_id')
            ->leftJoin('parroquia', 'direccion.par_id', '=', 'parroquia.id_par')
            ->leftJoin('municipio', 'parroquia.mun_id', '=', 'municipio.id_mun')
            ->leftJoin('estado', 'municipio.esd_id', '=', 'estado.id_esd')
            ->join('persona_programa', 'registro_diario_c.pep_id', '=', 'persona_programa.id_pep')
            ->join('programa', 'persona_programa.prg_id', '=', 'programa.id_prg');
    }

    public static function getRegister(int $id)
    {

        function formatColumn(array $column, string $prefix)
        {
            // Esta función podría ser usada para formatear los nombres de las columnas con un prefijo
            return array_map(function ($col) use ($prefix) {
                return $prefix . '.' . $col;
            }, $column);
        }
        //Creamos una variable para almacenar la columnas que necesitamos

        $personaColumn = formatColumn([
            'nombres_per',
            'segundo_nombre_per',
            'apellidos_per',
            'segundo_apellido_per',
            'cedula_per',
            'telefono_per',
            'genero_per',
            'fecha_nacimiento_per',
            'correo_per',
            'semestre_per'
        ], 'persona');

        $pnfColumn = formatColumn([
            'nombre_prg',
        ], 'programa');

        $registroColumn = formatColumn([
            'fecha_regis_diario_c',
            'hora'
        ], 'registro_diario_c');

        $direccionColumn = formatColumn([
            'sector_dir',
            'calle_dir'
        ], 'direccion');

        $localidadColumn = formatColumn([
            'nombre_par'
        ], 'parroquia');

        $municipioColumn = formatColumn([
            'nombre_mun'
        ], 'municipio');

        $estadoColumn = formatColumn([
            'nombre_esd'
        ], 'estado');

        //Lo unimos en un solo array
        $colums = [...$personaColumn, ...$pnfColumn, ...$registroColumn, ...$direccionColumn, ...$localidadColumn, ...$municipioColumn, ...$estadoColumn];
        $query = self::relacionTable()->select($colums)->where('registro_diario_c.id', $id);

        return $query->first();
    }

    public static function showData(array $filter = [], bool $isPdf = false)
    {
        $query = self::relacionTable()
            ->select('registro_diario_c.*', 'persona.nombres_per', 'persona.apellidos_per', 'persona.cedula_per', 'programa.nombre_prg', 'direccion.sector_dir', 'direccion.calle_dir', 'parroquia.nombre_par', 'municipio.nombre_mun', 'estado.nombre_esd');


        //Por si hay que buscar por el input
        if (isset($filter['buscar']) && $filter['buscar']) {
            $query->where(function ($q) use ($filter) {
                $q->where('persona.nombres_per', 'like', '%' . $filter['buscar'] . '%')
                    ->orWhere('persona.apellidos_per', 'like', '%' . $filter['buscar'] . '%')
                    ->orWhere('persona.cedula_per', 'like', '%' . $filter['buscar'] . '%')
                    ->orWhere('programa.nombre_prg', 'like', "%{$filter['buscar']}%");
            });
        }

        //Por si hay que buscar entre 2 fechas
        if (isset($filter['fecha_desde']) && isset($filter['fecha_hasta'])) {
            $query->whereBetween('registro_diario_c.fecha_regis_diario_c', [$filter['fecha_desde'], $filter['fecha_hasta']]);
        }

        //Por si hay que buscar por fecha
        if (isset($filter['fecha_desde'])) {
            $query->where('registro_diario_c.fecha_regis_diario_c', '>=', $filter['fecha_desde']);
        }

        if (isset($filter['fecha_hasta'])) {
            $query->where('registro_diario_c.fecha_regis_diario_c', '<=', $filter['fecha_hasta']);
        }

        //En el caso de que los datos lo necesitos para generar un pdf
        if ($isPdf) {
            return $query->get();
        }


        return $query->paginate(10);
    }

    public static function getAllByPnf()
    {
        //Relacionamos las tablas necesarias
        $query = self::relacionTable()
            // Obtenemos el nombre del pnf y contamos los registros por pnf
            ->select('programa.nombre_prg', DB::raw('COUNT(*) as total_registros'))
            ->groupBy('programa.nombre_prg')
            ->orderBy('total_registros', 'desc');

        return $query->get();
    }
}
