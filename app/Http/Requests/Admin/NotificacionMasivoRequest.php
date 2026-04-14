<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NotificacionMasivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenido' => 'required|string',
            'tipo_destinatarios' => 'required|in:todos,usuarios,administradores,fundaciones,veterinarias',
            'fecha_envio' => 'nullable|date',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ];
    }
}
