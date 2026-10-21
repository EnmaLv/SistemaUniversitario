<?php

namespace App\Console\Commands;

use App\Models\Becas\SolicitudDocumento;
use App\Support\Auditoria\Bitacora;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrarArchivosPrivados extends Command
{
    protected $signature = 'archivos:migrar-privados {--simular : Muestra lo que haría sin mover archivos ni tocar la base de datos}';

    protected $description = 'Mueve al disco privado los documentos de becas y los Excel importados que siguen en el disco público (idempotente)';

    private const CARPETAS_PUBLICAS_PERMITIDAS = ['imagenes', 'publicaciones'];

    public function handle(): int
    {
        $simular = (bool) $this->option('simular');
        $publico = Storage::disk('public');
        $privado = Storage::disk('local');

        $resumen = ['documentos_movidos' => 0, 'rutas_actualizadas' => 0, 'excel_movidos' => 0, 'faltantes' => 0];

        foreach (SolicitudDocumento::query()->whereNotNull('ruta_archivo')->cursor() as $documento) {
            $ruta = $this->normalizar($documento->ruta_archivo);

            if ($ruta !== $documento->ruta_archivo) {
                $resumen['rutas_actualizadas']++;
                if (! $simular) {
                    $documento->forceFill(['ruta_archivo' => $ruta])->save();
                }
            }

            if ($privado->exists($ruta)) {
                continue;
            }

            if (! $publico->exists($ruta)) {
                $resumen['faltantes']++;
                $this->warn("No se encontró el archivo de la solicitud {$documento->id_solicitud}: {$ruta}");
                continue;
            }

            $resumen['documentos_movidos']++;
            if (! $simular) {
                $this->mover($ruta, $ruta);
            }
        }

        foreach ($publico->allFiles('informacion') as $ruta) {
            $destino = 'importaciones/legado/' . basename($ruta);

            $resumen['excel_movidos']++;
            if (! $simular && ! $privado->exists($destino)) {
                $this->mover($ruta, $destino);
            } elseif (! $simular) {
                $publico->delete($ruta);
            }
        }

        $otros = collect($publico->allFiles())
            ->reject(fn ($ruta) => Str::startsWith($ruta, '.') || in_array(Str::before($ruta, '/'), self::CARPETAS_PUBLICAS_PERMITIDAS, true));

        foreach ($otros as $ruta) {
            $this->warn("Archivo en el disco público fuera de las carpetas permitidas: {$ruta}");
        }

        $this->table(['Concepto', 'Cantidad'], [
            ['Documentos de becas movidos', $resumen['documentos_movidos']],
            ['Rutas normalizadas en solicitud_documento', $resumen['rutas_actualizadas']],
            ['Excel importados movidos a importaciones/legado', $resumen['excel_movidos']],
            ['Documentos sin archivo físico', $resumen['faltantes']],
            ['Otros archivos públicos para revisar', $otros->count()],
        ]);

        if (! $simular && array_sum($resumen) > 0) {
            Bitacora::registrar(
                accion: 'archivos_migrados_a_privado',
                modulo: 'administracion',
                datosModificados: $resumen,
                observacion: 'consola: archivos:migrar-privados'
            );
        }

        return self::SUCCESS;
    }

    private function normalizar(string $ruta): string
    {
        $ruta = ltrim(str_replace('\\', '/', $ruta), '/');

        foreach (['storage/app/public/', 'storage/', 'public/'] as $prefijo) {
            if (Str::startsWith($ruta, $prefijo)) {
                return substr($ruta, strlen($prefijo));
            }
        }

        return $ruta;
    }

    private function mover(string $origen, string $destino): void
    {
        $privado = Storage::disk('local');
        $publico = Storage::disk('public');

        $privado->writeStream($destino, $publico->readStream($origen));

        if ($privado->exists($destino) && $privado->size($destino) === $publico->size($origen)) {
            $publico->delete($origen);
        }
    }
}
