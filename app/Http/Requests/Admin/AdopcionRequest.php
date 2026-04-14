<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdopcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_adopcion' => 'nullable|date',
            'estado' => 'required|in:en_proceso,aprobada,completada,rechazada,cancelada',
            'razon_rechazo' => 'nullable|string|max:500',
            'solicitud_id' => 'nullable|exists:solicitudes,id',
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'required|exists:mascotas,id',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
        ];
    }
}
