<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SeguimientoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_seguimiento' => 'sometimes|in:virtual,domiciliario,telefonico',
            'fecha_seguimiento' => 'sometimes|date',
            'proximo_seguimiento' => 'nullable|date',
            'observaciones' => 'sometimes|string',
            'recomendaciones' => 'nullable|string',
            'estado_mascota' => 'sometimes|in:excelente,bueno,regular,preocupante',
            'resultado' => 'sometimes|in:satisfactorio,observaciones,incumplimiento,reingreso',
            'condiciones_hogar' => 'nullable|in:optimas,aceptables,mejorables,precarias',
            'observaciones_hogar' => 'nullable|string',
            'convive_con_otros_animales' => 'nullable|boolean',
            'comportamiento_observado' => 'nullable|string',
            'requiere_nuevo_seguimiento' => 'boolean',
            'firma_adoptante' => 'boolean',
        ];
    }
}
