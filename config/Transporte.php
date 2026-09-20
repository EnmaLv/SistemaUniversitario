<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Peso promedio por estudiante (kg)
    |--------------------------------------------------------------------------
    | Los estudiantes no registran su peso en el sistema, así que se usa un
    | promedio fijo para calcular el factor de carga del vehículo.
    */
    'peso_estudiante_kg' => 60,

    /*
    |--------------------------------------------------------------------------
    | Multiplicador del factor de carga
    |--------------------------------------------------------------------------
    | factor_carga = 1 + (peso_extra_fraccion * este_valor)
    | donde peso_extra_fraccion = (pasajeros * peso_estudiante_kg) / peso_vehiculo_kg
    |
    | 0.3 equivale a: por cada 10% de peso extra sobre el peso del bus,
    | el consumo sube ~3%. Esto es una aproximación de industria, no un
    | hecho medido; recalíbralo cuando tengas datos reales de
    | carga_combustibles vs. litros_gastados estimados.
    */
    'factor_peso_multiplicador' => 0.3,

    /*
    |--------------------------------------------------------------------------
    | Umbral de velocidad urbano / carretera (km/h)
    |--------------------------------------------------------------------------
    | Un tramo entre dos puntos GPS se clasifica como "carretera" si su
    | velocidad promedio es mayor o igual a este valor; si no, "urbano".
    */
    'umbral_velocidad_carretera_kmh' => 45,

    /*
    |--------------------------------------------------------------------------
    | Umbral de ralentí (km/h)
    |--------------------------------------------------------------------------
    | Un tramo se considera "ralentí" (bus detenido con motor encendido)
    | si la velocidad de AMBOS puntos que lo delimitan es menor o igual
    | a este valor.
    */
    'umbral_velocidad_ralenti_kmh' => 3,

    /*
    |--------------------------------------------------------------------------
    | Tope máximo por hueco de ralentí (horas)
    |--------------------------------------------------------------------------
    | Si el GPS se queda sin señal, la app se cierra, o el conductor deja
    | el viaje "en_curso" sin finalizar por horas, un solo hueco entre dos
    | puntos no debe contarse íntegro como ralentí real. Se capa cada
    | hueco individual a este máximo antes de sumarlo.
    */
    'ralenti_max_gap_horas' => 0.5,

    /*
    |--------------------------------------------------------------------------
    | Origen por defecto para puntos GPS sin el campo 'origen'
    |--------------------------------------------------------------------------
    | La app móvil actual no envía este campo; se resuelve en el backend
    | mientras se actualiza el cliente Flutter para diferenciar
    | 'gps_movimiento' de 'heartbeat_ralenti'.
    */
    'origen_gps_por_defecto' => 'app_movil',

];