<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|in:pendiente,en_revision,aprobada,rechazada,completada',
            'razon_rechazo' => 'required_if:estado,rechazada|nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El estado es requerido',
            'estado.in' => 'El estado no es válido. Los estados permitidos son: pendiente, en_revision, aprobada, rechazada, completada',
            'razon_rechazo.required_if' => 'Debes proporcionar una razón para el rechazo',
        ];
    }
}
