<?php

namespace App\Http\Requests\beca;

use Illuminate\Foundation\Http\FormRequest;

class GuardarSolicitudRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Acceso controlado por middleware de rutas
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Forzar tipos booleanos en los inputs que correspondan
        $viviendaTransporte = $this->input('vivienda_transporte', []);
        
        if (isset($viviendaTransporte['residencia'])) {
            $viviendaTransporte['residencia']['paga'] = filter_var(
                $viviendaTransporte['residencia']['paga'] ?? false, 
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if (isset($viviendaTransporte['viaje_diario'])) {
            $viviendaTransporte['viaje_diario']['es_diario'] = filter_var(
                $viviendaTransporte['viaje_diario']['es_diario'] ?? false, 
                FILTER_VALIDATE_BOOLEAN
            );
        }

        $servicios = $this->input('datos_socioeconomicos.servicios', []);
        $serviciosMapeados = [];
        foreach (['agua', 'internet', 'cloacas', 'electricidad', 'TV cable', 'Tlf fijo', 'transport.Publico', 'Aseo_Urbano'] as $servicioKey) {
            $serviciosMapeados[$servicioKey] = filter_var($servicios[$servicioKey] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        $datosSocio = $this->input('datos_socioeconomicos', []);
        $datosSocio['servicios'] = $serviciosMapeados;

        $this->merge([
            'registro_patria' => $this->has('registro_patria') ? 1 : 0,
            'vivienda_transporte' => $viviendaTransporte,
            'datos_socioeconomicos' => $datosSocio,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id_persona' => 'required|exists:persona,id_persona',
            'jornada_id' => 'required|exists:be_jornadas_becas,id',
            'id_beneficio' => 'required|exists:be_beneficios,id',
            'id_lapso' => 'required|exists:be_lapsos,id',
            'tipo_solicitud' => 'required|in:nueva,renovacion',
            'indice_academico' => 'required|numeric|min:0|max:20',
            'direccion_temporal' => 'nullable|string|max:500',
            'gasto_pasaje' => 'required|numeric|min:0',
            'registro_patria' => 'required|boolean',
            
            // Vivienda y Transporte
            'vivienda_transporte' => 'required|array',
            'vivienda_transporte.residencia.paga' => 'required|boolean',
            'vivienda_transporte.residencia.monto' => 'nullable|numeric|min:0',
            'vivienda_transporte.viaje_diario.es_diario' => 'required|boolean',
            'vivienda_transporte.viaje_diario.frecuencia_semanal' => 'nullable|integer|min:1|max:7',
            'vivienda_transporte.tiempo_traslado.horas' => 'nullable|integer|min:0|max:24',
            'vivienda_transporte.tiempo_traslado.minutos' => 'nullable|integer|min:0|max:59',

            // Datos Socioeconómicos
            'datos_socioeconomicos' => 'required|array',
            'datos_socioeconomicos.vivienda.tipo' => 'required|string',
            'datos_socioeconomicos.vivienda.tenencia' => 'required|string',
            'datos_socioeconomicos.vivienda.distribucion' => 'required|array',
            'datos_socioeconomicos.vivienda.distribucion.dormitorios' => 'required|integer|min:0',
            'datos_socioeconomicos.vivienda.distribucion.cocina' => 'required|integer|min:0',
            'datos_socioeconomicos.vivienda.distribucion.comedor' => 'required|integer|min:0',
            'datos_socioeconomicos.vivienda.distribucion.baños' => 'required|integer|min:0',
            'datos_socioeconomicos.vivienda.distribucion.sala' => 'required|integer|min:0',
            'datos_socioeconomicos.vivienda.distribucion.patio' => 'required|integer|min:0',
            'datos_socioeconomicos.vivienda.distribucion.Garaje' => 'required|integer|min:0',
            'datos_socioeconomicos.vivienda.distribucion.lavadero' => 'required|integer|min:0',

            // Equipamientos
            'datos_socioeconomicos.equipamiento' => 'required|array',
            'datos_socioeconomicos.equipamiento.nevera' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.tv' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.Cocina' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.ventidalor' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.computadora' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.muebles' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.comedor' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.cama' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.lavadora' => 'required|integer|min:0',
            'datos_socioeconomicos.equipamiento.aire_acondicionado' => 'required|integer|min:0',

            // Servicios
            'datos_socioeconomicos.servicios' => 'required|array',

            // Carga Familiar
            'datos_socioeconomicos.carga_familiar' => 'nullable|array',
            'datos_socioeconomicos.carga_familiar.*.parentesco' => 'required|string|max:100',
            'datos_socioeconomicos.carga_familiar.*.nombre_apellido' => 'required|string|max:255',
            'datos_socioeconomicos.carga_familiar.*.edad' => 'required|integer|min:0|max:120',
            'datos_socioeconomicos.carga_familiar.*.nivel_instituto' => 'required|string|max:100',
            'datos_socioeconomicos.carga_familiar.*.ocupacion' => 'required|string|max:200',
            'datos_socioeconomicos.carga_familiar.*.ingreso_mens' => 'required|numeric|min:0',
        ];
    }

    /**
     * Mensajes de validación personalizados.
     */
    public function messages(): array
    {
        return [
            'id_persona.required' => 'Debe seleccionar un estudiante.',
            'id_persona.exists' => 'El estudiante seleccionado no es válido.',
            'jornada_id.required' => 'Debe asociar una jornada activa.',
            'id_beneficio.required' => 'Debe asociar un beneficio.',
            'id_lapso.required' => 'Debe asociar un lapso académico.',
            'tipo_solicitud.required' => 'El tipo de solicitud es obligatorio.',
            'indice_academico.required' => 'El índice académico es obligatorio.',
            'indice_academico.min' => 'El índice académico debe ser mínimo 0.',
            'indice_academico.max' => 'El índice académico debe ser máximo 20.',
            'gasto_pasaje.required' => 'El gasto de pasaje es obligatorio.',
            'datos_socioeconomicos.vivienda.tipo.required' => 'El tipo de vivienda es obligatorio.',
            'datos_socioeconomicos.vivienda.tenencia.required' => 'La tenencia de la vivienda es obligatoria.',
            'datos_socioeconomicos.carga_familiar.*.nombre_apellido.required' => 'El nombre del familiar es obligatorio.',
            'datos_socioeconomicos.carga_familiar.*.ingreso_mens.required' => 'El ingreso mensual del familiar es obligatorio.',
        ];
    }
}
