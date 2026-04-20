<?php
// app/Http/Requests/Entity/RazaRequest.php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

class RazaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La entidad (fundación/veterinaria) debe estar autenticada
        return auth()->check() && in_array(auth()->user()->rol, ['fundacion', 'veterinaria']);
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('raza');

        return [
            'nombre_raza' => 'required|string|max:255|unique:razas,nombre_raza,' . $id,
            'especie' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_raza.required' => 'El nombre de la raza es obligatorio',
            'nombre_raza.unique' => 'Esta raza ya está registrada',
        ];
    }
}
