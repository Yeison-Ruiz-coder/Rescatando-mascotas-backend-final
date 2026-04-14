<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SeguimientoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_seguimiento' => 'required|in:virtual,domiciliario,telefonico',
            'fecha_seguimiento' => 'required|date',
            'proximo_seguimiento' => 'nullable|date|after:fecha_seguimiento',
            'observaciones' => 'required|string',
            'recomendaciones' => 'nullable|string',
            'estado_mascota' => 'required|in:excelente,bueno,regular,preocupante',
            'resultado' => 'required|in:satisfactorio,observaciones,incumplimiento,reingreso',
            'foto_url' => 'nullable|image|max:2048',
            'fotos_adicionales' => 'nullable|array',
            'fotos_adicionales.*' => 'image|max:2048',
            'video_url' => 'nullable|url',
            'documento_url' => 'nullable|url',
            'condiciones_hogar' => 'nullable|in:optimas,aceptables,mejorables,precarias',
            'observaciones_hogar' => 'nullable|string',
            'convive_con_otros_animales' => 'nullable|boolean',
            'comportamiento_observado' => 'nullable|string',
            'requiere_nuevo_seguimiento' => 'boolean',
            'firma_adoptante' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requiere_nuevo_seguimiento' => $this->boolean('requiere_nuevo_seguimiento'),
            'firma_adoptante' => $this->boolean('firma_adoptante'),
            'convive_con_otros_animales' => $this->boolean('convive_con_otros_animales'),
        ]);
    }
}
