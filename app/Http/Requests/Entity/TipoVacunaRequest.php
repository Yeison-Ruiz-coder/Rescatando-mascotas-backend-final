<?php
// app/Http/Requests/Entity/TipoVacunaRequest.php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

class TipoVacunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->tipo, ['fundacion', 'veterinaria']);
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('tipo_vacuna');

        return [
            'nombre_vacuna' => 'required|string|max:255|unique:tipos_vacunas,nombre_vacuna,' . $id,
            'frecuencia_dias' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_vacuna.required' => 'El nombre de la vacuna es obligatorio',
            'nombre_vacuna.unique' => 'Esta vacuna ya está registrada',
            'frecuencia_dias.integer' => 'La frecuencia debe ser un número de días',
        ];
    }
}
