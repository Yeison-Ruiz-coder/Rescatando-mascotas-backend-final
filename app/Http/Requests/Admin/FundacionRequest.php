<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FundacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('fundacion') ?? 'null';

        return [
            'Nombre_1' => 'required|string|max:255',
            'Direccion' => 'required|string|unique:fundaciones,Direccion,' . $id,
            'Telefono' => 'required|string|unique:fundaciones,Telefono,' . $id,
            'Email' => 'required|email|unique:fundaciones,Email,' . $id,
            'registro_sanitario' => 'nullable|string|max:255',
            'capacidad_maxima' => 'nullable|integer|min:1',
            'necesidades_actuales' => 'nullable|array',
            'horario_atencion' => 'nullable|string',
            'recibe_voluntarios' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'sitio_web' => 'nullable|url|max:255',
            'redes_sociales' => 'nullable|array',
            'descripcion' => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'recibe_voluntarios' => $this->boolean('recibe_voluntarios'),
        ]);
    }
}
