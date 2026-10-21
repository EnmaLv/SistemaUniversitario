<?php

namespace App\Livewire;

use App\Jobs\GenerarCuentasComunidad;
use App\Models\Archivo;
use App\Support\Auditoria\Bitacora;
use App\Support\Cuentas\CuentasComunidad;
use App\Support\Cuentas\ImportadorPersonas;
use App\Support\Seguridad\MensajeError;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Archivos extends Component
{
    use WithFileUploads;

    public const SEGUNDOS_MAXIMOS = 600;

    public $archivo;
    public $archivoKey;
    public $buscar = '';
    public $procesando = false;
    public ?array $ultimoResumen = null;

    protected $rules = [
        'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
    ];

    public function mount()
    {
        $this->archivoKey = rand();
    }

    public function save()
    {
        $this->validate();

        $this->procesando = true;
        $this->dispatch('archivo-procesando');

        @set_time_limit(self::SEGUNDOS_MAXIMOS);

        try {
            $usuario = Auth::user();
            $extension = strtolower($this->archivo->getClientOriginalExtension() ?: 'xlsx');
            $ruta = $this->archivo->storeAs('importaciones', Str::uuid() . '.' . $extension, 'local');
            $idSede = (int) ($usuario->persona->id_sede ?? 1);

            $resumen = (new ImportadorPersonas())->importar(Storage::disk('local')->path($ruta), $idSede);
            $ids = $resumen['ids_persona'];
            unset($resumen['ids_persona']);

            $lotes = array_chunk($ids, CuentasComunidad::TAMANO_LOTE);
            $resumen['lotes_pendientes'] = count($lotes);
            $resumen['cuentas'] = ['usuarios_creados' => 0, 'usuarios_existentes' => 0, 'roles_agregados' => 0, 'errores' => 0];

            $registro = Archivo::create([
                'info_estudiantes' => 'Estudiantes UPTP - ' . now()->format('Y-m-d H:i:s'),
                'ruta_archivo'     => $ruta,
                'nombre_original'  => $this->archivo->getClientOriginalName(),
                'resumen'          => $resumen,
                'usuario_id'       => $usuario->getKey(),
                'fecha'            => now()->toDateString(),
                'estado'           => $lotes === [] ? 'Procesado' : 'Generando cuentas',
            ]);

            Bitacora::registrar(
                accion: 'importacion_personas',
                modulo: 'administracion',
                tablaAfectada: 'archivos',
                registroId: $registro->id,
                datosModificados: [
                    'total_filas'           => $resumen['total_filas'],
                    'personas_creadas'      => $resumen['personas_creadas'],
                    'personas_actualizadas' => $resumen['personas_actualizadas'],
                    'filas_con_error'       => $resumen['filas_con_error'],
                ],
                observacion: 'Importación de personas desde Excel',
            );

            foreach ($lotes as $lote) {
                GenerarCuentasComunidad::dispatch($lote, $registro->id, $usuario->getKey());
            }

            $registro->refresh();
            $this->ultimoResumen = $registro->resumen;

            $this->reset('archivo');
            $this->archivoKey = rand();
            $this->dispatch('archivo-limpiar');

            $cuentas = $registro->resumen['cuentas'] ?? [];
            $textoCuentas = $registro->estado === 'Procesado'
                ? "{$cuentas['usuarios_creados']} usuarios creados"
                : 'las cuentas de usuario se están generando en segundo plano';

            $this->dispatch(
                'swal',
                icon: $resumen['filas_con_error'] > 0 ? 'warning' : 'success',
                title: 'Importación terminada',
                text: "{$resumen['total_filas']} filas · {$resumen['personas_creadas']} personas nuevas · {$resumen['personas_actualizadas']} actualizadas · {$resumen['filas_con_error']} con error · {$textoCuentas}."
            );
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Error al procesar',
                text: MensajeError::de($e)
            );
        } finally {
            $this->procesando = false;
            $this->dispatch('archivo-finalizado');
        }
    }

    public function render()
    {
        $archivos = Archivo::latest()
            ->where('info_estudiantes', 'like', "%{$this->buscar}%")
            ->get();

        return view('livewire.archivos', [
            'archivos'      => $archivos,
            'hayPendientes' => $archivos->contains('estado', 'Generando cuentas'),
        ]);
    }
}
