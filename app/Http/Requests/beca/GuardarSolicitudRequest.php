<?php

namespace App\Http\Requests\beca;

use App\Models\Becas\BecaPregunta;
use App\Models\Becas\JornadaBeca;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class GuardarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_persona'      => 'required|exists:persona,id_persona',
            'jornada_id'      => 'required|exists:be_jornadas_becas,id',
            'id_beneficio'    => 'required|exists:be_beneficios,id',
            'id_lapso'        => 'required|exists:be_lapsos,id',
            'tipo_solicitud'  => 'required|in:nueva,renovacion',
            'archivo_notas'   => 'required|file|mimes:pdf|max:2048',

            'respuestas'                  => 'required|array',
            'respuestas.*.id_pregunta'    => 'required|exists:be_beca_preguntas,id',
            'respuestas.*.valor'          => 'nullable',
            'respuestas.*.valor_json'     => 'nullable|array',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $jornadaId = $this->input('jornada_id');
            if (!$jornadaId) return;

            $jornada = JornadaBeca::find($jornadaId);
            if (!$jornada) return;

            $respuestas = collect($this->input('respuestas', []))
                ->keyBy('id_pregunta');

            $preguntas = BecaPregunta::where('id_be_beneficio', $jornada->beneficio_id)
                ->where('activo', true)
                ->get();

            foreach ($preguntas as $p) {
                $r = $respuestas[$p->id] ?? null;
                $valor = $r['valor'] ?? null;
                $valorJson = $r['valor_json'] ?? null;

                $tieneValor = $valor !== null && $valor !== ''
                    || (!empty($valorJson));

                if ($p->obligatoria && !$tieneValor) {
                    $validator->errors()->add(
                        "respuestas.{$p->id}",
                        "La pregunta «{$p->etiqueta}» es obligatoria."
                    );
                    continue;
                }

                if (!$tieneValor) continue;

                if (in_array($p->tipo, ['number', 'decimal']) && $valor !== null && $valor !== '') {
                    if (!is_numeric($valor)) {
                        $validator->errors()->add("respuestas.{$p->id}", "«{$p->etiqueta}» debe ser numérico.");
                        continue;
                    }
                    if ($p->valor_min !== null && $valor < $p->valor_min) {
                        $validator->errors()->add("respuestas.{$p->id}", "«{$p->etiqueta}» debe ser mayor o igual a {$p->valor_min}.");
                    }
                    if ($p->valor_max !== null && $valor > $p->valor_max) {
                        $validator->errors()->add("respuestas.{$p->id}", "«{$p->etiqueta}» debe ser menor o igual a {$p->valor_max}.");
                    }
                }

                if (in_array($p->tipo, ['text', 'textarea', 'email']) && $valor !== null) {
                    if ($p->min_length && mb_strlen($valor) < $p->min_length) {
                        $validator->errors()->add("respuestas.{$p->id}", "«{$p->etiqueta}» debe tener al menos {$p->min_length} caracteres.");
                    }
                    if ($p->max_length && mb_strlen($valor) > $p->max_length) {
                        $validator->errors()->add("respuestas.{$p->id}", "«{$p->etiqueta}» no debe superar {$p->max_length} caracteres.");
                    }
                    if ($p->regex && !preg_match($p->regex, $valor)) {
                        $validator->errors()->add("respuestas.{$p->id}", "«{$p->etiqueta}» no tiene el formato esperado.");
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_persona.required' => 'Debe seleccionar un estudiante.',
            'jornada_id.required' => 'Debe asociar una jornada activa.',
            'respuestas.required' => 'Debe responder el formulario.',
            'archivo_notas.required' => 'El archivo de notas es obligatorio.',
            'archivo_notas.file' => 'El documento cargado debe ser un archivo válido.',
            'archivo_notas.mimes' => 'El documento de notas debe ser un archivo PDF.',
            'archivo_notas.max' => 'El documento de notas no debe pesar más de 2MB.',
        ];
    }
}