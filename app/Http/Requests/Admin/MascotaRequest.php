<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MascotaRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $id = $this->route('mascota') ?? 'null';

        return [
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string',
            'edad_aprox' => 'nullable|numeric|min:0|max:99.99',
            'genero' => 'required|in:Macho,Hembra,Desconocido',
            'estado' => 'required|in:En adopcion,Adoptado,Rescatada,En acogida',
            'fundacion_id' => 'required|exists:fundaciones,id',
            'foto_principal' => 'nullable|image|max:2048',
            'galeria_fotos.*' => 'nullable|image|max:2048',
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
        ];
    }

    public function attributes()
    {
        return [
            'nombre_mascota' => 'nombre de la mascota',
            'fundacion_id' => 'fundación',
        ];
    }
}
