<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_solicitud' => 'required|in:adopcion,rescate,apadrinamiento,donacion,otro',
            'contenido' => 'required|string|min:10',
            'user_id' => 'nullable|exists:users,id',
            'nombre_solicitante' => 'required_without:user_id|string|max:255',
            'email_solicitante' => 'required_without:user_id|email|max:255',
            'telefono_solicitante' => 'nullable|string|max:20',
            'solicitable_id' => 'required|integer',
            'solicitable_type' => 'required|string|in:App\Models\Mascota,App\Models\Fundacion',
            'datos_adopcion' => 'nullable|array',
        ];
    }
}
