<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RescateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_rescate' => 'required|date',
            'lugar_rescate' => 'required|string|max:255',
            'descripcion_rescate' => 'required|string',
            'tipo_emergencia' => 'nullable|in:herido,abandonado,urgente,otro',
            'prioridad' => 'nullable|in:alta,media,baja',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'mascota_id' => 'nullable|exists:mascotas,id',
            'reporte_id' => 'nullable|exists:reportes,id',
            'usuario_reporto_id' => 'nullable|exists:users,id',
            'gestionado_por' => 'nullable|exists:users,id',
        ];
    }
}
