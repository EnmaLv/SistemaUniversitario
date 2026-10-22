<?php

namespace App\Http\Requests\beca;

use App\Models\Becas\BecaPregunta;
use App\Models\Becas\JornadaBeca;
use App\Services\becas\SolicitudBecasService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class GuardarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $usuario = $this->user();

        if ($usuario && ! $usuario->puedeAlguna(['solicitud', 'solicitudes_becas'], 'crear')) {
            $this->merge(['per_id' => $usuario->per_id]);
        }
    }

    public function rules(): array
    {
        return [
            'per_id'      => 'required|exists:persona,id_per',
            'jornada_id'      => 'required|exists:jornada_beneficio,id_jnb',
            'id_beneficio'    => 'required|exists:beneficio_estudiantil,id_bes',
            'id_lapso'        => 'required|exists:lapso_academico,id_laa',
            'tipo_solicitud'  => 'required|in:nueva,renovacion',
            'archivo_notas'   => 'required|file|mimes:pdf|max:2048',

            'respuestas'                  => 'required|array',
            'respuestas.*.id_pregunta'    => 'required|exists:pregunta_beneficio,id_pgb',
            'respuestas.*.valor'          => 'nullable',
            'respuestas.*.valor_json'     => 'nullable|array',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $personaId = $this->input('per_id');

            if ($personaId && ! $validator->errors()->has('per_id') && ! SolicitudBecasService::esEstudiante($personaId)) {
                $validator->errors()->add('per_id', SolicitudBecasService::MENSAJE_SOLO_ESTUDIANTES);
            }

            $jornadaId = $this->input('jornada_id');
            if (!$jornadaId) return;

            $jornada = JornadaBeca::find($jornadaId);
            if (!$jornada) return;

            $respuestas = collect($this->input('respuestas', []))
                ->keyBy('id_pregunta');

            $preguntas = BecaPregunta::where('bes_id', $jornada->bes_id)
                ->where('status_id', 1)
                ->get();

            foreach ($preguntas as $p) {
                $r = $respuestas[$p->id_pgb] ?? null;
                $valor = $r['valor'] ?? null;
                $valorJson = $r['valor_json'] ?? null;

                $tieneValor = $valor !== null && $valor !== ''
                    || (!empty($valorJson));

                if ($p->obligatoria_pgb && !$tieneValor) {
                    $validator->errors()->add(
                        "respuestas.{$p->id_pgb}",
                        "La pregunta «{$p->etiqueta_pgb}» es obligatoria."
                    );
                    continue;
                }

                if (!$tieneValor) continue;

                if (in_array($p->tipo_campo_pgb, ['number', 'decimal']) && $valor !== null && $valor !== '') {
                    if (!is_numeric($valor)) {
                        $validator->errors()->add("respuestas.{$p->id_pgb}", "«{$p->etiqueta_pgb}» debe ser numérico.");
                        continue;
                    }
                    if ($p->valor_min_pgb !== null && $valor < $p->valor_min_pgb) {
                        $validator->errors()->add("respuestas.{$p->id_pgb}", "«{$p->etiqueta_pgb}» debe ser mayor o igual a {$p->valor_min_pgb}.");
                    }
                    if ($p->valor_max_pgb !== null && $valor > $p->valor_max_pgb) {
                        $validator->errors()->add("respuestas.{$p->id_pgb}", "«{$p->etiqueta_pgb}» debe ser menor o igual a {$p->valor_max_pgb}.");
                    }
                }

                if (in_array($p->tipo_campo_pgb, ['text', 'textarea', 'email']) && $valor !== null) {
                    if ($p->min_length_pgb && mb_strlen($valor) < $p->min_length_pgb) {
                        $validator->errors()->add("respuestas.{$p->id_pgb}", "«{$p->etiqueta_pgb}» debe tener al menos {$p->min_length_pgb} caracteres.");
                    }
                    if ($p->max_length_pgb && mb_strlen($valor) > $p->max_length_pgb) {
                        $validator->errors()->add("respuestas.{$p->id_pgb}", "«{$p->etiqueta_pgb}» no debe superar {$p->max_length_pgb} caracteres.");
                    }
                    if ($p->regex_pgb && !preg_match($p->regex_pgb, $valor)) {
                        $validator->errors()->add("respuestas.{$p->id_pgb}", "«{$p->etiqueta_pgb}» no tiene el formato esperado.");
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'per_id.required' => 'Debe seleccionar un estudiante.',
            'jornada_id.required' => 'Debe asociar una jornada activa.',
            'respuestas.required' => 'Debe responder el formulario.',
            'archivo_notas.required' => 'El archivo de notas es obligatorio.',
            'archivo_notas.file' => 'El documento cargado debe ser un archivo válido.',
            'archivo_notas.mimes' => 'El documento de notas debe ser un archivo PDF.',
            'archivo_notas.max' => 'El documento de notas no debe pesar más de 2MB.',
        ];
    }
}