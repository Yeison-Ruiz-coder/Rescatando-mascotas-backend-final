<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DonacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fundacion_id' => 'required|exists:fundaciones,id',
            'valor_donacion' => 'required|numeric|min:1000',
            'publica' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'fundacion_id.required' => 'Debes seleccionar una fundación',
            'fundacion_id.exists' => 'La fundación seleccionada no existe',
            'valor_donacion.required' => 'El valor de la donación es requerido',
            'valor_donacion.numeric' => 'El valor debe ser un número',
            'valor_donacion.min' => 'El valor mínimo de donación es $1,000 COP',
            'publica.boolean' => 'El campo pública debe ser verdadero o falso',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'publica' => $this->boolean('publica'),
        ]);
    }
}
