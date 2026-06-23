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
            // ===== OBLIGATORIOS =====
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string|in:Perro,Gato,Conejo,Otro',
            'fecha_ingreso' => 'required|date',

            // ===== OPCIONALES =====
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'nullable|in:Macho,Hembra,Desconocido',
            'descripcion' => 'nullable|string',
            'foto_principal' => 'nullable|image|max:2048',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'condiciones_especiales' => 'nullable|string',

            // ===== CARACTERÍSTICAS FÍSICAS =====
            'peso_aprox' => 'nullable|numeric|min:0|max:99.99',
            'tamano' => 'nullable|string|in:pequeño,mediano,grande,muy_grande',
            'color' => 'nullable|string|max:100',

            // ===== SALUD =====
            'salud_general' => 'nullable|string',
            'esterilizado' => 'nullable|boolean',
            'desparasitado' => 'nullable|boolean',
            'vacunado' => 'nullable|boolean',

            // ===== VIDEO =====
            'video_url' => 'nullable|url',

            // ===== FOTOS =====
            'galeria_fotos' => 'nullable|array',
            'galeria_fotos.*' => 'image|max:2048',

            // ===== ARRAYS - NUEVOS =====
            'requisitos_adopcion' => 'nullable|array',
            'requisitos_adopcion.*' => 'string|max:255',

            // ✅ RAZAS
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',

            // ✅ VACUNAS
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',

            // ✅ ENFERMEDADES CRÓNICAS
            'enfermedades_cronicas' => 'nullable|array',
            'enfermedades_cronicas.*' => 'string|max:255',

            // ✅ MEDICAMENTOS
            'medicamentos' => 'nullable|array',
            'medicamentos.*' => 'string|max:255',

            // ✅ HOGAR RECOMENDADO
            'hogar_recomendado' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'necesita_hogar_temporal' => $this->boolean('necesita_hogar_temporal'),
            'apto_con_ninos' => $this->boolean('apto_con_ninos'),
            'apto_con_otros_animales' => $this->boolean('apto_con_otros_animales'),
            'esterilizado' => $this->boolean('esterilizado'),
            'desparasitado' => $this->boolean('desparasitado'),
            'vacunado' => $this->boolean('vacunado'),
        ]);
    }

    public function messages(): array
    {
        return [
            'galeria_fotos.*.image' => 'Cada archivo debe ser una imagen válida',
            'galeria_fotos.*.max' => 'Cada foto no puede superar los 2MB',
            'razas.*.exists' => 'Una de las razas seleccionadas no existe',
            'vacunas.*.exists' => 'Una de las vacunas seleccionadas no existe',
        ];
    }
}
