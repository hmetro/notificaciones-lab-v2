<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateReglaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nombre' => 'string',
            'ene' => 'boolean',
            'tipo_validacion' => 'boolean',
            'x_servicio' => 'string',
            'x_origen' => 'string',
            'x_motivo' => 'string',
            'x_especialidad' => 'string',
            'x_medico' => 'string',
            'x_idprueba' => 'string',
            'add_json' => 'json',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }

    public function messages()
    {
        return [
            'nombre.string' => 'El nombre debe ser del tipo caracteres.',
            'ene.boolean' => 'eNe debe ser bolleano.',
            'tipo_validacion.boolean' => 'El tipo de validación debe ser booleano.',
            'x_servicio.string' => 'La regla por servicio debe ser del tipo caracteres.',
            'x_origen.string' => 'La regla por origen debe ser del tipo caracteres.',
            'x_motivo.string' => 'La regla por motivo debe ser del tipo caracteres.',
            'x_especialidad.string' => 'La regla por especialidad debe ser del tipo caracteres.',
            'x_medico.string' => 'La regla por medico debe ser del tipo caracteres.',
            'x_idprueba.string' => 'La regla por el ID de la Prueba debe ser del tipo caracteres.',
            'add_json.json' => 'JSON añadido debe ser de tipo JSON.',
        ];
    }
}
