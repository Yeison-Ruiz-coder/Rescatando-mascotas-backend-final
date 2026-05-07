<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RazaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('raza') ?? 'null';

        return [
            'nombre_raza' => 'required|string|max:255|unique:razas,nombre_raza,' . $id,
            'especie' => 'nullable|string|max:100', // SOLO ESTO
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_raza' => 'nombre de la raza',
            'esperanza_vida' => 'esperanza de vida',
        ];
    }
}
