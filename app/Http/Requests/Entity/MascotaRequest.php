<?php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

class MascotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string',
            'edad_aprox' => 'required|numeric',
            'genero' => 'required|string',
            'descripcion' => 'required|string',
            'estado' => 'required|string',
            'lugar_rescate' => 'nullable|string',
            'condiciones_especiales' => 'nullable|string',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'foto_principal' => 'nullable|image|max:2048',
            'razas' => 'array',
            'vacunas' => 'array',
            'galeria_fotos' => 'array',
            'galeria_fotos.*' => 'image|max:2048'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'necesita_hogar_temporal' => $this->boolean('necesita_hogar_temporal'),
            'apto_con_ninos' => $this->boolean('apto_con_ninos'),
            'apto_con_otros_animales' => $this->boolean('apto_con_otros_animales'),
        ]);
    }
}
