<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoAdopcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|in:en_proceso,aprobada,completada,rechazada,cancelada',
            'razon_rechazo' => 'required_if:estado,rechazada,cancelada|nullable|string'
        ];
    }
}
