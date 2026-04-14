<?php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarMascotaRescateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string|in:Perro,Gato,Conejo,Otro',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'nullable|in:Macho,Hembra,Desconocido',
            'descripcion' => 'nullable|string',
            'foto_principal' => 'nullable|image|max:2048',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'condiciones_especiales' => 'nullable|string',
            'fecha_ingreso' => 'required|date',
        ];
    }
}
