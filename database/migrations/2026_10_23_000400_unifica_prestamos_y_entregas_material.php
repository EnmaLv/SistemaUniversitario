<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('prestamo_deportivo', 'tipo_prd')) {
            Schema::table('prestamo_deportivo', function (Blueprint $table) {
                $table->string('tipo_prd', 20)->default('deportivo');
                $table->index(['per_id', 'tipo_prd', 'estado_prd'], 'prestamo_deportivo_persona_tipo_idx');
            });
        }

        if (! Schema::hasTable('detalle_prestamo_deportivo')) {
            Schema::create('detalle_prestamo_deportivo', function (Blueprint $table) {
                $table->id('id_dpd');
                $table->decimal('cantidad_dpd', 12, 2);
                $table->decimal('cantidad_devuelta_dpd', 12, 2)->default(0);
                $table->decimal('cantidad_danada_dpd', 12, 2)->default(0);
                $table->decimal('cantidad_consumida_dpd', 12, 2)->default(0);
                $table->decimal('cantidad_perdida_dpd', 12, 2)->default(0);
                $table->foreignId('prd_id')->constrained('prestamo_deportivo', 'id_prd')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('lot_id')->constrained('lote', 'id_lot')->restrictOnDelete()->cascadeOnUpdate();
                $table->timestamps();
                $table->auditoria();

                $table->index(['pdt_id', 'prd_id'], 'detalle_prestamo_deportivo_producto_idx');
            });
        }

        $this->convertirPrestamos();

        if (Schema::hasTable('entrega_material')) {
            $this->convertirEntregas();

            Schema::dropIfExists('detalle_entrega_material');
            Schema::dropIfExists('entrega_material');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('entrega_material')) {
            Schema::create('entrega_material', function (Blueprint $table) {
                $table->id('id_ema');
                $table->dateTime('fecha_ema')->useCurrent();
                $table->string('dependencia_ema', 150)->nullable();
                $table->string('motivo_ema', 255);
                $table->foreignId('per_id')->nullable()->constrained('persona', 'id_per')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('sed_id')->constrained('sede', 'id_sed')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('use_id')->constrained('user', 'id_use')->restrictOnDelete()->cascadeOnUpdate();
                $table->timestamps();
                $table->auditoria();

                $table->index(['sed_id', 'fecha_ema'], 'entrega_material_sede_fecha_idx');
            });

            Schema::create('detalle_entrega_material', function (Blueprint $table) {
                $table->id('id_dem');
                $table->decimal('cantidad_dem', 14, 2);
                $table->foreignId('ema_id')->constrained('entrega_material', 'id_ema')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('pdt_id')->constrained('producto', 'id_pdt')->restrictOnDelete()->cascadeOnUpdate();
                $table->timestamps();
                $table->auditoria();
            });
        }

        Schema::dropIfExists('detalle_prestamo_deportivo');

        if (Schema::hasColumn('prestamo_deportivo', 'tipo_prd')) {
            Schema::table('prestamo_deportivo', function (Blueprint $table) {
                $table->dropIndex('prestamo_deportivo_persona_tipo_idx');
                $table->dropColumn('tipo_prd');
            });
        }
    }

    private function convertirPrestamos(): void
    {
        $oficina = $this->productosDeOficina();

        DB::table('prestamo_deportivo')->chunkById(200, function ($prestamos) use ($oficina) {
            foreach ($prestamos as $prestamo) {
                DB::table('prestamo_deportivo')->where('id_prd', $prestamo->id_prd)->update([
                    'tipo_prd' => in_array((int) $prestamo->pdt_id, $oficina, true) ? 'oficina' : 'deportivo',
                ]);

                if (DB::table('detalle_prestamo_deportivo')->where('prd_id', $prestamo->id_prd)->exists()) {
                    continue;
                }

                $cantidad = (float) (DB::table('movimiento_inventario')
                    ->where('referencia_type_moi', 'prestamo_deportivo')
                    ->where('referencia_id_moi', $prestamo->id_prd)
                    ->where('tipo_movimiento_moi', 'SALIDA')
                    ->where('lot_id', $prestamo->lot_id)
                    ->orderBy('id_moi')
                    ->value('cantidad_convertida_moi') ?? $prestamo->cantidad_prd);

                $devuelta = 0.0;
                $danada = 0.0;
                $perdida = 0.0;

                if ($prestamo->estado_prd === 'devuelto') {
                    $devuelta = $cantidad;
                } elseif (in_array($prestamo->estado_prd, ['sancionado', 'repuesto'], true)) {
                    if ($prestamo->estado_devolucion_prd === 'dañado') {
                        $danada = $cantidad;
                    } else {
                        $perdida = $cantidad;
                    }
                }

                DB::table('detalle_prestamo_deportivo')->insert([
                    'cantidad_dpd'           => $cantidad,
                    'cantidad_devuelta_dpd'  => $devuelta,
                    'cantidad_danada_dpd'    => $danada,
                    'cantidad_consumida_dpd' => 0,
                    'cantidad_perdida_dpd'   => $perdida,
                    'prd_id'                 => $prestamo->id_prd,
                    'pdt_id'                 => $prestamo->pdt_id,
                    'lot_id'                 => $prestamo->lot_id,
                    'status_id'              => $prestamo->status_id ?? 1,
                    'created_at'             => $prestamo->created_at,
                    'updated_at'             => $prestamo->updated_at,
                    'deleted_at'             => $prestamo->deleted_at,
                    'created_id'             => $prestamo->created_id,
                    'updated_id'             => $prestamo->updated_id,
                ]);
            }
        }, 'id_prd');
    }

    private function convertirEntregas(): void
    {
        DB::table('entrega_material')->chunkById(100, function ($entregas) {
            foreach ($entregas as $entrega) {
                $personaId = $entrega->per_id ?? DB::table('user')->where('id_use', $entrega->use_id)->value('per_id');

                $lineas = DB::table('movimiento_inventario')
                    ->where('referencia_type_moi', 'entrega_material')
                    ->where('referencia_id_moi', $entrega->id_ema)
                    ->where('tipo_movimiento_moi', 'SALIDA')
                    ->orderBy('id_moi')
                    ->get(['id_moi', 'pdt_id', 'lot_id', 'cantidad_convertida_moi'])
                    ->map(fn ($movimiento) => [
                        'pdt_id'   => (int) $movimiento->pdt_id,
                        'lot_id'   => (int) $movimiento->lot_id,
                        'cantidad' => (float) $movimiento->cantidad_convertida_moi,
                    ])
                    ->all();

                if ($lineas === []) {
                    foreach (DB::table('detalle_entrega_material')->where('ema_id', $entrega->id_ema)->get() as $detalle) {
                        $lote = DB::table('lote')->where('pdt_id', $detalle->pdt_id)->orderBy('id_lot')->value('id_lot');

                        if ($lote) {
                            $lineas[] = ['pdt_id' => (int) $detalle->pdt_id, 'lot_id' => (int) $lote, 'cantidad' => (float) $detalle->cantidad_dem];
                        }
                    }
                }

                if (! $personaId || $lineas === []) {
                    continue;
                }

                $observacion = 'Convertido desde la entrega de material #' . $entrega->id_ema . '. Todo el material quedó como consumo justificado.';

                if ($entrega->dependencia_ema) {
                    $observacion .= ' Dependencia: ' . $entrega->dependencia_ema . '.';
                }

                $prestamoId = DB::table('prestamo_deportivo')->insertGetId([
                    'fecha_prestamo_prd'            => $entrega->fecha_ema,
                    'fecha_devolucion_esperada_prd' => $entrega->fecha_ema,
                    'fecha_devolucion_real_prd'     => $entrega->fecha_ema,
                    'estado_prd'                    => 'devuelto',
                    'estado_devolucion_prd'         => 'bueno',
                    'tipo_prd'                      => 'oficina',
                    'cantidad_prd'                  => (int) round(array_sum(array_column($lineas, 'cantidad'))),
                    'motivo_prd'                    => mb_substr((string) $entrega->motivo_ema, 0, 255),
                    'lugar_prd'                     => $entrega->dependencia_ema ?: null,
                    'observaciones_prd'             => $observacion,
                    'pdt_id'                        => $lineas[0]['pdt_id'],
                    'lot_id'                        => $lineas[0]['lot_id'],
                    'per_id'                        => $personaId,
                    'sed_id'                        => $entrega->sed_id,
                    'registrado_por_use_id'         => $entrega->use_id,
                    'recibido_por_use_id'           => $entrega->use_id,
                    'status_id'                     => $entrega->status_id ?? 1,
                    'created_at'                    => $entrega->created_at,
                    'updated_at'                    => $entrega->updated_at,
                    'deleted_at'                    => $entrega->deleted_at,
                    'created_id'                    => $entrega->created_id,
                    'updated_id'                    => $entrega->updated_id,
                ], 'id_prd');

                foreach ($lineas as $linea) {
                    DB::table('detalle_prestamo_deportivo')->insert([
                        'cantidad_dpd'           => $linea['cantidad'],
                        'cantidad_devuelta_dpd'  => 0,
                        'cantidad_danada_dpd'    => 0,
                        'cantidad_consumida_dpd' => $linea['cantidad'],
                        'cantidad_perdida_dpd'   => 0,
                        'prd_id'                 => $prestamoId,
                        'pdt_id'                 => $linea['pdt_id'],
                        'lot_id'                 => $linea['lot_id'],
                        'status_id'              => 1,
                        'created_at'             => $entrega->created_at,
                        'updated_at'             => $entrega->updated_at,
                        'deleted_at'             => $entrega->deleted_at,
                    ]);
                }

                DB::table('movimiento_inventario')
                    ->where('referencia_type_moi', 'entrega_material')
                    ->where('referencia_id_moi', $entrega->id_ema)
                    ->update(['referencia_type_moi' => 'prestamo_deportivo', 'referencia_id_moi' => $prestamoId]);
            }
        }, 'id_ema');
    }

    private function productosDeOficina(): array
    {
        return DB::table('producto')
            ->join('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
            ->join('tipo_producto', 'tipo_producto.id_tpr', '=', 'categoria_producto.tpr_id')
            ->whereRaw('LOWER(tipo_producto.nombre_tpr) = ?', ['material de oficina'])
            ->pluck('producto.id_pdt')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
};
