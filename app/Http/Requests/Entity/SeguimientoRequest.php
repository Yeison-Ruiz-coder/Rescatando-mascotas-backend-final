<?php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

class SeguimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ===== OBLIGATORIOS =====
            'tipo_seguimiento' => 'required|in:virtual,domiciliario,telefonico',
            'fecha_seguimiento' => 'required|date',
            'observaciones' => 'required|string|min:10',
            'estado_mascota' => 'required|in:excelente,bueno,regular,preocupante',

            // ===== OPCIONALES =====
            'proximo_seguimiento' => 'nullable|date|after_or_equal:fecha_seguimiento',
            'recomendaciones' => 'nullable|string',
            'resultado' => 'nullable|in:satisfactorio,observaciones,incumplimiento,reingreso',
            'condiciones_hogar' => 'nullable|in:optimas,aceptables,mejorables,precarias',
            'observaciones_hogar' => 'nullable|string',
            'convive_con_otros_animales' => 'nullable|boolean',
            'comportamiento_observado' => 'nullable|string',
            'requiere_nuevo_seguimiento' => 'nullable|boolean',
            'firma_adoptante' => 'nullable|boolean',

            // ===== ARCHIVOS =====
            'foto_url' => 'nullable|image|max:5120',
            'fotos_adicionales' => 'nullable|array',
            'fotos_adicionales.*' => 'image|max:5120',
            'video_url' => 'nullable|url',
            'documento_url' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_seguimiento.required' => 'El tipo de seguimiento es obligatorio',
            'tipo_seguimiento.in' => 'El tipo de seguimiento debe ser: virtual, domiciliario o telefonico',
            'fecha_seguimiento.required' => 'La fecha del seguimiento es obligatoria',
            'fecha_seguimiento.date' => 'La fecha del seguimiento no es válida',
            'observaciones.required' => 'Las observaciones son obligatorias',
            'observaciones.min' => 'Las observaciones deben tener al menos 10 caracteres',
            'estado_mascota.required' => 'El estado de la mascota es obligatorio',
            'estado_mascota.in' => 'El estado de la mascota debe ser: excelente, bueno, regular o preocupante',
            'proximo_seguimiento.after_or_equal' => 'El próximo seguimiento debe ser posterior o igual a la fecha actual',
            'foto_url.image' => 'El archivo debe ser una imagen',
            'foto_url.max' => 'La imagen no debe superar los 5MB',
            'fotos_adicionales.*.image' => 'Cada archivo debe ser una imagen',
            'fotos_adicionales.*.max' => 'Cada imagen no debe superar los 5MB',
            'video_url.url' => 'La URL del video no es válida',
            'documento_url.file' => 'El archivo debe ser un documento válido',
            'documento_url.mimes' => 'El documento debe ser PDF, DOC o DOCX',
            'documento_url.max' => 'El documento no debe superar los 10MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'resultado' => $this->resultado ?? 'satisfactorio',
            'requiere_nuevo_seguimiento' => $this->boolean('requiere_nuevo_seguimiento'),
            'firma_adoptante' => $this->boolean('firma_adoptante'),
            'convive_con_otros_animales' => $this->boolean('convive_con_otros_animales'),
        ]);
    }
}
