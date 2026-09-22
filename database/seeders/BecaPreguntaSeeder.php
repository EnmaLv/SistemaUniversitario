<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Becas\BecaPregunta;
use App\Models\Becas\BecaPreguntaOpciones;
use App\Models\Becas\Beneficio;

class BecaPreguntaSeeder extends Seeder
{
    public function run(): void
    {
        $comedor = Beneficio::where('slug', 'beca-comedor')->first();

        if (!$comedor) {
            return;
        }

        $idBeneficio = $comedor->id;

        $preguntas = [
            // ───────── Paso 1: Datos Básicos ─────────
            [
                'codigo' => 'registro_patria',
                'etiqueta' => '¿Registrado en Sistema Patria?',
                'ayuda' => 'Marca Sí si está registrado en el sistema Patria.',
                'tipo' => 'boolean',
                'obligatoria' => false,
                'orden' => 0,
            ],

            // ───────── Paso 2: Vivienda y Transporte ─────────
            [
                'codigo' => 'gasto_pasaje',
                'etiqueta' => 'Gasto Mensual Estimado en Pasaje',
                'placeholder' => 'Monto en pasajes',
                'ayuda' => 'Solo números, hasta 2 decimales. Ej: 150.50',
                'tipo' => 'decimal',
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'valor_min' => 0,
                'valor_max' => 99999,
                'obligatoria' => true,
                'orden' => 1,
            ],
            [
                'codigo' => 'direccion_temporal',
                'etiqueta' => 'Dirección Temporal (Si vive alquilado fuera)',
                'placeholder' => 'Indique dirección temporal...',
                'ayuda' => 'Solo letras, números, comas, puntos y guiones.',
                'tipo' => 'text',
                'min_length' => 5,
                'max_length' => 255,
                'obligatoria' => false,
                'orden' => 2,
            ],
            [
                'codigo' => 'paga_residencia',
                'etiqueta' => '¿Paga residencia/alquiler?',
                'tipo' => 'boolean',
                'obligatoria' => true,
                'orden' => 3,
            ],
            [
                'codigo' => 'monto_residencia',
                'etiqueta' => 'Monto Mensual del Alquiler',
                'placeholder' => 'Monto pagado',
                'ayuda' => 'Solo números, hasta 2 decimales. Ej: 500.00',
                'tipo' => 'decimal',
                'regex' => '/^\d+(\.\d{1,2})?$/',
                'valor_min' => 0,
                'valor_max' => 99999,
                'obligatoria' => false,
                'orden' => 4,
            ],
            [
                'codigo' => 'viaje_diario',
                'etiqueta' => '¿Viaja diariamente a la universidad?',
                'tipo' => 'boolean',
                'obligatoria' => true,
                'orden' => 5,
            ],
            [
                'codigo' => 'frecuencia_viaje',
                'etiqueta' => 'Frecuencia semanal (días de viaje)',
                'placeholder' => 'Días por semana',
                'ayuda' => 'Un número del 1 al 7.',
                'tipo' => 'number',
                'regex' => '/^[1-7]$/',
                'valor_min' => 1,
                'valor_max' => 7,
                'obligatoria' => false,
                'orden' => 6,
            ],
            [
                'codigo' => 'tiempo_traslado_horas',
                'etiqueta' => 'Tiempo Estimado de Traslado - Horas',
                'placeholder' => 'Ej: 2',
                'ayuda' => 'Un número entre 0 y 23.',
                'tipo' => 'number',
                'regex' => '/^(?:[0-9]|1[0-9]|2[0-3])$/',
                'valor_min' => 0,
                'valor_max' => 23,
                'obligatoria' => true,
                'orden' => 7,
            ],
            [
                'codigo' => 'tiempo_traslado_minutos',
                'etiqueta' => 'Tiempo Estimado de Traslado - Minutos',
                'placeholder' => 'Ej: 30',
                'ayuda' => 'Un número entre 0 y 59.',
                'tipo' => 'number',
                'regex' => '/^(?:[0-5]?[0-9])$/',
                'valor_min' => 0,
                'valor_max' => 59,
                'obligatoria' => true,
                'orden' => 8,
            ],

            // ───────── Paso 3: Datos Socioeconómicos ─────────
            [
                'codigo' => 'vivienda_tipo',
                'etiqueta' => 'Tipo de Vivienda',
                'tipo' => 'select',
                'obligatoria' => true,
                'orden' => 9,
                'opciones' => [
                    ['etiqueta' => 'Casa', 'valor' => 'Casa'],
                    ['etiqueta' => 'Quinta', 'valor' => 'Quinta'],
                    ['etiqueta' => 'Apartamento', 'valor' => 'Apartamento'],
                    ['etiqueta' => 'Rancho', 'valor' => 'Rancho'],
                    ['etiqueta' => 'Habitación', 'valor' => 'Habitación'],
                ],
            ],
            [
                'codigo' => 'vivienda_tenencia',
                'etiqueta' => 'Tenencia de la Vivienda',
                'tipo' => 'select',
                'obligatoria' => true,
                'orden' => 10,
                'opciones' => [
                    ['etiqueta' => 'Propia', 'valor' => 'Propia'],
                    ['etiqueta' => 'Alquilada', 'valor' => 'Alquilada'],
                    ['etiqueta' => 'Prestada', 'valor' => 'Prestada'],
                    ['etiqueta' => 'Heredada', 'valor' => 'Heredada'],
                    ['etiqueta' => 'Invadida', 'valor' => 'Invadida'],
                ],
            ],

            // ───────── Distribución de ambientes (números enteros ≥ 0) ─────────
            ['codigo' => 'ambiente_dormitorios', 'etiqueta' => 'Número de Dormitorios', 'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 11, 'ayuda' => 'Solo números enteros. Ej: 3'],
            ['codigo' => 'ambiente_cocina',      'etiqueta' => 'Número de Cocinas',     'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 12, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'ambiente_comedor',     'etiqueta' => 'Número de Comedores',   'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 13, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'ambiente_baños',       'etiqueta' => 'Número de Baños',       'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 14, 'ayuda' => 'Solo números enteros. Ej: 2'],
            ['codigo' => 'ambiente_sala',        'etiqueta' => 'Número de Salas',       'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 15, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'ambiente_patio',       'etiqueta' => 'Número de Patios',      'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 16, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'ambiente_garaje',      'etiqueta' => 'Número de Garajes',     'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 17, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'ambiente_lavadero',    'etiqueta' => 'Número de Lavaderos',   'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 18, 'ayuda' => 'Solo números enteros. Ej: 1'],

            // ───────── Equipamiento (números enteros ≥ 0) ─────────
            ['codigo' => 'equipo_nevera',              'etiqueta' => 'Cantidad de Neveras',              'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 19, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'equipo_tv',                  'etiqueta' => 'Cantidad de TVs',                  'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 20, 'ayuda' => 'Solo números enteros. Ej: 2'],
            ['codigo' => 'equipo_cocina',              'etiqueta' => 'Cantidad de Cocinas (Aparato)',    'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 21, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'equipo_ventidalor',          'etiqueta' => 'Cantidad de Ventiladores',         'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 22, 'ayuda' => 'Solo números enteros. Ej: 3'],
            ['codigo' => 'equipo_computadora',         'etiqueta' => 'Cantidad de Computadoras',         'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 23, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'equipo_muebles',             'etiqueta' => 'Cantidad de Muebles',              'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 24, 'ayuda' => 'Solo números enteros. Ej: 5'],
            ['codigo' => 'equipo_comedor',             'etiqueta' => 'Cantidad de Comedores (Mueble)',   'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 25, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'equipo_cama',                'etiqueta' => 'Cantidad de Camas',                'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 26, 'ayuda' => 'Solo números enteros. Ej: 3'],
            ['codigo' => 'equipo_lavadora',            'etiqueta' => 'Cantidad de Lavadoras',            'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 27, 'ayuda' => 'Solo números enteros. Ej: 1'],
            ['codigo' => 'equipo_aire_acondicionado',  'etiqueta' => 'Cantidad de Aires Acondicionados', 'tipo' => 'number', 'regex' => '/^\d+$/', 'valor_min' => 0, 'valor_max' => 50, 'obligatoria' => true, 'orden' => 28, 'ayuda' => 'Solo números enteros. Ej: 2'],

            // ───────── Servicios Básicos ─────────
            [
                'codigo' => 'servicios_basicos',
                'etiqueta' => 'Servicios Públicos / Conectividad disponibles',
                'tipo' => 'checkbox',
                'obligatoria' => true,
                'orden' => 29,
                'opciones' => [
                    ['etiqueta' => 'Agua potable', 'valor' => 'agua'],
                    ['etiqueta' => 'Internet fijo/datos', 'valor' => 'internet'],
                    ['etiqueta' => 'Alcantarillado / Cloacas', 'valor' => 'cloacas'],
                    ['etiqueta' => 'Electricidad', 'valor' => 'electricidad'],
                    ['etiqueta' => 'Televisión por Cable', 'valor' => 'TV cable'],
                    ['etiqueta' => 'Teléfono Fijo', 'valor' => 'Tlf fijo'],
                    ['etiqueta' => 'Transporte Público', 'valor' => 'transport.Publico'],
                    ['etiqueta' => 'Aseo Urbano', 'valor' => 'Aseo_Urbano'],
                ],
            ],

            // ───────── Carga Familiar ─────────
            [
                'codigo' => 'carga_familiar',
                'etiqueta' => 'Carga Familiar del Estudiante',
                'placeholder' => 'Lista de familiares',
                'ayuda' => 'Describe brevemente cada familiar (nombre, parentesco, edad).',
                'tipo' => 'textarea',
                'min_length' => 10,
                'max_length' => 2000,
                'obligatoria' => true,
                'orden' => 30,
            ],
        ];

        foreach ($preguntas as $data) {
            $opciones = $data['opciones'] ?? [];
            unset($data['opciones']);

            $data['id_be_beneficio'] = $idBeneficio;
            $data['activo'] = true;

            $pregunta = BecaPregunta::updateOrCreate(
                [
                    'id_be_beneficio' => $idBeneficio,
                    'codigo' => $data['codigo'],
                ],
                $data
            );

            if (!empty($opciones)) {
                BecaPreguntaOpciones::where('id_pregunta', $pregunta->id)->delete();

                foreach ($opciones as $i => $opcion) {
                    BecaPreguntaOpciones::create([
                        'id_pregunta' => $pregunta->id,
                        'etiqueta' => $opcion['etiqueta'],
                        'valor' => $opcion['valor'],
                        'orden' => $i + 1,
                        'activo' => true,
                    ]);
                }
            }
        }
    }
}