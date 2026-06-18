<?php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

class MascotaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // ===== OBLIGATORIOS =====
            'nombre_mascota' => 'required|string|max:255',
            'estado' => 'required|string|in:En adopcion,Adoptado,Rescatada,En acogida',

            // ===== OPCIONALES (nullable) =====
            'especie' => 'nullable|string|max:50',
            'edad_aprox' => 'nullable|numeric|min:0',
            'genero' => 'nullable|string|in:Macho,Hembra,Desconocido',
            'peso_aprox' => 'nullable|numeric|min:0',
            'tamano' => 'nullable|string|in:pequeño,mediano,grande,muy_grande',
            'color' => 'nullable|string|max:50',
            'lugar_rescate' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'condiciones_especiales' => 'nullable|string',
            'salud_general' => 'nullable|string',
            'hogar_recomendado' => 'nullable|string|max:255',
            'video_url' => 'nullable|url|max:255',

            // ===== BOOLEANOS (nullable) =====
            'esterilizado' => 'nullable|boolean',
            'desparasitado' => 'nullable|boolean',
            'vacunado' => 'nullable|boolean',
            'necesita_hogar_temporal' => 'nullable|boolean',
            'apto_con_ninos' => 'nullable|boolean',
            'apto_con_otros_animales' => 'nullable|boolean',
            'destacada' => 'nullable|boolean',

            // ===== ARRAYS (nullable) =====
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
            'enfermedades_cronicas' => 'nullable|array',
            'enfermedades_cronicas.*' => 'string|max:255',
            'medicamentos' => 'nullable|array',
            'medicamentos.*' => 'string|max:255',
            'requisitos_adopcion' => 'nullable|array',
            'requisitos_adopcion.*' => 'string|max:255',

            // ===== FECHAS (nullable) =====
            'fecha_ingreso' => 'nullable|date',
            'fecha_salida' => 'nullable|date|after_or_equal:fecha_ingreso',

            // ===== FOTOS (validación de archivos) =====
            'foto_principal' => 'nullable|file|image|max:5120', // 5MB
            'galeria_fotos' => 'nullable|array|max:10',
            'galeria_fotos.*' => 'file|image|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'nombre_mascota.required' => 'El nombre de la mascota es obligatorio',
            'estado.required' => 'El estado de la mascota es obligatorio',
            'estado.in' => 'El estado debe ser: En adopcion, Adoptado, Rescatada o En acogida',
            'foto_principal.image' => 'La foto principal debe ser una imagen',
            'foto_principal.max' => 'La foto principal no debe superar los 5MB',
            'galeria_fotos.*.image' => 'Cada foto de galería debe ser una imagen',
            'galeria_fotos.*.max' => 'Cada foto de galería no debe superar los 5MB',
            'galeria_fotos.max' => 'Máximo 10 fotos en la galería',
        ];
    }
}
