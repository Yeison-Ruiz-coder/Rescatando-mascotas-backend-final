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
            'foto_principal_public_id' => 'nullable|string|max:255',
            'galeria_fotos.*' => 'nullable|image|max:2048',
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
            // ===== NUEVOS CAMPOS =====
            'peso_aprox' => 'nullable|numeric|min:0|max:99.99',
            'tamano' => 'nullable|in:pequeño,mediano,grande,muy_grande',
            'color' => 'nullable|string|max:100',
            'salud_general' => 'nullable|string',
            'esterilizado' => 'nullable|boolean',
            'desparasitado' => 'nullable|boolean',
            'vacunado' => 'nullable|boolean',
            'enfermedades_cronicas' => 'nullable|array',
            'enfermedades_cronicas.*' => 'string|max:255',
            'medicamentos' => 'nullable|array',
            'medicamentos.*' => 'string|max:255',
            'requisitos_adopcion' => 'nullable|array',
            'hogar_recomendado' => 'nullable|string|max:255',
            'video_url' => 'nullable|url',
            'destacada' => 'nullable|boolean',
            'veterinaria_id' => 'nullable|exists:veterinarias,id',
        ];
    }

    public function attributes()
    {
        return [
            'nombre_mascota' => 'nombre de la mascota',
            'fundacion_id' => 'fundación',
            'veterinaria_id' => 'veterinaria',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'esterilizado' => $this->boolean('esterilizado'),
            'desparasitado' => $this->boolean('desparasitado'),
            'vacunado' => $this->boolean('vacunado'),
            'destacada' => $this->boolean('destacada'),
        ]);
    }
}
