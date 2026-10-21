<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculas;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Pnf extends Model
{
    use ConvierteAMayusculas;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'programa';
    protected $primaryKey = 'id_prg';
    protected $fillable = [
        'codigo_prg',
        'nombre_prg',
        'status_id'
    ];

    protected $mayusculas = [
        'nombre_prg'
    ];

    public function personaPnf()
    {
        return $this->hasMany(PersonaPnf::class, 'prg_id', 'id_prg');
    }

    public static function getPnfs(Request $request)
    {
        $paginacion = 10;

        $query = self::query();
        if ($request->filled('buscar')) {
            $query->where('nombre_prg', 'LIKE', "%{$request->buscar}%");
        }

        $estatus = $request->input('status_id', 1);
        $query->where('status_id', (int)$estatus);

        return $query->orderBy('id_prg', 'desc')->paginate($paginacion)->withQueryString();
    }

    public static function createPnf(Request $request, array $data = [])
    {
        $pnf = Pnf::where('nombre_prg', $request->nombre)->first();
        if ($pnf) {
            return redirect()->route('admin.maestros.pnf.index')->with('error', 'El PNF ya existe. Intente con otro nombre.');
        }

        return Pnf::create([
            'codigo_prg' => self::generarCodigo($request->nombre),
            'nombre_prg' => $request->nombre,
            'status_id' => self::STATUS_ACTIVO,
        ]);
    }

    public static function updatePnf(Request $request, array $data = [])
    {
        $pnf = Pnf::where('nombre_prg', $request->nombre)->first();

        if ($pnf && $pnf->id_prg != $request->id && $pnf->nombre_prg == $request->nombre) {
            return redirect()->route('admin.maestros.pnf.edit', $pnf->id_prg)->with('error', 'El PNF ya existe. Intente con otro nombre.');
        }

        return Pnf::where('id_prg', $request->id)->update([
            'nombre_prg' => strtoupper($request->nombre),
            'status_id' => $request->status_id,
        ]);
    }

    public static function activarPnf($id)
    {
        $pnf = Pnf::where('id_prg', $id)->first();

        if (!$pnf) {
            return false;
        }
        $pnf->alternarStatus();

        return true;
    }

    public static function generarCodigo(string $nombre): string
    {
        $base = 'PNF-' . Str::upper(Str::limit(Str::slug(Str::ascii($nombre), ''), 20, ''));
        $codigo = $base;
        $n = 1;

        while (self::withTrashed()->where('codigo_prg', $codigo)->exists()) {
            $codigo = $base . '-' . ++$n;
        }

        return $codigo;
    }
}
