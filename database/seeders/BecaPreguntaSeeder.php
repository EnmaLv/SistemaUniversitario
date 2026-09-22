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
            // Paso 1: Datos Básicos
            [
                'codigo' => 'registro_patria',
                'etiqueta' => '¿Registrado en Sistema Patria?',
                'tipo' => 'boolean',
                'obligatoria' => false,
                'orden' => 0,
            ],

            // Paso 2: Vivienda y Transporte
            [
                'codigo' => 'gasto_pasaje',
                'etiqueta' => 'Gasto Mensual Estimado en Pasaje',
                'placeholder' => 'Monto en pasajes',
                'tipo' => 'decimal',
                'obligatoria' => true,
                'orden' => 1,
            ],
            [
                'codigo' => 'direccion_temporal',
                'etiqueta' => 'Dirección Temporal (Si vive alquilado fuera)',
                'placeholder' => 'Indique dirección temporal...',
                'tipo' => 'text',
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
                'tipo' => 'decimal',
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
                'tipo' => 'number',
                'valor_min' => 1,
                'valor_max' => 7,
                'obligatoria' => false,
                'orden' => 6,
            ],
            [
                'codigo' => 'tiempo_traslado_horas',
                'etiqueta' => 'Tiempo Estimado de Traslado - Horas',
                'tipo' => 'number',
                'valor_min' => 0,
                'valor_max' => 23,
                'obligatoria' => true,
                'orden' => 7,
            ],
            [
                'codigo' => 'tiempo_traslado_minutos',
                'etiqueta' => 'Tiempo Estimado de Traslado - Minutos',
                'tipo' => 'number',
                'valor_min' => 0,
                'valor_max' => 59,
                'obligatoria' => true,
                'orden' => 8,
            ],

            // Paso 3: Datos Socioeconómicos
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

            // Distribución de ambientes
            [
                'codigo' => 'ambiente_dormitorios',
                'etiqueta' => 'Número de Dormitorios',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 11,
            ],
            [
                'codigo' => 'ambiente_cocina',
                'etiqueta' => 'Número de Cocinas',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 12,
            ],
            [
                'codigo' => 'ambiente_comedor',
                'etiqueta' => 'Número de Comedores',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 13,
            ],
            [
                'codigo' => 'ambiente_baños',
                'etiqueta' => 'Número de Baños',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 14,
            ],
            [
                'codigo' => 'ambiente_sala',
                'etiqueta' => 'Número de Salas',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 15,
            ],
            [
                'codigo' => 'ambiente_patio',
                'etiqueta' => 'Número de Patios',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 16,
            ],
            [
                'codigo' => 'ambiente_garaje',
                'etiqueta' => 'Número de Garajes',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 17,
            ],
            [
                'codigo' => 'ambiente_lavadero',
                'etiqueta' => 'Número de Lavaderos',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 18,
            ],

            // Equipamiento de la vivienda
            [
                'codigo' => 'equipo_nevera',
                'etiqueta' => 'Cantidad de Neveras',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 19,
            ],
            [
                'codigo' => 'equipo_tv',
                'etiqueta' => 'Cantidad de TVs',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 20,
            ],
            [
                'codigo' => 'equipo_cocina',
                'etiqueta' => 'Cantidad de Cocinas (Aparato)',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 21,
            ],
            [
                'codigo' => 'equipo_ventidalor',
                'etiqueta' => 'Cantidad de Ventiladores',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 22,
            ],
            [
                'codigo' => 'equipo_computadora',
                'etiqueta' => 'Cantidad de Computadoras',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 23,
            ],
            [
                'codigo' => 'equipo_muebles',
                'etiqueta' => 'Cantidad de Muebles',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 24,
            ],
            [
                'codigo' => 'equipo_comedor',
                'etiqueta' => 'Cantidad de Comedores (Mueble)',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 25,
            ],
            [
                'codigo' => 'equipo_cama',
                'etiqueta' => 'Cantidad de Camas',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 26,
            ],
            [
                'codigo' => 'equipo_lavadora',
                'etiqueta' => 'Cantidad de Lavadoras',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 27,
            ],
            [
                'codigo' => 'equipo_aire_acondicionado',
                'etiqueta' => 'Cantidad de Aires Acondicionados',
                'tipo' => 'number',
                'valor_min' => 0,
                'obligatoria' => true,
                'orden' => 28,
            ],

            // Servicios Básicos
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

            // Carga Familiar (Como texto dinámico / JSON representativo)
            [
                'codigo' => 'carga_familiar',
                'etiqueta' => 'Carga Familiar del Estudiante',
                'placeholder' => 'Lista de familiares',
                'ayuda' => 'Campo destinado para estructurar la carga familiar dinámicamente.',
                'tipo' => 'textarea',
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
                // Eliminar opciones anteriores
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
