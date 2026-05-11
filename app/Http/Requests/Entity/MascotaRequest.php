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
            // Campos existentes
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string',
            'edad_aprox' => 'required|numeric|min:0|max:99.99',
            'genero' => 'required|string|in:Macho,Hembra,Desconocido',
            'descripcion' => 'required|string',
            'estado' => 'required|string|in:En adopcion,Adoptado,Rescatada,En acogida',
            'lugar_rescate' => 'nullable|string',
            'condiciones_especiales' => 'nullable|string',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'foto_principal' => 'nullable|image|max:2048',
            'razas' => 'array|exists:razas,id',
            'vacunas' => 'array|exists:tipos_vacunas,id',
            'galeria_fotos' => 'array',
            'galeria_fotos.*' => 'image|max:2048',
            'fotos_eliminar' => 'nullable|array',
            'fotos_eliminar.*' => 'nullable|string',

            // ===== NUEVOS CAMPOS =====
            'peso_aprox' => 'nullable|numeric|min:0|max:99.99',
            'tamano' => 'nullable|string|in:pequeño,mediano,grande,muy_grande',
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
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'necesita_hogar_temporal' => $this->boolean('necesita_hogar_temporal'),
            'apto_con_ninos' => $this->boolean('apto_con_ninos'),
            'apto_con_otros_animales' => $this->boolean('apto_con_otros_animales'),
            // Nuevos booleanos
            'esterilizado' => $this->boolean('esterilizado'),
            'desparasitado' => $this->boolean('desparasitado'),
            'vacunado' => $this->boolean('vacunado'),
            'destacada' => $this->boolean('destacada'),
        ]);
    }

    public function messages(): array
    {
        return [
            'galeria_fotos.*.image' => 'Cada archivo debe ser una imagen válida',
            'galeria_fotos.*.max' => 'Cada foto no puede superar los 2MB',
            'vacunas.*.exists' => 'Una o más vacunas no existen en el sistema',
            'razas.*.exists' => 'Una o más razas no existen en el sistema',
        ];
    }
}
